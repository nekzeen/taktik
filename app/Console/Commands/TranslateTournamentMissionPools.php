<?php

namespace App\Console\Commands;

use App\Models\TournamentMissionPool;
use App\Models\Translation;
use Illuminate\Console\Command;

class TranslateTournamentMissionPools extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tournament:translate-mission-pools {--locale=fr}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer les traductions des missions du pool de tournoi';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $locale = $this->option('locale');
        $this->info("🌍 Création des traductions des missions du pool ($locale)...");

        $pools = TournamentMissionPool::all();

        if ($pools->isEmpty()) {
            $this->error('❌ Aucune mission du pool trouvée. Exécutez d\'abord : php artisan tournament:import-mission-pools');
            return Command::FAILURE;
        }

        $created = 0;
        $skipped = 0;

        foreach ($pools as $pool) {
            // Traduction du nom
            $nameTranslation = Translation::where('resource_type', 'TournamentMissionPool')
                ->where('resource_id', $pool->id)
                ->where('field', 'name')
                ->where('locale', $locale)
                ->first();

            if (!$nameTranslation) {
                Translation::create([
                    'resource_type' => 'TournamentMissionPool',
                    'resource_id' => $pool->id,
                    'field' => 'name',
                    'locale' => $locale,
                    'source_text' => $pool->name,
                    'translated_text' => $pool->name, // Placeholder, à traduire manuellement
                    'status' => 'pending',
                ]);
                $this->line("  ✅ Créé : {$pool->name}");
                $created++;
            } else {
                $this->line("  ⏭️  Ignoré (existe déjà) : {$pool->name}");
                $skipped++;
            }

            // Traduction de la description
            if ($pool->description) {
                $descTranslation = Translation::where('resource_type', 'TournamentMissionPool')
                    ->where('resource_id', $pool->id)
                    ->where('field', 'description')
                    ->where('locale', $locale)
                    ->first();

                if (!$descTranslation) {
                    Translation::create([
                        'resource_type' => 'TournamentMissionPool',
                        'resource_id' => $pool->id,
                        'field' => 'description',
                        'locale' => $locale,
                        'source_text' => $pool->description,
                        'translated_text' => $pool->description, // Placeholder
                        'status' => 'pending',
                    ]);
                    $created++;
                }
            }
        }

        $this->newLine();
        $this->info('📊 Résumé :');
        $this->line("  ✅ Créés : $created");
        $this->line("  ⏭️  Ignorés : $skipped");

        return Command::SUCCESS;
    }
}
