<?php

namespace App\Console\Commands;

use App\Models\Translation;
use App\Services\TranslationService;
use App\Services\IntelligentTranslationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TranslateDeploymentCards extends Command
{
    protected $signature = 'deployment-cards:translate {--locale=fr}';
    protected $description = 'Traduit les cartes de déploiement via DeepL avec glossaire Warhammer';

    public function handle()
    {
        $locale = $this->option('locale');
        $translationService = new TranslationService();
        $intelligentService = new IntelligentTranslationService();

        $this->info('🌍 Traduction des cartes de déploiement avec glossaire Warhammer...');
        $this->info('═══════════════════════════════════════════════════════════');

        // Traduire les cartes Asymmetric Warfare
        $this->translateCards('asymmetric_warfare_deployment_cards', 'AsymmetricWarfareDeploymentCard', $translationService, $intelligentService, $locale);

        // Traduire les cartes Strike Force
        $this->translateCards('strike_force_deployment_cards', 'StrikeForceDeploymentCard', $translationService, $intelligentService, $locale);

        // Traduire les cartes Incursion
        $this->translateCards('incursion_deployment_cards', 'IncursionDeploymentCard', $translationService, $intelligentService, $locale);

        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('✅ Traduction terminée !');

        return 0;
    }

    private function translateCards($tableName, $resourceType, $translationService, $intelligentService, $locale)
    {
        $cards = DB::table($tableName)->get();
        $translated = 0;
        $skipped = 0;

        $this->info("\n📋 Traduction de {$resourceType}:");

        foreach ($cards as $card) {
            // Vérifier si la traduction existe déjà
            $existing = Translation::where('resource_type', $resourceType)
                ->where('resource_id', $card->id)
                ->where('field', 'name')
                ->where('locale', $locale)
                ->first();

            if ($existing) {
                $this->line("  ⏭️  {$card->name}: Déjà traduit");
                $skipped++;
                continue;
            }

            try {
                // Obtenir le texte à traduire
                $sourceText = $card->name;

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
                    'deployment_zone',
                    $sourceText
                );

                // Créer la traduction
                Translation::create([
                    'source_text' => $sourceText,
                    'translated_text' => $translatedText,
                    'locale' => $locale,
                    'resource_type' => $resourceType,
                    'resource_id' => $card->id,
                    'field' => 'name',
                    'status' => 'auto', // Marqué comme automatique
                ]);

                $this->line("  ✅ {$card->name} → {$translatedText}");
                $translated++;
            } catch (\Exception $e) {
                $this->error("  ❌ {$card->name}: {$e->getMessage()}");
            }
        }

        $this->info("  📊 Résultats: {$translated} traduites, {$skipped} ignorées");
    }
}
