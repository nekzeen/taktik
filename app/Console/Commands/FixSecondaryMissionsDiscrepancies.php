<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixSecondaryMissionsDiscrepancies extends Command
{
    protected $signature = 'missions:fix-secondary-discrepancies {--dry-run : Afficher les changements sans les appliquer}';
    protected $description = 'Corriger automatiquement les discrepancies des missions secondaires';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('🔧 Correction des discrepancies (missions secondaires)...');
        $this->info('═══════════════════════════════════════════════════════════');

        if ($dryRun) {
            $this->warn('⚠️  Mode DRY-RUN : Les changements ne seront PAS appliqués');
            $this->line('');
        }

        // Données de référence Wahapedia
        $wahapediaData = $this->getWahapediaReferenceData();

        if (empty($wahapediaData)) {
            $this->info("ℹ️  Aucune donnée de référence trouvée pour les missions secondaires");
            $this->info("Les données de référence doivent être ajoutées dans la méthode getWahapediaReferenceData()");
            return 0;
        }

        $this->info("📊 Missions secondaires trouvées: " . count($wahapediaData));
        $this->line('');

        $fixed = 0;

        foreach ($wahapediaData as $name => $wahapediaInfo) {
            // Récupérer la mission en base de données
            $dbMission = $this->getMissionFromDatabase($name);

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
                $fixed += $this->fixMissionFromXml($name, $dryRun);
            }
        }

        // Résumé
        $this->info("\n═══════════════════════════════════════════════════════════");
        $this->info("📊 Résultats:");
        $this->line("  Missions corrigées: {$fixed}");

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

    protected function fixMissionFromXml(string $missionName, bool $dryRun): int
    {
        $xmlFile = storage_path('missions/secondary-missions-chapter-approved-2025-26.xml');

        if (!file_exists($xmlFile)) {
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

                $scoringItems = [];
                if (isset($missionXml->scoring->item)) {
                    foreach ($missionXml->scoring->item as $item) {
                        $scoringItems[] = (string) $item;
                    }
                }

                // Afficher les changements
                $this->line("  Changements détectés:");
                $this->line("    - Nombre d'items de scoring: " . count($scoringItems));

                if (!$dryRun) {
                    // Mettre à jour la mission en base de données
                    \DB::table('secondary_missions')
                        ->where('name', $missionName)
                        ->update([
                            'description' => $flavour ?: 'No description',
                            'scoring_conditions' => json_encode($scoringItems),
                            'updated_at' => now(),
                        ]);

                    $this->info("  ✅ Mission corrigée en base de données");
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
