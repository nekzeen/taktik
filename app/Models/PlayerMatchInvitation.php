<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerMatchInvitation extends Model
{
    protected $fillable = [
        'player_match_id',
        'invited_user_id',
        'invited_by_id',
        'status',
        'message',
    ];

    public function playerMatch()
    {
        return $this->belongsTo(PlayerMatch::class);
    }

    public function invitedUser()
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }

    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by_id');
    }
}
