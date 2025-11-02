<?php

namespace App\Console\Commands;

use App\Models\StrikeForceDeploymentCard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportStrikeForceDeploymentCards extends Command
{
    protected $signature = 'missions:import-strike-force {--source=chapter-approved-2025-26} {--download-images}';
    protected $description = 'Importer les cartes de déploiement Strike Force';

    protected $cards = [
        [
            'name' => 'TIPPING POINT',
            'description' => 'A delicate balance of power.',
            'image_filename' => 'CA6_SF_TippingPoint.png',
            'image_url' => 'https://wahapedia.ru/wh40k10ed/img/maps/cards/CA6_SF_TippingPoint.png',
        ],
        [
            'name' => 'HAMMER AND ANVIL',
            'description' => 'Strike with overwhelming force.',
            'image_filename' => 'CA6_SF_HammerAndAnvil.png',
            'image_url' => 'https://wahapedia.ru/wh40k10ed/img/maps/cards/CA6_SF_HammerAndAnvil.png',
        ],
        [
            'name' => 'SWEEPING ENGAGEMENT',
            'description' => 'A broad and decisive engagement.',
            'image_filename' => 'CA6_SF_SweepingEngagement.png',
            'image_url' => 'https://wahapedia.ru/wh40k10ed/img/maps/cards/CA6_SF_SweepingEngagement.png',
        ],
        [
            'name' => 'DAWN OF WAR',
            'description' => 'The battle begins at first light.',
            'image_filename' => 'CA6_SF_DawnOfWar.png',
            'image_url' => 'https://wahapedia.ru/wh40k10ed/img/maps/cards/CA6_SF_DawnOfWar.png',
        ],
        [
            'name' => 'SEARCH AND DESTROY',
            'description' => 'Hunt down and eliminate the enemy.',
            'image_filename' => 'CA6_SF_SearchAndDestroy.png',
            'image_url' => 'https://wahapedia.ru/wh40k10ed/img/maps/cards/CA6_SF_SearchAndDestroy.png',
        ],
        [
            'name' => 'CRUCIBLE OF BATTLE',
            'description' => 'The ultimate test of strength.',
            'image_filename' => 'CA6_SF_CrucibleOfBattle.png',
            'image_url' => 'https://wahapedia.ru/wh40k10ed/img/maps/cards/CA6_SF_CrucibleOfBattle.png',
        ],
    ];

    public function handle()
    {
        $source = $this->option('source');
        $downloadImages = $this->option('download-images');

        $this->info('📥 Importation des cartes de déploiement Strike Force...');
        $this->info('═══════════════════════════════════════════════════════════');

        // Créer le dossier de stockage s'il n'existe pas
        if (!Storage::disk('local')->exists('deployment-cards')) {
            Storage::disk('local')->makeDirectory('deployment-cards');
            $this->line('✅ Dossier de stockage créé : storage/app/deployment-cards/');
        }

        $created = 0;
        $updated = 0;

        foreach ($this->cards as $cardData) {
            try {
                $name = $cardData['name'];
                $description = $cardData['description'];
                $imageFilename = $cardData['image_filename'];
                $imageUrl = $cardData['image_url'];

                // Créer ou mettre à jour la carte
                $card = StrikeForceDeploymentCard::updateOrCreate(
                    ['name' => $name, 'source' => $source],
                    [
                        'description' => $description,
                        'full_text' => "Strike Force Deployment Card\n{$name}\n{$description}",
                        'image_url' => $imageUrl,
                        'image_filename' => $imageFilename,
                        'slug' => Str::slug($name),
                        'edition' => '10ed',
                        'source' => $source,
                        'is_active' => true,
                    ]
                );

                if ($card->wasRecentlyCreated) {
                    $this->line("  ✅ Créée: {$name}");
                    $created++;
                } else {
                    $this->line("  🔄 Mise à jour: {$name}");
                    $updated++;
                }

                // Télécharger l'image si demandé
                if ($downloadImages) {
                    $this->downloadImage($card, $imageUrl, $imageFilename);
                }
            } catch (\Exception $e) {
                $this->error("  ❌ Erreur pour {$name}: {$e->getMessage()}");
            }
        }

        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info("📊 Résultats:");
        $this->info("  ✅ Créées: {$created}");
        $this->info("  🔄 Mises à jour: {$updated}");
        $this->info('═══════════════════════════════════════════════════════════');

        if ($downloadImages) {
            $this->info('💾 Pour télécharger les images, exécutez:');
            $this->info('   php artisan missions:download-strike-force-images');
        }

        $this->info('✅ Importation terminée !');

        return 0;
    }

    protected function downloadImage(StrikeForceDeploymentCard $card, string $imageUrl, string $imageFilename): void
    {
        try {
            $this->line("  📥 Téléchargement de l'image: {$imageFilename}");

            // Télécharger l'image
            $imageContent = file_get_contents($imageUrl);

            if ($imageContent === false) {
                $this->warn("  ⚠️  Impossible de télécharger: {$imageUrl}");
                return;
            }

            // Sauvegarder l'image
            $path = "deployment-cards/{$imageFilename}";
            Storage::disk('local')->put($path, $imageContent);

            // Mettre à jour le chemin dans la base de données
            $card->update(['image_path' => $path]);

            $this->line("  ✅ Image sauvegardée: {$path}");
        } catch (\Exception $e) {
            $this->warn("  ⚠️  Erreur lors du téléchargement: {$e->getMessage()}");
        }
    }
}
