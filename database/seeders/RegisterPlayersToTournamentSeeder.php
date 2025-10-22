<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tournament;
use App\Models\ArmyList;
use App\Models\Faction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegisterPlayersToTournamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer le tournoi (ID 1 ou le premier disponible)
        $tournament = Tournament::find(1);
        
        if (!$tournament) {
            $this->command->error('❌ Aucun tournoi trouvé ! Créez d\'abord un tournoi.');
            return;
        }

        $this->command->info("📋 Inscription au tournoi : {$tournament->name}");

        // Récupérer tous les joueurs de test
        $players = User::whereIn('email', [
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

        if ($players->isEmpty()) {
            $this->command->error('❌ Aucun joueur de test trouvé ! Exécutez d\'abord TestPlayersSeeder.');
            return;
        }

        // Récupérer toutes les factions disponibles
        $factions = Faction::all();
        
        if ($factions->isEmpty()) {
            $this->command->warn('⚠️  Aucune faction trouvée, les listes seront créées sans faction.');
        }

        $registered = 0;

        foreach ($players as $player) {
            // Vérifier si le joueur n'est pas déjà inscrit
            $existingList = ArmyList::where('tournament_id', $tournament->id)
                ->where('user_id', $player->id)
                ->first();

            if ($existingList) {
                $this->command->warn("⚠️  {$player->name} est déjà inscrit");
                continue;
            }

            // Créer une liste d'armée pour le joueur
            $armyList = ArmyList::create([
                'user_id' => $player->id,
                'tournament_id' => $tournament->id,
                'faction_id' => $factions->isNotEmpty() ? $factions->random()->id : null,
                'points' => 2000,
                'status' => 'pending', // En attente de validation
                'pdf_path' => null, // Pas de PDF uploadé
            ]);

            $registered++;
            $factionName = $armyList->faction ? $armyList->faction->name : 'Aucune faction';
            $this->command->info("✓ {$player->name} inscrit avec {$factionName}");
        }

        $this->command->info("\n🎮 {$registered} joueurs inscrits au tournoi !");
        $this->command->info("📝 Statut : En attente (pending) - doivent uploader leur liste d'armée");
    }
}
