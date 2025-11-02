<?php

namespace App\Console\Commands;

use App\Models\TerrainLayout;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportTerrainLayouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'terrain:import-layouts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importer les dispositions de terrain depuis Wahapedia';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌍 Importation des dispositions de terrain...');

        // Créer les 8 dispositions de terrain
        $layouts = [];
        for ($i = 1; $i <= 8; $i++) {
            $layouts[] = [
                'name' => "Terrain Layout $i",
                'slug' => "terrain-layout-$i",
                'layout_number' => $i,
                'image_url' => TerrainLayout::getWahapediaImageUrl($i),
                'description' => "Disposition de terrain $i du Chapter Approved 2025-26",
                'source' => 'chapter-approved-2025-26',
                'is_active' => true,
            ];
        }

        $created = 0;
        $updated = 0;

        foreach ($layouts as $layoutData) {
            $existing = TerrainLayout::findByNumber($layoutData['layout_number']);

            if ($existing) {
                $existing->update($layoutData);
                $this->line("  ✏️  Mis à jour : {$layoutData['name']}");
                $updated++;
            } else {
                TerrainLayout::create($layoutData);
                $this->line("  ✅ Créé : {$layoutData['name']}");
                $created++;
            }
        }

        $this->newLine();
        $this->info("📊 Résumé :");
        $this->line("  ✅ Créés : $created");
        $this->line("  ✏️  Mis à jour : $updated");
        $this->line("  📦 Total : " . TerrainLayout::count());

        return Command::SUCCESS;
    }
}
