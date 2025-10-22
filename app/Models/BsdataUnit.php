<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BsdataUnit extends Model
{
    protected $fillable = [
        'bsdata_id',
        'faction_id',
        'name',
        'type',
        'points_min',
        'points_max',
        'keywords',
        'abilities',
        'wargear',
        'description',
        'raw_data',
    ];

    protected $casts = [
        'keywords' => 'array',
        'abilities' => 'array',
        'wargear' => 'array',
        'raw_data' => 'array',
    ];

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }
}
