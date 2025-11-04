<?php

namespace App\Observers;

use App\Models\WarhammerGlossary;
use App\Services\IntelligentTranslationService;

class WarhammerGlossaryObserver
{
    protected IntelligentTranslationService $translationService;
    
    /**
     * Flag pour éviter les boucles infinies
     */
    private static bool $isProcessing = false;

    public function __construct()
    {
        $this->translationService = new IntelligentTranslationService();
    }

    /**
     * Quand une entrée du glossaire est mise à jour
     * 
     * ⚠️ PROTÉGÉ - Utilise un flag pour éviter les boucles infinies
     * Voir INFINITE_LOOP_PREVENTION.md
     */
    public function updated(WarhammerGlossary $glossary): void
    {
        // Éviter les boucles infinies
        if (self::$isProcessing) {
            \Log::warning("⚠️ WarhammerGlossaryObserver.updated() - Boucle détectée, abandon");
            return;
        }

        self::$isProcessing = true;

        try {
            // Vérifier si une traduction a changé
            $changedLocales = [];

            if ($glossary->isDirty('french_translation')) {
                $changedLocales['fr'] = $glossary->french_translation;
            }
            if ($glossary->isDirty('german_translation')) {
                $changedLocales['de'] = $glossary->german_translation;
            }
            if ($glossary->isDirty('spanish_translation')) {
                $changedLocales['es'] = $glossary->spanish_translation;
            }
            if ($glossary->isDirty('italian_translation')) {
                $changedLocales['it'] = $glossary->italian_translation;
            }

            // Propager les changements à toutes les traductions
            foreach ($changedLocales as $locale => $newTranslation) {
                if ($newTranslation) {
                    $this->translationService->updateGlossaryTermAndPropagate(
                        $glossary->english_term,
                        $locale,
                        $newTranslation,
                        $glossary->context
                    );
                }
            }
        } finally {
            self::$isProcessing = false;
        }
    }

    /**
     * Quand une entrée du glossaire est créée
     */
    public function created(WarhammerGlossary $glossary): void
    {
        // Incrémenter le compteur d'utilisation
        $glossary->incrementUsage();
    }
}
