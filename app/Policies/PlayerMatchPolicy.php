<?php

namespace App\Policies;

use App\Models\PlayerMatch;
use App\Models\User;

class PlayerMatchPolicy
{
    public function update(User $user, PlayerMatch $playerMatch): bool
    {
        // Les super administrateurs peuvent toujours modifier
        if ($user->hasRole('super-admin')) {
            return true;
        }
        
        // Les matchs terminés ne peuvent pas être modifiés par les utilisateurs normaux
        if ($playerMatch->status === 'completed') {
            return false;
        }
        
        // Seul le créateur peut modifier un match ouvert
        return $user->id === $playerMatch->creator_id && $playerMatch->status === 'open';
    }

    public function delete(User $user, PlayerMatch $playerMatch): bool
    {
        // Les super administrateurs peuvent toujours supprimer
        if ($user->hasRole('super-admin')) {
            return true;
        }
        
        return $user->id === $playerMatch->creator_id;
    }
}
