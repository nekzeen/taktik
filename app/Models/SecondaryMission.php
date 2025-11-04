<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecondaryMission extends Model
{
    protected $fillable = [
        'name',
        'description',
        'full_text',
        'when_drawn',
        'when_condition',
        'timing',
        'scoring_conditions',
        'max_vp',
        'edition',
        'source',
        'slug',
        'is_active',
        'can_be_fixed',
    ];

    protected $casts = [
        'scoring_conditions' => 'array',
        'is_active' => 'boolean',
        'can_be_fixed' => 'boolean',
    ];

    /**
     * Relations
     */
    public function sections(): HasMany
    {
        return $this->hasMany(SecondaryMissionSection::class)->ordered();
    }

    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class, 'resource_id')
            ->where('resource_type', 'SecondaryMission');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    public function scopeByEdition($query, $edition)
    {
        return $query->where('edition', $edition);
    }

    /**
     * Accesseurs
     */
    public function getDisplayNameAttribute(): string
    {
        $translation = $this->translations()
            ->where('field', 'name')
            ->where('locale', 'fr')
            ->first();
        
        // Retourner UNIQUEMENT la traduction française si elle existe, sinon le nom anglais
        return $translation?->translated_text ?? $this->name;
    }

    /**
     * Obtenir une traduction
     */
    public function getTranslation(string $field, string $locale = 'fr'): ?string
    {
        return $this->translations()
            ->where('field', $field)
            ->where('locale', $locale)
            ->first()
            ?->translated_text;
    }
}
