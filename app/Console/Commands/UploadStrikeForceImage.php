<?php

namespace App\Console\Commands;

use App\Models\StrikeForceDeploymentCard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class UploadStrikeForceImage extends Command
{
    protected $signature = 'missions:upload-strike-force-image {card-name} {file-path}';
    protected $description = 'Uploader une image pour une carte Strike Force';

    public function handle()
    {
        $cardName = $this->argument('card-name');
        $filePath = $this->argument('file-path');

        // Vérifier que le fichier existe
        if (!file_exists($filePath)) {
            $this->error("❌ Fichier non trouvé: {$filePath}");
            return 1;
        }

        // Chercher la carte
        $card = StrikeForceDeploymentCard::where('name', strtoupper($cardName))->first();

        if (!$card) {
            $this->error("❌ Carte non trouvée: {$cardName}");
            return 1;
        }

        try {
            $this->info("📥 Upload de l'image pour: {$card->name}");

            // Lire le fichier
            $fileContent = file_get_contents($filePath);
            $filename = basename($filePath);

            // Créer le dossier s'il n'existe pas
            if (!Storage::disk('public')->exists('deployment-cards')) {
                Storage::disk('public')->makeDirectory('deployment-cards');
            }

            // Sauvegarder l'image
            $path = "deployment-cards/{$filename}";
            Storage::disk('public')->put($path, $fileContent);

            // Mettre à jour la carte
            $card->update([
                'image_path' => $path,
                'image_filename' => $filename,
            ]);

            $this->info("✅ Image sauvegardée: {$path}");
            $this->info("✅ Carte mise à jour!");

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Erreur: {$e->getMessage()}");
            return 1;
        }
    }
}
