<?php

namespace App\Services;

use App\Models\PrimaryMission;
use App\Models\SecondaryMission;
use App\Models\TwistMission;
use App\Models\TournamentMissionPool;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service pour la gestion des missions
 * 
 * Centralise la logique métier liée aux missions
 */
class MissionService
{
    /**
     * Obtenir toutes les missions primaires actives
     */
    public function getActivePrimaryMissions(): Collection
    {
        return PrimaryMission::where('is_active', true)
            ->with(['sections', 'translations'])
            ->get();
    }

    /**
     * Obtenir toutes les missions secondaires actives
     */
    public function getActiveSecondaryMissions(): Collection
    {
        return SecondaryMission::where('is_active', true)
            ->with(['translations'])
            ->get();
    }

    /**
     * Obtenir toutes les péripéties actives
     */
    public function getActiveTwistMissions(): Collection
    {
        return TwistMission::where('is_active', true)
            ->with(['translations'])
            ->get();
    }

    /**
     * Obtenir une mission primaire avec ses sections
     */
    public function getPrimaryMissionWithSections(PrimaryMission $mission)
    {
        return $mission->load(['sections' => function ($query) {
            $query->orderBy('order');
        }, 'translations']);
    }

    /**
     * Obtenir une mission secondaire avec ses traductions
     */
    public function getSecondaryMissionWithTranslations(SecondaryMission $mission)
    {
        return $mission->load(['translations']);
    }

    /**
     * Obtenir une péripétie avec ses traductions
     */
    public function getTwistMissionWithTranslations(TwistMission $mission)
    {
        return $mission->load(['translations']);
    }

    /**
     * Obtenir un pool de missions aléatoire
     */
    public function getRandomMissionPool(): ?TournamentMissionPool
    {
        return TournamentMissionPool::where('is_active', true)
            ->inRandomOrder()
            ->first();
    }

    /**
     * Obtenir tous les pools de missions actifs
     */
    public function getActiveMissionPools(): Collection
    {
        return TournamentMissionPool::where('is_active', true)
            ->with(['primaryMission', 'availableTerrainLayouts', 'secondaryMissions'])
            ->get();
    }

    /**
     * Obtenir la traduction d'une mission
     */
    public function getMissionTranslation($mission, string $locale = 'fr')
    {
        return $mission->translations()
            ->where('locale', $locale)
            ->first();
    }

    /**
     * Obtenir le texte traduit d'une mission
     */
    public function getMissionTranslatedText($mission, string $field = 'name', string $locale = 'fr'): string
    {
        $translation = $mission->translations()
            ->where('locale', $locale)
            ->where('field', $field)
            ->first();

        return $translation?->translated_text ?? $mission->{$field} ?? '';
    }

    /**
     * Obtenir le label du statut de traduction
     */
    public function getTranslationStatusLabel(string $status): string
    {
        return match($status) {
            'pending' => 'En attente',
            'auto' => 'Automatique',
            'reviewed' => 'Révisée',
            'approved' => 'Approuvée',
            default => 'Inconnu',
        };
    }

    /**
     * Vérifier si une mission est complètement traduite
     */
    public function isFullyTranslated($mission, string $locale = 'fr'): bool
    {
        $fields = ['name', 'description', 'full_text'];
        
        foreach ($fields as $field) {
            $translation = $mission->translations()
                ->where('locale', $locale)
                ->where('field', $field)
                ->first();

            if (!$translation || empty($translation->translated_text)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtenir le pourcentage de traduction
     */
    public function getTranslationPercentage($mission, string $locale = 'fr'): int
    {
        $fields = ['name', 'description', 'full_text'];
        $translated = 0;

        foreach ($fields as $field) {
            $translation = $mission->translations()
                ->where('locale', $locale)
                ->where('field', $field)
                ->first();

            if ($translation && !empty($translation->translated_text)) {
                $translated++;
            }
        }

        return (int) (($translated / count($fields)) * 100);
    }

    /**
     * Obtenir les missions par édition
     */
    public function getMissionsByEdition(string $edition): Collection
    {
        return PrimaryMission::where('edition', $edition)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Obtenir les missions par source
     */
    public function getMissionsBySource(string $source): Collection
    {
        return PrimaryMission::where('source', $source)
            ->where('is_active', true)
            ->get();
    }
}
