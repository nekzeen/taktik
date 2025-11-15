<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\CreateTestTournamentsOnly;

class CreateTournamentsOnly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tournaments:create-test';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Créer les tournois de test sans supprimer les données existantes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎮 Création des tournois de test...\n');

        $this->call('db:seed', [
            '--class' => CreateTestTournamentsOnly::class,
        ]);

        $this->info('\n✅ Tournois de test créés avec succès !');
        $this->info('\n📊 Les données existantes ont été conservées.');
        $this->info('\n🔗 Accès aux tournois : http://localhost/tournaments');
        
        return 0;
    }
}
