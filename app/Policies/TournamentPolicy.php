<?php

namespace App\Policies;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TournamentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Tout le monde peut voir la liste des tournois
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Tournament $tournament): bool
    {
        // Tout le monde peut voir un tournoi
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Vérifier si l'utilisateur peut créer des tournois
        if (!$user->can_create_tournaments) {
            return false;
        }
        
        // Les super-admins n'ont pas de limite
        if ($user->hasRole('super-admin')) {
            return true;
        }
        
        $openTournamentsCount = $user->countOpenTournaments();
        
        // Vérifier la limite personnalisée si elle existe
        if ($user->max_open_tournaments !== null) {
            if ($openTournamentsCount >= $user->max_open_tournaments) {
                return false;
            }
            return true;
        }
        
        // Admin : limité à 10 tournois ouverts
        if ($user->hasRole('admin')) {
            if ($openTournamentsCount >= 10) {
                return false;
            }
            return true;
        }
        
        // Player : limité à 1 tournoi ouvert
        if ($user->hasRole('player')) {
            if ($openTournamentsCount >= 1) {
                return false;
            }
            return true;
        }
        
        // Autres rôles : pas autorisés
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tournament $tournament): bool
    {
        // Super-admin peut modifier tous les tournois
        if ($user->hasRole('super-admin')) {
            return true;
        }
        
        // Admin peut modifier tous les tournois
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Seul le créateur du tournoi peut le modifier
        return $tournament->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tournament $tournament): bool
    {
        // Super-admin peut supprimer tous les tournois
        if ($user->hasRole('super-admin')) {
            return true;
        }
        
        // Admin peut supprimer tous les tournois
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Seul le créateur du tournoi peut le supprimer
        return $tournament->created_by === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tournament $tournament): bool
    {
        return $user->hasPermissionTo('manage-tournaments');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tournament $tournament): bool
    {
        return $user->hasPermissionTo('manage-tournaments');
    }

    /**
     * Determine whether the user can join the tournament.
     */
    public function join(User $user, Tournament $tournament): bool
    {
        return $user->hasPermissionTo('join-tournaments') 
            && $tournament->status === 'open';
    }

    /**
     * Determine whether the user can manage tournament bracket.
     */
    public function manageBracket(User $user, Tournament $tournament): bool
    {
        return $user->hasPermissionTo('manage-tournaments');
    }
}
