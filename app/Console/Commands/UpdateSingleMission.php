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

class UpdateSingleMission extends Command
{
    protected $signature = 'missions:update-single {type : Type de données (primary|secondary|twist|asymmetric|strike-force|incursion|asymmetric-warfare)} {name : Nom de la mission} {--description= : Nouvelle description} {--full-text= : Nouveau texte complet} {--active=1 : Statut actif (0 ou 1)} {--source=chapter-approved-2025-26 : Source des données}';
    protected $description = 'Mettre à jour une seule mission existante';

    public function handle()
    {
        $type = $this->argument('type');
        $name = $this->argument('name');
        $source = $this->option('source');

        $this->info('✏️  Mise à jour d\'une mission...');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info("Type : {$type}");
        $this->info("Nom : {$name}");
        $this->info('═══════════════════════════════════════════════════════════');

        try {
            match ($type) {
                'primary' => $this->updatePrimaryMission($name, $source),
                'secondary' => $this->updateSecondaryMission($name, $source),
                'twist' => $this->updateTwistMission($name, $source),
                'asymmetric' => $this->updateAsymmetricPrimaryMission($name, $source),
                'strike-force' => $this->updateStrikeForceMission($name, $source),
                'incursion' => $this->updateIncursionMission($name, $source),
                'asymmetric-warfare' => $this->updateAsymmetricWarfareMission($name, $source),
                default => $this->error("Type inconnu : {$type}"),
            };

            $this->info('');
            $this->info('═══════════════════════════════════════════════════════════');
            $this->info('✅ Mission mise à jour avec succès !');
        } catch (\Exception $e) {
            $this->error("❌ Erreur : {$e->getMessage()}");
            return 1;
        }

        return 0;
    }

    protected function updatePrimaryMission($name, $source)
    {
        $mission = PrimaryMission::where('name', $name)->where('source', $source)->first();
        if (!$mission) {
            throw new \Exception("Mission non trouvée : {$name}");
        }

        $data = $this->getUpdateData();
        $mission->update($data);
        $this->line("  ✅ Mission primaire mise à jour : {$name}");
    }

    protected function updateSecondaryMission($name, $source)
    {
        $mission = SecondaryMission::where('name', $name)->where('source', $source)->first();
        if (!$mission) {
            throw new \Exception("Mission non trouvée : {$name}");
        }

        $data = $this->getUpdateData();
        $mission->update($data);
        $this->line("  ✅ Mission secondaire mise à jour : {$name}");
    }

    protected function updateTwistMission($name, $source)
    {
        $mission = TwistMission::where('name', $name)->where('source', $source)->first();
        if (!$mission) {
            throw new \Exception("Péripétie non trouvée : {$name}");
        }

        $data = $this->getUpdateData();
        $mission->update($data);
        $this->line("  ✅ Péripétie mise à jour : {$name}");
    }

    protected function updateAsymmetricPrimaryMission($name, $source)
    {
        $mission = AsymmetricPrimaryMission::where('name', $name)->where('source', $source)->first();
        if (!$mission) {
            throw new \Exception("Mission non trouvée : {$name}");
        }

        $data = $this->getUpdateData();
        $mission->update($data);
        $this->line("  ✅ Mission primaire asymétrique mise à jour : {$name}");
    }

    protected function updateStrikeForceMission($name, $source)
    {
        $mission = StrikeForceDeploymentCard::where('name', $name)->where('source', $source)->first();
        if (!$mission) {
            throw new \Exception("Carte non trouvée : {$name}");
        }

        $data = $this->getUpdateData();
        $mission->update($data);
        $this->line("  ✅ Carte Strike Force mise à jour : {$name}");
    }

    protected function updateIncursionMission($name, $source)
    {
        $mission = IncursionDeploymentCard::where('name', $name)->where('source', $source)->first();
        if (!$mission) {
            throw new \Exception("Carte non trouvée : {$name}");
        }

        $data = $this->getUpdateData();
        $mission->update($data);
        $this->line("  ✅ Carte Incursion mise à jour : {$name}");
    }

    protected function updateAsymmetricWarfareMission($name, $source)
    {
        $mission = AsymmetricWarfareDeploymentCard::where('name', $name)->where('source', $source)->first();
        if (!$mission) {
            throw new \Exception("Carte non trouvée : {$name}");
        }

        $data = $this->getUpdateData();
        $mission->update($data);
        $this->line("  ✅ Carte Guerre Asymétrique mise à jour : {$name}");
    }

    protected function getUpdateData()
    {
        $data = [];

        if ($this->option('description')) {
            $data['description'] = $this->option('description');
        }

        if ($this->option('full-text')) {
            $data['full_text'] = $this->option('full-text');
        }

        if ($this->option('active') !== null) {
            $data['is_active'] = (bool) $this->option('active');
        }

        return $data;
    }
}
