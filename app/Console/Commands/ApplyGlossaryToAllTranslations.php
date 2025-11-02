<?php

namespace App\Console\Commands;

use App\Models\Translation;
use App\Models\WarhammerGlossary;
use App\Services\IntelligentTranslationService;
use Illuminate\Console\Command;

class ApplyGlossaryToAllTranslations extends Command
{
    protected $signature = 'glossary:apply-all {--locale=fr}';
    protected $description = 'Appliquer le glossaire à TOUTES les traductions du système';

    public function handle()
    {
        $locale = $this->option('locale');
        $translationService = new IntelligentTranslationService();

        $this->info('📚 Application du glossaire à toutes les traductions...');
        $this->info('═══════════════════════════════════════════════════════════');

        // Récupérer tous les termes approuvés du glossaire
        $glossaryTerms = WarhammerGlossary::approved()->get();

        if ($glossaryTerms->isEmpty()) {
            $this->warn('⚠️  Aucun terme approuvé dans le glossaire !');
            return 1;
        }

        $this->info("📖 Termes trouvés : {$glossaryTerms->count()}");
        $this->info('');

        $totalUpdated = 0;
        $totalTranslations = 0;

        foreach ($glossaryTerms as $term) {
            $translation = $term->getTranslation($locale);
            
            if (!$translation) {
                continue;
            }

            $this->line("🔄 Traitement : {$term->english_term} → {$translation}");

            // Appliquer le terme à toutes les traductions
            $results = $translationService->updateGlossaryTermAndPropagate(
                $term->english_term,
                $locale,
                $translation,
                $term->context
            );

            if ($results['translations_updated'] > 0) {
                $totalUpdated += $results['translations_updated'];
                $resourceTypes = implode(', ', $results['resource_types_updated']);
                $this->line("   ✅ {$results['translations_updated']} traductions mises à jour ({$resourceTypes})");
            } else {
                $this->line("   ⏭️  Aucune traduction à mettre à jour");
            }
        }

        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info("📊 Résultats:");
        $this->info("  📖 Termes traités : {$glossaryTerms->count()}");
        $this->info("  ✅ Traductions mises à jour : {$totalUpdated}");
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('✅ Application du glossaire terminée !');

        return 0;
    }
}
