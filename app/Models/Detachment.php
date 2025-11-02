<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Detachment extends Model
{
    protected $fillable = [
        'wahapedia_id',
        'faction_id',
        'name',
        'description',
    ];

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    public function abilities(): HasMany
    {
        return $this->hasMany(DetachmentAbility::class);
    }
}
