<?php

namespace App\Observers;

use App\Models\Translation;
use App\Models\WarhammerGlossary;

class TranslationObserver
{
    /**
     * Flag pour éviter les boucles infinies
     */
    private static bool $isProcessing = false;

    /**
     * Quand une traduction est créée
     * 
     * ⚠️ DÉSACTIVÉ POUR LES MISSIONS SECONDAIRES
     * Raison: Le glossaire ne peut stocker que 255 caractères
     * Les traductions de missions secondaires font 1000+ caractères
     * Cela causait une corruption du glossaire
     */
    public function created(Translation $translation): void
    {
        // Ignorer les missions secondaires
        if ($translation->resource_type === 'SecondaryMission') {
            return;
        }
        
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
     */
    public function updated(Translation $translation): void
    {
        // Éviter les boucles infinies
        if (self::$isProcessing) {
            \Log::warning("⚠️ TranslationObserver.updated() - Boucle détectée, abandon du traitement");
            return;
        }

        self::$isProcessing = true;

        try {
            \Log::info("🔄 TranslationObserver.updated() appelé pour translation ID: {$translation->id}");
            
            // Vérifier si le texte traduit a changé
            if ($translation->isDirty('translated_text')) {
            \Log::info("✏️ Texte traduit modifié: '{$translation->getOriginal('translated_text')}' → '{$translation->translated_text}'");
            $oldTranslation = $translation->getOriginal('translated_text');
            $newTranslation = $translation->translated_text;

            // Extraire le terme anglais du source_text
            $englishTerm = $this->extractTermFromSourceText($translation->source_text);

            if ($englishTerm) {
                \Log::info("📝 Terme anglais extrait: '{$englishTerm}'");
                
                // Chercher ou créer l'entrée dans le glossaire
                $glossaryTerm = WarhammerGlossary::findByEnglishTerm($englishTerm);

                if (!$glossaryTerm) {
                    \Log::info("➕ Création nouvelle entrée glossaire pour: '{$englishTerm}'");
                    // Créer une nouvelle entrée (approuvée automatiquement)
                    $glossaryTerm = WarhammerGlossary::create([
                        'english_term' => $englishTerm,
                        'category' => $this->categorizeTermByContext($englishTerm, $translation->resource_type),
                        'context' => $this->getContextFromResourceType($translation->resource_type),
                        'status' => 'approved', // Approuvé automatiquement
                    ]);
                } else {
                    \Log::info("✅ Entrée glossaire trouvée pour: '{$englishTerm}'");
                }

                // Mettre à jour la traduction appropriée dans le glossaire
                $locale = $this->extractLocaleFromTranslation($translation);
                if ($locale) {
                    \Log::info("🌍 Mise à jour traduction glossaire pour locale: '{$locale}'");
                    
                    // Vérifier que la nouvelle traduction n'est pas corrompue
                    if (!$this->isTextCorrupted($newTranslation)) {
                        $glossaryTerm->setTranslation($locale, $newTranslation);
                        // Garder le statut 'approved' (approuvé automatiquement)
                        if ($glossaryTerm->status !== 'approved') {
                            $glossaryTerm->status = 'approved';
                            $glossaryTerm->save();
                        }
                    } else {
                        \Log::error("❌ Tentative de mise à jour glossaire avec texte corrompu détecté");
                    }
                }

                // Marquer la traduction comme "reviewed" (modifiée manuellement)
                // Désactiver les événements pour éviter une boucle infinie
                $translation->withoutEvents(function () use ($translation) {
                    $translation->status = 'reviewed';
                    $translation->save();
                });
                \Log::info("🔖 Traduction marquée comme 'reviewed'");

                // Incrémenter le compteur d'utilisation
                $glossaryTerm->incrementUsage();
                \Log::info("📊 Compteur d'utilisation incrémenté");

                // Propager la modification à TOUTES les traductions du système
                \Log::info("📢 Propagation de la modification à toutes les traductions...");
                $this->propagateToAllTranslations($englishTerm, $locale, $newTranslation, $translation->id);
            }
            }
        } finally {
            self::$isProcessing = false;
        }
    }

    /**
     * Propager la modification à TOUTES les traductions du système
     */
    protected function propagateToAllTranslations(string $englishTerm, string $locale, string $newTranslation, int $currentTranslationId): void
    {
        // Chercher TOUTES les traductions contenant ce terme dans le source_text
        $translations = Translation::where('source_text', 'like', '%' . $englishTerm . '%')
            ->where('locale', $locale)
            ->where('id', '!=', $currentTranslationId)
            ->get();

        foreach ($translations as $trans) {
            // Remplacer le terme dans le texte traduit
            $oldTranslatedText = $trans->translated_text;
            $newTranslatedText = $this->replaceTermWithCaseInsensitivity(
                $oldTranslatedText,
                $englishTerm,
                $newTranslation
            );

            if ($oldTranslatedText !== $newTranslatedText) {
                // Désactiver les événements pour éviter une boucle infinie
                $trans->withoutEvents(function () use ($trans, $newTranslatedText) {
                    $trans->translated_text = $newTranslatedText;
                    $trans->status = 'reviewed'; // Marquer comme révisée
                    $trans->save();
                });
            }
        }

        // Aussi chercher les traductions qui contiennent l'ANCIENNE traduction du glossaire
        // Cela permet de propager les corrections même si le terme anglais n'est pas dans source_text
        $glossaryTerm = WarhammerGlossary::findByEnglishTerm($englishTerm);
        if ($glossaryTerm) {
            $oldGlossaryTranslation = $glossaryTerm->getTranslation($locale);
            
            // Si l'ancienne traduction du glossaire est différente de la nouvelle
            if ($oldGlossaryTranslation && $oldGlossaryTranslation !== $newTranslation) {
                // Chercher toutes les traductions contenant l'ancienne traduction
                $translationsWithOldTerm = Translation::where('translated_text', 'like', '%' . $oldGlossaryTranslation . '%')
                    ->where('locale', $locale)
                    ->where('id', '!=', $currentTranslationId)
                    ->get();

                foreach ($translationsWithOldTerm as $trans) {
                    $oldTranslatedText = $trans->translated_text;
                    $newTranslatedText = $this->replaceTermWithCaseInsensitivity(
                        $oldTranslatedText,
                        $oldGlossaryTranslation,
                        $newTranslation
                    );

                    if ($oldTranslatedText !== $newTranslatedText) {
                        // Désactiver les événements pour éviter une boucle infinie
                        $trans->withoutEvents(function () use ($trans, $newTranslatedText) {
                            $trans->translated_text = $newTranslatedText;
                            $trans->status = 'reviewed'; // Marquer comme révisée
                            $trans->save();
                        });
                    }
                }
            }
        }
    }

    /**
     * Remplacer un terme en respectant la casse
     */
    protected function replaceTermWithCaseInsensitivity(string $text, string $search, string $replace): string
    {
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
     * Vérifier si le texte est corrompu (contient des répétitions)
     */
    protected function isTextCorrupted(string $text): bool
    {
        // Vérifier si le texte contient des répétitions de "### " ou "**"
        if (preg_match('/###\s+\w+\s+\*\*.*###\s+\w+\s+\*\*/i', $text)) {
            return true;
        }

        // Vérifier si le texte contient plus de 3 répétitions du même pattern court
        if (preg_match_all('/###\s+\w+\s+\*\*/', $text, $matches) && count($matches[0]) > 3) {
            return true;
        }

        // Vérifier si la longueur est anormalement grande (plus de 10x la normale)
        $normalLength = 500; // Longueur normale estimée
        if (strlen($text) > $normalLength * 10) {
            return true;
        }

        return false;
    }

    /**
     * Extraire le terme anglais du texte source
     */
    protected function extractTermFromSourceText(string $sourceText): ?string
    {
        // Vérifier si le texte source est corrompu
        if ($this->isTextCorrupted($sourceText)) {
            \Log::error("❌ Texte source corrompu détecté: " . substr($sourceText, 0, 100));
            return null;
        }

        // Chercher les mots en majuscules (conventions Warhammer)
        // Limiter à 3 mots maximum pour éviter de capturer des phrases entières
        if (preg_match('/\b([A-Z][A-Z\s]{0,50}?)\b(?:\s|$|[^A-Z])/', $sourceText, $matches)) {
            $term = trim($matches[1]);
            
            // Limiter à 3 mots maximum
            $words = explode(' ', $term);
            if (count($words) > 3) {
                $term = implode(' ', array_slice($words, 0, 3));
            }
            
            // Vérifier que le terme n'est pas vide et ne contient pas trop d'espaces
            if (!empty($term) && strlen($term) <= 100) {
                return $term;
            }
        }

        return null;
    }

    /**
     * Catégoriser un terme basé sur le contexte
     */
    protected function categorizeTermByContext(string $term, string $resourceType): string
    {
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
     * Obtenir le contexte basé sur le type de ressource
     */
    protected function getContextFromResourceType(string $resourceType): string
    {
        return match($resourceType) {
            'PrimaryMission' => 'primary_mission',
            'SecondaryMission' => 'secondary_mission',
            'PrimaryMissionSection' => 'primary_mission',
            'SecondaryMissionSection' => 'secondary_mission',
            'TwistMission' => 'twist_mission',
            'AsymmetricPrimaryMission' => 'asymmetric_primary_mission',
            'StrikeForceDeploymentCard' => 'strike_force_deployment',
            'IncursionDeploymentCard' => 'incursion_deployment',
            'AsymmetricWarfareDeploymentCard' => 'asymmetric_warfare_deployment',
            default => 'general',
        };
    }

    /**
     * Extraire la locale de la traduction
     */
    protected function extractLocaleFromTranslation(Translation $translation): ?string
    {
        // La locale est stockée dans le champ 'locale' du modèle Translation
        return $translation->locale ?? 'fr';
    }
}
