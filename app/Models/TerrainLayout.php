<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TerrainLayout extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_url',
        'image_path',
        'layout_number',
        'source',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'layout_number' => 'integer',
    ];

    /**
     * Trouver un terrain layout par son numéro
     */
    public static function findByNumber(int $number): ?self
    {
        return self::where('layout_number', $number)->first();
    }

    /**
     * Obtenir l'URL de l'image Wahapedia
     */
    public static function getWahapediaImageUrl(int $number): string
    {
        return sprintf('https://wahapedia.ru/wh40k10ed/img/maps/TerrainLayout/CA_TerrainLayout%d.png', $number);
    }
}
