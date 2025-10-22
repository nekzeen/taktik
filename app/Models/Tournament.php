<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Storage;

class Tournament extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'format',
        'army_size',
        'start_date',
        'end_date',
        'registration_deadline',
        'max_players',
        'status',
        'bracket_generated_at',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_deadline' => 'datetime',
        'bracket_generated_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status', 'format', 'start_date', 'end_date'])
            ->logOnlyDirty();
    }

    // Relations
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(GameMatch::class);
    }

    public function tournamentMatches(): HasMany
    {
        return $this->hasMany(TournamentMatch::class);
    }

    public function armyLists(): HasMany
    {
        return $this->hasMany(ArmyList::class);
    }

    public function calendarSlots(): HasMany
    {
        return $this->hasMany(CalendarSlot::class);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Méthodes pour le format d'armée
    public function getArmySizeLabel(): string
    {
        return match($this->army_size) {
            'incursion' => 'INCURSION',
            'strike_force' => 'FORCE DE FRAPPE',
            'onslaught' => 'OFFENSIVE',
            default => 'FORCE DE FRAPPE',
        };
    }

    public function getArmySizePoints(): int
    {
        return match($this->army_size) {
            'incursion' => 1000,
            'strike_force' => 2000,
            'onslaught' => 3000,
            default => 2000,
        };
    }

    public function getArmySizeFormatted(): string
    {
        return $this->getArmySizeLabel() . ' (' . $this->getArmySizePoints() . ' pts)';
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($tournament) {
            // Supprimer les fichiers PDF des listes d'armée
            foreach ($tournament->armyLists as $armyList) {
                if ($armyList->pdf_path && Storage::exists($armyList->pdf_path)) {
                    Storage::delete($armyList->pdf_path);
                }
            }
        });

        static::deleted(function ($tournament) {
            // Aucune action supplémentaire nécessaire
            // Le compteur countOpenTournaments() se met à jour automatiquement
            // car il compte les tournois avec status = 'open' en temps réel
        });
    }
}
