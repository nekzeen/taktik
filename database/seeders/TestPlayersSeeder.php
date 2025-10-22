<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestPlayersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $players = [
            ['name' => 'Jean Dupont', 'email' => 'jean.dupont@test.fr'],
            ['name' => 'Marie Martin', 'email' => 'marie.martin@test.fr'],
            ['name' => 'Pierre Durand', 'email' => 'pierre.durand@test.fr'],
            ['name' => 'Sophie Bernard', 'email' => 'sophie.bernard@test.fr'],
            ['name' => 'Luc Petit', 'email' => 'luc.petit@test.fr'],
            ['name' => 'Emma Dubois', 'email' => 'emma.dubois@test.fr'],
            ['name' => 'Thomas Robert', 'email' => 'thomas.robert@test.fr'],
            ['name' => 'Julie Richard', 'email' => 'julie.richard@test.fr'],
            ['name' => 'Antoine Moreau', 'email' => 'antoine.moreau@test.fr'],
            ['name' => 'Camille Simon', 'email' => 'camille.simon@test.fr'],
        ];

        foreach ($players as $playerData) {
            $user = User::create([
                'name' => $playerData['name'],
                'email' => $playerData['email'],
                'password' => Hash::make('password'), // Mot de passe: password
                'email_verified_at' => now(),
            ]);

            // Assigner le rôle "player"
            $user->assignRole('player');

            $this->command->info("✓ Joueur créé : {$playerData['name']} ({$playerData['email']})");
        }

        $this->command->info("\n🎮 10 joueurs de test créés avec succès !");
        $this->command->info("📧 Email: [prenom.nom]@test.fr");
        $this->command->info("🔑 Mot de passe: password");
    }
}
