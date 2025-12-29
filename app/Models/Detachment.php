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

    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class, 'resource_id')
            ->where('resource_type', 'Detachment');
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    public function abilities(): HasMany
    {
        return $this->hasMany(DetachmentAbility::class);
    }

    public function getDisplayNameAttribute(): string
    {
        $translation = $this->translations()
            ->where('field', 'name')
            ->where('locale', 'fr')
            ->first();

        return $translation?->translated_text ?? $this->name;
    }

    public function getTranslation(string $field, string $locale = 'fr'): ?string
    {
        return $this->translations()
            ->where('field', $field)
            ->where('locale', $locale)
            ->first()
            ?->translated_text;
    }
}
