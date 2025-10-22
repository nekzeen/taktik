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
    ];

    protected $casts = [
        'rules' => 'array',
        'stratagems' => 'array',
        'enhancements' => 'array',
        'raw_data' => 'array',
    ];

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }
}
