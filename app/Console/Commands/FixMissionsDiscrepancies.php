<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixMissionsDiscrepancies extends Command
{
    protected $signature = 'missions:fix-discrepancies {--type=primary : Type of mission (primary, secondary, twist)} {--dry-run : Afficher les changements sans les appliquer}';
    protected $description = 'Corriger automatiquement les discrepancies détectées entre la base de données et Wahapedia';

    public function handle()
    {
        $type = $this->option('type');
        $dryRun = $this->option('dry-run');

        $this->info('🔧 Correction des discrepancies...');
        $this->info('═══════════════════════════════════════════════════════════');

        if ($dryRun) {
            $this->warn('⚠️  Mode DRY-RUN : Les changements ne seront PAS appliqués');
            $this->line('');
        }

        // Données de référence Wahapedia
        $wahapediaData = $this->getWahapediaReferenceData($type);

        if (empty($wahapediaData)) {
            $this->error("❌ Aucune donnée de référence trouvée pour le type: {$type}");
            return 1;
        }

        $this->info("📊 {$type} missions trouvées: " . count($wahapediaData));
        $this->line('');

        $fixed = 0;
        $errors = 0;

        foreach ($wahapediaData as $name => $wahapediaInfo) {
            // Récupérer la mission en base de données
            $dbMission = $this->getMissionFromDatabase($name, $type);

            if (!$dbMission) {
                $this->warn("⚠️  {$name} : Mission non trouvée en base de données");
                continue;
            }

            // Vérifier le nombre d'items de scoring
            $dbScoringCount = count($dbMission['scoring_items'] ?? []);
            $wahapeediaScoringCount = $wahapediaInfo['scoring_count'] ?? 0;

            if ($dbScoringCount !== $wahapeediaScoringCount) {
                $this->line("📋 {$name}");
                $this->error("  Nombre d'items de scoring différent (DB: {$dbScoringCount}, Wahapedia: {$wahapeediaScoringCount})");
                
                // Essayer de corriger en réimportant depuis le XML
                $fixed += $this->fixMissionFromXml($name, $type, $dryRun);
            }
        }

        // Résumé
        $this->info("\n═══════════════════════════════════════════════════════════");
        $this->info("📊 Résultats:");
        $this->line("  Missions corrigées: {$fixed}");
        $this->line("  Erreurs: {$errors}");

        if ($dryRun) {
            $this->warn("\n⚠️  Mode DRY-RUN : Aucun changement n'a été appliqué");
            $this->info("Exécutez sans --dry-run pour appliquer les corrections");
        } else {
            if ($fixed > 0) {
                $this->info("\n✅ Corrections appliquées avec succès");
            } else {
                $this->info("\n✅ Aucune correction nécessaire");
            }
        }

        return 0;
    }

    protected function fixMissionFromXml(string $missionName, string $type, bool $dryRun): int
    {
        // Déterminer le fichier XML
        $xmlFile = match($type) {
            'primary' => storage_path('missions/primary-missions-chapter-approved-2025-26.xml'),
            'secondary' => storage_path('missions/secondary-missions-chapter-approved-2025-26.xml'),
            'twist' => storage_path('missions/twist-missions-chapter-approved-2025-26.xml'),
            default => null
        };

        if (!$xmlFile || !file_exists($xmlFile)) {
            $this->error("    Fichier XML non trouvé: {$xmlFile}");
            return 0;
        }

        try {
            $xml = simplexml_load_file($xmlFile);
            $missions = $xml->mission;

            foreach ($missions as $missionXml) {
                $title = (string) $missionXml->title;
                
                if ($title !== $missionName) {
                    continue;
                }

                // Extraire les données du XML
                $flavour = (string) $missionXml->flavour;
                $when = (string) $missionXml->when;
                $action = (string) $missionXml->action;
                $setup = (string) $missionXml->setup;

                $scoringItems = [];
                if (isset($missionXml->scoring->item)) {
                    foreach ($missionXml->scoring->item as $item) {
                        $scoringItems[] = (string) $item;
                    }
                }

                // Afficher les changements
                $this->line("  Changements détectés:");
                $this->line("    - Nombre d'items de scoring: " . count($scoringItems));
                $this->line("    - Items: " . implode(", ", array_map(fn($i) => substr($i, 0, 40) . '...', $scoringItems)));

                if (!$dryRun) {
                    // Mettre à jour la mission en base de données
                    $table = match($type) {
                        'primary' => 'primary_missions',
                        'secondary' => 'secondary_missions',
                        'twist' => 'twist_missions',
                        default => null
                    };

                    if ($table) {
                        \DB::table($table)
                            ->where('name', $missionName)
                            ->update([
                                'description' => $flavour ?: 'No description',
                                'scoring_conditions' => json_encode($scoringItems),
                                'updated_at' => now(),
                            ]);

                        $this->info("  ✅ Mission corrigée en base de données");
                    }
                } else {
                    $this->line("  [DRY-RUN] Changements à appliquer:");
                    $this->line("    - description: {$flavour}");
                    $this->line("    - scoring_conditions: " . json_encode($scoringItems));
                }

                return 1;
            }

            $this->warn("    Mission '{$missionName}' non trouvée dans le XML");
            return 0;

        } catch (\Exception $e) {
            $this->error("    Erreur lors de la lecture du XML: {$e->getMessage()}");
            return 0;
        }
    }

    protected function getWahapediaReferenceData(string $type): array
    {
        if ($type === 'primary') {
            return [
                'LINCHPIN' => ['scoring_count' => 2, 'timing' => 'second_battle_round_onwards'],
                'BURDEN OF TRUST' => ['scoring_count' => 2, 'timing' => 'second_battle_round_onwards'],
                'TAKE AND HOLD' => ['scoring_count' => 1, 'timing' => 'second_battle_round_onwards'],
                'TERRAFORM' => ['scoring_count' => 2, 'timing' => 'second_battle_round_onwards'],
                'PURGE THE FOE' => ['scoring_count' => 3, 'timing' => 'any_battle_round'],
                'SCORCHED EARTH' => ['scoring_count' => 2, 'timing' => 'second_battle_round_onwards'],
                'UNEXPLODED ORDNANCE' => ['scoring_count' => 3, 'timing' => 'second_battle_round_onwards'],
                'HIDDEN SUPPLIES' => ['scoring_count' => 4, 'timing' => 'second_battle_round_onwards'],
                'THE RITUAL' => ['scoring_count' => 2, 'timing' => 'second_battle_round_onwards'],
                'SUPPLY DROP' => ['scoring_count' => 1, 'timing' => 'second_battle_round_onwards'],
            ];
        }

        if ($type === 'secondary') {
            return [];
        }

        if ($type === 'twist') {
            return [];
        }

        return [];
    }

    protected function getMissionFromDatabase(string $name, string $type): ?array
    {
        $table = match($type) {
            'primary' => 'primary_missions',
            'secondary' => 'secondary_missions',
            'twist' => 'twist_missions',
            default => null
        };

        if (!$table) {
            return null;
        }

        $mission = \DB::table($table)
            ->where('name', $name)
            ->first();

        if (!$mission) {
            return null;
        }

        $scoringItems = [];
        if ($mission->scoring_conditions) {
            $scoringItems = json_decode($mission->scoring_conditions, true) ?? [];
        }

        return [
            'name' => $mission->name,
            'description' => $mission->description,
            'timing' => $mission->timing ?? null,
            'scoring_items' => $scoringItems,
        ];
    }
}
