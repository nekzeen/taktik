<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetachmentAbility extends Model
{
    protected $fillable = [
        'wahapedia_id',
        'detachment_id',
        'name',
        'description',
    ];

    public function detachment(): BelongsTo
    {
        return $this->belongsTo(Detachment::class);
    }
}
