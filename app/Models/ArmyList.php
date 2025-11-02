<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ArmyList extends Model
{
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'tournament_id',
        'faction_id',
        'detachment_id',
        'detachment',
        'points',
        'pdf_path',
        'pdf_size',
        'pdf_hash',
        'status',
        'validated_at',
        'validated_by',
        'rejection_reason',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    /**
     * Accesseur pour obtenir un nom lisible de la liste
     */
    public function getDisplayNameAttribute(): string
    {
        $parts = [];
        
        if ($this->relationLoaded('user') && $this->user) {
            $parts[] = $this->user->name;
        }
        
        if ($this->relationLoaded('faction') && $this->faction) {
            $parts[] = $this->faction->name;
        }
        
        if ($this->detachment) {
            $parts[] = $this->detachment;
        }
        
        if ($this->points) {
            $parts[] = $this->points . ' pts';
        }
        
        return implode(' - ', array_filter($parts)) ?: 'Liste #' . $this->id;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'validated_at', 'validated_by', 'rejection_reason'])
            ->logOnlyDirty();
    }

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    public function detachment(): BelongsTo
    {
        return $this->belongsTo(Detachment::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // Scopes
    public function scopeValidated($query)
    {
        return $query->where('status', 'validated');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
