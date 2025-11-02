<?php

namespace App\Console\Commands;

use App\Models\TerrainLayout;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DownloadTerrainLayoutImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'terrain:download-images {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Télécharger les images des dispositions de terrain depuis Wahapedia';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📥 Téléchargement des images des dispositions de terrain...');

        $force = $this->option('force');
        $layouts = TerrainLayout::all();

        if ($layouts->isEmpty()) {
            $this->error('❌ Aucune disposition de terrain trouvée. Exécutez d\'abord : php artisan terrain:import-layouts');
            return Command::FAILURE;
        }

        $downloaded = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($layouts as $layout) {
            // Vérifier si l'image existe déjà
            if (!$force && $layout->image_path && Storage::disk('public')->exists($layout->image_path)) {
                $this->line("  ⏭️  Ignoré (existe déjà) : {$layout->name}");
                $skipped++;
                continue;
            }

            try {
                $this->line("  ⬇️  Téléchargement : {$layout->name}...");

                // Télécharger l'image
                $response = Http::timeout(30)->get($layout->image_url);

                if ($response->successful()) {
                    // Créer le répertoire s'il n'existe pas
                    $directory = 'terrain-layouts';
                    if (!Storage::disk('public')->exists($directory)) {
                        Storage::disk('public')->makeDirectory($directory);
                    }

                    // Sauvegarder l'image
                    $filename = "terrain-layout-{$layout->layout_number}.png";
                    $path = "$directory/$filename";
                    Storage::disk('public')->put($path, $response->body());

                    // Mettre à jour le modèle
                    $layout->update(['image_path' => $path]);

                    $this->line("  ✅ Téléchargé : {$layout->name}");
                    $downloaded++;
                } else {
                    $this->line("  ❌ Erreur HTTP {$response->status()} : {$layout->name}");
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->line("  ❌ Erreur : {$layout->name} - {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info('📊 Résumé :');
        $this->line("  ✅ Téléchargés : $downloaded");
        $this->line("  ⏭️  Ignorés : $skipped");
        $this->line("  ❌ Échoués : $failed");

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
