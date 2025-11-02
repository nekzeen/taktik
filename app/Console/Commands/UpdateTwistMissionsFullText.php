<?php

namespace App\Console\Commands;

use App\Models\TwistMission;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class UpdateTwistMissionsFullText extends Command
{
    protected $signature = 'twist:update-full-text';
    protected $description = 'Mettre à jour le texte complet des péripéties depuis le fichier JSON';

    public function handle()
    {
        $this->info('📝 Mise à jour du texte complet des péripéties...');
        $this->info('═══════════════════════════════════════════════════════════');

        // Lire le fichier JSON
        $jsonPath = storage_path('missions/twist-missions-complete-texts.json');
        
        if (!file_exists($jsonPath)) {
            $this->error("❌ Fichier non trouvé: {$jsonPath}");
            return 1;
        }

        $json = json_decode(file_get_contents($jsonPath), true);
        
        if (!$json || !isset($json['twist_missions'])) {
            $this->error('❌ Format JSON invalide');
            return 1;
        }

        $updated = 0;
        $skipped = 0;

        foreach ($json['twist_missions'] as $data) {
            // Vérifier que le texte n'est pas un placeholder
            if (strpos($data['full_text'], 'TEXTE COMPLET À FOURNIR') !== false) {
                $this->line("  ⏭️  {$data['name']}: Texte incomplet (placeholder)");
                $skipped++;
                continue;
            }

            // Trouver et mettre à jour la péripétie
            $mission = TwistMission::where('name', $data['name'])->first();
            
            if (!$mission) {
                $this->line("  ❌ {$data['name']}: Péripétie non trouvée");
                continue;
            }

            $mission->update([
                'full_text' => $data['full_text'],
                'effect' => $data['effect'] ?? $data['full_text'], // Utiliser effect du JSON, sinon full_text
            ]);

            $this->line("  ✅ {$data['name']}: Texte complet mis à jour");
            $updated++;
        }

        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('📊 Résultats:');
        $this->info("  ✅ Mises à jour: {$updated}");
        $this->info("  ⏭️  Ignorées (placeholder): {$skipped}");
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('✅ Mise à jour terminée !');

        return 0;
    }
}
