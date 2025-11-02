<?php

namespace App\Console\Commands;

use App\Models\TwistMission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class ScrapeTwistMissionsFromWahapedia extends Command
{
    protected $signature = 'twist:scrape-wahapedia {--force}';
    protected $description = 'Scraper les péripéties depuis Wahapedia';

    public function handle()
    {
        $this->error('❌ COMMANDE DÉSACTIVÉE');
        $this->info('');
        $this->warn('⚠️  La mise à jour directe depuis Wahapedia est DÉSACTIVÉE pour les péripéties.');
        $this->info('');
        $this->line('📝 Pour mettre à jour les péripéties:');
        $this->line('   1. Modifiez le fichier JSON: storage/missions/twist-missions-complete-texts.json');
        $this->line('   2. Exécutez: php artisan twist:update-full-text');
        $this->line('   3. Supprimez les traductions: php artisan tinker');
        $this->line('   4. Recréez les traductions: php artisan missions:translate-twist --locale=fr');
        $this->info('');
        $this->line('ℹ️  Raison: Les péripéties doivent être mises à jour manuellement pour éviter les erreurs.');
        $this->info('');

        return 0;
    }

    public function handleDisabled()
    {
        $this->info('🌐 Scraping des péripéties depuis Wahapedia...');
        $this->info('═══════════════════════════════════════════════════════════');

        // Les péripéties correctes du Twist deck avec texte complet
        $twistMissions = [
            [
                'name' => 'MARTIAL PRIDE',
                'description' => 'The rank and file of your armies are determined to demonstrate their consummate skill, proceeding towards their objectives relentlessly and maintaining a punishing assault as they go about their duties.',
                'full_text' => 'The rank and file of your armies are determined to demonstrate their consummate skill, proceeding towards their objectives relentlessly and maintaining a punishing assault as they go about their duties.

Advancing does not make a BATTLELINE unit ineligible to start an Action (excluding VEHICLE units).

Starting an Action does not make a BATTLELINE unit ineligible to shoot (excluding VEHICLE units).',
                'edition' => 'Chapter Approved 2025-26',
                'source' => 'chapter-approved-2025-26',
            ],
            [
                'name' => 'BLOODLUST',
                'description' => 'The warriors at your command are bloodthirsty indeed, hurling themselves into the fight with reckless aggression and frightening speed.',
                'full_text' => 'The warriors at your command are bloodthirsty indeed, hurling themselves into the fight with reckless aggression and frightening speed.',
                'edition' => 'Chapter Approved 2025-26',
                'source' => 'chapter-approved-2025-26',
            ],
            [
                'name' => 'RUINSCAPE',
                'description' => 'Decades of war have reduced the structures on this battlefield to hollow ruins. Your warriors stalk and scramble through the skeletal remnants of half-destroyed buildings as they close in upon their enemies.',
                'full_text' => 'Decades of war have reduced the structures on this battlefield to hollow ruins. Your warriors stalk and scramble through the skeletal remnants of half-destroyed buildings as they close in upon their enemies.',
                'edition' => 'Chapter Approved 2025-26',
                'source' => 'chapter-approved-2025-26',
            ],
            [
                'name' => 'ADAPT OR DIE',
                'description' => 'On a changing battlefield such as this, you must adapt your strategies swiftly and decisively if you are to stand any chance of seizing victory.',
                'full_text' => 'On a changing battlefield such as this, you must adapt your strategies swiftly and decisively if you are to stand any chance of seizing victory.',
                'edition' => 'Chapter Approved 2025-26',
                'source' => 'chapter-approved-2025-26',
            ],
            [
                'name' => 'NIGHT FIGHTING',
                'description' => 'A starless night has fallen across the battlefield, obscuring the vision of your warriors yet providing them with cover.',
                'full_text' => 'A starless night has fallen across the battlefield, obscuring the vision of your warriors yet providing them with cover.',
                'edition' => 'Chapter Approved 2025-26',
                'source' => 'chapter-approved-2025-26',
            ],
            [
                'name' => 'HIGH OCTANE',
                'description' => 'Your warriors are addicted to the thrill of speed, the deafening roar of engines and the thunderous sound of booted feet upon the ground.',
                'full_text' => 'Your warriors are addicted to the thrill of speed, the deafening roar of engines and the thunderous sound of booted feet upon the ground.',
                'edition' => 'Chapter Approved 2025-26',
                'source' => 'chapter-approved-2025-26',
            ],
            [
                'name' => 'POINT BLANK',
                'description' => 'Your soldiers excel at close-range combat, utilising the most unwieldy of ranged weapons with great precision, even as the enemy closes in around them.',
                'full_text' => 'Your soldiers excel at close-range combat, utilising the most unwieldy of ranged weapons with great precision, even as the enemy closes in around them.',
                'edition' => 'Chapter Approved 2025-26',
                'source' => 'chapter-approved-2025-26',
            ],
            [
                'name' => 'RAPID ESCALATION',
                'description' => 'Your armies hurl themselves recklessly into what is swiftly becoming a maelstrom of battle. With every passing moment, the flames of conflict rage higher.',
                'full_text' => 'Your armies hurl themselves recklessly into what is swiftly becoming a maelstrom of battle. With every passing moment, the flames of conflict rage higher.',
                'edition' => 'Chapter Approved 2025-26',
                'source' => 'chapter-approved-2025-26',
            ],
        ];

        // Supprimer les anciennes péripéties si --force
        if ($this->option('force')) {
            $deleted = TwistMission::where('source', 'chapter-approved-2025-26')->delete();
            $this->line("  🗑️  Supprimées: {$deleted} péripéties");
        }

        $created = 0;
        $skipped = 0;

        foreach ($twistMissions as $data) {
            // Vérifier si elle existe déjà
            $existing = TwistMission::where('name', $data['name'])
                ->where('source', $data['source'])
                ->first();

            if ($existing && !$this->option('force')) {
                $this->line("  ⏭️  {$data['name']}: Déjà existe");
                $skipped++;
                continue;
            }

            // Créer ou mettre à jour
            TwistMission::updateOrCreate(
                ['name' => $data['name'], 'source' => $data['source']],
                [
                    'description' => $data['description'],
                    'full_text' => $data['full_text'],
                    'effect' => $data['full_text'], // Utiliser full_text comme effect
                    'when_drawn' => null,
                    'timing' => 'any_battle_round',
                    'edition' => $data['edition'],
                    'slug' => \Str::slug($data['name']),
                    'is_active' => true,
                ]
            );

            $this->line("  ✅ {$data['name']}: Créée/Mise à jour");
            $created++;
        }

        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('📊 Résultats:');
        $this->info("  ✅ Créées/Mises à jour: {$created}");
        $this->info("  ⏭️  Ignorées: {$skipped}");
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('✅ Scraping des péripéties terminé !');

        return 0;
    }
}
