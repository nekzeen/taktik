<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerAvailability extends Model
{
    protected $fillable = [
        'tournament_id',
        'user_id',
        'type',
        'available_at',
        'available_from',
        'available_to',
        'notes',
    ];

    protected $casts = [
        'available_at' => 'datetime',
        'available_from' => 'datetime',
        'available_to' => 'datetime',
    ];

    // Relations
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
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

    public function scopeForTournament($query, $tournamentId)
    {
        return $query->where('tournament_id', $tournamentId);
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

    public function getShortFormat(): string
    {
        if ($this->type === 'single') {
            return $this->available_at->format('d/m H:i');
        }
        
        if ($this->type === 'period') {
            return $this->available_from->format('d/m') . '-' . $this->available_to->format('d/m');
        }
        
        return '';
    }
}
