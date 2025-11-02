<?php

namespace App\Console\Commands;

use App\Models\Translation;
use App\Models\WarhammerGlossary;
use Illuminate\Console\Command;

class SyncGlossaryTranslations extends Command
{
    protected $signature = 'glossary:sync {--locale=fr}';
    protected $description = 'Synchroniser les traductions du glossaire avec les traductions existantes du système';

    public function handle()
    {
        $locale = $this->option('locale');

        $this->info('🔄 Synchronisation du glossaire avec les traductions...');
        $this->info('═══════════════════════════════════════════════════════════');

        // Récupérer toutes les traductions approuvées du glossaire
        $glossaryTerms = WarhammerGlossary::approved()->get();

        if ($glossaryTerms->isEmpty()) {
            $this->warn('⚠️  Aucun terme approuvé dans le glossaire !');
            return 1;
        }

        $this->info("📖 Termes approuvés : {$glossaryTerms->count()}");
        $this->info('');

        $synced = 0;
        $skipped = 0;

        foreach ($glossaryTerms as $term) {
            $translation = $term->getTranslation($locale);
            
            if (!$translation) {
                $this->line("  ⏭️  {$term->english_term} : Pas de traduction {$locale}");
                $skipped++;
                continue;
            }

            // Chercher les traductions du système contenant ce terme
            $systemTranslations = Translation::where('source_text', 'like', '%' . $term->english_term . '%')
                ->get();

            if ($systemTranslations->isEmpty()) {
                $this->line("  ⏭️  {$term->english_term} : Aucune utilisation");
                $skipped++;
                continue;
            }

            $this->line("  🔄 {$term->english_term} : {$systemTranslations->count()} utilisations");
            $synced += $systemTranslations->count();
        }

        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info("📊 Résultats:");
        $this->info("  📖 Termes approuvés : {$glossaryTerms->count()}");
        $this->info("  ✅ Traductions synchronisées : {$synced}");
        $this->info("  ⏭️  Termes non utilisés : {$skipped}");
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('✅ Synchronisation terminée !');

        return 0;
    }
}
