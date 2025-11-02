<?php

namespace App\Console\Commands;

use App\Models\TwistMission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class ImportTwistFromWahapedia extends Command
{
    protected $signature = 'twist:import-wahapedia';
    protected $description = 'Scraper et importer les péripéties depuis Wahapedia';

    public function handle()
    {
        $this->info('🎯 SCRAPING DES PÉRIPÉTIES DEPUIS WAHAPEDIA');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('');

        // Exécuter le script Python
        $this->info('📥 Scraping Wahapedia...');
        $pythonScript = base_path('extract_twists.py');

        if (!File::exists($pythonScript)) {
            $this->error('❌ Script Python non trouvé: ' . $pythonScript);
            return 1;
        }

        $process = new Process(['python3', $pythonScript]);
        $process->setWorkingDirectory(base_path());
        $process->setTimeout(60);

        try {
            $process->mustRun();
            $this->line($process->getOutput());
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors du scraping: ' . $e->getMessage());
            return 1;
        }

        // Lire le fichier JSON généré
        $jsonPath = base_path('twist_cards.json');

        if (!File::exists($jsonPath)) {
            $this->error('❌ Fichier JSON non généré');
            return 1;
        }

        $jsonData = json_decode(File::get($jsonPath), true);

        if (!isset($jsonData['twist_cards']) || empty($jsonData['twist_cards'])) {
            $this->error('❌ Aucune péripétie trouvée dans le JSON');
            return 1;
        }

        // Importer les péripéties
        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('📥 Importation des données...');
        $this->info('');

        $created = 0;
        $skipped = 0;

        foreach ($jsonData['twist_cards'] as $data) {
            $name = trim($data['name'] ?? '');
            $text = trim($data['text'] ?? '');

            if (empty($name) || strlen($name) < 3) {
                $this->line("  ⏭️  Nom invalide: '{$name}'");
                $skipped++;
                continue;
            }

            // Vérifier si elle existe déjà
            $existing = TwistMission::where('name', $name)
                ->where('source', 'chapter-approved-2025-26')
                ->first();

            if ($existing) {
                $this->line("  ⏭️  {$name}: Déjà existe (non écrasée)");
                $skipped++;
                continue;
            }

            // Extraire description et effet
            $lines = explode("\n", $text);
            $description = isset($lines[0]) ? trim($lines[0]) : '';
            $effectLines = array_slice($lines, 1);
            $effect = implode("\n", $effectLines);

            // Créer la péripétie
            try {
                TwistMission::create([
                    'name' => $name,
                    'description' => $description,
                    'full_text' => $text,
                    'effect' => $effect,
                    'when_drawn' => null,
                    'timing' => 'any_battle_round',
                    'edition' => 'Chapter Approved 2025-26',
                    'source' => 'chapter-approved-2025-26',
                    'slug' => \Str::slug($name),
                    'is_active' => true,
                ]);

                $this->line("  ✅ {$name}: Importée");
                $created++;
            } catch (\Exception $e) {
                $this->line("  ❌ {$name}: Erreur - " . $e->getMessage());
                $skipped++;
            }
        }

        // Résumé
        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info("✅ Importation terminée:");
        $this->line("   • Créées: {$created}");
        $this->line("   • Ignorées: {$skipped}");
        $this->info('═══════════════════════════════════════════════════════════');

        // Nettoyer le fichier JSON
        File::delete($jsonPath);

        return 0;
    }
}
