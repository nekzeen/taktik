<?php

namespace App\Services;

use App\Models\Translation;
use App\Models\WarhammerGlossary;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service pour la gestion des traductions
 * 
 * Centralise la logique métier liée aux traductions et au glossaire
 */
class TranslationManagementService
{
    /**
     * Obtenir toutes les traductions en attente
     */
    public function getPendingTranslations(): Collection
    {
        return Translation::where('status', 'pending')
            ->with(['resource'])
            ->get();
    }

    /**
     * Obtenir toutes les traductions automatiques
     */
    public function getAutoTranslations(): Collection
    {
        return Translation::where('status', 'auto')
            ->with(['resource'])
            ->get();
    }

    /**
     * Obtenir toutes les traductions révisées
     */
    public function getReviewedTranslations(): Collection
    {
        return Translation::where('status', 'reviewed')
            ->with(['resource'])
            ->get();
    }

    /**
     * Obtenir toutes les traductions approuvées
     */
    public function getApprovedTranslations(): Collection
    {
        return Translation::where('status', 'approved')
            ->with(['resource'])
            ->get();
    }

    /**
     * Obtenir les traductions d'une ressource
     */
    public function getResourceTranslations($resourceType, $resourceId): Collection
    {
        return Translation::where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->get();
    }

    /**
     * Obtenir les traductions d'une ressource par locale
     */
    public function getResourceTranslationsByLocale($resourceType, $resourceId, string $locale): Collection
    {
        return Translation::where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->where('locale', $locale)
            ->get();
    }

    /**
     * Obtenir le statut de traduction d'une ressource
     */
    public function getResourceTranslationStatus($resourceType, $resourceId, string $locale): string
    {
        $translations = $this->getResourceTranslationsByLocale($resourceType, $resourceId, $locale);

        if ($translations->isEmpty()) {
            return 'not_translated';
        }

        $statuses = $translations->pluck('status')->unique();

        if ($statuses->count() === 1) {
            return $statuses->first();
        }

        // Si plusieurs statuts, retourner le plus avancé
        $statusOrder = ['approved', 'reviewed', 'auto', 'pending'];
        foreach ($statusOrder as $status) {
            if ($statuses->contains($status)) {
                return $status;
            }
        }

        return 'pending';
    }

    /**
     * Obtenir le pourcentage de traduction d'une ressource
     */
    public function getResourceTranslationPercentage($resourceType, $resourceId, string $locale): int
    {
        $translations = $this->getResourceTranslationsByLocale($resourceType, $resourceId, $locale);

        if ($translations->isEmpty()) {
            return 0;
        }

        $translated = $translations->filter(fn($t) => !empty($t->translated_text))->count();

        return (int) (($translated / $translations->count()) * 100);
    }

    /**
     * Obtenir les traductions par statut
     */
    public function getTranslationsByStatus(string $status): Collection
    {
        return Translation::where('status', $status)
            ->with(['resource'])
            ->get();
    }

    /**
     * Obtenir les traductions par locale
     */
    public function getTranslationsByLocale(string $locale): Collection
    {
        return Translation::where('locale', $locale)
            ->with(['resource'])
            ->get();
    }

    /**
     * Obtenir les entrées du glossaire
     */
    public function getGlossaryEntries(): Collection
    {
        return WarhammerGlossary::with(['translations'])
            ->orderBy('english_term')
            ->get();
    }

    /**
     * Obtenir les entrées du glossaire par catégorie
     */
    public function getGlossaryEntriesByCategory(string $category): Collection
    {
        return WarhammerGlossary::where('category', $category)
            ->orderBy('english_term')
            ->get();
    }

    /**
     * Obtenir les entrées du glossaire par contexte
     */
    public function getGlossaryEntriesByContext(string $context): Collection
    {
        return WarhammerGlossary::where('context', $context)
            ->orderBy('english_term')
            ->get();
    }

    /**
     * Chercher une entrée du glossaire
     */
    public function searchGlossary(string $term): Collection
    {
        return WarhammerGlossary::where('english_term', 'LIKE', "%{$term}%")
            ->orWhere('french_translation', 'LIKE', "%{$term}%")
            ->orderBy('english_term')
            ->get();
    }

    /**
     * Obtenir les entrées du glossaire les plus utilisées
     */
    public function getMostUsedGlossaryEntries(int $limit = 10): Collection
    {
        return WarhammerGlossary::orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir les entrées du glossaire non utilisées
     */
    public function getUnusedGlossaryEntries(): Collection
    {
        return WarhammerGlossary::where('usage_count', 0)
            ->orderBy('english_term')
            ->get();
    }

    /**
     * Obtenir les statistiques de traduction
     */
    public function getTranslationStats(): array
    {
        $total = Translation::count();
        $pending = Translation::where('status', 'pending')->count();
        $auto = Translation::where('status', 'auto')->count();
        $reviewed = Translation::where('status', 'reviewed')->count();
        $approved = Translation::where('status', 'approved')->count();

        $locales = Translation::distinct('locale')->pluck('locale');
        $resourceTypes = Translation::distinct('resource_type')->pluck('resource_type');

        return [
            'total' => $total,
            'pending' => $pending,
            'auto' => $auto,
            'reviewed' => $reviewed,
            'approved' => $approved,
            'locales' => $locales,
            'resource_types' => $resourceTypes,
            'glossary_entries' => WarhammerGlossary::count(),
        ];
    }

    /**
     * Obtenir le label du statut de traduction
     */
    public function getStatusLabel(string $status): string
    {
        return match($status) {
            'pending' => 'En attente',
            'auto' => 'Automatique',
            'reviewed' => 'Révisée',
            'approved' => 'Approuvée',
            'not_translated' => 'Non traduit',
            default => 'Inconnu',
        };
    }

    /**
     * Obtenir la couleur du badge pour un statut
     */
    public function getStatusColor(string $status): string
    {
        return match($status) {
            'pending' => 'warning',
            'auto' => 'info',
            'reviewed' => 'success',
            'approved' => 'success',
            'not_translated' => 'danger',
            default => 'gray',
        };
    }
}
