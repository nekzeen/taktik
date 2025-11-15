<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\TournamentTestSeeder;

class SeedTestTournaments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:test-tournaments';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Créer des tournois de test pour Nek, Nekzeen et les joueurs de test';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎮 Création des tournois de test...\n');

        $this->call('db:seed', [
            '--class' => TournamentTestSeeder::class,
        ]);

        $this->info('\n✅ Tournois de test créés avec succès !');
        $this->info('\n📊 Résumé :');
        $this->info('  • Nek : créateur d\'1 tournoi');
        $this->info('  • Nekzeen : créateur d\'1 tournoi');
        $this->info('  • 10 joueurs de test : créateurs de 10 tournois');
        $this->info('  • Total : 12 tournois');
        
        $this->info('\n📧 Identifiants de connexion :');
        $this->info('  • nek@test.fr / password');
        $this->info('  • nekzeen@test.fr / password');
        $this->info('  • [prenom.nom]@test.fr / password');
        
        $this->info('\n🔗 Accès aux tournois :');
        $this->info('  • http://localhost/tournaments');
        
        return 0;
    }
}
