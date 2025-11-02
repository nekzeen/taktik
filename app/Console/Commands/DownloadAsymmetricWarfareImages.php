<?php

namespace App\Console\Commands;

use App\Models\AsymmetricWarfareDeploymentCard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DownloadAsymmetricWarfareImages extends Command
{
    protected $signature = 'missions:download-asymmetric-warfare-images {--force}';
    protected $description = 'Télécharger les images des cartes Guerre Asymétrique';

    public function handle()
    {
        $force = $this->option('force');

        $this->info('📥 Téléchargement des images Guerre Asymétrique...');
        $this->info('═══════════════════════════════════════════════════════════');

        // Créer le dossier de stockage s'il n'existe pas
        if (!Storage::disk('public')->exists('asymmetric-warfare-cards')) {
            Storage::disk('public')->makeDirectory('asymmetric-warfare-cards');
            $this->line('✅ Dossier créé : storage/app/public/asymmetric-warfare-cards/');
        }

        $cards = AsymmetricWarfareDeploymentCard::whereNotNull('image_url')->get();
        $downloaded = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($cards as $card) {
            try {
                // Vérifier si l'image existe déjà
                if (!$force && $card->image_path && Storage::disk('public')->exists($card->image_path)) {
                    $this->line("  ⏭️  {$card->name}: Image déjà téléchargée");
                    $skipped++;
                    continue;
                }

                $this->line("  📥 Téléchargement: {$card->name}");

                // Télécharger l'image
                $imageContent = @file_get_contents($card->image_url);

                if ($imageContent === false) {
                    $this->warn("  ❌ Impossible de télécharger: {$card->image_url}");
                    $failed++;
                    continue;
                }

                // Sauvegarder l'image
                $path = "asymmetric-warfare-cards/{$card->image_filename}";
                Storage::disk('public')->put($path, $imageContent);

                // Mettre à jour le chemin dans la base de données
                $card->update(['image_path' => $path]);

                $this->line("  ✅ Sauvegardée: {$path}");
                $downloaded++;
            } catch (\Exception $e) {
                $this->error("  ❌ Erreur pour {$card->name}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info("📊 Résultats:");
        $this->info("  ✅ Téléchargées: {$downloaded}");
        $this->info("  ⏭️  Ignorées: {$skipped}");
        $this->info("  ❌ Échouées: {$failed}");
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('✅ Téléchargement terminé !');

        return 0;
    }
}
