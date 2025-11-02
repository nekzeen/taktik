<?php

namespace App\Console\Commands;

use App\Models\SecondaryMission;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportSecondaryMissionsFromXml extends Command
{
    protected $signature = 'missions:import-secondary-xml {file? : Path to XML file} {--source=chapter-approved-2025-26}';
    protected $description = 'Importer les missions secondaires depuis un fichier XML';

    public function handle()
    {
        $file = $this->argument('file') ?? storage_path('missions/secondary-missions-chapter-approved-2025-26.xml');
        $source = $this->option('source');

        if (!file_exists($file)) {
            $this->error("❌ Fichier non trouvé: {$file}");
            return 1;
        }

        $this->info('📥 Importation des missions secondaires depuis XML...');
        $this->info('═══════════════════════════════════════════════════════════');

        try {
            $xml = simplexml_load_file($file);
            $missions = $xml->mission;

            if (empty($missions)) {
                $this->warn('⚠️  Aucune mission trouvée dans le fichier XML !');
                return 1;
            }

            $created = 0;
            $updated = 0;

            foreach ($missions as $missionXml) {
                try {
                    $title = (string) $missionXml->title;
                    $flavour = (string) $missionXml->flavour;
                    $when_drawn = (string) $missionXml->when_drawn;
                    $when = (string) $missionXml->when;
                    $notes = (string) $missionXml->notes;

                    // Extraire les items de scoring
                    $scoringItems = [];
                    if (isset($missionXml->scoring->item)) {
                        foreach ($missionXml->scoring->item as $item) {
                            $scoringItems[] = (string) $item;
                        }
                    }

                    // Créer ou mettre à jour la mission
                    $mission = SecondaryMission::updateOrCreate(
                        ['name' => $title, 'source' => $source],
                        [
                            'description' => $flavour ?: 'No description',
                            'full_text' => $this->buildFullText($title, $flavour, $when_drawn, $when, $scoringItems),
                            'when_drawn' => $when_drawn,
                            'when_condition' => $when,
                            'timing' => $this->extractTiming($when),
                            'scoring_conditions' => $scoringItems,
                            'max_vp' => $this->extractMaxVP($scoringItems),
                            'slug' => Str::slug($title),
                            'edition' => '10ed',
                            'source' => $source,
                            'is_active' => true,
                        ]
                    );

                    // Créer les sections
                    $mission->sections()->delete();
                    $sectionOrder = 0;

                    // Section When Drawn si présente
                    if (!empty($when_drawn)) {
                        $mission->sections()->create([
                            'type' => 'condition',
                            'order' => $sectionOrder++,
                            'title' => 'When Drawn',
                            'content' => $when_drawn,
                            'victory_points' => null,
                        ]);
                    }

                    // Sections Scoring
                    foreach ($scoringItems as $index => $scoringItem) {
                        $vp = $this->extractVPFromText($scoringItem);
                        $mission->sections()->create([
                            'type' => 'scoring',
                            'order' => $sectionOrder++,
                            'title' => 'Scoring ' . ($index + 1),
                            'content' => $scoringItem,
                            'victory_points' => $vp,
                        ]);
                    }

                    if ($mission->wasRecentlyCreated) {
                        $this->line("  ✅ Créée: {$title}");
                        $created++;
                    } else {
                        $this->line("  🔄 Mise à jour: {$title}");
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $this->error("  ❌ Erreur pour {$title}: {$e->getMessage()}");
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
        } catch (\Exception $e) {
            $this->error("❌ Erreur: {$e->getMessage()}");
            return 1;
        }
    }

    protected function buildFullText(string $title, string $flavour, string $when_drawn, string $when, array $scoring): string
    {
        $text = "Secondary Mission\n";
        $text .= "{$title}\n";
        if ($flavour) {
            $text .= "{$flavour}\n\n";
        }
        if ($when_drawn) {
            $text .= "WHEN DRAWN: {$when_drawn}\n\n";
        }
        if ($when) {
            $text .= "WHEN: {$when}\n\n";
        }
        if (!empty($scoring)) {
            $text .= "SCORING:\n";
            foreach ($scoring as $item) {
                $text .= "- {$item}\n";
            }
        }
        return $text;
    }

    protected function extractTiming(string $when): string
    {
        if (stripos($when, 'ANY BATTLE ROUND') !== false) {
            return 'any_battle_round';
        }
        if (stripos($when, 'SECOND BATTLE ROUND') !== false) {
            return 'second_battle_round_onwards';
        }
        return 'any_battle_round';
    }

    protected function extractMaxVP(array $scoringItems): int
    {
        $maxVP = 0;
        foreach ($scoringItems as $item) {
            if (preg_match('/(\d+)VP/i', $item, $matches)) {
                $vp = (int) $matches[1];
                if ($vp > $maxVP) {
                    $maxVP = $vp;
                }
            }
        }
        return $maxVP > 0 ? $maxVP : 15;
    }

    protected function extractVPFromText(string $text): ?int
    {
        if (preg_match('/(\d+)VP/i', $text, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }
}
