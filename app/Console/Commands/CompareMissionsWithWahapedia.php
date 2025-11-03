<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CompareMissionsWithWahapedia extends Command
{
    protected $signature = 'missions:compare-wahapedia {mission? : Mission name to compare} {--type=primary : Type of mission (primary, secondary, twist)}';
    protected $description = 'Comparer les missions en base de données avec les données de Wahapedia';

    public function handle()
    {
        $missionName = $this->argument('mission');
        $type = $this->option('type');

        $this->info('🔍 Comparaison des missions avec Wahapedia...');
        $this->info('═══════════════════════════════════════════════════════════');

        // Données de référence Wahapedia (à mettre à jour manuellement)
        $wahapediaData = $this->getWahapediaReferenceData($type);

        if (empty($wahapediaData)) {
            $this->error("❌ Aucune donnée de référence trouvée pour le type: {$type}");
            return 1;
        }

        $this->info("📊 {$type} missions trouvées: " . count($wahapediaData));

        $discrepancies = [];

        foreach ($wahapediaData as $name => $wahapediaInfo) {
            if ($missionName && $name !== $missionName) {
                continue;
            }

            $this->line("\n📋 Vérification: {$name}");

            // Récupérer la mission en base de données
            $dbMission = $this->getMissionFromDatabase($name, $type);

            if (!$dbMission) {
                $this->warn("  ⚠️  Mission non trouvée en base de données");
                $discrepancies[] = [
                    'mission' => $name,
                    'issue' => 'Mission non trouvée en base de données',
                    'severity' => 'error'
                ];
                continue;
            }

            // Vérifier le nombre d'items de scoring
            $dbScoringCount = count($dbMission['scoring_items'] ?? []);
            $wahapeediaScoringCount = $wahapediaInfo['scoring_count'] ?? 0;

            if ($dbScoringCount !== $wahapeediaScoringCount) {
                $this->error("  ❌ Nombre d'items de scoring différent");
                $this->line("     Base de données: {$dbScoringCount}");
                $this->line("     Wahapedia: {$wahapeediaScoringCount}");
                $discrepancies[] = [
                    'mission' => $name,
                    'issue' => "Nombre d'items de scoring différent (DB: {$dbScoringCount}, Wahapedia: {$wahapeediaScoringCount})",
                    'severity' => 'error'
                ];
            } else {
                $this->line("  ✅ Nombre d'items de scoring correct: {$dbScoringCount}");
            }

            // Vérifier la description
            if (isset($wahapediaInfo['description'])) {
                if ($dbMission['description'] !== $wahapediaInfo['description']) {
                    $this->warn("  ⚠️  Description différente");
                    $discrepancies[] = [
                        'mission' => $name,
                        'issue' => 'Description différente',
                        'severity' => 'warning'
                    ];
                } else {
                    $this->line("  ✅ Description correcte");
                }
            }

            // Vérifier le timing
            if (isset($wahapediaInfo['timing'])) {
                if ($dbMission['timing'] !== $wahapediaInfo['timing']) {
                    $this->warn("  ⚠️  Timing différent");
                    $this->line("     Base de données: {$dbMission['timing']}");
                    $this->line("     Wahapedia: {$wahapediaInfo['timing']}");
                    $discrepancies[] = [
                        'mission' => $name,
                        'issue' => "Timing différent (DB: {$dbMission['timing']}, Wahapedia: {$wahapediaInfo['timing']})",
                        'severity' => 'warning'
                    ];
                } else {
                    $this->line("  ✅ Timing correct: {$dbMission['timing']}");
                }
            }
        }

        // Résumé
        $this->info("\n═══════════════════════════════════════════════════════════");
        $this->info("📊 Résultats de comparaison:");

        $errors = array_filter($discrepancies, fn($d) => $d['severity'] === 'error');
        $warnings = array_filter($discrepancies, fn($d) => $d['severity'] === 'warning');

        $this->line("  Erreurs: " . count($errors));
        $this->line("  Avertissements: " . count($warnings));

        if (!empty($errors)) {
            $this->error("\n❌ Erreurs trouvées:");
            foreach ($errors as $error) {
                $this->error("  - {$error['mission']}: {$error['issue']}");
            }
        }

        if (!empty($warnings)) {
            $this->warn("\n⚠️  Avertissements:");
            foreach ($warnings as $warning) {
                $this->warn("  - {$warning['mission']}: {$warning['issue']}");
            }
        }

        if (empty($discrepancies)) {
            $this->info("\n✅ Toutes les missions correspondent à Wahapedia");
            return 0;
        }

        return count($errors) > 0 ? 1 : 0;
    }

    protected function getWahapediaReferenceData(string $type): array
    {
        // Données de référence Wahapedia - À METTRE À JOUR MANUELLEMENT
        // Format: 'Mission Name' => ['scoring_count' => N, 'description' => '...', 'timing' => '...']
        
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
            return [
                // À remplir avec les données Wahapedia
            ];
        }

        if ($type === 'twist') {
            return [
                // À remplir avec les données Wahapedia
            ];
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

        // Compter les items de scoring
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
