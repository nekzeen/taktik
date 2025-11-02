<?php

namespace App\Console\Commands;

use App\Models\TournamentMissionPool;
use App\Models\PrimaryMission;
use App\Models\TerrainLayout;
use Illuminate\Console\Command;

class ImportTournamentMissionPoolsFromWahapedia extends Command
{
    protected $signature = 'tournament:import-pools-wahapedia';
    protected $description = 'Importer les pools de missions depuis le tableau Wahapedia';

    public function handle()
    {
        $this->info('Importation des pools de missions depuis Wahapedia...');

        // Données du tableau Wahapedia
        $pools = [
            ['letter' => 'A', 'primary_mission' => 'Take and Hold', 'deployment' => 'Tipping Point', 'terrains' => [1, 2, 4, 6, 7, 8]],
            ['letter' => 'B', 'primary_mission' => 'Supply Drop', 'deployment' => 'Tipping Point', 'terrains' => [1, 2, 4, 6, 7, 8]],
            ['letter' => 'C', 'primary_mission' => 'Linchpin', 'deployment' => 'Tipping Point', 'terrains' => [1, 2, 4, 6, 7, 8]],
            ['letter' => 'D', 'primary_mission' => 'Scorched Earth', 'deployment' => 'Tipping Point', 'terrains' => [1, 2, 4, 6, 7, 8]],
            ['letter' => 'E', 'primary_mission' => 'Take and Hold', 'deployment' => 'Hammer and Anvil', 'terrains' => [1, 7, 8]],
            ['letter' => 'F', 'primary_mission' => 'Hidden Supplies', 'deployment' => 'Hammer and Anvil', 'terrains' => [1, 7, 8]],
            ['letter' => 'G', 'primary_mission' => 'Purge the Foe', 'deployment' => 'Hammer and Anvil', 'terrains' => [1, 7, 8]],
            ['letter' => 'H', 'primary_mission' => 'Supply Drop', 'deployment' => 'Hammer and Anvil', 'terrains' => [1, 7, 8]],
            ['letter' => 'I', 'primary_mission' => 'Hidden Supplies', 'deployment' => 'Search and Destroy', 'terrains' => [1, 2, 3, 4, 6]],
            ['letter' => 'J', 'primary_mission' => 'Linchpin', 'deployment' => 'Search and Destroy', 'terrains' => [1, 2, 3, 4, 6]],
            ['letter' => 'K', 'primary_mission' => 'Scorched Earth', 'deployment' => 'Search and Destroy', 'terrains' => [1, 2, 3, 4, 6]],
            ['letter' => 'L', 'primary_mission' => 'Take and Hold', 'deployment' => 'Search and Destroy', 'terrains' => [1, 2, 3, 4, 6]],
            ['letter' => 'M', 'primary_mission' => 'Purge the Foe', 'deployment' => 'Crucible of Battle', 'terrains' => [1, 2, 4, 6, 8]],
            ['letter' => 'N', 'primary_mission' => 'Hidden Supplies', 'deployment' => 'Crucible of Battle', 'terrains' => [1, 2, 4, 6, 8]],
            ['letter' => 'O', 'primary_mission' => 'Terraform', 'deployment' => 'Crucible of Battle', 'terrains' => [1, 2, 4, 6, 8]],
            ['letter' => 'P', 'primary_mission' => 'Scorched Earth', 'deployment' => 'Crucible of Battle', 'terrains' => [1, 2, 4, 6, 8]],
            ['letter' => 'Q', 'primary_mission' => 'Supply Drop', 'deployment' => 'Sweeping Engagement', 'terrains' => [3, 5]],
            ['letter' => 'R', 'primary_mission' => 'Terraform', 'deployment' => 'Sweeping Engagement', 'terrains' => [3, 5]],
            ['letter' => 'S', 'primary_mission' => 'Linchpin', 'deployment' => 'Dawn of War', 'terrains' => [5]],
            ['letter' => 'T', 'primary_mission' => 'Purge the Foe', 'deployment' => 'Dawn of War', 'terrains' => [5]],
        ];

        $imported = 0;
        $updated = 0;

        foreach ($pools as $poolData) {
            // Récupérer la mission primaire
            $primaryMission = PrimaryMission::where('name', $poolData['primary_mission'])->first();
            if (!$primaryMission) {
                $this->warn("Mission primaire '{$poolData['primary_mission']}' non trouvée");
                continue;
            }

            // Créer ou mettre à jour le pool
            $pool = TournamentMissionPool::updateOrCreate(
                ['pool_letter' => $poolData['letter']],
                [
                    'name' => "Pool {$poolData['letter']}",
                    'slug' => "pool-" . strtolower($poolData['letter']),
                    'description' => "{$poolData['primary_mission']} - {$poolData['deployment']}",
                    'primary_mission_id' => $primaryMission->id,
                    'deployment_mode' => $poolData['deployment'],
                    'source' => 'chapter-approved-2025-26',
                    'is_active' => true,
                ]
            );

            // Associer les dispositions de terrain
            $terrainIds = [];
            foreach ($poolData['terrains'] as $layoutNumber) {
                // Chercher le terrain par le numéro
                $terrain = TerrainLayout::where('name', 'LIKE', "%Layout $layoutNumber%")
                    ->orWhere('name', 'LIKE', "%Layout {$layoutNumber}%")
                    ->first();
                
                if (!$terrain) {
                    // Essayer avec juste le numéro
                    $terrain = TerrainLayout::where('name', "Layout $layoutNumber")->first();
                }
                
                if ($terrain) {
                    $terrainIds[$terrain->id] = ['order' => count($terrainIds) + 1];
                } else {
                    $this->warn("Terrain Layout $layoutNumber non trouvé pour Pool {$poolData['letter']}");
                }
            }

            if (!empty($terrainIds)) {
                $pool->availableTerrainLayouts()->sync($terrainIds);
            }

            if ($pool->wasRecentlyCreated) {
                $imported++;
                $this->line("✅ Pool {$poolData['letter']} créé");
            } else {
                $updated++;
                $this->line("🔄 Pool {$poolData['letter']} mis à jour");
            }
        }

        $this->info("\n✅ Importation terminée: $imported créés, $updated mis à jour");
    }
}
