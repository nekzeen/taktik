<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class GameMatch extends Model
{
    use LogsActivity;

    protected $table = 'matches';

    protected $fillable = [
        'tournament_id',
        'type',
        'round',
        'player1_id',
        'player2_id',
        'player1_score',
        'player2_score',
        'winner_id',
        'status',
        'scheduled_at',
        'played_at',
        'location',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'played_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'player1_score', 'player2_score', 'winner_id'])
            ->logOnlyDirty();
    }

    // Relations
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function player1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player1_id');
    }

    public function player2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player2_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function calendarSlots()
    {
        return $this->hasMany(CalendarSlot::class, 'match_id');
    }
}
