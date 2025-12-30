<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentInvitation extends Model
{
    protected $fillable = [
        'tournament_id',
        'invited_user_id',
        'invited_by_id',
        'status',
        'message',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function invitedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_id');
    }
}
