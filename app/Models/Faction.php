<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faction extends Model
{
    protected $fillable = [
        'bsdata_id',
        'name',
        'name_fr',
        'version',
        'imported_at',
    ];

    protected $casts = [
        'imported_at' => 'datetime',
    ];

    // Relations
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function armyLists(): HasMany
    {
        return $this->hasMany(ArmyList::class);
    }

    public function detachments(): HasMany
    {
        return $this->hasMany(Detachment::class);
    }

    public function bsdataDetachments(): HasMany
    {
        return $this->hasMany(BsdataDetachment::class);
    }
}
