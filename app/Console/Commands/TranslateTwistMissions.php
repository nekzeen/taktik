<?php

namespace App\Console\Commands;

use App\Models\TwistMission;
use App\Models\Translation;
use App\Services\TranslationService;
use App\Services\IntelligentTranslationService;
use Illuminate\Console\Command;

class TranslateTwistMissions extends Command
{
    protected $signature = 'missions:translate-twist {--locale=fr}';
    protected $description = 'Traduire les péripéties via DeepL avec glossaire Warhammer';

    public function handle()
    {
        $locale = $this->option('locale');
        $translationService = new TranslationService();
        $intelligentService = new IntelligentTranslationService();

        $this->info('🌍 Traduction des péripéties avec glossaire Warhammer...');
        $this->info('═══════════════════════════════════════════════════════════');

        $missions = TwistMission::active()->get();
        $translated = 0;
        $skipped = 0;

        foreach ($missions as $mission) {
            $fields = ['name', 'description', 'full_text', 'when_drawn', 'effect'];

            foreach ($fields as $field) {
                // Vérifier si la traduction existe déjà
                $existing = Translation::where('resource_type', 'TwistMission')
                    ->where('resource_id', $mission->id)
                    ->where('field', $field)
                    ->where('locale', $locale)
                    ->first();

                if ($existing) {
                    $this->line("  ⏭️  {$mission->name} ({$field}): Déjà traduit");
                    $skipped++;
                    continue;
                }

                try {
                    // Obtenir le texte à traduire
                    $sourceText = $mission->{$field};

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

                    // Appliquer les termes du glossaire Warhammer
                    $translatedText = $intelligentService->translateWithGlossary(
                        $translatedText,
                        $locale,
                        'twist_deck',
                        $sourceText
                    );

                    // Créer la traduction
                    Translation::create([
                        'source_text' => $sourceText,
                        'translated_text' => $translatedText,
                        'locale' => $locale,
                        'resource_type' => 'TwistMission',
                        'resource_id' => $mission->id,
                        'field' => $field,
                        'status' => 'auto', // Marqué comme automatique
                    ]);

                    $this->line("  ✅ {$mission->name} ({$field}): Traduit (glossaire appliqué)");
                    $translated++;
                } catch (\Exception $e) {
                    $this->error("  ❌ {$mission->name} ({$field}): {$e->getMessage()}");
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
