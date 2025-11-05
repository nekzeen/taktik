<?php

namespace App\Observers;

use App\Models\Translation;

class TranslationObserver
{
    /**
     * Quand une traduction est créée
     * 
     * ⚠️ GLOSSAIRE COMPLÈTEMENT DÉSACTIVÉ
     * Raison: Le glossaire n'est plus utilisé dans les traductions
     * Les traductions se font uniquement via DeepL
     * Le glossaire ne doit pas être appliqué automatiquement
     */
    public function created(Translation $translation): void
    {
        // Glossaire désactivé - ne rien faire
        return;
        
        // Extraire le terme anglais du source_text
        $englishTerm = $this->extractTermFromSourceText($translation->source_text);

        if ($englishTerm) {
            // Chercher ou créer l'entrée dans le glossaire
            $glossaryTerm = WarhammerGlossary::findByEnglishTerm($englishTerm);

            if (!$glossaryTerm) {
                // Créer une nouvelle entrée (approuvée automatiquement)
                $glossaryTerm = WarhammerGlossary::create([
                    'english_term' => $englishTerm,
                    'category' => $this->categorizeTermByContext($englishTerm, $translation->resource_type),
                    'context' => $this->getContextFromResourceType($translation->resource_type),
                    'status' => 'approved', // Approuvé automatiquement
                ]);
            }

            // Mettre à jour la traduction appropriée dans le glossaire
            $locale = $this->extractLocaleFromTranslation($translation);
            if ($locale) {
                $glossaryTerm->setTranslation($locale, $translation->translated_text);
                // Garder le statut 'approved' (approuvé automatiquement)
                if ($glossaryTerm->status !== 'approved') {
                    $glossaryTerm->status = 'approved';
                    $glossaryTerm->save();
                }
            }

            // Incrémenter le compteur d'utilisation
            $glossaryTerm->incrementUsage();
        }
    }

    /**
     * Quand une traduction est mise à jour
     * 
     * ⚠️ GLOSSAIRE COMPLÈTEMENT DÉSACTIVÉ
     */
    public function updated(Translation $translation): void
    {
        // Glossaire désactivé - ne rien faire
        return;
    }

}
