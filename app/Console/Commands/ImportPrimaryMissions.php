<?php

namespace App\Console\Commands;

use App\Models\PrimaryMission;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportPrimaryMissions extends Command
{
    protected $signature = 'missions:import {--source=chapter-approved-2025-26}';
    protected $description = 'Importer les missions primaires depuis Wahapedia';

    public function handle()
    {
        $this->info('📥 Importation des missions primaires...');
        $this->info('═══════════════════════════════════════════════════════════');

        $source = $this->option('source');

        // Données des missions à importer manuellement
        // (À remplacer par un scraper si nécessaire)
        $missions = [
            [
                'name' => 'LINCHPIN',
                'description' => 'True victory is built upon a firm foundation. If you cannot hold the centre, then all else will crumble swiftly.',
                'full_text' => 'Primary Mission
LINCHPIN
True victory is built upon a firm foundation. If you cannot hold the centre, then all else will crumble swiftly.

SECOND BATTLE ROUND ONWARDS
WHEN: End of the Command phase (or the end of your turn if it is the fifth battle round and you are going second).

If the player whose turn it is does not control the objective marker in their deployment zone, they score 3VP for each objective marker they control.

OR
If the player whose turn it is controls the objective marker in their deployment zone, they score 3VP for controlling that objective marker, and 5VP for each other objective marker they control (up to 15VP per turn).',
                'when_condition' => 'End of the Command phase (or the end of your turn if it is the fifth battle round and you are going second)',
                'timing' => 'second_battle_round_onwards',
                'scoring_conditions' => [
                    [
                        'condition' => 'Does not control objective in deployment zone',
                        'vp' => '3VP per objective marker controlled',
                    ],
                    [
                        'condition' => 'Controls objective in deployment zone',
                        'vp' => '3VP for that marker + 5VP for each other (max 15VP)',
                    ],
                ],
                'max_vp' => 15,
                'sections' => [
                    [
                        'type' => 'scoring',
                        'order' => 1,
                        'timing' => 'End of the Command phase (or the end of your turn if it is the fifth battle round and you are going second)',
                        'content' => 'If the player whose turn it is does not control the objective marker in their deployment zone, they score 3VP for each objective marker they control. OR If the player whose turn it is controls the objective marker in their deployment zone, they score 3VP for controlling that objective marker, and 5VP for each other objective marker they control (up to 15VP per turn).',
                        'victory_points' => 15,
                    ],
                ],
            ],
            // Ajouter d'autres missions ici
        ];

        $created = 0;
        $updated = 0;

        foreach ($missions as $missionData) {
            try {
                $mission = PrimaryMission::updateOrCreate(
                    ['name' => $missionData['name'], 'source' => $source],
                    [
                        'description' => $missionData['description'],
                        'full_text' => $missionData['full_text'],
                        'when_condition' => $missionData['when_condition'],
                        'timing' => $missionData['timing'],
                        'scoring_conditions' => $missionData['scoring_conditions'],
                        'max_vp' => $missionData['max_vp'],
                        'slug' => Str::slug($missionData['name']),
                        'edition' => '10ed',
                        'source' => $source,
                        'is_active' => true,
                    ]
                );

                // Créer les sections
                if (isset($missionData['sections'])) {
                    $mission->sections()->delete(); // Supprimer les anciennes sections
                    foreach ($missionData['sections'] as $sectionData) {
                        $mission->sections()->create($sectionData);
                    }
                }

                if ($mission->wasRecentlyCreated) {
                    $this->line("  ✅ Créée: {$mission->name}");
                    $created++;
                } else {
                    $this->line("  🔄 Mise à jour: {$mission->name}");
                    $updated++;
                }
            } catch (\Exception $e) {
                $this->error("  ❌ Erreur pour {$missionData['name']}: {$e->getMessage()}");
            }
        }

        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info("📊 Résultats:");
        $this->info("  ✅ Créées: {$created}");
        $this->info("  🔄 Mises à jour: {$updated}");
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('✅ Importation terminée !');

        return 0;
    }
}
