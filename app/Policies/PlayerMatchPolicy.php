<?php

namespace App\Policies;

use App\Models\PlayerMatch;
use App\Models\User;

class PlayerMatchPolicy
{
    public function update(User $user, PlayerMatch $playerMatch): bool
    {
        return $user->id === $playerMatch->creator_id && $playerMatch->status === 'open';
    }

    public function delete(User $user, PlayerMatch $playerMatch): bool
    {
        return $user->id === $playerMatch->creator_id;
    }
}
