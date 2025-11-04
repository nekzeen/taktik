<?php

namespace App\Enums;

/**
 * Statuts des matchs joueurs
 * 
 * Valeurs en base de données:
 * - 'open': Match ouvert, en attente de configuration ou de demandes
 * - 'confirmed': Match confirmé, adversaire accepté
 * - 'completed': Match terminé
 * - 'cancelled': Match annulé
 * 
 * Affichage utilisateur (voir PlayerMatch::getStatusLabel()):
 * - open + is_setup_validated=false → "Configuration en cours"
 * - open + is_setup_validated=true → "Ouvert"
 * - confirmed → "Confirmé"
 * - completed → "Terminé"
 * - cancelled → "Annulé"
 */
enum MatchStatus: string
{
    case OPEN = 'open';
    case CONFIRMED = 'confirmed';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    /**
     * Obtenir le label d'affichage
     */
    public function label(): string
    {
        return match($this) {
            self::OPEN => 'Ouvert',
            self::CONFIRMED => 'Confirmé',
            self::COMPLETED => 'Terminé',
            self::CANCELLED => 'Annulé',
        };
    }

    /**
     * Obtenir la couleur pour le badge
     */
    public function color(): string
    {
        return match($this) {
            self::OPEN => 'info',
            self::CONFIRMED => 'success',
            self::COMPLETED => 'gray',
            self::CANCELLED => 'danger',
        };
    }

    /**
     * Vérifier si le match est actif (peut recevoir des demandes)
     */
    public function isActive(): bool
    {
        return $this === self::OPEN || $this === self::CONFIRMED;
    }

    /**
     * Vérifier si le match est terminé
     */
    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Vérifier si le match est annulé
     */
    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }

    /**
     * Obtenir tous les statuts
     */
    public static function all(): array
    {
        return [
            self::OPEN->value => self::OPEN->label(),
            self::CONFIRMED->value => self::CONFIRMED->label(),
            self::COMPLETED->value => self::COMPLETED->label(),
            self::CANCELLED->value => self::CANCELLED->label(),
        ];
    }
}
