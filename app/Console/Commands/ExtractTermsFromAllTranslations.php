<?php

namespace App\Console\Commands;

use App\Models\Translation;
use App\Models\WarhammerGlossary;
use App\Services\IntelligentTranslationService;
use Illuminate\Console\Command;

class ExtractTermsFromAllTranslations extends Command
{
    protected $signature = 'glossary:extract-terms {--context=general}';
    protected $description = 'Extraire les termes Warhammer de TOUTES les traductions et les ajouter au glossaire';

    public function handle()
    {
        $context = $this->option('context');
        $translationService = new IntelligentTranslationService();

        $this->info('🔍 Extraction des termes Warhammer de toutes les traductions...');
        $this->info('═══════════════════════════════════════════════════════════');

        // Récupérer toutes les traductions
        $translations = Translation::all();

        if ($translations->isEmpty()) {
            $this->warn('⚠️  Aucune traduction trouvée !');
            return 1;
        }

        $this->info("📊 Traductions trouvées : {$translations->count()}");
        $this->info('');

        $suggestedTerms = [];
        $addedTerms = 0;

        foreach ($translations as $translation) {
            // Extraire les termes du texte source
            $terms = $translationService->extractAndSuggestTerms(
                $translation->source_text,
                $context
            );

            foreach ($terms as $term) {
                $key = strtolower($term['term']);
                
                if (!isset($suggestedTerms[$key])) {
                    $suggestedTerms[$key] = $term;
                }
            }
        }

        $this->info("📖 Termes suggérés : " . count($suggestedTerms));
        $this->info('');

        // Ajouter les termes au glossaire
        foreach ($suggestedTerms as $term) {
            try {
                $existing = WarhammerGlossary::findByEnglishTerm($term['term']);
                
                if (!$existing) {
                    WarhammerGlossary::create([
                        'english_term' => $term['term'],
                        'category' => $term['category'],
                        'context' => $term['context'],
                        'status' => 'pending', // À approuver manuellement
                    ]);

                    $this->line("  ✅ Ajouté : {$term['term']} ({$term['category']})");
                    $addedTerms++;
                } else {
                    $this->line("  ⏭️  Existe déjà : {$term['term']}");
                }
            } catch (\Exception $e) {
                $this->error("  ❌ Erreur pour {$term['term']}: {$e->getMessage()}");
            }
        }

        $this->info('');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info("📊 Résultats:");
        $this->info("  📖 Termes suggérés : " . count($suggestedTerms));
        $this->info("  ✅ Termes ajoutés : {$addedTerms}");
        $this->info("  ⏳ Statut : pending (à approuver manuellement)");
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('✅ Extraction terminée !');
        $this->info('');
        $this->info('💡 Prochaines étapes :');
        $this->info('   1. Admin → Warhammer 40k → Glossaire Warhammer');
        $this->info('   2. Filtrer par statut "En attente"');
        $this->info('   3. Ajouter les traductions et approuver');
        $this->info('   4. Exécuter : php artisan glossary:apply-all');

        return 0;
    }
}
