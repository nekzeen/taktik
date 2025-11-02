<?php

namespace App\Console\Commands;

use App\Models\IncursionDeploymentCard;
use App\Models\Translation;
use App\Services\TranslationService;
use Illuminate\Console\Command;

class TranslateIncursionCards extends Command
{
    protected $signature = 'missions:translate-incursion {--locale=fr}';
    protected $description = 'Traduire les cartes de déploiement Incursions via DeepL';

    public function handle()
    {
        $locale = $this->option('locale');
        $translationService = new TranslationService();

        $this->info('🌍 Traduction des cartes Incursions...');
        $this->info('═══════════════════════════════════════════════════════════');

        $cards = IncursionDeploymentCard::active()->get();
        $translated = 0;
        $skipped = 0;

        foreach ($cards as $card) {
            $fields = ['name', 'description', 'full_text', 'card_content', 'rules'];

            foreach ($fields as $field) {
                // Vérifier si la traduction existe déjà
                $existing = Translation::where('resource_type', 'IncursionDeploymentCard')
                    ->where('resource_id', $card->id)
                    ->where('field', $field)
                    ->where('locale', $locale)
                    ->first();

                if ($existing) {
                    $this->line("  ⏭️  {$card->name} ({$field}): Déjà traduit");
                    $skipped++;
                    continue;
                }

                try {
                    // Obtenir le texte à traduire
                    $sourceText = $card->{$field};

                    // Ignorer les champs vides
                    if (empty($sourceText)) {
                        continue;
                    }

                    // Traduire via DeepL
                    $translatedText = $translationService->translate(
                        $sourceText,
                        'en',
                        $locale
                    );

                    // Créer la traduction
                    Translation::create([
                        'source_text' => $sourceText,
                        'translated_text' => $translatedText,
                        'locale' => $locale,
                        'resource_type' => 'IncursionDeploymentCard',
                        'resource_id' => $card->id,
                        'field' => $field,
                        'status' => 'auto', // Marqué comme automatique
                    ]);

                    $this->line("  ✅ {$card->name} ({$field}): Traduit");
                    $translated++;
                } catch (\Exception $e) {
                    $this->error("  ❌ {$card->name} ({$field}): {$e->getMessage()}");
                }
            }
        }

        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info("📊 Résultats:");
        $this->info("  ✅ Traduites: {$translated}");
        $this->info("  ⏭️  Ignorées: {$skipped}");
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('✅ Traduction terminée !');

        return 0;
    }
}
