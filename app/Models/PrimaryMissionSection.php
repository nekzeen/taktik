<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrimaryMissionSection extends Model
{
    protected $fillable = [
        'primary_mission_id',
        'type',
        'order',
        'title',
        'timing',
        'content',
        'conditions',
        'victory_points',
        'title_fr',
        'timing_fr',
        'content_fr',
    ];

    protected $casts = [
        'conditions' => 'array',
    ];

    /**
     * Relations
     */
    public function primaryMission(): BelongsTo
    {
        return $this->belongsTo(PrimaryMission::class);
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
