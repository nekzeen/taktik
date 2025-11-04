<?php

namespace App\Services;

use App\Models\PlayerMatch;
use App\Models\User;

/**
 * Service pour la gestion des permissions des matchs
 * 
 * Centralise toutes les vérifications de permissions
 * Facilite la maintenance et les tests
 */
class MatchPermissionService
{
    /**
     * Vérifier si un utilisateur peut rejoindre un match
     * 
     * Conditions:
     * - status = 'open'
     * - opponent_id = null
     * - creator_id ≠ user_id
     * - match disponible (date pas expirée)
     * - is_setup_validated = true
     */
    public function canJoin(PlayerMatch $match, User $user): bool
    {
        return $this->isMatchOpen($match)
            && $this->hasNoOpponent($match)
            && $this->isNotCreator($match, $user)
            && $this->isAvailable($match)
            && $this->isSetupValidated($match);
    }

    /**
     * Vérifier si un utilisateur peut saisir les scores
     * 
     * Conditions:
     * - status = 'confirmed'
     * - creator_id = user_id
     */
    public function canSetScore(PlayerMatch $match, User $user): bool
    {
        return $this->isMatchConfirmed($match)
            && $this->isCreator($match, $user);
    }

    /**
     * Vérifier si un utilisateur peut accepter une demande
     * 
     * Conditions:
     * - creator_id = user_id
     * - status = 'open'
     */
    public function canAcceptRequest(PlayerMatch $match, User $user): bool
    {
        return $this->isCreator($match, $user)
            && $this->isMatchOpen($match);
    }

    /**
     * Vérifier si un utilisateur peut rejeter une demande
     * 
     * Conditions:
     * - creator_id = user_id
     */
    public function canRejectRequest(PlayerMatch $match, User $user): bool
    {
        return $this->isCreator($match, $user);
    }

    /**
     * Vérifier si un utilisateur peut modifier le match
     * 
     * Conditions:
     * - creator_id = user_id
     * - status = 'open'
     * - is_setup_validated = false
     */
    public function canEdit(PlayerMatch $match, User $user): bool
    {
        return $this->isCreator($match, $user)
            && $this->isMatchOpen($match)
            && !$this->isSetupValidated($match);
    }

    /**
     * Vérifier si un utilisateur peut annuler le match
     * 
     * Conditions:
     * - creator_id = user_id
     * - status ≠ 'completed'
     */
    public function canCancel(PlayerMatch $match, User $user): bool
    {
        return $this->isCreator($match, $user)
            && !$this->isMatchCompleted($match);
    }

    /**
     * Vérifier si un utilisateur peut voir les détails du match
     * 
     * Conditions:
     * - creator_id = user_id OU opponent_id = user_id OU status = 'open'
     */
    public function canView(PlayerMatch $match, User $user = null): bool
    {
        if (!$user) {
            return $this->isMatchOpen($match);
        }

        return $this->isCreator($match, $user)
            || $this->isOpponent($match, $user)
            || $this->isMatchOpen($match);
    }

    // ==================== Vérifications Basiques ====================

    /**
     * Vérifier si le match est ouvert
     */
    private function isMatchOpen(PlayerMatch $match): bool
    {
        return $match->status === 'open';
    }

    /**
     * Vérifier si le match est confirmé
     */
    private function isMatchConfirmed(PlayerMatch $match): bool
    {
        return $match->status === 'confirmed';
    }

    /**
     * Vérifier si le match est terminé
     */
    private function isMatchCompleted(PlayerMatch $match): bool
    {
        return $match->status === 'completed';
    }

    /**
     * Vérifier si le match n'a pas d'adversaire
     */
    private function hasNoOpponent(PlayerMatch $match): bool
    {
        return $match->opponent_id === null;
    }

    /**
     * Vérifier si l'utilisateur est le créateur
     */
    private function isCreator(PlayerMatch $match, User $user): bool
    {
        return $match->creator_id === $user->id;
    }

    /**
     * Vérifier si l'utilisateur n'est pas le créateur
     */
    private function isNotCreator(PlayerMatch $match, User $user): bool
    {
        return !$this->isCreator($match, $user);
    }

    /**
     * Vérifier si l'utilisateur est l'adversaire
     */
    private function isOpponent(PlayerMatch $match, User $user): bool
    {
        return $match->opponent_id === $user->id;
    }

    /**
     * Vérifier si le match est disponible (date pas expirée)
     */
    private function isAvailable(PlayerMatch $match): bool
    {
        return $match->isAvailable();
    }

    /**
     * Vérifier si la configuration est validée
     */
    private function isSetupValidated(PlayerMatch $match): bool
    {
        return $match->is_setup_validated;
    }

    /**
     * Obtenir un message d'erreur pour une permission refusée
     */
    public function getErrorMessage(PlayerMatch $match, User $user, string $action): string
    {
        return match($action) {
            'join' => $this->getJoinErrorMessage($match, $user),
            'set_score' => $this->getSetScoreErrorMessage($match, $user),
            'accept_request' => $this->getAcceptRequestErrorMessage($match, $user),
            'edit' => $this->getEditErrorMessage($match, $user),
            'cancel' => $this->getCancelErrorMessage($match, $user),
            default => 'Action non autorisée',
        };
    }

    private function getJoinErrorMessage(PlayerMatch $match, User $user): string
    {
        if (!$this->isMatchOpen($match)) {
            return 'Ce match n\'est pas ouvert';
        }
        if (!$this->hasNoOpponent($match)) {
            return 'Ce match a déjà un adversaire';
        }
        if ($this->isCreator($match, $user)) {
            return 'Vous ne pouvez pas rejoindre votre propre match';
        }
        if (!$this->isAvailable($match)) {
            return 'Ce match n\'est plus disponible';
        }
        if (!$this->isSetupValidated($match)) {
            return 'Ce match n\'est pas encore configuré';
        }
        return 'Vous ne pouvez pas rejoindre ce match';
    }

    private function getSetScoreErrorMessage(PlayerMatch $match, User $user): string
    {
        if (!$this->isMatchConfirmed($match)) {
            return 'Ce match n\'est pas confirmé';
        }
        if (!$this->isCreator($match, $user)) {
            return 'Seul le créateur du match peut saisir les scores';
        }
        return 'Vous ne pouvez pas saisir les scores';
    }

    private function getAcceptRequestErrorMessage(PlayerMatch $match, User $user): string
    {
        if (!$this->isCreator($match, $user)) {
            return 'Seul le créateur du match peut accepter les demandes';
        }
        if (!$this->isMatchOpen($match)) {
            return 'Ce match n\'est pas ouvert';
        }
        return 'Vous ne pouvez pas accepter cette demande';
    }

    private function getEditErrorMessage(PlayerMatch $match, User $user): string
    {
        if (!$this->isCreator($match, $user)) {
            return 'Seul le créateur du match peut le modifier';
        }
        if (!$this->isMatchOpen($match)) {
            return 'Ce match n\'est pas ouvert';
        }
        if ($this->isSetupValidated($match)) {
            return 'Vous ne pouvez pas modifier un match configuré';
        }
        return 'Vous ne pouvez pas modifier ce match';
    }

    private function getCancelErrorMessage(PlayerMatch $match, User $user): string
    {
        if (!$this->isCreator($match, $user)) {
            return 'Seul le créateur du match peut l\'annuler';
        }
        if ($this->isMatchCompleted($match)) {
            return 'Vous ne pouvez pas annuler un match terminé';
        }
        return 'Vous ne pouvez pas annuler ce match';
    }
}
