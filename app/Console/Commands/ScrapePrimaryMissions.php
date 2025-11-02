<?php

namespace App\Console\Commands;

use App\Models\PrimaryMission;
use App\Services\WahapediaScraperService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ScrapePrimaryMissions extends Command
{
    protected $signature = 'missions:scrape {--source=chapter-approved-2025-26}';
    protected $description = 'Scraper les missions primaires depuis Wahapedia';

    public function handle()
    {
        $this->info('🕷️  Scraping des missions primaires depuis Wahapedia...');
        $this->info('═══════════════════════════════════════════════════════════');

        try {
            $scraper = new WahapediaScraperService();
            $missions = $scraper->scrapePrimaryMissions();

            if (empty($missions)) {
                $this->warn('⚠️  Aucune mission trouvée !');
                return 1;
            }

            $source = $this->option('source');
            $created = 0;
            $updated = 0;

            foreach ($missions as $missionData) {
                try {
                    $mission = PrimaryMission::updateOrCreate(
                        ['name' => $missionData['name'], 'source' => $source],
                        [
                            'description' => $missionData['description'],
                            'full_text' => $missionData['full_text'],
                            'when_condition' => $missionData['when_condition'],
                            'timing' => $missionData['timing'],
                            'scoring_conditions' => $missionData['scoring_conditions'],
                            'max_vp' => $missionData['max_vp'],
                            'slug' => $missionData['slug'],
                            'edition' => $missionData['edition'],
                            'source' => $source,
                            'is_active' => true,
                        ]
                    );

                    if ($mission->wasRecentlyCreated) {
                        $this->line("  ✅ Créée: {$mission->name}");
                        $created++;
                    } else {
                        $this->line("  🔄 Mise à jour: {$mission->name}");
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $this->error("  ❌ Erreur pour {$missionData['name']}: {$e->getMessage()}");
                }
            }

            $this->info('');
            $this->info('═══════════════════════════════════════════════════════════');
            $this->info("📊 Résultats:");
            $this->info("  ✅ Créées: {$created}");
            $this->info("  🔄 Mises à jour: {$updated}");
            $this->info('═══════════════════════════════════════════════════════════');
            $this->info('✅ Scraping terminé !');

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Erreur: {$e->getMessage()}");
            return 1;
        }
    }
}
