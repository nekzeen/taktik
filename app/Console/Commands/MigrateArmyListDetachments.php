<?php

namespace App\Console\Commands;

use App\Models\ArmyList;
use App\Models\Detachment;
use App\Services\ArmyListAnalyzer;
use Illuminate\Console\Command;

class MigrateArmyListDetachments extends Command
{
    protected $signature = 'army-lists:migrate-detachments';
    protected $description = 'Migrer les détachements texte vers les IDs de détachements';

    public function handle()
    {
        $this->info('🔄 Migration des détachements...');
        $this->info('═══════════════════════════════════════════════════════════');

        $analyzer = new ArmyListAnalyzer();
        $updated = 0;
        $skipped = 0;
        $notFound = 0;

        $armyLists = ArmyList::whereNull('detachment_id')->get();

        foreach ($armyLists as $armyList) {
            // Si pas de détachement texte, skip
            if (!$armyList->detachment) {
                $skipped++;
                continue;
            }

            // Chercher le détachement par nom
            $detachment = Detachment::where('name', $armyList->detachment)->first();

            if ($detachment) {
                $armyList->update(['detachment_id' => $detachment->id]);
                $this->line("  ✅ Liste #{$armyList->id}: {$armyList->detachment} → ID {$detachment->id}");
                $updated++;
            } else {
                // Essayer avec similarité
                $allDetachments = Detachment::all();
                $bestMatch = null;
                $bestSimilarity = 0;

                foreach ($allDetachments as $det) {
                    $similarity = $this->calculateSimilarity($armyList->detachment, $det->name);
                    if ($similarity > $bestSimilarity && $similarity >= 80) {
                        $bestSimilarity = $similarity;
                        $bestMatch = $det;
                    }
                }

                if ($bestMatch) {
                    $armyList->update(['detachment_id' => $bestMatch->id]);
                    $this->line("  ⚠️  Liste #{$armyList->id}: {$armyList->detachment} → {$bestMatch->name} (similarité: {$bestSimilarity}%)");
                    $updated++;
                } else {
                    $this->line("  ❌ Liste #{$armyList->id}: {$armyList->detachment} - Pas de correspondance");
                    $notFound++;
                }
            }
        }

        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info("📊 Résultats:");
        $this->info("  ✅ Mis à jour: {$updated}");
        $this->info("  ⏭️  Ignorés (pas de détachement): {$skipped}");
        $this->info("  ❌ Non trouvés: {$notFound}");
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('✅ Migration terminée !');

        return 0;
    }

    /**
     * Calculer la similarité entre deux chaînes (0-100%)
     */
    private function calculateSimilarity(string $str1, string $str2): float
    {
        $str1 = mb_strtolower(trim($str1), 'UTF-8');
        $str2 = mb_strtolower(trim($str2), 'UTF-8');
        
        // Normaliser les caractères spéciaux
        $str1 = str_replace(["'", "-", " "], "", $str1);
        $str2 = str_replace(["'", "-", " "], "", $str2);
        
        if ($str1 === $str2) {
            return 100.0;
        }
        
        $distance = levenshtein($str1, $str2);
        $maxLen = max(strlen($str1), strlen($str2));
        
        if ($maxLen === 0) {
            return 100.0;
        }
        
        return round((1 - ($distance / $maxLen)) * 100, 2);
    }
}
