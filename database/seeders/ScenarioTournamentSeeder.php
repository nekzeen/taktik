<?php

namespace Database\Seeders;

use App\Models\ArmyList;
use App\Models\Faction;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ScenarioTournamentSeeder extends Seeder
{
    private function upsertTestUser(string $email, string $name): User
    {
        $user = User::withTrashed()->where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'email' => $email,
                'name' => $name,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        } else {
            if (method_exists($user, 'restore') && $user->trashed()) {
                $user->restore();
            }

            $user->name = $name;
            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
            }
            $user->save();
        }

        if (!$user->hasRole('player')) {
            $user->assignRole('player');
        }

        return $user;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $factions = Faction::all();
        if ($factions->isEmpty()) {
            $this->command->warn('⚠️  Aucune faction trouvée. Seeder interrompu.');
            return;
        }

        $nek = $this->upsertTestUser('nek@test.fr', 'Nek');

        $opponentsData = [
            ['name' => 'Test Victoire', 'email' => 'test.victoire@test.fr'],
            ['name' => 'Test Défaite', 'email' => 'test.defaite@test.fr'],
            ['name' => 'Test Nul', 'email' => 'test.nul@test.fr'],
            ['name' => 'Test Abandon', 'email' => 'test.abandon@test.fr'],
            ['name' => 'Test Table Rase', 'email' => 'test.table_rase@test.fr'],
        ];

        $opponents = collect($opponentsData)->map(function (array $data) {
            return $this->upsertTestUser($data['email'], $data['name']);
        });

        $tournamentName = 'Tournoi Scénarios - Nek';

        $tournament = Tournament::firstOrCreate(
            ['name' => $tournamentName, 'created_by' => $nek->id],
            [
                'description' => 'Tournoi de test pour couvrir tous les scénarios de scoring (victoire/défaite/nul/abandon/table rase).',
                'status' => 'open',
                'format' => 'league',
                'army_size' => 'strike_force',
                'max_players' => 16,
                'start_date' => now()->addDays(1),
                'end_date' => now()->addDays(2),
                'registration_deadline' => now()->addHours(23),
            ]
        );

        $this->command->info("✓ Tournoi prêt : {$tournament->name} (ID: {$tournament->id})");

        // Assurer les inscriptions (ArmyLists validées)
        $allPlayers = collect([$nek])->merge($opponents);
        foreach ($allPlayers as $player) {
            $existingList = ArmyList::where('tournament_id', $tournament->id)
                ->where('user_id', $player->id)
                ->first();

            if ($existingList) {
                if ($existingList->status !== 'validated') {
                    $existingList->status = 'validated';
                    $existingList->save();
                }
                continue;
            }

            ArmyList::create([
                'user_id' => $player->id,
                'tournament_id' => $tournament->id,
                'faction_id' => $factions->random()->id,
                'points' => 2000,
                'status' => 'validated',
                'pdf_path' => null,
            ]);
        }

        // Nettoyer les matchs existants du tournoi pour éviter les doublons
        TournamentMatch::where('tournament_id', $tournament->id)->delete();

        // Recharger les ArmyLists pour créer des matchs valides
        $armyListsByUser = ArmyList::where('tournament_id', $tournament->id)
            ->whereIn('user_id', $allPlayers->pluck('id')->all())
            ->get()
            ->keyBy('user_id');

        $scenarios = [
            [
                'label' => 'Victoire (scores)',
                'opponent' => $opponents->firstWhere('email', 'test.victoire@test.fr'),
            ],
            [
                'label' => 'Défaite (scores)',
                'opponent' => $opponents->firstWhere('email', 'test.defaite@test.fr'),
            ],
            [
                'label' => 'Nul (scores égaux)',
                'opponent' => $opponents->firstWhere('email', 'test.nul@test.fr'),
            ],
            [
                'label' => 'J\'abandonne le combat',
                'opponent' => $opponents->firstWhere('email', 'test.abandon@test.fr'),
            ],
            [
                'label' => 'Je suis table rase',
                'opponent' => $opponents->firstWhere('email', 'test.table_rase@test.fr'),
            ],
        ];

        $round = 1;
        $table = 1;

        foreach ($scenarios as $scenario) {
            $opponent = $scenario['opponent'];
            if (!$opponent) {
                continue;
            }

            $p1List = $armyListsByUser->get($nek->id);
            $p2List = $armyListsByUser->get($opponent->id);

            if (!$p1List || !$p2List) {
                $this->command->warn("⚠️  ArmyList manquante pour {$nek->email} ou {$opponent->email}");
                continue;
            }

            $match = TournamentMatch::create([
                'tournament_id' => $tournament->id,
                'round' => $round,
                'table_number' => $table,
                'player1_id' => $nek->id,
                'player1_army_list_id' => $p1List->id,
                'player2_id' => $opponent->id,
                'player2_army_list_id' => $p2List->id,
                'status' => 'pending',
                'notes' => $scenario['label'],
            ]);

            $match->randomizeSetup();

            $this->command->info("✓ Match créé (ID: {$match->id}) - {$scenario['label']} : Nek vs {$opponent->name}");
            $table++;
        }

        $this->command->info("\n📧 Identifiants :");
        $this->command->info("  • nek@test.fr / password");
        $this->command->info("  • test.*@test.fr / password");
        $this->command->info("\n🔗 Tournoi : /tournaments/{$tournament->id}/matches");
    }
}
