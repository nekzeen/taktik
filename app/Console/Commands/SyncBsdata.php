<?php

namespace App\Console\Commands;

use App\Services\BsdataImporter;
use Illuminate\Console\Command;

class SyncBsdata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bsdata:sync
                            {--force : Forcer la synchronisation même si récente}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchroniser les données BSData depuis GitHub (Warhammer 40k 10e)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Début de la synchronisation BSData...');
        $this->newLine();

        $importer = new BsdataImporter();

        $this->info('📥 Téléchargement des données depuis GitHub...');
        $this->info('Repository: BSData/wh40k-10e');
        $this->newLine();

        try {
            $results = $importer->importAll();

            $this->newLine();
            $this->info('✅ Synchronisation terminée !');
            $this->newLine();

            $this->table(
                ['Type', 'Nombre'],
                [
                    ['Factions', $results['factions']],
                    ['Unités', $results['units']],
                    ['Détachements', $results['detachments']],
                ]
            );

            if (count($results['errors']) > 0) {
                $this->newLine();
                $this->warn('⚠️  Erreurs rencontrées :');
                foreach ($results['errors'] as $error) {
                    $this->error('  - ' . $error);
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la synchronisation : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
