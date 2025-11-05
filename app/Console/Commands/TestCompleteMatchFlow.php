<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PlayerMatch;
use App\Models\User;
use App\Models\PrimaryMission;
use App\Models\TerrainLayout;
use App\Models\TwistMission;
use App\Models\SecondaryMission;
use App\Models\AsymmetricPrimaryMission;

class TestCompleteMatchFlow extends Command
{
    protected $signature = 'test:complete-match-flow';
    protected $description = 'Test complet du flux de match: création, configuration, jeu, sauvegarde';

    public function handle()
    {
        $this->info('🎮 TEST COMPLET DU FLUX DE MATCH');
        $this->info('================================\n');

        try {
            // ÉTAPE 1: Créer les utilisateurs de test
            $this->info('📝 ÉTAPE 1: Préparation des utilisateurs');
            $creator = User::find(4); // Player User
            $opponent = User::find(5); // Nekzeen
            
            if (!$creator || !$opponent) {
                $this->error('❌ Utilisateurs non trouvés');
                return;
            }

            $this->line("  ✅ Créateur: {$creator->name} (ID: {$creator->id})");
            $this->line("  ✅ Adversaire: {$opponent->name} (ID: {$opponent->id})\n");

            // ÉTAPE 2: Créer un match
            $this->info('📝 ÉTAPE 2: Création du match');
            $match = PlayerMatch::create([
                'creator_id' => $creator->id,
                'opponent_id' => $opponent->id,
                'type' => 'competitive',
                'army_points' => '2000',
                'faction' => 'Space Marines',
                'detachment' => 'Ultramarines',
                'notes' => 'Test complet du flux',
                'city' => 'Paris',
                'department' => '75',
                'availability_type' => 'single',
                'available_at' => now()->addDay(),
                'status' => 'confirmed',
                'is_setup_validated' => true,
            ]);

            $this->line("  ✅ Match créé: ID {$match->id}");
            $this->line("  ✅ Status: {$match->status}");
            $this->line("  ✅ Configuration validée: " . ($match->is_setup_validated ? 'Oui' : 'Non') . "\n");

            // ÉTAPE 3: Configurer le match (missions, terrain, péripétie)
            $this->info('📝 ÉTAPE 3: Configuration du match');
            
            $primaryMission = PrimaryMission::first();
            $terrain = TerrainLayout::first();
            $twist = TwistMission::first();
            $secondary = SecondaryMission::first();

            $match->update([
                'primary_mission_id' => $primaryMission->id,
                'secondary_mission_id' => $secondary->id,
                'terrain_layout_id' => $terrain->id,
                'twist_mission_id' => $twist->id,
                'setup_mode' => 'manual',
                'is_setup_complete' => true,
            ]);

            $this->line("  ✅ Mission primaire: {$primaryMission->name}");
            $this->line("  ✅ Mission secondaire: {$secondary->name}");
            $this->line("  ✅ Terrain: {$terrain->name}");
            $this->line("  ✅ Péripétie: {$twist->name}\n");

            // ÉTAPE 4: Simuler le jeu - Créateur met à jour les points
            $this->info('📝 ÉTAPE 4: Jeu du match - Mise à jour des points');
            
            // Créateur marque 15 points
            $match->update([
                'creator_score' => 15,
                'creator_victory_points' => 3,
            ]);
            $this->line("  ✅ Créateur: 15 points, 3 VP");

            // Adversaire marque 12 points
            $match->update([
                'opponent_score' => 12,
                'opponent_victory_points' => 2,
            ]);
            $this->line("  ✅ Adversaire: 12 points, 2 VP\n");

            // ÉTAPE 5: Vérifier la sauvegarde en cours de match
            $this->info('📝 ÉTAPE 5: Vérification de la sauvegarde en cours');
            $match->refresh();
            $this->line("  ✅ Créateur score sauvegardé: {$match->creator_score}");
            $this->line("  ✅ Adversaire score sauvegardé: {$match->opponent_score}");
            $this->line("  ✅ Créateur VP sauvegardés: {$match->creator_victory_points}");
            $this->line("  ✅ Adversaire VP sauvegardés: {$match->opponent_victory_points}\n");

            // ÉTAPE 6: Tester les états tactiques (draft_tactical_state)
            $this->info('📝 ÉTAPE 6: Test des états tactiques');
            $tacticalState = [
                'round' => 1,
                'phase' => 'command',
                'units_deployed' => 5,
                'units_destroyed' => 1,
            ];
            
            $match->update([
                'draft_tactical_state_creator' => $tacticalState,
                'draft_tactical_state_opponent' => $tacticalState,
            ]);
            $this->line("  ✅ État tactique créateur sauvegardé");
            $this->line("  ✅ État tactique adversaire sauvegardé\n");

            // ÉTAPE 7: Finaliser le match
            $this->info('📝 ÉTAPE 7: Finalisation du match');
            $match->determineWinner();
            $match->update([
                'status' => 'completed',
                'played_at' => now(),
            ]);

            $this->line("  ✅ Match finalisé");
            $this->line("  ✅ Gagnant: " . ($match->winner ? $match->winner->name : 'Nul'));
            $this->line("  ✅ Status: {$match->status}\n");

            // ÉTAPE 8: Vérifications finales
            $this->info('📝 ÉTAPE 8: Vérifications finales');
            $match->refresh();

            $checks = [
                'Match créé' => $match->id !== null,
                'Créateur assigné' => $match->creator_id === $creator->id,
                'Adversaire assigné' => $match->opponent_id === $opponent->id,
                'Mission primaire' => $match->primary_mission_id !== null,
                'Mission secondaire' => $match->secondary_mission_id !== null,
                'Terrain' => $match->terrain_layout_id !== null,
                'Péripétie' => $match->twist_mission_id !== null,
                'Points créateur' => $match->creator_score === 15,
                'Points adversaire' => $match->opponent_score === 12,
                'VP créateur' => $match->creator_victory_points === 3,
                'VP adversaire' => $match->opponent_victory_points === 2,
                'État tactique créateur' => $match->draft_tactical_state_creator !== null,
                'État tactique adversaire' => $match->draft_tactical_state_opponent !== null,
                'Gagnant déterminé' => $match->winner_id !== null,
                'Match complété' => $match->status === 'completed',
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
                $this->info("\n✅ TEST COMPLET RÉUSSI!");
                $this->line("   Tous les éléments du flux de match fonctionnent correctement.");
                $this->line("   Match ID: {$match->id}");
                return 0;
            } else {
                $this->error("\n❌ TEST ÉCHOUÉ!");
                $this->error("   Certaines vérifications ont échoué.");
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
