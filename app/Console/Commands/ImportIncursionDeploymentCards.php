<?php

namespace App\Console\Commands;

use App\Models\IncursionDeploymentCard;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportIncursionDeploymentCards extends Command
{
    protected $signature = 'missions:import-incursion {--source=chapter-approved-2025-26}';
    protected $description = 'Importer les cartes de déploiement Incursions';

    protected $cards = [
        [
            'name' => 'TIPPING POINT',
            'description' => 'A delicate balance of power.',
            'image_filename' => 'CA6_INC_TippingPoint.png',
            'image_url' => 'https://wahapedia.ru/wh40k10ed/img/maps/cards/CA6_INC_TippingPoint.png',
        ],
        [
            'name' => 'HAMMER AND ANVIL',
            'description' => 'Strike with overwhelming force.',
            'image_filename' => 'CA6_INC_HammerAndAnvil.png',
            'image_url' => 'https://wahapedia.ru/wh40k10ed/img/maps/cards/CA6_INC_HammerAndAnvil.png',
        ],
        [
            'name' => 'SWEEPING ENGAGEMENT',
            'description' => 'A broad and decisive engagement.',
            'image_filename' => 'CA6_INC_SweepingEngagement.png',
            'image_url' => 'https://wahapedia.ru/wh40k10ed/img/maps/cards/CA6_INC_SweepingEngagement.png',
        ],
        [
            'name' => 'DAWN OF WAR',
            'description' => 'The battle begins at first light.',
            'image_filename' => 'CA6_INC_DawnOfWar.png',
            'image_url' => 'https://wahapedia.ru/wh40k10ed/img/maps/cards/CA6_INC_DawnOfWar.png',
        ],
        [
            'name' => 'SEARCH AND DESTROY',
            'description' => 'Hunt down and eliminate the enemy.',
            'image_filename' => 'CA6_INC_SearchAndDestroy.png',
            'image_url' => 'https://wahapedia.ru/wh40k10ed/img/maps/cards/CA6_INC_SearchAndDestroy.png',
        ],
        [
            'name' => 'CRUCIBLE OF BATTLE',
            'description' => 'The ultimate test of strength.',
            'image_filename' => 'CA6_INC_CrucibleOfBattle.png',
            'image_url' => 'https://wahapedia.ru/wh40k10ed/img/maps/cards/CA6_INC_CrucibleOfBattle.png',
        ],
    ];

    public function handle()
    {
        $source = $this->option('source');

        $this->info('📥 Importation des cartes de déploiement Incursions...');
        $this->info('═══════════════════════════════════════════════════════════');

        $created = 0;
        $updated = 0;

        foreach ($this->cards as $cardData) {
            try {
                $name = $cardData['name'];
                $description = $cardData['description'];
                $imageFilename = $cardData['image_filename'];
                $imageUrl = $cardData['image_url'];

                // Créer ou mettre à jour la carte
                $card = IncursionDeploymentCard::updateOrCreate(
                    ['name' => $name, 'source' => $source],
                    [
                        'description' => $description,
                        'full_text' => "Incursion Deployment Card\n{$name}\n{$description}",
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
        $this->info('✅ Importation terminée !');

        return 0;
    }
}
