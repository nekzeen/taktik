<?php

namespace App\Console\Commands;

use App\Models\TournamentMissionPool;
use App\Models\PrimaryMission;
use App\Models\SecondaryMission;
use App\Models\TerrainLayout;
use Illuminate\Console\Command;

class ScrapeTournamentMissionPools extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tournament:scrape-mission-pools';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scraper et importer les données du tableau Wahapedia avec associations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌐 Scraping du tableau Wahapedia...');

        // Données du tableau Wahapedia
        // Format : [pool_number => [primary_mission, secondary_missions[], deployment_mode, primary_terrain_layout, use_twist_deck, available_terrain_layouts[]]]
        $poolData = [
            1 => ['LINCHPIN', ['BREAK THROUGH', 'ASSASSINATE'], 'Hammer and Anvil', 'Terrain Layout 1', false, [1, 2, 4, 6, 7, 8]],
            2 => ['BURDEN OF TRUST', ['SECURE OBJECTIVE', 'REPAIR OBJECTIVE'], 'Dawn of War', 'Terrain Layout 2', false, [1, 2, 3, 5, 6, 8]],
            3 => ['TAKE AND HOLD', ['RETRIEVE ARTEFACT', 'ENGAGE ON ALL FRONTS'], 'Hammer and Anvil', 'Terrain Layout 3', true, [1, 3, 4, 5, 7, 8]],
            4 => ['TERRAFORM', ['DEFEND OBJECTIVE', 'BRING IT DOWN'], 'Dawn of War', 'Terrain Layout 4', false, [2, 3, 4, 6, 7, 8]],
            5 => ['PURGE THE FOE', ['LINEBREAKER', 'HOLD THE LINE'], 'Hammer and Anvil', 'Terrain Layout 5', true, [1, 2, 5, 6, 7, 8]],
            6 => ['SCORCHED EARTH', ['BREAK THROUGH', 'SECURE OBJECTIVE'], 'Dawn of War', 'Terrain Layout 6', false, [1, 3, 4, 5, 6, 7]],
            7 => ['UNEXPLODED ORDNANCE', ['ASSASSINATE', 'REPAIR OBJECTIVE'], 'Hammer and Anvil', 'Terrain Layout 7', true, [2, 3, 4, 5, 7, 8]],
            8 => ['HIDDEN SUPPLIES', ['RETRIEVE ARTEFACT', 'DEFEND OBJECTIVE'], 'Dawn of War', 'Terrain Layout 8', false, [1, 2, 3, 6, 7, 8]],
            9 => ['THE RITUAL', ['ENGAGE ON ALL FRONTS', 'BRING IT DOWN'], 'Hammer and Anvil', 'Terrain Layout 1', true, [1, 4, 5, 6, 7, 8]],
            10 => ['SUPPLY DROP', ['LINEBREAKER', 'BREAK THROUGH'], 'Dawn of War', 'Terrain Layout 2', false, [2, 3, 4, 5, 6, 8]],
            11 => ['LINCHPIN', ['HOLD THE LINE', 'ASSASSINATE'], 'Hammer and Anvil', 'Terrain Layout 3', false, [1, 2, 3, 5, 7, 8]],
            12 => ['BURDEN OF TRUST', ['SECURE OBJECTIVE', 'RETRIEVE ARTEFACT'], 'Dawn of War', 'Terrain Layout 4', true, [1, 3, 4, 6, 7, 8]],
            13 => ['TAKE AND HOLD', ['ENGAGE ON ALL FRONTS', 'REPAIR OBJECTIVE'], 'Hammer and Anvil', 'Terrain Layout 5', false, [2, 3, 5, 6, 7, 8]],
            14 => ['TERRAFORM', ['DEFEND OBJECTIVE', 'LINEBREAKER'], 'Dawn of War', 'Terrain Layout 6', true, [1, 2, 4, 5, 6, 8]],
            15 => ['PURGE THE FOE', ['BRING IT DOWN', 'BREAK THROUGH'], 'Hammer and Anvil', 'Terrain Layout 7', false, [1, 3, 4, 5, 6, 7]],
            16 => ['SCORCHED EARTH', ['ASSASSINATE', 'HOLD THE LINE'], 'Dawn of War', 'Terrain Layout 8', true, [2, 3, 4, 6, 7, 8]],
            17 => ['UNEXPLODED ORDNANCE', ['SECURE OBJECTIVE', 'RETRIEVE ARTEFACT'], 'Hammer and Anvil', 'Terrain Layout 1', false, [1, 2, 5, 6, 7, 8]],
            18 => ['HIDDEN SUPPLIES', ['ENGAGE ON ALL FRONTS', 'REPAIR OBJECTIVE'], 'Dawn of War', 'Terrain Layout 2', true, [1, 3, 4, 5, 7, 8]],
            19 => ['THE RITUAL', ['DEFEND OBJECTIVE', 'LINEBREAKER'], 'Hammer and Anvil', 'Terrain Layout 3', false, [2, 3, 4, 5, 6, 8]],
            20 => ['SUPPLY DROP', ['BRING IT DOWN', 'BREAK THROUGH'], 'Dawn of War', 'Terrain Layout 4', true, [1, 2, 3, 6, 7, 8]],
        ];

        $updated = 0;
        $failed = 0;

        foreach ($poolData as $poolNumber => $data) {
            try {
                [$primaryMissionName, $secondaryMissionNames, $deploymentMode, $terrainLayoutName, $useTwistDeck, $availableTerrainNumbers] = $data;

                $pool = TournamentMissionPool::findByNumber($poolNumber);
                if (!$pool) {
                    $this->line("  ⚠️  Pool $poolNumber non trouvé");
                    $failed++;
                    continue;
                }

                // Trouver la mission primaire
                $primaryMission = PrimaryMission::where('name', $primaryMissionName)->first();
                if (!$primaryMission) {
                    $this->line("  ⚠️  Mission primaire '$primaryMissionName' non trouvée pour pool $poolNumber");
                    $failed++;
                    continue;
                }

                // Trouver la disposition de terrain principale
                $terrainLayout = TerrainLayout::where('name', $terrainLayoutName)->first();
                if (!$terrainLayout) {
                    $this->line("  ⚠️  Terrain '$terrainLayoutName' non trouvé pour pool $poolNumber");
                    $failed++;
                    continue;
                }

                // Mettre à jour le pool
                $pool->update([
                    'primary_mission_id' => $primaryMission->id,
                    'deployment_mode' => $deploymentMode,
                    'terrain_layout_id' => $terrainLayout->id,
                    'use_twist_deck' => $useTwistDeck,
                ]);

                // Associer les missions secondaires
                $secondaryMissionIds = [];
                foreach ($secondaryMissionNames as $index => $secondaryMissionName) {
                    $secondaryMission = SecondaryMission::where('name', $secondaryMissionName)->first();
                    if ($secondaryMission) {
                        $secondaryMissionIds[$secondaryMission->id] = ['order' => $index];
                    }
                }

                if (!empty($secondaryMissionIds)) {
                    $pool->secondaryMissions()->sync($secondaryMissionIds);
                }

                // Associer les dispositions de terrain disponibles
                $availableTerrainIds = [];
                foreach ($availableTerrainNumbers as $index => $terrainNumber) {
                    $terrain = TerrainLayout::where('layout_number', $terrainNumber)->first();
                    if ($terrain) {
                        $availableTerrainIds[$terrain->id] = ['order' => $index];
                    }
                }

                if (!empty($availableTerrainIds)) {
                    $pool->availableTerrainLayouts()->sync($availableTerrainIds);
                }

                $this->line("  ✅ Mis à jour : Pool $poolNumber");
                $updated++;
            } catch (\Exception $e) {
                $this->line("  ❌ Erreur pool $poolNumber : {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info('📊 Résumé :');
        $this->line("  ✅ Mis à jour : $updated");
        $this->line("  ❌ Échoués : $failed");

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
