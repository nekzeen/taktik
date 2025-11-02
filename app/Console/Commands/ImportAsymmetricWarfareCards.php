<?php

namespace App\Console\Commands;

use App\Models\AsymmetricWarfareDeploymentCard;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportAsymmetricWarfareCards extends Command
{
    protected $signature = 'missions:import-asymmetric-warfare {--source=chapter-approved-2025-26}';
    protected $description = 'Importer les cartes de déploiement Guerre Asymétrique';

    protected $cards = [
        [
            'name' => 'TIP OF THE SPEAR',
            'description' => 'Strike at the heart of the enemy.',
            'image_filename' => 'CA6_Ass_TipOfTheSpear.png',
            'image_url' => 'https://wahapedia.ru/wh40k10ed/img/maps/cards/CA6_Ass_TipOfTheSpear.png',
        ],
        [
            'name' => 'DEFENSIVE LINE',
            'description' => 'Hold the line against all odds.',
            'image_filename' => 'CA6_Ass_DefensiveLine.png',
            'image_url' => 'https://wahapedia.ru/wh40k10ed/img/maps/cards/CA6_Ass_DefensiveLine.png',
        ],
        [
            'name' => 'PINCER ATTACK',
            'description' => 'Attack from multiple directions.',
            'image_filename' => 'CA6_Ass_PincerAttack.png',
            'image_url' => 'https://wahapedia.ru/wh40k10ed/img/maps/cards/CA6_Ass_PincerAttack.png',
        ],
        [
            'name' => 'BREAKOUT',
            'description' => 'Break through enemy lines.',
            'image_filename' => 'CA6_Ass_Breakout.png',
            'image_url' => 'https://wahapedia.ru/wh40k10ed/img/maps/cards/CA6_Ass_Breakout.png',
        ],
        [
            'name' => 'LAST STAND',
            'description' => 'Make a final desperate stand.',
            'image_filename' => 'CA6_Ass_LastStand.png',
            'image_url' => 'https://wahapedia.ru/wh40k10ed/img/maps/cards/CA6_Ass_LastStand.png',
        ],
    ];

    public function handle()
    {
        $source = $this->option('source');

        $this->info('📥 Importation des cartes de déploiement Guerre Asymétrique...');
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
                $card = AsymmetricWarfareDeploymentCard::updateOrCreate(
                    ['name' => $name, 'source' => $source],
                    [
                        'description' => $description,
                        'full_text' => "Asymmetric Warfare Deployment Card\n{$name}\n{$description}",
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
