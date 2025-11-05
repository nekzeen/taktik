<?php

namespace App\Console\Commands;

use App\Models\TournamentMissionPool;
use App\Models\PrimaryMission;
use App\Models\TerrainLayout;
use Illuminate\Console\Command;

class ImportMissionPools extends Command
{
    protected $signature = 'pools:import';
    protected $description = 'Importer les pools de missions depuis les données fournies';

    public function handle()
    {
        $this->info('🎮 IMPORT DES POOLS DE MISSIONS');
        $this->info('================================');

        // Données des pools
        $poolsData = [
            ['id' => 'A', 'mission' => 'Take and Hold', 'deployment' => 'Tipping Point', 'layouts' => [1, 2, 4, 6, 7, 8]],
            ['id' => 'B', 'mission' => 'Supply Drop', 'deployment' => 'Tipping Point', 'layouts' => [1, 2, 4, 6, 7, 8]],
            ['id' => 'C', 'mission' => 'Linchpin', 'deployment' => 'Tipping Point', 'layouts' => [1, 2, 4, 6, 7, 8]],
            ['id' => 'D', 'mission' => 'Scorched Earth', 'deployment' => 'Tipping Point', 'layouts' => [1, 2, 4, 6, 7, 8]],
            ['id' => 'E', 'mission' => 'Take and Hold', 'deployment' => 'Hammer and Anvil', 'layouts' => [1, 7, 8]],
            ['id' => 'F', 'mission' => 'Hidden Supplies', 'deployment' => 'Hammer and Anvil', 'layouts' => [1, 7, 8]],
            ['id' => 'G', 'mission' => 'Purge the Foe', 'deployment' => 'Hammer and Anvil', 'layouts' => [1, 7, 8]],
            ['id' => 'H', 'mission' => 'Supply Drop', 'deployment' => 'Hammer and Anvil', 'layouts' => [1, 7, 8]],
            ['id' => 'I', 'mission' => 'Hidden Supplies', 'deployment' => 'Search and Destroy', 'layouts' => [1, 2, 3, 4, 6]],
            ['id' => 'J', 'mission' => 'Linchpin', 'deployment' => 'Search and Destroy', 'layouts' => [1, 2, 3, 4, 6]],
            ['id' => 'K', 'mission' => 'Scorched Earth', 'deployment' => 'Search and Destroy', 'layouts' => [1, 2, 3, 4, 6]],
            ['id' => 'L', 'mission' => 'Take and Hold', 'deployment' => 'Search and Destroy', 'layouts' => [1, 2, 3, 4, 6]],
            ['id' => 'M', 'mission' => 'Purge the Foe', 'deployment' => 'Crucible of Battle', 'layouts' => [1, 2, 4, 6, 8]],
            ['id' => 'N', 'mission' => 'Hidden Supplies', 'deployment' => 'Crucible of Battle', 'layouts' => [1, 2, 4, 6, 8]],
            ['id' => 'O', 'mission' => 'Terraform', 'deployment' => 'Crucible of Battle', 'layouts' => [1, 2, 4, 6, 8]],
            ['id' => 'P', 'mission' => 'Scorched Earth', 'deployment' => 'Crucible of Battle', 'layouts' => [1, 2, 4, 6, 8]],
            ['id' => 'Q', 'mission' => 'Supply Drop', 'deployment' => 'Sweeping Engagement', 'layouts' => [3, 5]],
            ['id' => 'R', 'mission' => 'Terraform', 'deployment' => 'Sweeping Engagement', 'layouts' => [3, 5]],
            ['id' => 'S', 'mission' => 'Linchpin', 'deployment' => 'Dawn of War', 'layouts' => [5]],
            ['id' => 'T', 'mission' => 'Purge the Foe', 'deployment' => 'Dawn of War', 'layouts' => [5]],
        ];

        $created = 0;
        $updated = 0;

        foreach ($poolsData as $data) {
            // Trouver la mission primaire
            $primaryMission = PrimaryMission::where('name', $data['mission'])->first();
            if (!$primaryMission) {
                $this->warn("❌ Mission primaire '{$data['mission']}' non trouvée");
                continue;
            }

            // Créer ou mettre à jour le pool
            $pool = TournamentMissionPool::updateOrCreate(
                ['pool_letter' => strtoupper($data['id'])],
                [
                    'pool_number' => ord(strtoupper($data['id'])) - 64, // A=1, B=2, etc.
                    'name' => "Pool {$data['id']} - {$data['mission']} ({$data['deployment']})",
                    'slug' => "pool-{$data['id']}-" . \Illuminate\Support\Str::slug($data['mission']),
                    'primary_mission_id' => $primaryMission->id,
                    'deployment_mode' => $data['deployment'],
                    'is_active' => true,
                    'source' => 'chapter-approved-2025-26',
                ]
            );

            if ($pool->wasRecentlyCreated) {
                $created++;
                $this->line("✅ Pool {$data['id']} créé");
            } else {
                $updated++;
                $this->line("🔄 Pool {$data['id']} mis à jour");
            }

            // Associer les terrains disponibles
            $terrainIds = [];
            foreach ($data['layouts'] as $index => $layoutNumber) {
                $terrain = TerrainLayout::where('layout_number', $layoutNumber)->first();
                if ($terrain) {
                    $terrainIds[$terrain->id] = ['order' => $index];
                }
            }

            if (!empty($terrainIds)) {
                $pool->availableTerrainLayouts()->sync($terrainIds);
            }
        }

        $this->info('');
        $this->info('📊 RÉSUMÉ');
        $this->info('=========');
        $this->line("✅ Pools créés: $created");
        $this->line("🔄 Pools mis à jour: $updated");
        $this->line("📊 Total: " . ($created + $updated));
        $this->info('');
        $this->info('✅ IMPORT TERMINÉ!');
    }
}
