<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Faction;
use App\Services\BsdataImporter;

class SyncBsdataFaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bsdata:sync-faction {faction_id : ID de la faction}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchroniser les données BSData pour une faction spécifique';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $factionId = $this->argument('faction_id');
        
        $faction = Faction::find($factionId);
        if (!$faction) {
            $this->error("Faction avec l'ID {$factionId} non trouvée");
            return 1;
        }
        
        $this->info("Synchronisation de {$faction->name}...");
        
        try {
            $importer = app(BsdataImporter::class);
            $importer->importFaction($faction);
            
            $this->info("✅ Synchronisation de {$faction->name} terminée avec succès");
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la synchronisation : " . $e->getMessage());
            return 1;
        }
    }
}
