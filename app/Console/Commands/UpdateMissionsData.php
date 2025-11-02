<?php

namespace App\Console\Commands;

use App\Models\PrimaryMission;
use App\Models\SecondaryMission;
use App\Models\TwistMission;
use App\Models\AsymmetricPrimaryMission;
use App\Models\StrikeForceDeploymentCard;
use App\Models\IncursionDeploymentCard;
use App\Models\AsymmetricWarfareDeploymentCard;
use Illuminate\Console\Command;

class UpdateMissionsData extends Command
{
    protected $signature = 'missions:update {type : Type de données (primary|secondary|twist|asymmetric|strike-force|incursion|asymmetric-warfare)} {--mode=merge : Mode de mise à jour (merge|replace|add-only)} {--source=chapter-approved-2025-26 : Source des données}';
    protected $description = 'Mettre à jour les données de missions avec flexibilité';

    public function handle()
    {
        $type = $this->argument('type');
        $mode = $this->option('mode');
        $source = $this->option('source');

        $this->info('🔄 Mise à jour des données...');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info("Type : {$type}");
        $this->info("Mode : {$mode}");
        $this->info("Source : {$source}");
        $this->info('═══════════════════════════════════════════════════════════');

        match ($type) {
            'primary' => $this->updatePrimaryMissions($mode, $source),
            'secondary' => $this->updateSecondaryMissions($mode, $source),
            'twist' => $this->updateTwistMissions($mode, $source),
            'asymmetric' => $this->updateAsymmetricPrimaryMissions($mode, $source),
            'strike-force' => $this->updateStrikeForceMissions($mode, $source),
            'incursion' => $this->updateIncursionMissions($mode, $source),
            'asymmetric-warfare' => $this->updateAsymmetricWarfareMissions($mode, $source),
            default => $this->error("Type inconnu : {$type}"),
        };

        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('✅ Mise à jour terminée !');

        return 0;
    }

    protected function updatePrimaryMissions($mode, $source)
    {
        $this->info('📝 Mise à jour des missions primaires...');

        if ($mode === 'replace') {
            PrimaryMission::where('source', $source)->delete();
            $this->line('  🗑️  Missions existantes supprimées');
        }

        $this->line('  ℹ️  Exécutez : php artisan missions:import-xml');
        $this->line('  ℹ️  Puis : php artisan missions:translate --locale=fr');
    }

    protected function updateSecondaryMissions($mode, $source)
    {
        $this->info('📝 Mise à jour des missions secondaires...');

        if ($mode === 'replace') {
            SecondaryMission::where('source', $source)->delete();
            $this->line('  🗑️  Missions existantes supprimées');
        }

        $this->line('  ℹ️  Exécutez : php artisan missions:import-secondary-xml');
        $this->line('  ℹ️  Puis : php artisan missions:translate-secondary --locale=fr');
    }

    protected function updateTwistMissions($mode, $source)
    {
        $this->info('📝 Mise à jour des péripéties...');

        if ($mode === 'replace') {
            TwistMission::where('source', $source)->delete();
            $this->line('  🗑️  Péripéties existantes supprimées');
        }

        $this->line('  ℹ️  Exécutez : php artisan missions:import-twist-xml');
        $this->line('  ℹ️  Puis : php artisan missions:translate-twist --locale=fr');
    }

    protected function updateAsymmetricPrimaryMissions($mode, $source)
    {
        $this->info('📝 Mise à jour des missions primaires asymétriques...');

        if ($mode === 'replace') {
            AsymmetricPrimaryMission::where('source', $source)->delete();
            $this->line('  🗑️  Missions existantes supprimées');
        }

        $this->line('  ℹ️  Exécutez : php artisan missions:import-asymmetric-xml');
        $this->line('  ℹ️  Puis : php artisan missions:translate-asymmetric --locale=fr');
    }

    protected function updateStrikeForceMissions($mode, $source)
    {
        $this->info('📝 Mise à jour des cartes Strike Force...');

        if ($mode === 'replace') {
            StrikeForceDeploymentCard::where('source', $source)->delete();
            $this->line('  🗑️  Cartes existantes supprimées');
        }

        $this->line('  ℹ️  Exécutez : php artisan missions:import-strike-force');
        $this->line('  ℹ️  Puis : php artisan missions:download-strike-force-images --force');
        $this->line('  ℹ️  Puis : php artisan missions:translate-strike-force --locale=fr');
    }

    protected function updateIncursionMissions($mode, $source)
    {
        $this->info('📝 Mise à jour des cartes Incursions...');

        if ($mode === 'replace') {
            IncursionDeploymentCard::where('source', $source)->delete();
            $this->line('  🗑️  Cartes existantes supprimées');
        }

        $this->line('  ℹ️  Exécutez : php artisan missions:import-incursion');
        $this->line('  ℹ️  Puis : php artisan missions:download-incursion-images --force');
        $this->line('  ℹ️  Puis : php artisan missions:translate-incursion --locale=fr');
    }

    protected function updateAsymmetricWarfareMissions($mode, $source)
    {
        $this->info('📝 Mise à jour des cartes Guerre Asymétrique...');

        if ($mode === 'replace') {
            AsymmetricWarfareDeploymentCard::where('source', $source)->delete();
            $this->line('  🗑️  Cartes existantes supprimées');
        }

        $this->line('  ℹ️  Exécutez : php artisan missions:import-asymmetric-warfare');
        $this->line('  ℹ️  Puis : php artisan missions:download-asymmetric-warfare-images --force');
        $this->line('  ℹ️  Puis : php artisan missions:translate-asymmetric-warfare --locale=fr');
    }
}
