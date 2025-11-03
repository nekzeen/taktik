<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CompareSecondaryMissionsWithWahapedia extends Command
{
    protected $signature = 'missions:compare-secondary-wahapedia {mission? : Mission name to compare}';
    protected $description = 'Comparer les missions secondaires en base de données avec les données de Wahapedia';

    public function handle()
    {
        $missionName = $this->argument('mission');

        $this->info('🔍 Comparaison des missions secondaires avec Wahapedia...');
        $this->info('═══════════════════════════════════════════════════════════');

        // Données de référence Wahapedia pour missions secondaires
        $wahapediaData = $this->getWahapediaReferenceData();

        if (empty($wahapediaData)) {
            $this->error("❌ Aucune donnée de référence trouvée");
            return 1;
        }

        $this->info("📊 Missions secondaires trouvées: " . count($wahapediaData));

        $discrepancies = [];

        foreach ($wahapediaData as $name => $wahapediaInfo) {
            if ($missionName && $name !== $missionName) {
                continue;
            }

            $this->line("\n📋 Vérification: {$name}");

            // Récupérer la mission en base de données
            $dbMission = $this->getMissionFromDatabase($name);

            if (!$dbMission) {
                $this->warn("  Mission non trouvée en base de données");
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
                $this->error("  Nombre d'items de scoring différent");
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
        }

        // Résumé
        $this->info("\n═══════════════════════════════════════════════════════════");
        $this->info("📊 Résultats de comparaison:");

        $errors = array_filter($discrepancies, fn($d) => $d['severity'] === 'error');
        $warnings = array_filter($discrepancies, fn($d) => $d['severity'] === 'warning');

        $this->line("  Erreurs: " . count($errors));
        $this->line("  Avertissements: " . count($warnings));

        if (!empty($errors)) {
            $this->error("\nErreurs trouvées:");
            foreach ($errors as $error) {
                $this->error("  - {$error['mission']}: {$error['issue']}");
            }
        }

        if (!empty($warnings)) {
            $this->warn("\nAvertissements:");
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

    protected function getWahapediaReferenceData(): array
    {
        // Données de référence Wahapedia pour missions secondaires
        // À METTRE À JOUR MANUELLEMENT avec les données réelles
        return [
            // Format: 'Mission Name' => ['scoring_count' => N]
            // À remplir avec les missions secondaires réelles
        ];
    }

    protected function getMissionFromDatabase(string $name): ?array
    {
        $mission = \DB::table('secondary_missions')
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
            'scoring_items' => $scoringItems,
        ];
    }
}
