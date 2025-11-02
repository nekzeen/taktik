<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Services\TranslationService;
use Exception;
use League\Csv\Reader;

class ImportWahapediaData extends Command
{
    protected $signature = 'wahapedia:import {--force : Forcer l\'import sans confirmation} {--translator=deepl : Traducteur à utiliser (deepl ou google)}';

    protected $description = 'Importe les données Wahapedia (CSV) et les traduit automatiquement';

    private $translationService;
    private $translator;
    private $report = [
        'factions' => ['success' => 0, 'errors' => 0, 'skipped' => 0],
        'datasheets' => ['success' => 0, 'errors' => 0, 'skipped' => 0],
        'abilities' => ['success' => 0, 'errors' => 0, 'skipped' => 0],
        'stratagems' => ['success' => 0, 'errors' => 0, 'skipped' => 0],
        'detachments' => ['success' => 0, 'errors' => 0, 'skipped' => 0],
        'detachment_abilities' => ['success' => 0, 'errors' => 0, 'skipped' => 0],
    ];
    private $errors = [];

    public function __construct(TranslationService $translationService)
    {
        parent::__construct();
        $this->translationService = $translationService;
    }

    public function handle()
    {
        $this->translator = $this->option('translator');

        $this->info('🚀 Démarrage de l\'import Wahapedia...');
        $this->info("📝 Traducteur utilisé : {$this->translator}");

        // Vérifier les clés API
        if (!$this->verifyTranslatorConfig()) {
            $this->error('❌ Configuration du traducteur manquante');
            return 1;
        }

        if (!$this->option('force')) {
            $this->warn('⚠️  Cet import va télécharger et importer les données Wahapedia');
            if (!$this->confirm('Voulez-vous continuer ?')) {
                return 0;
            }
        }

        try {
            // Télécharger les fichiers CSV
            $this->info('📥 Téléchargement des fichiers CSV...');
            $csvFiles = $this->downloadCsvFiles();

            if (empty($csvFiles)) {
                $this->error('❌ Aucun fichier CSV téléchargé');
                return 1;
            }

            // Importer les données
            $this->info('📊 Importation des données...');
            $this->importFactions($csvFiles['factions'] ?? null);
            $this->importDatasheets($csvFiles['datasheets'] ?? null);
            $this->importAbilities($csvFiles['abilities'] ?? null);
            $this->importStratagems($csvFiles['stratagems'] ?? null);
            $this->importDetachments($csvFiles['detachments'] ?? null);
            $this->importDetachmentAbilities($csvFiles['detachment_abilities'] ?? null);

            // Afficher le rapport
            $this->displayReport();

            return 0;
        } catch (Exception $e) {
            $this->error("❌ Erreur lors de l'import : {$e->getMessage()}");
            return 1;
        }
    }

    private function verifyTranslatorConfig(): bool
    {
        if ($this->translator === 'deepl') {
            $key = config('translation.services.deepl.key');
            if (!$key) {
                $this->error('Clé API DeepL manquante dans .env (DEEPL_API_KEY)');
                return false;
            }
        } elseif ($this->translator === 'google') {
            $key = config('translation.services.google.key');
            if (!$key) {
                $this->error('Clé API Google Translate manquante dans .env (GOOGLE_TRANSLATE_API_KEY)');
                return false;
            }
        }
        return true;
    }

    private function downloadCsvFiles(): array
    {
        $csvFiles = [];
        $baseUrl = 'http://wahapedia.ru/wh40k10ed/';
        $files = [
            'factions' => 'Factions.csv',
            'datasheets' => 'Datasheets.csv',
            'abilities' => 'Datasheets_abilities.csv',
            'stratagems' => 'Stratagems.csv',
            'detachments' => 'Detachments.csv',
            'detachment_abilities' => 'Detachment_abilities.csv',
        ];

        foreach ($files as $key => $filename) {
            try {
                $url = $baseUrl . $filename;
                $this->info("  ⬇️  Téléchargement de $filename...");

                $response = Http::timeout(30)->get($url);

                if ($response->successful()) {
                    $csvFiles[$key] = $response->body();
                    $this->line("  ✅ $filename téléchargé");
                } else {
                    $this->warn("  ⚠️  Impossible de télécharger $filename (HTTP {$response->status()})");
                }
            } catch (Exception $e) {
                $this->warn("  ⚠️  Erreur lors du téléchargement de $filename : {$e->getMessage()}");
            }
        }

        return $csvFiles;
    }

    private function importFactions(?string $csvContent): void
    {
        if (!$csvContent) {
            $this->warn('⚠️  Fichier factions.csv non disponible');
            return;
        }

        $this->info('📌 Importation des factions...');

        try {
            $reader = Reader::createFromString($csvContent);
            $reader->setDelimiter('|');
            $reader->setHeaderOffset(0);

            foreach ($reader->getRecords() as $row) {
                try {
                    $name = trim($row['name'] ?? '');
                    $wahapediaId = trim($row['id'] ?? '');

                    if (!$name) {
                        $this->report['factions']['skipped']++;
                        continue;
                    }

                    // Vérifier les doublons
                    if ($this->factionExists($wahapediaId, $name)) {
                        $this->report['factions']['skipped']++;
                        continue;
                    }

                    // Traduire
                    $nameFr = $this->translationService->translate($name, 'en', 'fr', $this->translator);

                    // Insérer
                    DB::table('factions')->insert([
                        'name' => $name,
                        'name_fr' => $nameFr,
                        'bsdata_id' => $wahapediaId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->report['factions']['success']++;
                    $this->line("  ✅ Faction créée : $name → $nameFr");

                } catch (Exception $e) {
                    $this->report['factions']['errors']++;
                    $this->errors[] = "Faction : {$e->getMessage()}";
                }
            }
        } catch (Exception $e) {
            $this->error("❌ Erreur lors de l'importation des factions : {$e->getMessage()}");
        }
    }

    private function importDatasheets(?string $csvContent): void
    {
        if (!$csvContent) {
            $this->warn('⚠️  Fichier datasheets.csv non disponible');
            return;
        }

        $this->info('📋 Importation des fiches de données...');

        try {
            $reader = Reader::createFromString($csvContent);
            $reader->setDelimiter('|');
            $reader->setHeaderOffset(0);

            foreach ($reader->getRecords() as $row) {
                try {
                    $nameEn = trim($row['name'] ?? '');
                    $factionId = trim($row['faction_id'] ?? '');
                    $wahapediaId = trim($row['id'] ?? '');

                    if (!$nameEn || !$factionId) {
                        $this->report['datasheets']['skipped']++;
                        continue;
                    }

                    // Trouver la faction
                    $faction = DB::table('factions')->where('bsdata_id', $factionId)->first(); // bsdata_id contient wahapedia_id
                    if (!$faction) {
                        $this->report['datasheets']['skipped']++;
                        continue;
                    }

                    // Vérifier les doublons
                    if ($this->datasheetExists($wahapediaId, $nameEn)) {
                        $this->report['datasheets']['skipped']++;
                        continue;
                    }

                    // Traduire
                    $nameFr = $this->translationService->translate($nameEn, 'en', 'fr', $this->translator);

                    // Insérer
                    DB::table('datasheets')->insert([
                        'name_en' => $nameEn,
                        'name_fr' => $nameFr,
                        'faction_id' => $faction->id,
                        'wahapedia_id' => $wahapediaId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->report['datasheets']['success']++;
                    $this->line("  ✅ Fiche créée : $nameEn → $nameFr");

                } catch (Exception $e) {
                    $this->report['datasheets']['errors']++;
                    $this->errors[] = "Datasheet : {$e->getMessage()}";
                }
            }
        } catch (Exception $e) {
            $this->error("❌ Erreur lors de l'importation des fiches : {$e->getMessage()}");
        }
    }

    private function importAbilities(?string $csvContent): void
    {
        if (!$csvContent) {
            $this->warn('⚠️  Fichier abilities.csv non disponible');
            return;
        }

        $this->info('✨ Importation des capacités...');

        try {
            $reader = Reader::createFromString($csvContent);
            $reader->setDelimiter('|');
            $reader->setHeaderOffset(0);

            foreach ($reader->getRecords() as $row) {
                try {
                    $name = trim($row['name'] ?? '');
                    $wahapediaId = trim($row['id'] ?? '');

                    // Ignorer les capacités sans ID
                    if (!$name || !$wahapediaId) {
                        $this->report['abilities']['skipped']++;
                        continue;
                    }

                    // Vérifier les doublons
                    if ($this->abilityExists($wahapediaId, $name)) {
                        $this->report['abilities']['skipped']++;
                        continue;
                    }

                    // Traduire
                    $nameFr = $this->translationService->translate($name, 'en', 'fr', $this->translator);

                    // Insérer
                    DB::table('abilities')->insert([
                        'name' => $name,
                        'bsdata_id' => $wahapediaId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->report['abilities']['success']++;
                    $this->line("  ✅ Capacité créée : $name → $nameFr");

                } catch (Exception $e) {
                    $this->report['abilities']['errors']++;
                    $this->errors[] = "Ability : {$e->getMessage()}";
                }
            }
        } catch (Exception $e) {
            $this->error("❌ Erreur lors de l'importation des capacités : {$e->getMessage()}");
        }
    }

    private function importStratagems(?string $csvContent): void
    {
        if (!$csvContent) {
            $this->warn('⚠️  Fichier stratagems.csv non disponible');
            return;
        }

        $this->info('💡 Importation des stratagèmes...');

        try {
            $reader = Reader::createFromString($csvContent);
            $reader->setDelimiter('|');
            $reader->setHeaderOffset(0);

            foreach ($reader->getRecords() as $row) {
                try {
                    $name = trim($row['name'] ?? '');
                    $wahapediaId = trim($row['id'] ?? '');
                    $description = trim($row['description'] ?? '');
                    $cpCost = (int)($row['cp_cost'] ?? 0) ?: null;
                    $when = trim($row['when'] ?? '');

                    if (!$name) {
                        $this->report['stratagems']['skipped']++;
                        continue;
                    }

                    // Vérifier les doublons
                    if ($this->stratagemExists($wahapediaId, $name)) {
                        $this->report['stratagems']['skipped']++;
                        continue;
                    }

                    // Traduire
                    $nameFr = $this->translationService->translate($name, 'en', 'fr', $this->translator);
                    $descriptionFr = $description ? $this->translationService->translate($description, 'en', 'fr', $this->translator) : null;

                    // Insérer
                    DB::table('stratagems')->insert([
                        'name_en' => $name,
                        'name_fr' => $nameFr,
                        'description_en' => $description,
                        'description_fr' => $descriptionFr,
                        'cp_cost' => $cpCost,
                        'when' => $when,
                        'wahapedia_id' => $wahapediaId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->report['stratagems']['success']++;
                    $this->line("  ✅ Stratagème créé : $name → $nameFr");

                } catch (Exception $e) {
                    $this->report['stratagems']['errors']++;
                    $this->errors[] = "Stratagem : {$e->getMessage()}";
                }
            }
        } catch (Exception $e) {
            $this->error("❌ Erreur lors de l'importation des stratagèmes : {$e->getMessage()}");
        }
    }

    private function factionExists(?string $wahapediaId, string $name): bool
    {
        if ($wahapediaId) {
            return DB::table('factions')->where('bsdata_id', $wahapediaId)->exists(); // bsdata_id stocke wahapedia_id
        }
        return DB::table('factions')->where('name', $name)->exists();
    }

    private function datasheetExists(?string $wahapediaId, string $nameEn): bool
    {
        if ($wahapediaId) {
            return DB::table('datasheets')->where('wahapedia_id', $wahapediaId)->exists();
        }
        return DB::table('datasheets')->where('name_en', $nameEn)->exists();
    }

    private function abilityExists(?string $wahapediaId, string $name): bool
    {
        if ($wahapediaId) {
            return DB::table('abilities')->where('bsdata_id', $wahapediaId)->exists(); // bsdata_id stocke wahapedia_id
        }
        return DB::table('abilities')->where('name', $name)->exists();
    }

    private function stratagemExists(?string $wahapediaId, string $nameEn): bool
    {
        if ($wahapediaId) {
            return DB::table('stratagems')->where('wahapedia_id', $wahapediaId)->exists();
        }
        return DB::table('stratagems')->where('name_en', $nameEn)->exists();
    }

    private function displayReport(): void
    {
        $this->info("\n");
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('📊 RAPPORT D\'IMPORT WAHAPEDIA');
        $this->info('═══════════════════════════════════════════════════════════');

        $totalSuccess = 0;
        $totalErrors = 0;
        $totalSkipped = 0;

        foreach ($this->report as $type => $stats) {
            $icon = match($type) {
                'factions' => '🚩',
                'datasheets' => '📋',
                'abilities' => '✨',
                'stratagems' => '💡',
                default => '📊',
            };

            $this->info("$icon " . ucfirst($type) . ":");
            $this->info("  ✅ Succès : {$stats['success']}");
            $this->info("  ❌ Erreurs : {$stats['errors']}");
            $this->info("  ⏭️  Ignorés : {$stats['skipped']}");

            $totalSuccess += $stats['success'];
            $totalErrors += $stats['errors'];
            $totalSkipped += $stats['skipped'];
        }

        $this->info('───────────────────────────────────────────────────────────');
        $this->info("📈 TOTAL : $totalSuccess créés, $totalErrors erreurs, $totalSkipped ignorés");
        $this->info('═══════════════════════════════════════════════════════════');

        if (!empty($this->errors)) {
            $this->warn("\n⚠️  Erreurs rencontrées :");
            foreach ($this->errors as $error) {
                $this->warn("  • $error");
            }
        }

        $this->info("\n✅ Import terminé !");
    }

    private function importDetachments(?string $csvContent): void
    {
        if (!$csvContent) {
            $this->warn('⚠️  Fichier Detachments.csv non disponible');
            return;
        }

        $this->info('🎖️  Importation des détachements...');

        try {
            $reader = Reader::createFromString($csvContent);
            $reader->setDelimiter('|');
            $reader->setHeaderOffset(0);

            foreach ($reader->getRecords() as $row) {
                try {
                    $name = trim($row['name'] ?? '');
                    $wahapediaId = trim($row['id'] ?? '');
                    $factionId = trim($row['faction_id'] ?? '');
                    $description = trim($row['description'] ?? '');

                    if (!$name || !$wahapediaId) {
                        $this->report['detachments']['skipped']++;
                        continue;
                    }

                    // Trouver la faction
                    $faction = DB::table('factions')->where('bsdata_id', $factionId)->first();
                    if (!$faction) {
                        $this->report['detachments']['skipped']++;
                        continue;
                    }

                    // Vérifier les doublons
                    if ($this->detachmentExists($wahapediaId)) {
                        $this->report['detachments']['skipped']++;
                        continue;
                    }

                    // Insérer
                    DB::table('detachments')->insert([
                        'name' => $name,
                        'description' => $description,
                        'wahapedia_id' => $wahapediaId,
                        'faction_id' => $faction->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->report['detachments']['success']++;
                    $this->line("  ✅ Détachement créé : $name");

                } catch (Exception $e) {
                    $this->report['detachments']['errors']++;
                    $this->errors[] = "Detachment : {$e->getMessage()}";
                }
            }
        } catch (Exception $e) {
            $this->error("❌ Erreur lors de l'importation des détachements : {$e->getMessage()}");
        }
    }

    private function importDetachmentAbilities(?string $csvContent): void
    {
        if (!$csvContent) {
            $this->warn('⚠️  Fichier Detachment_abilities.csv non disponible');
            return;
        }

        $this->info('✨ Importation des capacités de détachement...');

        try {
            $reader = Reader::createFromString($csvContent);
            $reader->setDelimiter('|');
            $reader->setHeaderOffset(0);

            foreach ($reader->getRecords() as $row) {
                try {
                    $name = trim($row['name'] ?? '');
                    $wahapediaId = trim($row['id'] ?? '');
                    $detachmentId = trim($row['detachment_id'] ?? '');

                    if (!$name || !$wahapediaId) {
                        $this->report['detachment_abilities']['skipped']++;
                        continue;
                    }

                    // Trouver le détachement
                    $detachment = DB::table('detachments')->where('wahapedia_id', $detachmentId)->first();
                    if (!$detachment) {
                        $this->report['detachment_abilities']['skipped']++;
                        continue;
                    }

                    // Vérifier les doublons
                    if ($this->detachmentAbilityExists($wahapediaId)) {
                        $this->report['detachment_abilities']['skipped']++;
                        continue;
                    }

                    // Insérer
                    DB::table('detachment_abilities')->insert([
                        'name' => $name,
                        'wahapedia_id' => $wahapediaId,
                        'detachment_id' => $detachment->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->report['detachment_abilities']['success']++;
                    $this->line("  ✅ Capacité détachement créée : $name");

                } catch (Exception $e) {
                    $this->report['detachment_abilities']['errors']++;
                    $this->errors[] = "Detachment Ability : {$e->getMessage()}";
                }
            }
        } catch (Exception $e) {
            $this->error("❌ Erreur lors de l'importation des capacités de détachement : {$e->getMessage()}");
        }
    }

    private function detachmentExists(?string $wahapediaId): bool
    {
        return DB::table('detachments')->where('wahapedia_id', $wahapediaId)->exists();
    }

    private function detachmentAbilityExists(?string $wahapediaId): bool
    {
        return DB::table('detachment_abilities')->where('wahapedia_id', $wahapediaId)->exists();
    }
}
