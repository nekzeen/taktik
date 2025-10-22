<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlayerMatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'creator_id',
        'opponent_id',
        'type',
        'army_points',
        'faction',
        'detachment',
        'notes',
        'city',
        'department',
        'availability_type',
        'available_at',
        'available_from',
        'available_to',
        'status',
        'creator_score',
        'opponent_score',
        'winner_id',
        'is_draw',
        'played_at',
    ];

    protected $casts = [
        'available_at' => 'datetime',
        'available_from' => 'datetime',
        'available_to' => 'datetime',
        'played_at' => 'datetime',
        'is_draw' => 'boolean',
    ];

    // Relations
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function opponent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opponent_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function requests()
    {
        return $this->hasMany(PlayerMatchRequest::class);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->whereNull('available_at')
                    ->orWhere('available_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('available_from')
                    ->orWhere('available_to', '>=', now());
            });
    }

    // Méthodes utilitaires
    public function isAvailable(): bool
    {
        if ($this->availability_type === 'single') {
            return $this->available_at !== null && $this->available_at >= now();
        }

        return $this->available_from !== null && $this->available_to !== null && $this->available_to >= now();
    }

    public function getAvailabilityDisplay(): string
    {
        if ($this->availability_type === 'single') {
            return $this->available_at->format('d/m/Y H:i');
        }

        return $this->available_from->format('d/m') . ' - ' . $this->available_to->format('d/m');
    }

    public function getLocationDisplay(): string
    {
        return $this->city . ' (' . $this->department . ')';
    }

    public function canJoin(User $user): bool
    {
        return $this->status === 'open'
            && $this->opponent_id === null
            && $this->creator_id !== $user->id
            && $this->isAvailable();
    }

    public function canSetScore(User $user): bool
    {
        return $this->status === 'confirmed'
            && ($this->creator_id === $user->id || $this->opponent_id === $user->id);
    }

    public function determineWinner(): void
    {
        if ($this->creator_score === null || $this->opponent_score === null) {
            return;
        }

        if ($this->creator_score === $this->opponent_score) {
            $this->is_draw = true;
            $this->winner_id = null;
        } else {
            $this->is_draw = false;
            $this->winner_id = $this->creator_score > $this->opponent_score
                ? $this->creator_id
                : $this->opponent_id;
        }
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'competitive' => 'Compétitif',
            'narrative' => 'Narratif',
            default => 'Inconnu',
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'open' => 'Ouvert',
            'confirmed' => 'Confirmé',
            'completed' => 'Terminé',
            'cancelled' => 'Annulé',
            default => 'Inconnu',
        };
    }
}
