<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TournamentMissionPool extends Model
{
    protected $fillable = [
        'pool_number',
        'pool_letter',
        'name',
        'slug',
        'description',
        'primary_mission_id',
        'deployment_mode',
        'terrain_layout_id',
        'use_twist_deck',
        'source',
        'is_active',
    ];

    protected $casts = [
        'use_twist_deck' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relation : Mission primaire
     */
    public function primaryMission(): BelongsTo
    {
        return $this->belongsTo(PrimaryMission::class);
    }

    /**
     * Relation : Disposition de terrain (principale, pour compatibilité)
     */
    public function terrainLayout(): BelongsTo
    {
        return $this->belongsTo(TerrainLayout::class);
    }

    /**
     * Relation : Dispositions de terrain disponibles (many-to-many)
     */
    public function availableTerrainLayouts(): BelongsToMany
    {
        return $this->belongsToMany(TerrainLayout::class, 'tournament_mission_pool_terrain_layouts')
            ->withPivot('order')
            ->orderBy('order');
    }

    /**
     * Relation : Missions secondaires (many-to-many)
     */
    public function secondaryMissions(): BelongsToMany
    {
        return $this->belongsToMany(SecondaryMission::class, 'tournament_mission_pool_secondary_missions')
            ->withPivot('order')
            ->orderBy('order');
    }

    /**
     * Trouver un pool par sa lettre
     */
    public static function findByLetter(string $letter): ?self
    {
        return self::where('pool_letter', strtoupper($letter))->first();
    }

    /**
     * Obtenir un pool aléatoire
     */
    public static function random(): ?self
    {
        return self::where('is_active', true)->inRandomOrder()->first();
    }
}
