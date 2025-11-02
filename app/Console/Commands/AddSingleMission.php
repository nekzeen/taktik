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
use Illuminate\Support\Str;

class AddSingleMission extends Command
{
    protected $signature = 'missions:add-single {type : Type de données (primary|secondary|twist|asymmetric|strike-force|incursion|asymmetric-warfare)} {name : Nom de la mission} {description : Description courte} {--source=chapter-approved-2025-26 : Source des données}';
    protected $description = 'Ajouter une seule mission sans modifier les existantes';

    public function handle()
    {
        $type = $this->argument('type');
        $name = $this->argument('name');
        $description = $this->argument('description');
        $source = $this->option('source');

        $this->info('➕ Ajout d\'une nouvelle mission...');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info("Type : {$type}");
        $this->info("Nom : {$name}");
        $this->info("Description : {$description}");
        $this->info('═══════════════════════════════════════════════════════════');

        try {
            match ($type) {
                'primary' => $this->addPrimaryMission($name, $description, $source),
                'secondary' => $this->addSecondaryMission($name, $description, $source),
                'twist' => $this->addTwistMission($name, $description, $source),
                'asymmetric' => $this->addAsymmetricPrimaryMission($name, $description, $source),
                'strike-force' => $this->addStrikeForceMission($name, $description, $source),
                'incursion' => $this->addIncursionMission($name, $description, $source),
                'asymmetric-warfare' => $this->addAsymmetricWarfareMission($name, $description, $source),
                default => $this->error("Type inconnu : {$type}"),
            };

            $this->info('');
            $this->info('═══════════════════════════════════════════════════════════');
            $this->info('✅ Mission ajoutée avec succès !');
        } catch (\Exception $e) {
            $this->error("❌ Erreur : {$e->getMessage()}");
            return 1;
        }

        return 0;
    }

    protected function addPrimaryMission($name, $description, $source)
    {
        $exists = PrimaryMission::where('name', $name)->where('source', $source)->exists();
        if ($exists) {
            throw new \Exception("Cette mission existe déjà");
        }

        PrimaryMission::create([
            'name' => $name,
            'description' => $description,
            'full_text' => "{$name}\n{$description}",
            'when_condition' => 'At the start of the game',
            'timing' => 'Ongoing',
            'scoring_conditions' => 'See mission details',
            'max_vp' => 10,
            'slug' => Str::slug($name),
            'edition' => '10ed',
            'source' => $source,
            'is_active' => true,
        ]);

        $this->line("  ✅ Mission primaire créée : {$name}");
    }

    protected function addSecondaryMission($name, $description, $source)
    {
        $exists = SecondaryMission::where('name', $name)->where('source', $source)->exists();
        if ($exists) {
            throw new \Exception("Cette mission existe déjà");
        }

        SecondaryMission::create([
            'name' => $name,
            'description' => $description,
            'full_text' => "{$name}\n{$description}",
            'when_drawn' => 'At the start of the game',
            'timing' => 'Ongoing',
            'scoring_conditions' => 'See mission details',
            'max_vp' => 5,
            'slug' => Str::slug($name),
            'edition' => '10ed',
            'source' => $source,
            'is_active' => true,
        ]);

        $this->line("  ✅ Mission secondaire créée : {$name}");
    }

    protected function addTwistMission($name, $description, $source)
    {
        $exists = TwistMission::where('name', $name)->where('source', $source)->exists();
        if ($exists) {
            throw new \Exception("Cette péripétie existe déjà");
        }

        TwistMission::create([
            'name' => $name,
            'description' => $description,
            'full_text' => "{$name}\n{$description}",
            'slug' => Str::slug($name),
            'edition' => '10ed',
            'source' => $source,
            'is_active' => true,
        ]);

        $this->line("  ✅ Péripétie créée : {$name}");
    }

    protected function addAsymmetricPrimaryMission($name, $description, $source)
    {
        $exists = AsymmetricPrimaryMission::where('name', $name)->where('source', $source)->exists();
        if ($exists) {
            throw new \Exception("Cette mission existe déjà");
        }

        AsymmetricPrimaryMission::create([
            'name' => $name,
            'description' => $description,
            'full_text' => "{$name}\n{$description}",
            'slug' => Str::slug($name),
            'edition' => '10ed',
            'source' => $source,
            'is_active' => true,
        ]);

        $this->line("  ✅ Mission primaire asymétrique créée : {$name}");
    }

    protected function addStrikeForceMission($name, $description, $source)
    {
        $exists = StrikeForceDeploymentCard::where('name', $name)->where('source', $source)->exists();
        if ($exists) {
            throw new \Exception("Cette carte existe déjà");
        }

        StrikeForceDeploymentCard::create([
            'name' => $name,
            'description' => $description,
            'full_text' => "{$name}\n{$description}",
            'slug' => Str::slug($name),
            'edition' => '10ed',
            'source' => $source,
            'is_active' => true,
        ]);

        $this->line("  ✅ Carte Strike Force créée : {$name}");
    }

    protected function addIncursionMission($name, $description, $source)
    {
        $exists = IncursionDeploymentCard::where('name', $name)->where('source', $source)->exists();
        if ($exists) {
            throw new \Exception("Cette carte existe déjà");
        }

        IncursionDeploymentCard::create([
            'name' => $name,
            'description' => $description,
            'full_text' => "{$name}\n{$description}",
            'slug' => Str::slug($name),
            'edition' => '10ed',
            'source' => $source,
            'is_active' => true,
        ]);

        $this->line("  ✅ Carte Incursion créée : {$name}");
    }

    protected function addAsymmetricWarfareMission($name, $description, $source)
    {
        $exists = AsymmetricWarfareDeploymentCard::where('name', $name)->where('source', $source)->exists();
        if ($exists) {
            throw new \Exception("Cette carte existe déjà");
        }

        AsymmetricWarfareDeploymentCard::create([
            'name' => $name,
            'description' => $description,
            'full_text' => "{$name}\n{$description}",
            'slug' => Str::slug($name),
            'edition' => '10ed',
            'source' => $source,
            'is_active' => true,
        ]);

        $this->line("  ✅ Carte Guerre Asymétrique créée : {$name}");
    }
}
