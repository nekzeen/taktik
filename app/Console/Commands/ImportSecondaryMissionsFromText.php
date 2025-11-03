<?php

namespace App\Console\Commands;

use App\Models\SecondaryMission;
use Illuminate\Console\Command;

class ImportSecondaryMissionsFromText extends Command
{
    protected $signature = 'missions:import-secondary-text {--dry-run : Afficher les changements sans les appliquer}';
    protected $description = 'Importer les missions secondaires depuis du texte copié/collé';

    public function handle()
    {
        $this->info('📝 Import des missions secondaires depuis texte...');
        $this->line('');
        $this->info('Collez le texte des missions (terminez par une ligne vide):');
        $this->line('');

        $input = '';
        while (true) {
            $line = readline();
            if ($line === '') {
                break;
            }
            $input .= $line . "\n";
        }

        if (empty(trim($input))) {
            $this->error('Aucun texte fourni');
            return 1;
        }

        $missions = $this->parseMissions($input);

        if (empty($missions)) {
            $this->error('Aucune mission trouvée');
            return 1;
        }

        $this->info("📊 " . count($missions) . " missions trouvées\n");

        $dryRun = $this->option('dry-run');
        $count = 0;

        foreach ($missions as $mission) {
            try {
                $this->line("📋 {$mission['name']}");

                if (!$dryRun) {
                    SecondaryMission::updateOrCreate(
                        ['name' => $mission['name']],
                        [
                            'description' => $mission['description'] ?? '',
                            'full_text' => $mission['full_text'] ?? '',
                            'when_drawn' => $mission['when_drawn'] ?? '',
                            'when_condition' => $mission['when_condition'] ?? '',
                            'scoring_conditions' => json_encode($mission['scoring_items'] ?? []),
                            'max_vp' => $mission['max_vp'] ?? 15,
                            'slug' => \Str::slug($mission['name']),
                            'edition' => '10ed',
                            'source' => 'chapter-approved-2025-26',
                            'is_active' => true,
                        ]
                    );
                    $this->info('  ✅ Importée');
                } else {
                    $this->line('  [DRY-RUN] Serait importée');
                }
                $count++;
            } catch (\Exception $e) {
                $this->error("  ❌ Erreur: {$e->getMessage()}");
            }
        }

        $this->line('');
        $this->info("✅ {$count} missions traitées");

        if ($dryRun) {
            $this->warn('Mode DRY-RUN: Aucune donnée n\'a été modifiée');
        }

        return 0;
    }

    protected function parseMissions($text)
    {
        $missions = [];
        $blocks = preg_split('/^---\s*$/m', $text);

        foreach ($blocks as $block) {
            $block = trim($block);
            if (empty($block)) {
                continue;
            }

            $mission = $this->parseMission($block);
            if ($mission) {
                $missions[] = $mission;
            }
        }

        return $missions;
    }

    protected function parseMission($block)
    {
        // Extraire le titre (entre ** **)
        if (!preg_match('/\*\*([^*]+)\*\*/', $block, $matches)) {
            return null;
        }

        $name = trim($matches[1]);

        // Extraire la description (première ligne après le titre)
        $lines = explode("\n", $block);
        $description = '';
        $foundTitle = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, '**') !== false) {
                $foundTitle = true;
                continue;
            }
            if ($foundTitle && !empty($line) && $line !== 'When Drawn:' && !preg_match('/^(ANY|SECOND)/', $line)) {
                $description = $line;
                break;
            }
        }

        // Extraire "When Drawn"
        $whenDrawn = '';
        if (preg_match('/When Drawn:\s*(.+?)(?=\n\n|ANY BATTLE|SECOND BATTLE|$)/s', $block, $matches)) {
            $whenDrawn = trim($matches[1]);
        }

        // Extraire le texte complet
        $fullText = $block;

        // Extraire les conditions de scoring
        $scoringItems = [];
        if (preg_match_all('/^([^:]+?):\s*(.+?)(?=\n(?:[A-Z]|$))/m', $block, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $condition = trim($match[1]);
                $vp = trim($match[2]);
                if (!empty($condition) && !empty($vp)) {
                    $scoringItems[] = "{$condition}: {$vp}";
                }
            }
        }

        // Calculer max VP
        $maxVp = 15;
        if (preg_match_all('/(\d+)VP/', $block, $matches)) {
            $vps = array_map('intval', $matches[1]);
            $maxVp = max($vps);
        }

        return [
            'name' => $name,
            'description' => $description,
            'full_text' => $fullText,
            'when_drawn' => $whenDrawn,
            'when_condition' => 'ANY BATTLE ROUND',
            'scoring_items' => $scoringItems,
            'max_vp' => $maxVp,
        ];
    }
}
