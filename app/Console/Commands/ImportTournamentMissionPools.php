<?php

namespace App\Console\Commands;

use App\Models\TournamentMissionPool;
use App\Models\PrimaryMission;
use App\Models\SecondaryMission;
use App\Models\TerrainLayout;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportTournamentMissionPools extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tournament:import-mission-pools';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importer les 20 missions du pool de tournoi';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎯 Importation des 20 missions du pool de tournoi...');

        // Données des 20 missions du pool (structure de base)
        // À compléter avec les vraies données de Wahapedia
        $pools = [];
        for ($i = 1; $i <= 20; $i++) {
            $pools[] = [
                'pool_number' => $i,
                'name' => "Mission Pool $i",
                'slug' => "mission-pool-$i",
                'description' => "Mission du pool de tournoi $i du Chapter Approved 2025-26",
                'source' => 'chapter-approved-2025-26',
                'is_active' => true,
            ];
        }

        $created = 0;
        $updated = 0;

        foreach ($pools as $poolData) {
            $existing = TournamentMissionPool::findByNumber($poolData['pool_number']);

            if ($existing) {
                $existing->update($poolData);
                $this->line("  ✏️  Mis à jour : {$poolData['name']}");
                $updated++;
            } else {
                TournamentMissionPool::create($poolData);
                $this->line("  ✅ Créé : {$poolData['name']}");
                $created++;
            }
        }

        $this->newLine();
        $this->info('📊 Résumé :');
        $this->line("  ✅ Créés : $created");
        $this->line("  ✏️  Mis à jour : $updated");
        $this->line("  📦 Total : " . TournamentMissionPool::count());
        $this->newLine();
        $this->info('ℹ️  Note : Les associations avec les missions primaires/secondaires et terrains');
        $this->info('   doivent être configurées manuellement via l\'interface d\'administration.');

        return Command::SUCCESS;
    }
}
