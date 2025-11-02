<?php

namespace App\Console\Commands;

use App\Models\ArmyList;
use App\Services\ArmyListAnalyzer;
use Illuminate\Console\Command;

class AnalyzeArmyListPdfs extends Command
{
    protected $signature = 'army-lists:analyze-pdfs {--force : Réanalyser tous les PDFs}';
    protected $description = 'Analyser les PDFs des listes d\'armées pour mettre à jour les détachements';

    public function handle()
    {
        $this->info('📄 Analyse des PDFs des listes d\'armées...');
        $this->info('═══════════════════════════════════════════════════════════');

        $analyzer = new ArmyListAnalyzer();
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        $query = ArmyList::whereNotNull('pdf_path');
        
        if (!$this->option('force')) {
            $query->whereNull('detachment_id');
        }

        $armyLists = $query->get();

        foreach ($armyLists as $armyList) {
            try {
                $fullPath = storage_path('app/public/' . $armyList->pdf_path);
                
                if (!file_exists($fullPath)) {
                    $this->line("  ⚠️  Liste #{$armyList->id}: PDF non trouvé");
                    $skipped++;
                    continue;
                }

                // Analyser le PDF
                $content = file_get_contents($fullPath);
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseContent($content);
                $text = $pdf->getText();

                // Détecter le détachement
                $detachmentId = $analyzer->detectDetachmentId($text);

                if ($detachmentId && $armyList->detachment_id !== $detachmentId) {
                    $detachmentName = \App\Models\Detachment::find($detachmentId)?->name ?? 'Détachement #' . $detachmentId;
                    $armyList->update(['detachment_id' => $detachmentId]);
                    $this->line("  ✅ Liste #{$armyList->id}: Détachement détecté → {$detachmentName}");
                    $updated++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $this->line("  ❌ Liste #{$armyList->id}: Erreur - {$e->getMessage()}");
                $errors++;
            }
        }

        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info("📊 Résultats:");
        $this->info("  ✅ Mis à jour: {$updated}");
        $this->info("  ⏭️  Ignorés: {$skipped}");
        $this->info("  ❌ Erreurs: {$errors}");
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('✅ Analyse terminée !');

        return 0;
    }
}
