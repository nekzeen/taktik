<?php

namespace App\Services;

use App\Models\WarhammerGlossary;
use App\Models\Translation;

class IntelligentTranslationService
{
    protected TranslationService $translationService;

    public function __construct()
    {
        $this->translationService = new TranslationService();
    }

    /**
     * Traduire un texte en utilisant le glossaire Warhammer
     * Cherche les termes anglais dans le texte SOURCE et les remplace dans le texte TRADUIT
     */
    public function translateWithGlossary(
        string $translatedText,
        string $locale = 'fr',
        string $context = 'general',
        string $sourceText = null
    ): string {
        // Chercher tous les termes du glossaire approuvés pour ce contexte
        $glossaryTerms = WarhammerGlossary::approved()
            ->byContext($context)
            ->get();

        // Remplacer les termes du glossaire dans le texte traduit
        // en cherchant d'abord dans le texte source
        foreach ($glossaryTerms as $term) {
            $translation = $term->getTranslation($locale);
            if ($translation) {
                // Si on a le texte source, vérifier que le terme y est présent
                if ($sourceText && stripos($sourceText, $term->english_term) === false) {
                    continue; // Le terme n'est pas dans le source, passer au suivant
                }
                
                // Remplacer avec respect de la casse
                $translatedText = $this->replaceTermWithCaseInsensitivity(
                    $translatedText,
                    $term->english_term,
                    $translation
                );
            }
        }

        return $translatedText;
    }

    /**
     * Remplacer un terme en respectant la casse
     */
    protected function replaceTermWithCaseInsensitivity(
        string $text,
        string $search,
        string $replace
    ): string {
        // Créer un pattern regex qui respecte les limites de mots
        $pattern = '/\b' . preg_quote($search, '/') . '\b/i';
        
        return preg_replace_callback($pattern, function ($matches) use ($search, $replace) {
            // Vérifier la casse du terme trouvé
            if (ctype_upper($matches[0][0])) {
                // Première lettre en majuscule
                return ucfirst($replace);
            }
            if (ctype_upper($matches[0])) {
                // Tout en majuscule
                return strtoupper($replace);
            }
            return $replace;
        }, $text);
    }

    /**
     * Extraire les termes Warhammer d'un texte et les ajouter au glossaire
     */
    public function extractAndSuggestTerms(string $text, string $context = 'general'): array
    {
        $suggestedTerms = [];

        // Chercher les mots en majuscules (conventions Warhammer)
        if (preg_match_all('/\b([A-Z][A-Z\s]+)\b/', $text, $matches)) {
            foreach ($matches[1] as $term) {
                $term = trim($term);
                
                // Vérifier si le terme existe déjà
                $existing = WarhammerGlossary::findByEnglishTerm($term);
                
                if (!$existing) {
                    $suggestedTerms[] = [
                        'term' => $term,
                        'context' => $context,
                        'category' => $this->categorizeTermByContext($term, $context),
                    ];
                }
            }
        }

        return $suggestedTerms;
    }

    /**
     * Catégoriser un terme basé sur le contexte
     */
    protected function categorizeTermByContext(string $term, string $context): string
    {
        // Patterns pour identifier les catégories
        $patterns = [
            'ability' => ['ACTION', 'ABILITY', 'POWER', 'EFFECT', 'AURA'],
            'keyword' => ['KEYWORD', 'TRAIT', 'RULE', 'SPECIAL'],
            'unit' => ['UNIT', 'MODEL', 'SQUAD', 'DETACHMENT'],
            'condition' => ['CONDITION', 'STATE', 'STATUS', 'WHEN', 'IF'],
            'action' => ['ACTION', 'MOVE', 'SHOOT', 'FIGHT', 'CHARGE'],
        ];

        foreach ($patterns as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($term, $keyword) !== false) {
                    return $category;
                }
            }
        }

        return 'general';
    }

    /**
     * Mettre à jour une traduction dans le glossaire et tous les textes associés (TOUTES les traductions du système)
     */
    public function updateGlossaryTermAndPropagate(
        string $englishTerm,
        string $locale,
        string $newTranslation,
        string $context = 'general'
    ): array {
        $results = [
            'glossary_updated' => false,
            'translations_updated' => 0,
            'resource_types_updated' => [],
        ];

        // Mettre à jour le glossaire
        $glossaryTerm = WarhammerGlossary::findByEnglishTerm($englishTerm);
        
        if ($glossaryTerm) {
            $glossaryTerm->setTranslation($locale, $newTranslation);
            $results['glossary_updated'] = true;
        }

        // Chercher TOUTES les traductions du système (tous les resource_type)
        $translations = Translation::all();

        foreach ($translations as $translation) {
            // Vérifier si la traduction contient le terme anglais
            if (stripos($translation->source_text, $englishTerm) !== false) {
                // Remplacer le terme dans le texte traduit
                $oldTranslatedText = $translation->translated_text;
                $newTranslatedText = $this->replaceTermWithCaseInsensitivity(
                    $oldTranslatedText,
                    $englishTerm,
                    $newTranslation
                );

                if ($oldTranslatedText !== $newTranslatedText) {
                    $translation->translated_text = $newTranslatedText;
                    $translation->status = 'reviewed'; // Marquer comme révisée
                    $translation->save();
                    $results['translations_updated']++;
                    
                    // Tracker les types de ressources mises à jour
                    if (!in_array($translation->resource_type, $results['resource_types_updated'])) {
                        $results['resource_types_updated'][] = $translation->resource_type;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Générer des suggestions de traductions basées sur le glossaire
     */
    public function suggestTranslations(string $text, string $locale = 'fr'): array
    {
        $suggestions = [];

        // Chercher les termes Warhammer dans le texte
        if (preg_match_all('/\b([A-Z][A-Za-z\s]+)\b/', $text, $matches)) {
            foreach ($matches[1] as $term) {
                $term = trim($term);
                $glossaryTerm = WarhammerGlossary::findByEnglishTerm($term);

                if ($glossaryTerm) {
                    $translation = $glossaryTerm->getTranslation($locale);
                    if ($translation) {
                        $suggestions[$term] = $translation;
                    }
                }
            }
        }

        return $suggestions;
    }
}
