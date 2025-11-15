<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tournament;
use App\Models\ArmyList;
use App\Models\Faction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TournamentTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les utilisateurs spéciaux s'ils n'existent pas
        $nek = User::firstOrCreate(
            ['email' => 'nek@test.fr'],
            [
                'name' => 'Nek',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $nek->assignRole('player');

        $nekzeen = User::firstOrCreate(
            ['email' => 'nekzeen@test.fr'],
            [
                'name' => 'Nekzeen',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $nekzeen->assignRole('player');

        // Récupérer tous les joueurs de test
        $testPlayers = User::whereIn('email', [
            'jean.dupont@test.fr',
            'marie.martin@test.fr',
            'pierre.durand@test.fr',
            'sophie.bernard@test.fr',
            'luc.petit@test.fr',
            'emma.dubois@test.fr',
            'thomas.robert@test.fr',
            'julie.richard@test.fr',
            'antoine.moreau@test.fr',
            'camille.simon@test.fr',
        ])->get();

        // Tous les utilisateurs (nek, nekzeen + joueurs de test)
        $allUsers = collect([$nek, $nekzeen])->merge($testPlayers);

        $this->command->info("📋 Création de tournois de test...\n");

        // Récupérer les factions
        $factions = Faction::all();
        if ($factions->isEmpty()) {
            $this->command->warn('⚠️  Aucune faction trouvée');
            return;
        }

        // Créer un tournoi pour chaque utilisateur
        foreach ($allUsers as $creator) {
            $tournamentName = "Tournoi {$creator->name}";
            
            // Vérifier si le tournoi existe déjà
            $existingTournament = Tournament::where('name', $tournamentName)
                ->where('created_by', $creator->id)
                ->first();

            if ($existingTournament) {
                $this->command->warn("⚠️  Tournoi '{$tournamentName}' existe déjà");
                continue;
            }

            // Créer le tournoi
            $tournament = Tournament::create([
                'name' => $tournamentName,
                'description' => "Tournoi de test créé par {$creator->name}",
                'created_by' => $creator->id,
                'status' => 'open',
                'format' => 'swiss',
                'army_size' => 'strike_force',
                'max_players' => 8,
                'start_date' => now()->addDays(7),
                'end_date' => now()->addDays(8),
                'registration_deadline' => now()->addDays(6),
            ]);

            $this->command->info("✓ Tournoi créé : {$tournamentName} (ID: {$tournament->id})");

            // Inscrire tous les autres utilisateurs au tournoi
            $otherUsers = $allUsers->where('id', '!=', $creator->id);
            $registered = 0;

            foreach ($otherUsers as $player) {
                // Vérifier si le joueur n'est pas déjà inscrit
                $existingList = ArmyList::where('tournament_id', $tournament->id)
                    ->where('user_id', $player->id)
                    ->first();

                if ($existingList) {
                    continue;
                }

                // Créer une liste d'armée pour le joueur
                $armyList = ArmyList::create([
                    'user_id' => $player->id,
                    'tournament_id' => $tournament->id,
                    'faction_id' => $factions->random()->id,
                    'points' => 2000,
                    'status' => 'validated', // Pré-validée pour les tests
                    'pdf_path' => null,
                ]);

                $registered++;
            }

            $this->command->info("  └─ {$registered} joueurs inscrits\n");
        }

        $this->command->info("\n🎮 Tournois de test créés avec succès !");
        $this->command->info("📊 Résumé :");
        $this->command->info("  • Nek : créateur d'1 tournoi");
        $this->command->info("  • Nekzeen : créateur d'1 tournoi");
        $this->command->info("  • 10 joueurs de test : créateurs de 10 tournois");
        $this->command->info("  • Total : 12 tournois");
        $this->command->info("\n📧 Identifiants :");
        $this->command->info("  • nek@test.fr / password");
        $this->command->info("  • nekzeen@test.fr / password");
        $this->command->info("  • [prenom.nom]@test.fr / password");
    }
}
