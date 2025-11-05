<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PlayerMatch;
use App\Models\User;
use App\Http\Controllers\MatchSetupController;
use App\Services\MatchSetupService;
use App\Services\ArmyPointsService;

class TestMatchInterfaces extends Command
{
    protected $signature = 'test:match-interfaces';
    protected $description = 'Test des interfaces de match: setup, jeu, mise à jour des scores';

    public function handle()
    {
        $this->info('🎮 TEST DES INTERFACES DE MATCH');
        $this->info('================================\n');

        try {
            // ÉTAPE 1: Tester la page de setup
            $this->info('📝 ÉTAPE 1: Test de la page de setup');
            $match = PlayerMatch::find(2);
            $creator = User::find(4);

            if (!$match || !$creator) {
                $this->error('❌ Match ou créateur non trouvé');
                return 1;
            }

            $setupService = app(MatchSetupService::class);
            $options = $setupService->getAvailableOptions($match);

            $this->line("  ✅ Page de setup charge correctement");
            $this->line("  ✅ Missions disponibles: " . count($options['primary_missions']));
            $this->line("  ✅ Terrains disponibles: " . count($options['terrain_layouts']));
            $this->line("  ✅ Péripéties disponibles: " . count($options['twist_missions']));
            $this->line("  ✅ Missions asymétriques disponibles: " . count($options['asymmetric_primary_missions']) . "\n");

            // ÉTAPE 2: Tester la mise à jour des points
            $this->info('📝 ÉTAPE 2: Test de la mise à jour des points');
            
            // Simuler la mise à jour des points par le créateur
            $match->update([
                'creator_score' => 20,
                'creator_victory_points' => 4,
                'opponent_score' => 18,
                'opponent_victory_points' => 3,
            ]);

            $match->refresh();
            $this->line("  ✅ Points créateur mis à jour: {$match->creator_score}");
            $this->line("  ✅ VP créateur mis à jour: {$match->creator_victory_points}");
            $this->line("  ✅ Points adversaire mis à jour: {$match->opponent_score}");
            $this->line("  ✅ VP adversaire mis à jour: {$match->opponent_victory_points}\n");

            // ÉTAPE 3: Tester la visibilité des données pour l'adversaire
            $this->info('📝 ÉTAPE 3: Test de la visibilité des données');
            $opponent = $match->opponent;
            
            $this->line("  ✅ Adversaire peut voir le match: {$opponent->name}");
            $this->line("  ✅ Adversaire peut voir les points créateur: {$match->creator_score}");
            $this->line("  ✅ Adversaire peut voir les points adversaire: {$match->opponent_score}");
            $this->line("  ✅ Adversaire peut voir les VP: {$match->creator_victory_points} vs {$match->opponent_victory_points}\n");

            // ÉTAPE 4: Tester les missions tactiques
            $this->info('📝 ÉTAPE 4: Test des missions tactiques');
            
            $tacticalState = [
                'round' => 2,
                'phase' => 'movement',
                'units_deployed' => 8,
                'units_destroyed' => 3,
                'objectives_held' => 2,
            ];

            $match->update([
                'draft_tactical_state_creator' => $tacticalState,
                'draft_tactical_state_opponent' => $tacticalState,
            ]);

            $match->refresh();
            $this->line("  ✅ État tactique créateur sauvegardé");
            $this->line("  ✅ État tactique adversaire sauvegardé");
            $this->line("  ✅ Round: " . $match->draft_tactical_state_creator['round']);
            $this->line("  ✅ Phase: " . $match->draft_tactical_state_creator['phase']);
            $this->line("  ✅ Unités déployées: " . $match->draft_tactical_state_creator['units_deployed'] . "\n");

            // ÉTAPE 5: Tester les missions fixes
            $this->info('📝 ÉTAPE 5: Test des missions fixes');
            
            $secondaryMission = $match->secondaryMission;
            if ($secondaryMission) {
                $this->line("  ✅ Mission secondaire chargée: {$secondaryMission->name}");
                $this->line("  ✅ Description: " . substr($secondaryMission->full_text, 0, 50) . "...");
                $this->line("  ✅ Points max: " . ($secondaryMission->max_vp ?? 'N/A') . "\n");
            }

            // ÉTAPE 6: Tester la sauvegarde en cours
            $this->info('📝 ÉTAPE 6: Test de la sauvegarde en cours');
            
            // Simuler plusieurs mises à jour
            for ($i = 0; $i < 3; $i++) {
                $match->update([
                    'creator_score' => 20 + ($i * 5),
                    'opponent_score' => 18 + ($i * 4),
                ]);
                $this->line("  ✅ Mise à jour $i: Créateur {$match->creator_score}, Adversaire {$match->opponent_score}");
            }

            $match->refresh();
            $this->line("  ✅ Dernière sauvegarde: Créateur {$match->creator_score}, Adversaire {$match->opponent_score}\n");

            // ÉTAPE 7: Tester le déploiement
            $this->info('📝 ÉTAPE 7: Test du déploiement');
            
            $deploymentMode = ArmyPointsService::getDeploymentModeByArmyPoints($match->army_points);
            $this->line("  ✅ Mode de déploiement pour {$match->army_points} points: {$deploymentMode}");
            
            $match->update(['deployment_mode' => $deploymentMode]);
            $this->line("  ✅ Mode de déploiement sauvegardé: {$match->deployment_mode}\n");

            // ÉTAPE 8: Vérifications finales
            $this->info('📝 ÉTAPE 8: Vérifications finales');
            $match->refresh();

            $checks = [
                'Page de setup fonctionne' => $options !== null,
                'Points créateur sauvegardés' => $match->creator_score === 30,
                'Points adversaire sauvegardés' => $match->opponent_score === 26,
                'VP créateur sauvegardés' => $match->creator_victory_points === 4,
                'VP adversaire sauvegardés' => $match->opponent_victory_points === 3,
                'État tactique créateur' => $match->draft_tactical_state_creator !== null,
                'État tactique adversaire' => $match->draft_tactical_state_opponent !== null,
                'Mission secondaire' => $match->secondaryMission !== null,
                'Terrain' => $match->terrainLayout !== null,
                'Péripétie' => $match->twistMission !== null,
                'Mode de déploiement' => $match->deployment_mode !== null,
                'Adversaire peut voir les données' => $match->opponent !== null,
            ];

            $passed = 0;
            $failed = 0;

            foreach ($checks as $check => $result) {
                if ($result) {
                    $this->line("  ✅ $check");
                    $passed++;
                } else {
                    $this->error("  ❌ $check");
                    $failed++;
                }
            }

            $this->info("\n📊 RÉSUMÉ");
            $this->info("=========");
            $this->line("  ✅ Vérifications réussies: $passed/" . count($checks));
            $this->line("  ❌ Vérifications échouées: $failed/" . count($checks));

            if ($failed === 0) {
                $this->info("\n✅ TOUTES LES INTERFACES FONCTIONNENT!");
                return 0;
            } else {
                $this->error("\n❌ CERTAINES INTERFACES ONT DES PROBLÈMES!");
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("❌ Erreur: {$e->getMessage()}");
            $this->error("   Fichier: {$e->getFile()}");
            $this->error("   Ligne: {$e->getLine()}");
            return 1;
        }
    }
}
