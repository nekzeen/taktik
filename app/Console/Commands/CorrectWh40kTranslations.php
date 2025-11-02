<?php

namespace App\Console\Commands;

use App\Data\Wh40kTerminology;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CorrectWh40kTranslations extends Command
{
    protected $signature = 'wahapedia:correct-translations';
    protected $description = 'Corrige les traductions avec la terminologie Warhammer 40k';

    public function handle()
    {
        $this->info('🎯 Correction des traductions avec terminologie Warhammer 40k');
        $this->info('═══════════════════════════════════════════════════════════');

        $corrected = 0;
        $terminology = Wh40kTerminology::getTerminology();

        foreach ($terminology as $english => $french) {
            // Chercher les traductions qui correspondent au texte source
            $translations = DB::table('translations')
                ->where('source_text', $english)
                ->where('locale', 'fr')
                ->get();

            foreach ($translations as $translation) {
                // Mettre à jour si différent
                if ($translation->translated_text !== $french) {
                    DB::table('translations')
                        ->where('id', $translation->id)
                        ->update([
                            'translated_text' => $french,
                            'status' => 'reviewed', // Marquer comme révisé
                            'updated_at' => now(),
                        ]);

                    $this->line("  ✅ {$english} → {$french}");
                    $corrected++;
                }
            }
        }

        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info("📊 {$corrected} traductions corrigées");
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('✅ Correction terminée !');

        return 0;
    }
}
