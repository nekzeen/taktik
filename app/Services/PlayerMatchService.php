<?php

namespace App\Services;

use App\Models\PlayerMatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service pour la gestion des matchs joueurs
 * 
 * Centralise la logique métier liée aux matchs joueurs
 * Facilite la réutilisabilité et les tests
 * 
 * Utilisation:
 * $service = new PlayerMatchService();
 * $availableMatches = $service->getAvailableMatches($user);
 * $canJoin = $service->canJoin($match, $user);
 */
class PlayerMatchService
{
    /**
     * Obtenir les matchs disponibles pour un utilisateur
     * 
     * Matchs visibles = status='open' + is_setup_validated=true + pas expiré
     */
    public function getAvailableMatches(User $user = null): Collection
    {
        return PlayerMatch::where('status', 'open')
            ->where('opponent_id', null)
            ->with(['creator'])
            ->get()
            ->filter(fn($match) => $match->isAvailable())
            ->filter(fn($match) => !$user || $match->creator_id !== $user->id)
            ->values();
    }

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
        return $match->canJoin($user);
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
        return $match->canSetScore($user);
    }

    /**
     * Obtenir le label d'affichage du statut
     * 
     * Logique métier:
     * - open + is_setup_validated=false → "Configuration en cours"
     * - open + is_setup_validated=true → "Ouvert"
     * - confirmed → "Confirmé"
     * - completed → "Terminé"
     * - cancelled → "Annulé"
     */
    public function getStatusLabel(PlayerMatch $match): string
    {
        return $match->getStatusLabel();
    }

    /**
     * Obtenir les matchs confirmés d'un utilisateur
     */
    public function getConfirmedMatches(User $user): Collection
    {
        return PlayerMatch::where(function ($q) use ($user) {
            $q->where('creator_id', $user->id)
                ->orWhere('opponent_id', $user->id);
        })
            ->where('status', 'confirmed')
            ->with(['creator', 'opponent'])
            ->get();
    }

    /**
     * Obtenir les matchs terminés d'un utilisateur
     */
    public function getCompletedMatches(User $user): Collection
    {
        return PlayerMatch::where(function ($q) use ($user) {
            $q->where('creator_id', $user->id)
                ->orWhere('opponent_id', $user->id);
        })
            ->where('status', 'completed')
            ->with(['creator', 'opponent', 'winner'])
            ->get();
    }

    /**
     * Obtenir les matchs proposés par un utilisateur
     */
    public function getProposedMatches(User $user): Collection
    {
        return PlayerMatch::where('creator_id', $user->id)
            ->with(['opponent', 'requests'])
            ->get()
            ->filter(fn($match) => $match->isAvailable() || $match->status !== 'open' || $match->opponent_id !== null)
            ->values();
    }

    /**
     * Déterminer le gagnant d'un match
     */
    public function determineWinner(PlayerMatch $match): void
    {
        $match->determineWinner();
        $match->save();
    }

    /**
     * Obtenir les statistiques d'un utilisateur
     */
    public function getUserStats(User $user): array
    {
        $totalMatches = PlayerMatch::where('status', 'completed')
            ->where(function ($q) use ($user) {
                $q->where('creator_id', $user->id)
                    ->orWhere('opponent_id', $user->id);
            })
            ->count();

        $wins = PlayerMatch::where('status', 'completed')
            ->where('winner_id', $user->id)
            ->count();

        $draws = PlayerMatch::where('status', 'completed')
            ->where('is_draw', true)
            ->where(function ($q) use ($user) {
                $q->where('creator_id', $user->id)
                    ->orWhere('opponent_id', $user->id);
            })
            ->count();

        $losses = $totalMatches - $wins - $draws;
        $winRatio = $totalMatches > 0 ? round(($wins / $totalMatches) * 100, 1) : 0;

        return [
            'total_matches' => $totalMatches,
            'wins' => $wins,
            'losses' => $losses,
            'draws' => $draws,
            'win_ratio' => $winRatio,
        ];
    }

    /**
     * Vérifier si un match est disponible (date pas expirée)
     */
    public function isAvailable(PlayerMatch $match): bool
    {
        return $match->isAvailable();
    }

    /**
     * Obtenir le label de disponibilité
     */
    public function getAvailabilityDisplay(PlayerMatch $match): string
    {
        return $match->getAvailabilityDisplay();
    }

    /**
     * Obtenir le label de localisation
     */
    public function getLocationDisplay(PlayerMatch $match): string
    {
        return $match->getLocationDisplay();
    }
}
