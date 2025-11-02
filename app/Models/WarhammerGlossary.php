<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarhammerGlossary extends Model
{
    protected $table = 'warhammer_glossary';

    protected $fillable = [
        'english_term',
        'category',
        'context',
        'french_translation',
        'german_translation',
        'spanish_translation',
        'italian_translation',
        'description',
        'example',
        'status',
        'usage_count',
    ];

    /**
     * Scopes
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByContext($query, $context)
    {
        return $query->where('context', $context);
    }

    public function scopeByLanguage($query, $locale)
    {
        $column = match($locale) {
            'fr' => 'french_translation',
            'de' => 'german_translation',
            'es' => 'spanish_translation',
            'it' => 'italian_translation',
            default => 'french_translation',
        };
        return $query->whereNotNull($column);
    }

    /**
     * Obtenir la traduction pour une langue
     */
    public function getTranslation(string $locale = 'fr'): ?string
    {
        $column = match($locale) {
            'fr' => 'french_translation',
            'de' => 'german_translation',
            'es' => 'spanish_translation',
            'it' => 'italian_translation',
            default => 'french_translation',
        };
        return $this->{$column};
    }

    /**
     * Définir la traduction pour une langue
     * Limite à 255 caractères pour éviter les erreurs de troncature
     */
    public function setTranslation(string $locale, string $translation): void
    {
        $column = match($locale) {
            'fr' => 'french_translation',
            'de' => 'german_translation',
            'es' => 'spanish_translation',
            'it' => 'italian_translation',
            default => 'french_translation',
        };
        // Limiter à 255 caractères et ajouter "..." si tronqué
        $truncatedTranslation = strlen($translation) > 255 
            ? substr($translation, 0, 252) . '...' 
            : $translation;
        $this->{$column} = $truncatedTranslation;
        $this->save();
    }

    /**
     * Incrémenter le compteur d'utilisation
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Chercher un terme par son texte anglais
     */
    public static function findByEnglishTerm(string $term): ?self
    {
        return self::whereRaw('LOWER(english_term) = LOWER(?)', [$term])->first();
    }
}
