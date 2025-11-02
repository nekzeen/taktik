<?php

namespace App\Services;

use App\Models\TournamentMatch;
use App\Models\PlayerMatch;
use App\Models\TwistMission;
use App\Models\TerrainLayout;
use App\Models\PrimaryMission;
use App\Models\AsymmetricPrimaryMission;

class MatchSetupService
{
    /**
     * Tirage au sort aléatoire complet
     */
    public function randomizeMatch($match, string $mode = 'normal'): void
    {
        // Déterminer le type de match
        $isTournamentMatch = $match instanceof TournamentMatch;
        
        if ($mode === 'asymmetric') {
            // Mode asymétrique : utiliser les missions asymétriques
            $randomAsymmetricMission = AsymmetricPrimaryMission::inRandomOrder()->first();
            if ($randomAsymmetricMission) {
                $match->asymmetric_primary_mission_id = $randomAsymmetricMission->id;
            }

            // Terrain aléatoire (tous les terrains disponibles)
            $randomTerrain = TerrainLayout::inRandomOrder()->first();
            if ($randomTerrain) {
                $match->terrain_layout_id = $randomTerrain->id;
            }

            // Zone de déploiement asymétrique aléatoire
            $asymmetricDeploymentModes = ['Hammer and Anvil', 'Dawn of War', 'Incursion'];
            $match->deployment_mode = $asymmetricDeploymentModes[array_rand($asymmetricDeploymentModes)];
        } else {
            // Mode normal : utiliser le pool de missions
            // Nettoyer la mission asymétrique
            $match->asymmetric_primary_mission_id = null;
            
            if ($isTournamentMatch) {
                // Pour les tournois : utiliser le pool de missions
                $randomPool = \App\Models\TournamentMissionPool::where('is_active', true)
                    ->inRandomOrder()
                    ->first();
                
                if ($randomPool) {
                    // Mission primaire (du pool)
                    $match->primary_mission_id = $randomPool->primary_mission_id;

                    // Disposition de terrain (parmi les 6 disponibles du pool)
                    $availableTerrains = $randomPool->availableTerrainLayouts()
                        ->inRandomOrder()
                        ->first();
                    
                    if ($availableTerrains) {
                        $match->terrain_layout_id = $availableTerrains->id;
                    }

                    // Zone de déploiement (du pool)
                    $match->deployment_mode = $randomPool->deployment_mode;
                } else {
                    // Si pas de pool, sélectionner aléatoirement
                    $randomMission = PrimaryMission::inRandomOrder()->first();
                    if ($randomMission) {
                        $match->primary_mission_id = $randomMission->id;
                    }

                    $randomTerrain = TerrainLayout::inRandomOrder()->first();
                    if ($randomTerrain) {
                        $match->terrain_layout_id = $randomTerrain->id;
                    }
                }
            } else {
                // Pour les matchs simples : utiliser aussi un pool de missions
                $randomPool = \App\Models\TournamentMissionPool::where('is_active', true)
                    ->inRandomOrder()
                    ->first();
                
                if ($randomPool) {
                    // Mission primaire (du pool)
                    $match->primary_mission_id = $randomPool->primary_mission_id;

                    // Disposition de terrain (parmi les disponibles du pool)
                    $availableTerrains = $randomPool->availableTerrainLayouts()
                        ->inRandomOrder()
                        ->first();
                    
                    if ($availableTerrains) {
                        $match->terrain_layout_id = $availableTerrains->id;
                    }

                    // Zone de déploiement (du pool)
                    $match->deployment_mode = $randomPool->deployment_mode;
                } else {
                    // Si pas de pool, sélectionner aléatoirement
                    $randomMission = PrimaryMission::inRandomOrder()->first();
                    if ($randomMission) {
                        $match->primary_mission_id = $randomMission->id;
                    }

                    $randomTerrain = TerrainLayout::inRandomOrder()->first();
                    if ($randomTerrain) {
                        $match->terrain_layout_id = $randomTerrain->id;
                    }
                }
            }
        }

        // Péripétie aléatoire (pour tous les types)
        $randomTwist = TwistMission::inRandomOrder()->first();
        if ($randomTwist) {
            $match->twist_mission_id = $randomTwist->id;
        }

        $match->setup_mode = 'random';
        $match->is_setup_complete = true;
        $match->save();
    }

    /**
     * Mise à jour manuelle du tirage
     */
    public function updateMatchSetup(
        $match,
        ?int $primaryMissionId = null,
        ?int $terrainLayoutId = null,
        ?int $twistMissionId = null,
        ?int $asymmetricPrimaryMissionId = null
    ): void
    {
        if ($primaryMissionId !== null) {
            $match->primary_mission_id = $primaryMissionId;
        }

        if ($terrainLayoutId !== null) {
            $match->terrain_layout_id = $terrainLayoutId;
        }

        if ($twistMissionId !== null) {
            $match->twist_mission_id = $twistMissionId;
        }

        if ($asymmetricPrimaryMissionId !== null) {
            $match->asymmetric_primary_mission_id = $asymmetricPrimaryMissionId;
        }

        $match->setup_mode = 'manual';
        $match->is_setup_complete = $match->isSetupValid();
        $match->save();
    }

    /**
     * Réinitialiser le tirage
     */
    public function resetSetup($match): void
    {
        $match->primary_mission_id = null;
        $match->terrain_layout_id = null;
        $match->twist_mission_id = null;
        $match->asymmetric_primary_mission_id = null;
        $match->deployment_mode = null;
        $match->setup_mode = 'random';
        $match->is_setup_complete = false;
        $match->save();
    }

    /**
     * Obtenir les options disponibles pour le tirage
     */
    public function getAvailableOptions($match): array
    {
        $isTournamentMatch = $match instanceof TournamentMatch;
        $missionPool = $isTournamentMatch ? $match->tournament->missionPool : null;

        return [
            'primary_missions' => PrimaryMission::all(),
            'terrain_layouts' => $missionPool 
                ? $missionPool->availableTerrainLayouts()->get()
                : TerrainLayout::all(),
            'twist_missions' => TwistMission::all(),
            'asymmetric_primary_missions' => AsymmetricPrimaryMission::all(),
        ];
    }
}
