<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerMatchRequest extends Model
{
    protected $with = ['playerMatch'];

    protected $fillable = [
        'player_match_id',
        'requester_id',
        'faction',
        'detachment',
        'message',
        'status',
        'creator_response',
    ];

    public function playerMatch()
    {
        return $this->belongsTo(PlayerMatch::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
}
