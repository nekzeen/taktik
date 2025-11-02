<?php

namespace App\Console\Commands;

use App\Models\TwistMission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportTwistFromJson extends Command
{
    protected $signature = 'twist:import-json {file?}';
    protected $description = 'Importer les péripéties depuis un fichier JSON';

    public function handle()
    {
        $file = $this->argument('file') ?? storage_path('missions/twist-missions-import.json');

        $this->info('🎯 IMPORTATION DES PÉRIPÉTIES DEPUIS JSON');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('');

        if (!File::exists($file)) {
            $this->error("❌ Fichier non trouvé: {$file}");
            return 1;
        }

        $jsonData = json_decode(File::get($file), true);

        if (!isset($jsonData['twist_missions']) || empty($jsonData['twist_missions'])) {
            $this->error('❌ Aucune péripétie trouvée dans le JSON');
            return 1;
        }

        $this->info('📥 Importation des données...');
        $this->info('');

        $created = 0;
        $skipped = 0;

        foreach ($jsonData['twist_missions'] as $data) {
            $name = trim($data['name'] ?? '');
            $description = trim($data['description'] ?? '');
            $effect = trim($data['effect'] ?? '');
            $fullText = trim($data['full_text'] ?? ($description . "\n\n" . $effect));

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

            // Créer la péripétie
            try {
                TwistMission::create([
                    'name' => $name,
                    'description' => $description,
                    'full_text' => $fullText,
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

        return 0;
    }
}
