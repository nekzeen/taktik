<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlayerMatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'creator_id',
        'opponent_id',
        'type',
        'army_points',
        'faction',
        'detachment',
        'notes',
        'city',
        'department',
        'availability_type',
        'available_at',
        'available_from',
        'available_to',
        'status',
        'creator_score',
        'opponent_score',
        'creator_victory_points',
        'opponent_victory_points',
        'winner_id',
        'is_draw',
        'played_at',
        'primary_mission_id',
        'secondary_mission_id',
        'terrain_layout_id',
        'twist_mission_id',
        'asymmetric_primary_mission_id',
        'deployment_mode',
        'setup_mode',
        'is_setup_complete',
        'is_setup_validated',
        'draft_scores',
        'draft_tactical_state_creator',
        'draft_tactical_state_opponent',
    ];

    protected $casts = [
        'available_at' => 'datetime',
        'available_from' => 'datetime',
        'available_to' => 'datetime',
        'played_at' => 'datetime',
        'is_draw' => 'boolean',
        'is_setup_complete' => 'boolean',
        'is_setup_validated' => 'boolean',
        'draft_scores' => 'array',
        'draft_tactical_state_creator' => 'array',
        'draft_tactical_state_opponent' => 'array',
    ];

    // Relations
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function opponent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opponent_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function primaryMission(): BelongsTo
    {
        return $this->belongsTo(PrimaryMission::class);
    }

    public function secondaryMission(): BelongsTo
    {
        return $this->belongsTo(SecondaryMission::class);
    }

    public function terrainLayout(): BelongsTo
    {
        return $this->belongsTo(TerrainLayout::class);
    }

    public function twistMission(): BelongsTo
    {
        return $this->belongsTo(TwistMission::class);
    }

    public function asymmetricPrimaryMission(): BelongsTo
    {
        return $this->belongsTo(AsymmetricPrimaryMission::class);
    }

    public function requests()
    {
        return $this->hasMany(PlayerMatchRequest::class);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->whereNull('available_at')
                    ->orWhere('available_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('available_from')
                    ->orWhere('available_to', '>=', now());
            });
    }

    // Méthodes utilitaires
    public function isAvailable(): bool
    {
        if ($this->availability_type === 'single') {
            return $this->available_at !== null && $this->available_at >= now();
        }

        return $this->available_from !== null && $this->available_to !== null && $this->available_to >= now();
    }

    public function getAvailabilityDisplay(): string
    {
        if ($this->availability_type === 'single') {
            return $this->available_at ? $this->available_at->format('d/m/Y H:i') : 'Non défini';
        }

        if ($this->available_from && $this->available_to) {
            return $this->available_from->format('d/m') . ' - ' . $this->available_to->format('d/m');
        }

        return 'Non défini';
    }

    public function getLocationDisplay(): string
    {
        return $this->city . ' (' . $this->department . ')';
    }

    public function canJoin(User $user): bool
    {
        return $this->status === 'open'
            && $this->opponent_id === null
            && $this->creator_id !== $user->id
            && $this->isAvailable()
            && $this->is_setup_validated;
    }

    public function canSetScore(User $user): bool
    {
        return $this->status === 'confirmed'
            && $this->creator_id === $user->id;
    }

    public function determineWinner(?string $creatorResult = null): void
    {
        // Si un résultat spécial est fourni (Nul, Abandon, Table rase), l'utiliser
        if ($creatorResult !== null && $creatorResult !== '') {
            if ($creatorResult === 'nul') {
                // Nul
                $this->is_draw = true;
                $this->winner_id = null;
            } elseif ($creatorResult === 'creator_abandon' || $creatorResult === 'creator_table_rase') {
                // Le créateur abandonne ou est table rase → l'adversaire gagne
                $this->is_draw = false;
                $this->winner_id = $this->opponent_id;
            } elseif ($creatorResult === 'opponent_abandon' || $creatorResult === 'opponent_table_rase') {
                // L'adversaire abandonne ou est table rase → le créateur gagne
                $this->is_draw = false;
                $this->winner_id = $this->creator_id;
            }
            return;
        }

        // Sinon, utiliser le score pour déterminer le gagnant
        if ($this->creator_score === null || $this->opponent_score === null) {
            return;
        }

        if ($this->creator_score === $this->opponent_score) {
            $this->is_draw = true;
            $this->winner_id = null;
        } else {
            $this->is_draw = false;
            $this->winner_id = $this->creator_score > $this->opponent_score
                ? $this->creator_id
                : $this->opponent_id;
        }
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'competitive' => 'Compétitif',
            'narrative' => 'Narratif',
            default => 'Inconnu',
        };
    }

    public function getStatusLabel(): string
    {
        // Si le match est en statut "open" mais la configuration n'est pas validée
        if ($this->status === 'open' && !$this->is_setup_validated) {
            return 'Configuration en cours';
        }

        return match($this->status) {
            'open' => 'Ouvert',
            'confirmed' => 'Confirmé',
            'completed' => 'Terminé',
            'cancelled' => 'Annulé',
            default => 'Inconnu',
        };
    }

    /**
     * Tirage au sort aléatoire
     */
    public function randomizeSetup(): void
    {
        // Sélectionner aléatoirement une mission primaire
        $randomMission = PrimaryMission::inRandomOrder()->first();
        if ($randomMission) {
            $this->primary_mission_id = $randomMission->id;
        }

        // Sélectionner aléatoirement une disposition de terrain
        $randomTerrain = TerrainLayout::inRandomOrder()->first();
        if ($randomTerrain) {
            $this->terrain_layout_id = $randomTerrain->id;
        }

        // Sélectionner aléatoirement une péripétie
        $randomTwist = TwistMission::inRandomOrder()->first();
        if ($randomTwist) {
            $this->twist_mission_id = $randomTwist->id;
        }

        $this->setup_mode = 'random';
        $this->is_setup_complete = true;
        $this->save();
    }

    /**
     * Vérifier si le tirage est complet
     */
    public function isSetupValid(): bool
    {
        return $this->primary_mission_id !== null 
            && $this->terrain_layout_id !== null 
            && $this->twist_mission_id !== null;
    }
}
