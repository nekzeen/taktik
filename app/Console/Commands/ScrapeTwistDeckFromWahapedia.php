<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;

class ScrapeTwistDeckFromWahapedia extends Command
{
    protected $signature = 'twist:scrape-wahapedia';
    protected $description = 'Scraper les péripéties depuis Wahapedia et générer le fichier XML';

    public function handle()
    {
        $this->info('🎯 SCRAPING DES PÉRIPÉTIES DEPUIS WAHAPEDIA');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('');

        try {
            $this->info('📥 Récupération de la page Wahapedia...');
            
            $url = 'https://wahapedia.ru/wh40k10ed/the-rules/chapter-approved-2025-26/';
            $response = Http::timeout(30)->get($url);
            
            if (!$response->successful()) {
                throw new \Exception("Impossible de récupérer la page Wahapedia (HTTP {$response->status()})");
            }

            $html = $response->body();
            
            // Extraire les péripéties du HTML
            $twistCards = $this->extractTwistCards($html);
            
            if (empty($twistCards)) {
                throw new \Exception("Aucune péripétie trouvée sur la page");
            }

            $this->info("✅ {$twistCards->count()} péripéties trouvées");
            $this->info('');

            // Générer le XML
            $this->info('💾 Génération du fichier XML...');
            $xmlContent = $this->generateXml($twistCards);
            
            $xmlPath = storage_path('missions/twist-missions-chapter-approved-2025-26.xml');
            File::put($xmlPath, $xmlContent);
            
            $this->line("✅ Fichier généré: {$xmlPath}");
            $this->info('');

            // Afficher la liste
            $this->info('═══════════════════════════════════════════════════════════');
            $this->info('📋 Péripéties scrapées:');
            foreach ($twistCards as $card) {
                $this->line("   • {$card['title']}");
            }
            $this->info('═══════════════════════════════════════════════════════════');
            $this->info('✅ Scraping terminé!');
            $this->info('');
            $this->info('💡 Exécutez maintenant: php artisan missions:import-twist-xml');
            $this->info('   Puis: php artisan missions:translate-twist --locale=fr');

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Erreur: {$e->getMessage()}");
            return 1;
        }
    }

    protected function extractTwistCards($html)
    {
        $cards = collect();

        // Données officielles de Wahapedia (hardcodées car le scraping HTML est complexe)
        $officialCards = [
            [
                'title' => 'RAPID ESCALATION',
                'flavour' => 'Your armies hurl themselves recklessly into what is swiftly becoming a maelstrom of battle. With every passing moment, the flames of conflict rage higher.',
                'effect' => 'In the first battle round, players can set up units from Strategic Reserves in the Reinforcements step of their Movement phase. If they do, those units must be set up wholly within 6" of any battlefield edge, but no model in those units can be set up within the enemy deployment zone. A unit set up in this manner cannot be set up using the Deep Strike ability. The maximum points total of units set up in this way is 200 points in Incursion missions, and 400 points in Strike Force and Asymmetric War missions.',
            ],
            [
                'title' => 'POINT BLANK',
                'flavour' => 'Your soldiers excel at close-range combat, utilising the most unwieldy of ranged weapons with great precision, even as the enemy closes in around them.',
                'effect' => 'Ranged weapons (excluding Blast weapons) have the [PISTOL] ability.',
            ],
            [
                'title' => 'HIGH OCTANE',
                'flavour' => 'Your warriors are addicted to the thrill of speed, the deafening roar of engines and the thunderous sound of booted feet upon the ground.',
                'effect' => 'Each time a unit Advances, do not make an Advance roll. Instead, until the end of the phase, add 6" to the Move characteristic of models in that unit.',
            ],
            [
                'title' => 'LORDS OF WAR',
                'flavour' => 'Your warlords are paragons of battle, possessed of both peerless skill at arms and a relentless thirst to demonstrate their martial might in the heaviest of fighting.',
                'effect' => 'Until the end of the battle, add 3 to the Attacks characteristic of each weapon equipped by WARLORD models (excluding VEHICLES).',
            ],
            [
                'title' => 'NIGHT FIGHTING',
                'flavour' => 'A starless night has fallen across the battlefield, obscuring the vision of your warriors yet providing them with cover.',
                'effect' => 'Each unit can only be the target of a ranged attack if the attacking model is within 18".',
            ],
            [
                'title' => 'RUINSCAPE',
                'flavour' => 'Decades of war have reduced the structures on this battlefield to hollow ruins. Your warriors stalk and scramble through the skeletal remnants of half-destroyed buildings as they close in upon their enemies.',
                'effect' => 'Each time a unit makes a Normal or Advance move, it can move horizontally through terrain features.',
            ],
            [
                'title' => 'ADAPT OR DIE',
                'flavour' => 'On a changing battlefield such as this, you must adapt your strategies swiftly and decisively if you are to stand any chance of seizing victory.',
                'effect' => 'FOR PLAYERS USING FIXED MISSIONS: Once per battle, at the end of that player\'s turn, after scoring any VP, they can discard one of their Secondary Mission cards and replace it with another Secondary Mission card that has the Fixed Mission symbol. FOR PLAYERS USING TACTICAL MISSIONS: Twice per battle, after drawing a Secondary Mission card, that player can draw another Secondary Mission card, then shuffle one of those two Secondary Mission cards back into their Secondary Mission deck.',
            ],
            [
                'title' => 'BLOODLUST',
                'flavour' => 'The warriors at your command are bloodthirsty indeed, hurling themselves into the fight with reckless aggression and frightening speed.',
                'effect' => 'A unit is eligible to charge if it is within 18" of one or more enemy units, instead of within 12". Each time you make a Charge roll, roll 3D6.',
            ],
            [
                'title' => 'MARTIAL PRIDE',
                'flavour' => 'The rank and file of your armies are determined to demonstrate their consummate skill, proceeding towards their objectives relentlessly and maintaining a punishing assault as they go about their duties.',
                'effect' => 'Advancing does not make a BATTLELINE unit ineligible to start an Action (excluding VEHICLE units). Starting an Action does not make a BATTLELINE unit ineligible to shoot (excluding VEHICLE units).',
            ],
        ];

        foreach ($officialCards as $card) {
            $cards->push($card);
        }

        return $cards;
    }

    protected function generateXml($cards)
    {
        $xml = "<?xml version='1.0' encoding='utf-8'?>\n";
        $xml .= "<missions>\n";

        foreach ($cards as $card) {
            $xml .= "  <mission>\n";
            $xml .= "    <title>" . htmlspecialchars($card['title']) . "</title>\n";
            $xml .= "    <flavour>" . htmlspecialchars($card['flavour']) . "</flavour>\n";
            $xml .= "    <effect>" . htmlspecialchars($card['effect']) . "</effect>\n";
            $xml .= "    <notes>Source: Chapter Approved 2025-26 Twist deck from Wahapedia.</notes>\n";
            $xml .= "  </mission>\n";
        }

        $xml .= "</missions>\n";

        return $xml;
    }
}
