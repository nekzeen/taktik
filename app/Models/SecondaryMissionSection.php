<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecondaryMissionSection extends Model
{
    protected $fillable = [
        'secondary_mission_id',
        'type',
        'order',
        'title',
        'content',
        'victory_points',
        'conditions',
    ];

    protected $casts = [
        'conditions' => 'array',
    ];

    /**
     * Relations
     */
    public function secondaryMission(): BelongsTo
    {
        return $this->belongsTo(SecondaryMission::class);
    }

    /**
     * Scopes
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}
