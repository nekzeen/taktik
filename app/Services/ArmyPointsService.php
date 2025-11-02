<?php

namespace App\Services;

/**
 * Service pour gérer les points d'armée et le déploiement automatique
 */
class ArmyPointsService
{
    /**
     * Options de points d'armée disponibles
     */
    public static function getArmyPointsOptions(): array
    {
        return [
            1000 => '1000 points (Incursion)',
            1500 => '1500 points (Engagement à l\'aube)',
            2000 => '2000 points (Force de frappe)',
            3000 => '3000 points (Offensive)',
            '3000+' => '3000+ points (Apocalypse)',
        ];
    }

    /**
     * Obtenir le mode de déploiement basé sur les points d'armée
     * 
     * @param int|string $armyPoints Points d'armée
     * @return string Mode de déploiement
     */
    public static function getDeploymentModeByArmyPoints($armyPoints): ?string
    {
        // Convertir '3000+' en 3000 pour la comparaison
        $points = $armyPoints === '3000+' ? 3000 : (int)$armyPoints;

        return match ($points) {
            1000 => 'Incursion',
            1500 => 'Dawn of War',
            2000, 3000 => 'Hammer and Anvil', // Strike Force / Force de frappe
            default => null,
        };
    }

    /**
     * Obtenir le label du mode de déploiement
     * 
     * @param int|string $armyPoints Points d'armée
     * @return string Label du mode
     */
    public static function getDeploymentLabel($armyPoints): string
    {
        $points = $armyPoints === '3000+' ? 3000 : (int)$armyPoints;

        return match ($points) {
            1000 => 'Incursion',
            1500 => 'Engagement à l\'aube',
            2000, 3000 => 'Force de frappe',
            default => 'Non défini',
        };
    }

    /**
     * Vérifier si les points d'armée sont valides
     * 
     * @param int|string $armyPoints Points d'armée
     * @return bool
     */
    public static function isValidArmyPoints($armyPoints): bool
    {
        $validPoints = [1000, 1500, 2000, 3000, '3000+'];
        return in_array($armyPoints, $validPoints);
    }

    /**
     * Obtenir la description du format de jeu basée sur les points
     * 
     * @param int|string $armyPoints Points d'armée
     * @return string Description
     */
    public static function getGameFormatDescription($armyPoints): string
    {
        return match ($armyPoints) {
            1000 => 'Incursion - Format rapide pour parties courtes',
            1500 => 'Format standard - Équilibre entre rapidité et profondeur',
            2000 => 'Force de frappe - Format compétitif standard',
            3000 => 'Offensive - Format étendu pour parties longues',
            '3000+' => 'Apocalypse - Format ouvert pour grandes batailles',
            default => 'Format non défini',
        };
    }
}
