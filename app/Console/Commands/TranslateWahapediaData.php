<?php

namespace App\Console\Commands;

use App\Services\TranslationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TranslateWahapediaData extends Command
{
    protected $signature = 'wahapedia:translate {--force : Forcer la traduction même si déjà traduit}';
    protected $description = 'Traduit les données Wahapedia (Détachements, Capacités, etc.) avec DeepL';

    private $translationService;
    private $report = [
        'detachments' => ['translated' => 0, 'skipped' => 0, 'errors' => 0],
        'detachment_abilities' => ['translated' => 0, 'skipped' => 0, 'errors' => 0],
        'factions' => ['translated' => 0, 'skipped' => 0, 'errors' => 0],
        'datasheets' => ['translated' => 0, 'skipped' => 0, 'errors' => 0],
        'stratagems' => ['translated' => 0, 'skipped' => 0, 'errors' => 0],
    ];

    public function __construct(TranslationService $translationService)
    {
        parent::__construct();
        $this->translationService = $translationService;
    }

    public function handle()
    {
        $this->info('🌍 Traduction des données Wahapedia avec DeepL');
        $this->info('═══════════════════════════════════════════════════════════');

        $force = $this->option('force');

        // Traduire les détachements
        $this->translateDetachments($force);

        // Traduire les capacités de détachement
        $this->translateDetachmentAbilities($force);

        // Traduire les factions
        $this->translateFactions($force);

        // Traduire les datasheets
        $this->translateDatasheets($force);

        // Traduire les stratagèmes
        $this->translateStratagems($force);

        // Afficher le rapport
        $this->displayReport();

        return 0;
    }

    private function translateDetachments(bool $force): void
    {
        $this->info('🎖️  Traduction des détachements...');

        $detachments = DB::table('detachments')->get();

        foreach ($detachments as $detachment) {
            try {
                // Vérifier si déjà traduit
                if (!$force && $this->translationExists('Detachment', $detachment->id, 'name', 'fr')) {
                    $this->report['detachments']['skipped']++;
                    continue;
                }

                // Traduire le nom
                $translatedName = $this->translationService->translate(
                    $detachment->name,
                    'en',
                    'fr',
                    'deepl'
                );

                // Sauvegarder la traduction
                DB::table('translations')->updateOrInsert(
                    [
                        'resource_type' => 'Detachment',
                        'resource_id' => $detachment->id,
                        'field' => 'name',
                        'locale' => 'fr',
                    ],
                    [
                        'source_text' => $detachment->name,
                        'translated_text' => $translatedName,
                        'status' => 'auto',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $this->report['detachments']['translated']++;
                $this->line("  ✅ {$detachment->name} → {$translatedName}");
                
                // Délai pour respecter les limites de l'API DeepL
                usleep(500000); // 0.5 secondes

            } catch (\Exception $e) {
                $this->report['detachments']['errors']++;
                $this->error("  ❌ Erreur : {$e->getMessage()}");
            }
        }
    }

    private function translateDetachmentAbilities(bool $force): void
    {
        $this->info('✨ Traduction des capacités de détachement...');

        $abilities = DB::table('detachment_abilities')->get();

        foreach ($abilities as $ability) {
            try {
                // Vérifier si déjà traduit
                if (!$force && $this->translationExists('DetachmentAbility', $ability->id, 'name', 'fr')) {
                    $this->report['detachment_abilities']['skipped']++;
                    continue;
                }

                // Traduire le nom
                $translatedName = $this->translationService->translate(
                    $ability->name,
                    'en',
                    'fr',
                    'deepl'
                );

                // Sauvegarder la traduction
                DB::table('translations')->updateOrInsert(
                    [
                        'resource_type' => 'DetachmentAbility',
                        'resource_id' => $ability->id,
                        'field' => 'name',
                        'locale' => 'fr',
                    ],
                    [
                        'source_text' => $ability->name,
                        'translated_text' => $translatedName,
                        'status' => 'auto',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $this->report['detachment_abilities']['translated']++;
                $this->line("  ✅ {$ability->name} → {$translatedName}");
                
                // Délai pour respecter les limites de l'API DeepL
                usleep(500000); // 0.5 secondes

            } catch (\Exception $e) {
                $this->report['detachment_abilities']['errors']++;
                $this->error("  ❌ Erreur : {$e->getMessage()}");
            }
        }
    }

    private function translateFactions(bool $force): void
    {
        $this->info('🚩 Traduction des factions...');

        $factions = DB::table('factions')->get();

        foreach ($factions as $faction) {
            try {
                // Vérifier si déjà traduit
                if (!$force && $this->translationExists('Faction', $faction->id, 'name', 'fr')) {
                    $this->report['factions']['skipped']++;
                    continue;
                }

                // Traduire le nom
                $translatedName = $this->translationService->translate(
                    $faction->name,
                    'en',
                    'fr',
                    'deepl'
                );

                // Sauvegarder la traduction
                DB::table('translations')->updateOrInsert(
                    [
                        'resource_type' => 'Faction',
                        'resource_id' => $faction->id,
                        'field' => 'name',
                        'locale' => 'fr',
                    ],
                    [
                        'source_text' => $faction->name,
                        'translated_text' => $translatedName,
                        'status' => 'auto',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $this->report['factions']['translated']++;
                $this->line("  ✅ {$faction->name} → {$translatedName}");
                
                // Délai pour respecter les limites de l'API DeepL
                usleep(500000); // 0.5 secondes

            } catch (\Exception $e) {
                $this->report['factions']['errors']++;
                $this->error("  ❌ Erreur : {$e->getMessage()}");
            }
        }
    }

    private function translateDatasheets(bool $force): void
    {
        $this->info('📄 Traduction des datasheets...');

        $datasheets = DB::table('datasheets')->limit(100)->get();

        foreach ($datasheets as $datasheet) {
            try {
                // Vérifier si déjà traduit
                if (!$force && $this->translationExists('Datasheet', $datasheet->id, 'name', 'fr')) {
                    $this->report['datasheets']['skipped']++;
                    continue;
                }

                // Traduire le nom
                $translatedName = $this->translationService->translate(
                    $datasheet->name,
                    'en',
                    'fr',
                    'deepl'
                );

                // Sauvegarder la traduction
                DB::table('translations')->updateOrInsert(
                    [
                        'resource_type' => 'Datasheet',
                        'resource_id' => $datasheet->id,
                        'field' => 'name',
                        'locale' => 'fr',
                    ],
                    [
                        'source_text' => $datasheet->name,
                        'translated_text' => $translatedName,
                        'status' => 'auto',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $this->report['datasheets']['translated']++;
                $this->line("  ✅ {$datasheet->name} → {$translatedName}");
                
                // Délai pour respecter les limites de l'API DeepL
                usleep(500000); // 0.5 secondes

            } catch (\Exception $e) {
                $this->report['datasheets']['errors']++;
                $this->error("  ❌ Erreur : {$e->getMessage()}");
            }
        }
    }

    private function translateStratagems(bool $force): void
    {
        $this->info('💡 Traduction des stratagèmes...');

        $stratagems = DB::table('stratagems')->limit(100)->get();

        foreach ($stratagems as $stratagem) {
            try {
                // Vérifier si déjà traduit
                if (!$force && $this->translationExists('Stratagem', $stratagem->id, 'name', 'fr')) {
                    $this->report['stratagems']['skipped']++;
                    continue;
                }

                // Traduire le nom
                $translatedName = $this->translationService->translate(
                    $stratagem->name,
                    'en',
                    'fr',
                    'deepl'
                );

                // Sauvegarder la traduction
                DB::table('translations')->updateOrInsert(
                    [
                        'resource_type' => 'Stratagem',
                        'resource_id' => $stratagem->id,
                        'field' => 'name',
                        'locale' => 'fr',
                    ],
                    [
                        'source_text' => $stratagem->name,
                        'translated_text' => $translatedName,
                        'status' => 'auto',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $this->report['stratagems']['translated']++;
                $this->line("  ✅ {$stratagem->name} → {$translatedName}");
                
                // Délai pour respecter les limites de l'API DeepL
                usleep(500000); // 0.5 secondes

            } catch (\Exception $e) {
                $this->report['stratagems']['errors']++;
                $this->error("  ❌ Erreur : {$e->getMessage()}");
            }
        }
    }

    private function translationExists(string $resourceType, int $resourceId, string $field, string $locale): bool
    {
        return DB::table('translations')
            ->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->where('field', $field)
            ->where('locale', $locale)
            ->exists();
    }

    private function displayReport(): void
    {
        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('📊 RAPPORT DE TRADUCTION');
        $this->info('═══════════════════════════════════════════════════════════');

        $totalTranslated = 0;
        $totalSkipped = 0;
        $totalErrors = 0;

        foreach ($this->report as $resource => $stats) {
            $this->info("📋 {$resource}:");
            $this->line("  ✅ Traduits : {$stats['translated']}");
            $this->line("  ⏭️  Ignorés : {$stats['skipped']}");
            $this->line("  ❌ Erreurs : {$stats['errors']}");

            $totalTranslated += $stats['translated'];
            $totalSkipped += $stats['skipped'];
            $totalErrors += $stats['errors'];
        }

        $this->info('───────────────────────────────────────────────────────────');
        $this->info("📈 TOTAL : $totalTranslated traduits, $totalErrors erreurs, $totalSkipped ignorés");
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('✅ Traduction terminée !');
    }
}
