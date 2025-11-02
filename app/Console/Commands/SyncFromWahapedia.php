<?php

namespace App\Console\Commands;

use App\Services\WahapediaDataService;
use App\Models\PrimaryMission;
use App\Models\SecondaryMission;
use App\Models\TwistMission;
use App\Models\AsymmetricPrimaryMission;
use App\Models\StrikeForceDeploymentCard;
use App\Models\IncursionDeploymentCard;
use App\Models\AsymmetricWarfareDeploymentCard;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncFromWahapedia extends Command
{
    protected $signature = 'missions:sync-wahapedia {type? : Type de données (primary|secondary|twist|asymmetric|strike-force|incursion|asymmetric-warfare|all)} {--mode=merge : Mode de mise à jour (merge|replace|add-only)} {--source=chapter-approved-2025-26 : Source des données}';
    protected $description = 'Synchroniser les données depuis Wahapedia';

    protected $wahapediaService;

    public function __construct(WahapediaDataService $wahapediaService)
    {
        parent::__construct();
        $this->wahapediaService = $wahapediaService;
    }

    public function handle()
    {
        $type = $this->argument('type') ?? 'all';
        $mode = $this->option('mode');
        $source = $this->option('source');

        $this->info('🔄 Synchronisation depuis Wahapedia...');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info("Type : {$type}");
        $this->info("Mode : {$mode}");
        $this->info("Source : {$source}");
        $this->info('═══════════════════════════════════════════════════════════');

        if ($type === 'all') {
            $this->syncAll($mode, $source);
        } else {
            match ($type) {
                'primary' => $this->syncPrimaryMissions($mode, $source),
                'secondary' => $this->syncSecondaryMissions($mode, $source),
                'twist' => $this->syncTwistMissions($mode, $source),
                'asymmetric' => $this->syncAsymmetricPrimaryMissions($mode, $source),
                'strike-force' => $this->syncStrikeForceMissions($mode, $source),
                'incursion' => $this->syncIncursionMissions($mode, $source),
                'asymmetric-warfare' => $this->syncAsymmetricWarfareMissions($mode, $source),
                default => $this->error("Type inconnu : {$type}"),
            };
        }

        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('✅ Synchronisation terminée !');
        $this->info('📝 Exécutez ensuite : php artisan missions:translate --locale=fr');

        return 0;
    }

    protected function syncAll($mode, $source)
    {
        $this->info('📥 Synchronisation de TOUTES les données...');
        
        $this->syncPrimaryMissions($mode, $source);
        $this->syncSecondaryMissions($mode, $source);
        $this->syncTwistMissions($mode, $source);
        $this->syncAsymmetricPrimaryMissions($mode, $source);
        $this->syncStrikeForceMissions($mode, $source);
        $this->syncIncursionMissions($mode, $source);
        $this->syncAsymmetricWarfareMissions($mode, $source);
    }

    protected function syncPrimaryMissions($mode, $source)
    {
        $this->info('📝 Synchronisation des missions primaires depuis Wahapedia...');
        
        $missions = $this->wahapediaService->getPrimaryMissions();
        
        if (empty($missions)) {
            $this->warn('  ⚠️  Aucune mission trouvée sur Wahapedia');
            return;
        }

        if ($mode === 'replace') {
            PrimaryMission::where('source', $source)->delete();
            $this->line('  🗑️  Missions existantes supprimées');
        }

        $count = 0;
        foreach ($missions as $mission) {
            try {
                PrimaryMission::updateOrCreate(
                    ['name' => $mission['name'], 'source' => $source],
                    [
                        'description' => $mission['description'] ?? '',
                        'full_text' => $mission['full_text'] ?? '',
                        'when_condition' => $mission['when_condition'] ?? 'At the start of the game',
                        'timing' => $mission['timing'] ?? 'Ongoing',
                        'scoring_conditions' => $mission['scoring_conditions'] ?? '',
                        'max_vp' => $mission['max_vp'] ?? 10,
                        'slug' => Str::slug($mission['name']),
                        'edition' => '10ed',
                        'is_active' => true,
                    ]
                );
                $count++;
            } catch (\Exception $e) {
                $this->error("  ❌ Erreur pour {$mission['name']}: {$e->getMessage()}");
            }
        }

        $this->line("  ✅ {$count} missions synchronisées");
    }

    protected function syncSecondaryMissions($mode, $source)
    {
        $this->info('📝 Synchronisation des missions secondaires depuis Wahapedia...');
        
        $missions = $this->wahapediaService->getSecondaryMissions();
        
        if (empty($missions)) {
            $this->warn('  ⚠️  Aucune mission trouvée sur Wahapedia');
            return;
        }

        if ($mode === 'replace') {
            SecondaryMission::where('source', $source)->delete();
            $this->line('  🗑️  Missions existantes supprimées');
        }

        $count = 0;
        foreach ($missions as $mission) {
            try {
                SecondaryMission::updateOrCreate(
                    ['name' => $mission['name'], 'source' => $source],
                    [
                        'description' => $mission['description'] ?? '',
                        'full_text' => $mission['full_text'] ?? '',
                        'when_drawn' => $mission['when_drawn'] ?? 'At the start of the game',
                        'timing' => $mission['timing'] ?? 'Ongoing',
                        'scoring_conditions' => $mission['scoring_conditions'] ?? '',
                        'max_vp' => $mission['max_vp'] ?? 5,
                        'slug' => Str::slug($mission['name']),
                        'edition' => '10ed',
                        'is_active' => true,
                    ]
                );
                $count++;
            } catch (\Exception $e) {
                $this->error("  ❌ Erreur pour {$mission['name']}: {$e->getMessage()}");
            }
        }

        $this->line("  ✅ {$count} missions synchronisées");
    }

    protected function syncTwistMissions($mode, $source)
    {
        $this->info('📝 Synchronisation des péripéties depuis Wahapedia...');
        
        $missions = $this->wahapediaService->getTwistMissions();
        
        if (empty($missions)) {
            $this->warn('  ⚠️  Aucune péripétie trouvée sur Wahapedia');
            return;
        }

        if ($mode === 'replace') {
            TwistMission::where('source', $source)->delete();
            $this->line('  🗑️  Péripéties existantes supprimées');
        }

        $count = 0;
        foreach ($missions as $mission) {
            try {
                TwistMission::updateOrCreate(
                    ['name' => $mission['name'], 'source' => $source],
                    [
                        'description' => $mission['description'] ?? '',
                        'full_text' => $mission['full_text'] ?? '',
                        'when_drawn' => $mission['when_drawn'] ?? 'At the start of the game',
                        'effect' => $mission['effect'] ?? '',
                        'timing' => $mission['timing'] ?? 'Ongoing',
                        'slug' => Str::slug($mission['name']),
                        'edition' => '10ed',
                        'is_active' => true,
                    ]
                );
                $count++;
            } catch (\Exception $e) {
                $this->error("  ❌ Erreur pour {$mission['name']}: {$e->getMessage()}");
            }
        }

        $this->line("  ✅ {$count} péripéties synchronisées");
    }

    protected function syncAsymmetricPrimaryMissions($mode, $source)
    {
        $this->info('📝 Synchronisation des missions primaires asymétriques depuis Wahapedia...');
        
        $missions = $this->wahapediaService->getAsymmetricPrimaryMissions();
        
        if (empty($missions)) {
            $this->warn('  ⚠️  Aucune mission trouvée sur Wahapedia');
            return;
        }

        if ($mode === 'replace') {
            AsymmetricPrimaryMission::where('source', $source)->delete();
            $this->line('  🗑️  Missions existantes supprimées');
        }

        $count = 0;
        foreach ($missions as $mission) {
            try {
                AsymmetricPrimaryMission::updateOrCreate(
                    ['name' => $mission['name'], 'source' => $source],
                    [
                        'description' => $mission['description'] ?? '',
                        'full_text' => $mission['full_text'] ?? '',
                        'objectives' => $mission['objectives'] ?? '',
                        'attacker_objective' => $mission['attacker_objective'] ?? '',
                        'defender_objective' => $mission['defender_objective'] ?? '',
                        'timing' => $mission['timing'] ?? 'Ongoing',
                        'scoring_conditions' => $mission['scoring_conditions'] ?? '',
                        'max_vp' => $mission['max_vp'] ?? 10,
                        'slug' => Str::slug($mission['name']),
                        'edition' => '10ed',
                        'is_active' => true,
                    ]
                );
                $count++;
            } catch (\Exception $e) {
                $this->error("  ❌ Erreur pour {$mission['name']}: {$e->getMessage()}");
            }
        }

        $this->line("  ✅ {$count} missions synchronisées");
    }

    protected function syncStrikeForceMissions($mode, $source)
    {
        $this->info('📝 Synchronisation des cartes Strike Force depuis Wahapedia...');
        
        $missions = $this->wahapediaService->getStrikeForceMissions();
        
        if (empty($missions)) {
            $this->warn('  ⚠️  Aucune carte trouvée sur Wahapedia');
            return;
        }

        if ($mode === 'replace') {
            StrikeForceDeploymentCard::where('source', $source)->delete();
            $this->line('  🗑️  Cartes existantes supprimées');
        }

        $count = 0;
        foreach ($missions as $mission) {
            try {
                StrikeForceDeploymentCard::updateOrCreate(
                    ['name' => $mission['name'], 'source' => $source],
                    [
                        'description' => $mission['description'] ?? '',
                        'full_text' => $mission['full_text'] ?? '',
                        'image_url' => $mission['image_url'] ?? null,
                        'image_filename' => $mission['image_filename'] ?? null,
                        'slug' => Str::slug($mission['name']),
                        'edition' => '10ed',
                        'is_active' => true,
                    ]
                );
                $count++;
            } catch (\Exception $e) {
                $this->error("  ❌ Erreur pour {$mission['name']}: {$e->getMessage()}");
            }
        }

        $this->line("  ✅ {$count} cartes synchronisées");
    }

    protected function syncIncursionMissions($mode, $source)
    {
        $this->info('📝 Synchronisation des cartes Incursions depuis Wahapedia...');
        
        $missions = $this->wahapediaService->getIncursionMissions();
        
        if (empty($missions)) {
            $this->warn('  ⚠️  Aucune carte trouvée sur Wahapedia');
            return;
        }

        if ($mode === 'replace') {
            IncursionDeploymentCard::where('source', $source)->delete();
            $this->line('  🗑️  Cartes existantes supprimées');
        }

        $count = 0;
        foreach ($missions as $mission) {
            try {
                IncursionDeploymentCard::updateOrCreate(
                    ['name' => $mission['name'], 'source' => $source],
                    [
                        'description' => $mission['description'] ?? '',
                        'full_text' => $mission['full_text'] ?? '',
                        'image_url' => $mission['image_url'] ?? null,
                        'image_filename' => $mission['image_filename'] ?? null,
                        'slug' => Str::slug($mission['name']),
                        'edition' => '10ed',
                        'is_active' => true,
                    ]
                );
                $count++;
            } catch (\Exception $e) {
                $this->error("  ❌ Erreur pour {$mission['name']}: {$e->getMessage()}");
            }
        }

        $this->line("  ✅ {$count} cartes synchronisées");
    }

    protected function syncAsymmetricWarfareMissions($mode, $source)
    {
        $this->info('📝 Synchronisation des cartes Guerre Asymétrique depuis Wahapedia...');
        
        $missions = $this->wahapediaService->getAsymmetricWarfareMissions();
        
        if (empty($missions)) {
            $this->warn('  ⚠️  Aucune carte trouvée sur Wahapedia');
            return;
        }

        if ($mode === 'replace') {
            AsymmetricWarfareDeploymentCard::where('source', $source)->delete();
            $this->line('  🗑️  Cartes existantes supprimées');
        }

        $count = 0;
        foreach ($missions as $mission) {
            try {
                AsymmetricWarfareDeploymentCard::updateOrCreate(
                    ['name' => $mission['name'], 'source' => $source],
                    [
                        'description' => $mission['description'] ?? '',
                        'full_text' => $mission['full_text'] ?? '',
                        'image_url' => $mission['image_url'] ?? null,
                        'image_filename' => $mission['image_filename'] ?? null,
                        'slug' => Str::slug($mission['name']),
                        'edition' => '10ed',
                        'is_active' => true,
                    ]
                );
                $count++;
            } catch (\Exception $e) {
                $this->error("  ❌ Erreur pour {$mission['name']}: {$e->getMessage()}");
            }
        }

        $this->line("  ✅ {$count} cartes synchronisées");
    }
}
