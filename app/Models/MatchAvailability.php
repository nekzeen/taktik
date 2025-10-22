<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MatchAvailability extends Model
{
    protected $fillable = [
        'tournament_match_id',
        'user_id',
        'type',
        'available_at',
        'available_from',
        'available_to',
        'notes',
        'opponent_notified',
        'notified_at',
    ];

    protected $casts = [
        'available_at' => 'datetime',
        'available_from' => 'datetime',
        'available_to' => 'datetime',
        'opponent_notified' => 'boolean',
        'notified_at' => 'datetime',
    ];

    // Relations
    public function tournamentMatch(): BelongsTo
    {
        return $this->belongsTo(TournamentMatch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        $now = now();
        return $query->where(function ($q) use ($now) {
            // Disponibilité ponctuelle non dépassée
            $q->where(function ($sq) use ($now) {
                $sq->where('type', 'single')
                   ->where('available_at', '>=', $now);
            })
            // OU disponibilité sur période en cours
            ->orWhere(function ($sq) use ($now) {
                $sq->where('type', 'period')
                   ->where('available_from', '<=', $now)
                   ->where('available_to', '>=', $now);
            });
        });
    }

    public function scopeForMatch($query, $matchId)
    {
        return $query->where('tournament_match_id', $matchId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Méthodes utilitaires
    public function isActive(): bool
    {
        $now = now();
        
        if ($this->type === 'single') {
            return $this->available_at && $this->available_at >= $now;
        }
        
        if ($this->type === 'period') {
            return $this->available_from && $this->available_to 
                && $this->available_from <= $now 
                && $this->available_to >= $now;
        }
        
        return false;
    }

    public function isPast(): bool
    {
        $now = now();
        
        if ($this->type === 'single') {
            return $this->available_at && $this->available_at < $now;
        }
        
        if ($this->type === 'period') {
            return $this->available_to && $this->available_to < $now;
        }
        
        return false;
    }

    public function getFormattedAvailability(): string
    {
        if ($this->type === 'single') {
            return $this->available_at->format('d/m/Y à H:i');
        }
        
        if ($this->type === 'period') {
            return 'Du ' . $this->available_from->format('d/m/Y H:i') 
                 . ' au ' . $this->available_to->format('d/m/Y H:i');
        }
        
        return '';
    }
}
