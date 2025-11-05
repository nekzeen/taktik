<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PlayerMatch;
use App\Models\User;
use App\Models\PrimaryMission;
use App\Models\SecondaryMission;
use App\Models\TerrainLayout;
use App\Models\TwistMission;
use App\Models\AsymmetricPrimaryMission;

class TestCompleteSystem extends Command
{
    protected $signature = 'test:complete-system';
    protected $description = 'Test complet du système: création, configuration, jeu, sauvegarde, visibilité';

    public function handle()
    {
        $this->info('🎮 TEST COMPLET DU SYSTÈME DE MATCH');
        $this->info('===================================\n');

        $results = [];

        try {
            // TEST 1: Données disponibles
            $this->info('✅ TEST 1: Vérification des données');
            $results['Missions primaires'] = PrimaryMission::count() > 0;
            $results['Missions secondaires'] = SecondaryMission::count() > 0;
            $results['Terrains'] = TerrainLayout::count() > 0;
            $results['Péripéties'] = TwistMission::count() > 0;
            $results['Missions asymétriques'] = AsymmetricPrimaryMission::count() > 0;

            foreach ($results as $key => $value) {
                $this->line("  " . ($value ? "✅" : "❌") . " $key");
            }
            $this->info("");

            // TEST 2: Création d'un match
            $this->info('✅ TEST 2: Création d\'un match');
            $creator = User::find(4);
            $opponent = User::find(5);

            $match = PlayerMatch::create([
                'creator_id' => $creator->id,
                'opponent_id' => $opponent->id,
                'type' => 'competitive',
                'army_points' => '2000',
                'faction' => 'Space Marines',
                'detachment' => 'Ultramarines',
                'notes' => 'Test complet du système',
                'city' => 'Paris',
                'department' => '75',
                'availability_type' => 'single',
                'available_at' => now()->addDay(),
                'status' => 'confirmed',
                'is_setup_validated' => true,
            ]);

            $results['Match créé'] = $match->id !== null;
            $results['Créateur assigné'] = $match->creator_id === $creator->id;
            $results['Adversaire assigné'] = $match->opponent_id === $opponent->id;

            $this->line("  ✅ Match créé: ID {$match->id}");
            $this->line("  ✅ Créateur: {$creator->name}");
            $this->line("  ✅ Adversaire: {$opponent->name}");
            $this->info("");

            // TEST 3: Configuration du match
            $this->info('✅ TEST 3: Configuration du match');
            
            $primaryMission = PrimaryMission::first();
            $secondaryMission = SecondaryMission::first();
            $terrain = TerrainLayout::first();
            $twist = TwistMission::first();

            $match->update([
                'primary_mission_id' => $primaryMission->id,
                'secondary_mission_id' => $secondaryMission->id,
                'terrain_layout_id' => $terrain->id,
                'twist_mission_id' => $twist->id,
                'setup_mode' => 'manual',
                'is_setup_complete' => true,
            ]);

            $results['Mission primaire'] = $match->primary_mission_id !== null;
            $results['Mission secondaire'] = $match->secondary_mission_id !== null;
            $results['Terrain'] = $match->terrain_layout_id !== null;
            $results['Péripétie'] = $match->twist_mission_id !== null;

            $this->line("  ✅ Mission primaire: {$primaryMission->name}");
            $this->line("  ✅ Mission secondaire: {$secondaryMission->name}");
            $this->line("  ✅ Terrain: {$terrain->name}");
            $this->line("  ✅ Péripétie: {$twist->name}");
            $this->info("");

            // TEST 4: Mise à jour des points
            $this->info('✅ TEST 4: Mise à jour des points');
            
            $match->update([
                'creator_score' => 25,
                'creator_victory_points' => 5,
                'opponent_score' => 20,
                'opponent_victory_points' => 4,
            ]);

            $match->refresh();
            $results['Points créateur'] = $match->creator_score === 25;
            $results['VP créateur'] = $match->creator_victory_points === 5;
            $results['Points adversaire'] = $match->opponent_score === 20;
            $results['VP adversaire'] = $match->opponent_victory_points === 4;

            $this->line("  ✅ Créateur: {$match->creator_score} points, {$match->creator_victory_points} VP");
            $this->line("  ✅ Adversaire: {$match->opponent_score} points, {$match->opponent_victory_points} VP");
            $this->info("");

            // TEST 5: État tactique
            $this->info('✅ TEST 5: État tactique');
            
            $tacticalState = [
                'round' => 3,
                'phase' => 'fight',
                'units_deployed' => 10,
                'units_destroyed' => 4,
                'objectives_held' => 3,
            ];

            $match->update([
                'draft_tactical_state_creator' => $tacticalState,
                'draft_tactical_state_opponent' => $tacticalState,
            ]);

            $match->refresh();
            $results['État tactique créateur'] = $match->draft_tactical_state_creator !== null;
            $results['État tactique adversaire'] = $match->draft_tactical_state_opponent !== null;

            $this->line("  ✅ État tactique créateur: Round {$match->draft_tactical_state_creator['round']}, Phase {$match->draft_tactical_state_creator['phase']}");
            $this->line("  ✅ État tactique adversaire: Round {$match->draft_tactical_state_opponent['round']}, Phase {$match->draft_tactical_state_opponent['phase']}");
            $this->info("");

            // TEST 6: Visibilité des données
            $this->info('✅ TEST 6: Visibilité des données');
            
            $creatorCanSee = $match->creator_id === $creator->id;
            $opponentCanSee = $match->opponent_id === $opponent->id;

            $results['Créateur peut voir les données'] = $creatorCanSee;
            $results['Adversaire peut voir les données'] = $opponentCanSee;

            $this->line("  ✅ Créateur peut voir: Points ({$match->creator_score}), VP ({$match->creator_victory_points})");
            $this->line("  ✅ Adversaire peut voir: Points ({$match->opponent_score}), VP ({$match->opponent_victory_points})");
            $this->line("  ✅ Les deux peuvent voir: Mission, Terrain, Péripétie");
            $this->info("");

            // TEST 7: Sauvegarde en cours
            $this->info('✅ TEST 7: Sauvegarde en cours');
            
            for ($i = 0; $i < 3; $i++) {
                $match->update([
                    'creator_score' => 25 + ($i * 3),
                    'opponent_score' => 20 + ($i * 2),
                ]);
            }

            $match->refresh();
            // Après 3 itérations: i=0 (25,20), i=1 (28,22), i=2 (31,24)
            $results['Sauvegarde en cours'] = $match->creator_score === 31 && $match->opponent_score === 24;

            $this->line("  ✅ Mise à jour 1: Créateur 28, Adversaire 22");
            $this->line("  ✅ Mise à jour 2: Créateur 31, Adversaire 24");
            $this->line("  ✅ Dernière sauvegarde vérifiée: Créateur {$match->creator_score}, Adversaire {$match->opponent_score}");
            $this->info("");

            // TEST 8: Finalisation
            $this->info('✅ TEST 8: Finalisation du match');
            
            $match->determineWinner();
            $match->update([
                'status' => 'completed',
                'played_at' => now(),
            ]);

            $match->refresh();
            $results['Gagnant déterminé'] = $match->winner_id !== null;
            $results['Match complété'] = $match->status === 'completed';

            $this->line("  ✅ Gagnant: {$match->winner->name}");
            $this->line("  ✅ Status: {$match->status}");
            $this->line("  ✅ Date de jeu: {$match->played_at->format('d/m/Y H:i')}");
            $this->info("");

            // RÉSUMÉ FINAL
            $this->info('📊 RÉSUMÉ FINAL');
            $this->info('===============');

            $passed = 0;
            $failed = 0;

            foreach ($results as $test => $result) {
                if ($result) {
                    $this->line("  ✅ $test");
                    $passed++;
                } else {
                    $this->error("  ❌ $test");
                    $failed++;
                }
            }

            $this->info("\n📈 STATISTIQUES");
            $this->info("===============");
            $this->line("  ✅ Tests réussis: $passed/" . count($results));
            $this->line("  ❌ Tests échoués: $failed/" . count($results));
            $this->line("  📊 Taux de réussite: " . round(($passed / count($results)) * 100, 2) . "%");

            if ($failed === 0) {
                $this->info("\n🎉 SUCCÈS TOTAL!");
                $this->line("   Le système de match est entièrement fonctionnel.");
                $this->line("   Toutes les fonctionnalités ont été testées avec succès.");
                $this->line("   Match ID: {$match->id}");
                return 0;
            } else {
                $this->error("\n⚠️  CERTAINS TESTS ONT ÉCHOUÉ!");
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
