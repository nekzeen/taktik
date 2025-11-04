<?php

namespace App\Services;

use App\Models\SecondaryMission;
use App\Models\PlayerMatch;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service pour la gestion des missions secondaires
 * 
 * Gère la logique des missions secondaires selon les règles Warhammer 40k :
 * - Missions Fixes : objectifs permanents
 * - Missions Tactiques : renouvelées chaque phase de commandement
 */
class SecondaryMissionService
{
    /**
     * Types de missions secondaires
     */
    public const TYPE_FIXED = 'fixed';
    public const TYPE_TACTICAL = 'tactical';

    /**
     * Obtenir toutes les missions secondaires actives
     */
    public function getActiveMissions(): Collection
    {
        return SecondaryMission::where('is_active', true)
            ->with(['translations'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtenir toutes les missions secondaires qui peuvent être fixes
     */
    public function getFixableMissions(): Collection
    {
        return SecondaryMission::where('is_active', true)
            ->where('can_be_fixed', true)
            ->with(['translations'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtenir la traduction d'une mission secondaire
     */
    public function getMissionTranslation(SecondaryMission $mission, string $locale = 'fr'): ?string
    {
        return $mission->translations()
            ->where('locale', $locale)
            ->where('field', 'name')
            ->value('translated_text');
    }

    /**
     * Obtenir le texte complet traduit
     */
    public function getMissionFullText(SecondaryMission $mission, string $locale = 'fr'): ?string
    {
        return $mission->translations()
            ->where('locale', $locale)
            ->where('field', 'full_text')
            ->value('translated_text');
    }

    /**
     * Obtenir les conditions de la mission
     */
    public function getMissionConditions(SecondaryMission $mission): array
    {
        return [
            'when_drawn' => $mission->when_drawn,
            'when_condition' => $mission->when_condition,
            'scoring_conditions' => $mission->scoring_conditions,
        ];
    }

    /**
     * Initialiser les missions secondaires pour un joueur (Missions Fixes)
     * 
     * Retourne 2 missions fixes sélectionnées
     */
    public function initializeFixedMissions(array $selectedMissionIds): array
    {
        if (count($selectedMissionIds) !== 2) {
            throw new \InvalidArgumentException('Vous devez sélectionner exactement 2 missions fixes');
        }

        $missions = SecondaryMission::whereIn('id', $selectedMissionIds)
            ->where('is_active', true)
            ->get();

        if ($missions->count() !== 2) {
            throw new \InvalidArgumentException('Les missions sélectionnées n\'existent pas');
        }

        return $missions->toArray();
    }

    /**
     * Initialiser les missions tactiques pour un joueur
     * 
     * Retourne 2 missions tactiques aléatoires
     */
    public function initializeTacticalMissions(int $count = 2): Collection
    {
        return SecondaryMission::where('is_active', true)
            ->inRandomOrder()
            ->limit($count)
            ->get();
    }

    /**
     * Obtenir les missions actives d'un joueur (Missions Tactiques)
     * 
     * Simule le deck de missions tactiques
     */
    public function getActiveTacticalMissions(array $activeMissionIds): Collection
    {
        return SecondaryMission::whereIn('id', $activeMissionIds)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Appliquer la stratégie "Ordres Nouveaux"
     * 
     * Remplace une mission tactique active par une nouvelle
     */
    public function applyNewOrdersStratagem(array $activeMissionIds, int $missionToRemoveId): Collection
    {
        // Retirer la mission
        $remaining = array_filter($activeMissionIds, fn($id) => $id !== $missionToRemoveId);

        // Piocher une nouvelle mission
        $newMission = SecondaryMission::where('is_active', true)
            ->whereNotIn('id', $remaining)
            ->inRandomOrder()
            ->first();

        if (!$newMission) {
            // Pas de nouvelle mission disponible
            return $this->getActiveTacticalMissions($remaining);
        }

        $remaining[] = $newMission->id;

        return $this->getActiveTacticalMissions($remaining);
    }

    /**
     * Marquer une mission comme accomplie (Missions Tactiques)
     * 
     * La mission est défaussée et peut être remplacée
     */
    public function accomplishMission(array $activeMissionIds, int $missionId): array
    {
        return array_filter($activeMissionIds, fn($id) => $id !== $missionId);
    }

    /**
     * Vérifier si une mission est accomplissable
     */
    public function canAccomplish(SecondaryMission $mission, int $victoryPoints): bool
    {
        // Vérifier les conditions de scoring
        if (empty($mission->scoring_conditions)) {
            return false;
        }

        // Vérifier si les points de victoire sont suffisants
        // Cette logique dépend des conditions spécifiques de la mission
        return $victoryPoints > 0;
    }

    /**
     * Obtenir les points de victoire pour une mission
     */
    public function getVictoryPoints(SecondaryMission $mission): int
    {
        // Les missions secondaires valent généralement 3 ou 4 points
        // Cette valeur peut être stockée dans la mission
        return $mission->victory_points ?? 3;
    }

    /**
     * Obtenir le résumé des règles pour les missions fixes
     */
    public function getFixedMissionsRules(): string
    {
        return <<<'RULES'
MISSIONS FIXES

Les Missions Fixes sont des objectifs permanents tout au long de la bataille, que vous pouvez accomplir plusieurs fois.

• Sélectionnez 2 Missions Fixes parmi les cartes disponibles
• Ces missions ne peuvent pas être défaussées
• Elles restent actives pendant toute la bataille
• Vous pouvez les accomplir plusieurs fois
• Mettez de côté le reste de votre deck (non utilisé)
RULES;
    }

    /**
     * Obtenir le résumé des règles pour les missions tactiques
     */
    public function getTacticalMissionsRules(): string
    {
        return <<<'RULES'
MISSIONS TACTIQUES

Les Missions Tactiques se renouvellent au début de votre phase de Commandement et sont défaussées une fois accomplies.

• Mélangez votre deck de Missions Secondaires
• Au début de votre première phase de Commandement : piochez 2 cartes
• Ces cartes deviennent vos missions actives jusqu'à accomplissement
• Au début de chaque phase suivante : piochez jusqu'à avoir 2 cartes
• À la fin de votre phase de Commandement : vous pouvez dépenser 1 PC pour utiliser "Ordres Nouveaux"

ORDRES NOUVEAUX (1 PC)
• Défaussez l'une de vos missions actives
• Piochez une nouvelle carte de Mission Secondaire

À la fin du tour de chaque joueur :
• Si vous avez marqué ≥ 1 PV : défaussez cette mission (accomplie)
• Vous pouvez défausser une ou plusieurs missions actives
• Si vous le faites pendant votre tour : gagnez 1 PC
• Si votre deck est vide : vous ne pouvez plus en générer
RULES;
    }

    /**
     * Calculer les points de victoire totaux des missions secondaires
     */
    public function calculateTotalVictoryPoints(Collection $missions): int
    {
        return $missions->sum(fn($mission) => $this->getVictoryPoints($mission));
    }

    /**
     * Obtenir les informations complètes d'une mission
     */
    public function getMissionInfo(SecondaryMission $mission, string $locale = 'fr'): array
    {
        return [
            'id' => $mission->id,
            'name_en' => $mission->name,
            'name_fr' => $this->getMissionTranslation($mission, $locale),
            'full_text_en' => $mission->full_text,
            'full_text_fr' => $this->getMissionFullText($mission, $locale),
            'when_drawn' => $mission->when_drawn,
            'when_condition' => $mission->when_condition,
            'scoring_conditions' => $mission->scoring_conditions,
            'victory_points' => $this->getVictoryPoints($mission),
        ];
    }
}
