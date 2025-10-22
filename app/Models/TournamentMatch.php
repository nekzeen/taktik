<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TournamentMatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tournament_id',
        'round',
        'table_number',
        'player1_id',
        'player1_army_list_id',
        'player1_score',
        'player1_victory_points',
        'player2_id',
        'player2_army_list_id',
        'player2_score',
        'player2_victory_points',
        'status',
        'winner_id',
        'is_draw',
        'notes',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'is_draw' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Relations
     */
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

    public function player1ArmyList(): BelongsTo
    {
        return $this->belongsTo(ArmyList::class, 'player1_army_list_id');
    }

    public function player2ArmyList(): BelongsTo
    {
        return $this->belongsTo(ArmyList::class, 'player2_army_list_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(MatchAvailability::class, 'tournament_match_id');
    }

    /**
     * Méthodes utilitaires
     */
    public function isPlayer(User $user): bool
    {
        return $this->player1_id === $user->id || $this->player2_id === $user->id;
    }

    public function canEditResult(User $user): bool
    {
        // Le créateur du tournoi ou les joueurs du match peuvent éditer
        return $this->tournament->created_by === $user->id || $this->isPlayer($user);
    }

    public function getOpponent(User $user): ?User
    {
        if ($this->player1_id === $user->id) {
            return $this->player2;
        }
        if ($this->player2_id === $user->id) {
            return $this->player1;
        }
        return null;
    }

    public function determineWinner(): void
    {
        if ($this->player1_score === null || $this->player2_score === null) {
            return;
        }

        if ($this->player1_score === $this->player2_score) {
            $this->is_draw = true;
            $this->winner_id = null;
        } else {
            $this->is_draw = false;
            $this->winner_id = $this->player1_score > $this->player2_score 
                ? $this->player1_id 
                : $this->player2_id;
        }
    }

    public function getActiveAvailabilities()
    {
        return $this->availabilities()->active()->with('user')->get();
    }

    public function hasActiveAvailability(User $user): bool
    {
        return $this->availabilities()
            ->forUser($user->id)
            ->active()
            ->exists();
    }

    public function bothPlayersAvailable(): bool
    {
        $player1Available = $this->availabilities()
            ->forUser($this->player1_id)
            ->active()
            ->exists();

        $player2Available = $this->availabilities()
            ->forUser($this->player2_id)
            ->active()
            ->exists();

        return $player1Available && $player2Available;
    }
}
