<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncursionDeploymentCard extends Model
{
    protected $fillable = [
        'name',
        'description',
        'full_text',
        'card_content',
        'rules',
        'image_url',
        'image_path',
        'image_filename',
        'edition',
        'source',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relations
     */
    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class, 'resource_id')
            ->where('resource_type', 'IncursionDeploymentCard');
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

    /**
     * Obtenir le chemin complet de l'image
     */
    public function getImageFullPathAttribute(): ?string
    {
        if ($this->image_path) {
            return storage_path('app/public/' . $this->image_path);
        }
        return null;
    }

    /**
     * Obtenir l'URL publique de l'image
     */
    public function getImagePublicUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return null;
    }
}
