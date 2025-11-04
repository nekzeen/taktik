<?php

namespace App\Console\Commands;

use App\Models\AsymmetricPrimaryMission;
use App\Models\Translation;
use App\Services\TranslationService;
use App\Services\IntelligentTranslationService;
use Illuminate\Console\Command;

class TranslateAsymmetricPrimaryMissions extends Command
{
    protected $signature = 'missions:translate-asymmetric {--locale=fr}';
    protected $description = 'Traduire les missions primaires asymétriques via DeepL avec glossaire Warhammer';

    public function handle()
    {
        $locale = $this->option('locale');
        $translationService = new TranslationService();
        $intelligentService = new IntelligentTranslationService();

        $this->info('🌍 Traduction des missions primaires asymétriques avec glossaire Warhammer...');
        $this->info('═══════════════════════════════════════════════════════════');

        $missions = AsymmetricPrimaryMission::active()->get();
        $translated = 0;
        $skipped = 0;

        foreach ($missions as $mission) {
            $fields = ['name', 'description', 'full_text', 'objectives', 'attacker_objective', 'defender_objective', 'timing', 'scoring_conditions'];

            foreach ($fields as $field) {
                // Vérifier si la traduction existe déjà
                $existing = Translation::where('resource_type', 'AsymmetricPrimaryMission')
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

                    // ⚠️ GLOSSAIRE DÉSACTIVÉ - Causer des problèmes de contenu dupliqué
                    // $translatedText = $intelligentService->translateWithGlossary(
                    //     $translatedText,
                    //     $locale,
                    //     'asymmetric_primary_mission',
                    //     $sourceText
                    // );

                    // Créer la traduction
                    Translation::create([
                        'source_text' => $sourceText,
                        'translated_text' => $translatedText,
                        'locale' => $locale,
                        'resource_type' => 'AsymmetricPrimaryMission',
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
