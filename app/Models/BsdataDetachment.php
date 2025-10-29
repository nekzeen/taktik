<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BsdataDetachment extends Model
{
    protected $fillable = [
        'bsdata_id',
        'faction_id',
        'name',
        'description',
        'rules',
        'stratagems',
        'enhancements',
        'raw_data',
        'is_manual',
        'manually_modified',
    ];

    protected $casts = [
        'rules' => 'array',
        'stratagems' => 'array',
        'enhancements' => 'array',
        'raw_data' => 'array',
        'is_manual' => 'boolean',
        'manually_modified' => 'boolean',
    ];

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }
}
