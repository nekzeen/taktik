<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TournamentMatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tournament_id',
        'primary_mission_id',
        'terrain_layout_id',
        'twist_mission_id',
        'deployment_mode',
        'setup_mode',
        'asymmetric_primary_mission_id',
        'is_setup_complete',
        'army_points',
        'round',
        'table_number',
        'player1_id',
        'player1_army_list_id',
        'player1_score',
        'player1_victory_points',
        'player1_primary_points',
        'player1_secondary_points',
        'player1_painting_points',
        'player1_score_validated',
        'player2_id',
        'player2_army_list_id',
        'player2_score',
        'player2_victory_points',
        'player2_primary_points',
        'player2_secondary_points',
        'player2_painting_points',
        'player2_score_validated',
        'status',
        'winner_id',
        'is_draw',
        'notes',
        'started_at',
        'completed_at',
        'scheduled_at',
        'scheduled_by_user_id',
        'scheduled_from_user_id',
        'score_recorder_id',
        'score_recorder_selected_at',
        'draft_scores',
        'draft_tactical_state_player1',
        'draft_tactical_state_player2',
    ];

    // Les colonnes sont déjà dans fillable, pas besoin de les ajouter à nouveau

    protected $casts = [
        'is_draw' => 'boolean',
        'is_setup_complete' => 'boolean',
        'player1_painting_points' => 'boolean',
        'player1_score_validated' => 'boolean',
        'player2_painting_points' => 'boolean',
        'player2_score_validated' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'score_recorder_selected_at' => 'datetime',
        'draft_scores' => 'array',
        'draft_tactical_state_player1' => 'array',
        'draft_tactical_state_player2' => 'array',
    ];

    /**
     * Relations
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function missionPool(): BelongsTo
    {
        return $this->belongsTo(TournamentMissionPool::class, 'primary_mission_id', 'primary_mission_id');
    }

    public function primaryMission(): BelongsTo
    {
        return $this->belongsTo(PrimaryMission::class);
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

    public function player1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player1_id');
    }

    public function player2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player2_id');
    }

    public function player1ArmyList(): BelongsTo
    {
        return $this->belongsTo(ArmyList::class, 'player1_army_list_id');
    }

    public function player2ArmyList(): BelongsTo
    {
        return $this->belongsTo(ArmyList::class, 'player2_army_list_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function scoreRecorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'score_recorder_id');
    }

    public function scheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_by_user_id');
    }

    public function scheduledFrom(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_from_user_id');
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(MatchAvailability::class, 'tournament_match_id');
    }

    /**
     * Méthodes utilitaires
     */
    public function isPlayer(User $user): bool
    {
        return $this->player1_id === $user->id || $this->player2_id === $user->id;
    }

    public function canEditResult(User $user): bool
    {
        // Le créateur du tournoi ou les joueurs du match peuvent éditer
        return $this->tournament->created_by === $user->id || $this->isPlayer($user);
    }

    public function getOpponent(User $user): ?User
    {
        if ($this->player1_id === $user->id) {
            return $this->player2;
        }
        if ($this->player2_id === $user->id) {
            return $this->player1;
        }
        return null;
    }

    public function getActiveAvailabilities()
    {
        return $this->availabilities()->active()->with('user')->get();
    }

    public function hasActiveAvailability(User $user): bool
    {
        return $this->availabilities()
            ->forUser($user->id)
            ->active()
            ->exists();
    }

    public function bothPlayersAvailable(): bool
    {
        $player1Available = $this->availabilities()
            ->forUser($this->player1_id)
            ->active()
            ->exists();

        $player2Available = $this->availabilities()
            ->forUser($this->player2_id)
            ->active()
            ->exists();

        return $player1Available && $player2Available;
    }

    /**
     * Tirage au sort aléatoire
     */
    public function randomizeSetup(): void
    {
        // Récupérer un pool de missions aléatoire (comme pour les matchs simples)
        $missionPool = TournamentMissionPool::random();
        
        if (!$missionPool) {
            return;
        }

        // Sélectionner la mission primaire du pool
        if (!$this->primary_mission_id) {
            $this->primary_mission_id = $missionPool->primary_mission_id;
        }

        // Sélectionner aléatoirement une disposition de terrain parmi les disponibles du pool
        $availableTerrains = $missionPool->availableTerrainLayouts()->inRandomOrder()->first();
        if ($availableTerrains) {
            $this->terrain_layout_id = $availableTerrains->id;
        }

        // Sélectionner aléatoirement une zone de déploiement (toujours obligatoire)
        $randomDeployment = StrikeForceDeploymentCard::where('is_active', true)->inRandomOrder()->first();
        if ($randomDeployment) {
            $this->deployment_mode = $randomDeployment->name;
        }

        // Sélectionner aléatoirement une péripétie (toujours obligatoire)
        $randomTwist = TwistMission::where('is_active', true)->inRandomOrder()->first();
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
        // Les champs obligatoires sont la mission primaire, le terrain et la péripétie
        return $this->primary_mission_id !== null 
            && $this->terrain_layout_id !== null 
            && $this->twist_mission_id !== null;
    }

    /**
     * Obtenir la zone de déploiement (deployment_mode)
     */
    public function getDeploymentMode(): ?string
    {
        // Retourner la valeur stockée si elle existe
        if ($this->deployment_mode) {
            return $this->deployment_mode;
        }

        // Sinon, chercher le pool de missions qui contient cette mission primaire
        $pool = TournamentMissionPool::where('primary_mission_id', $this->primary_mission_id)->first();
        return $pool?->deployment_mode;
    }

    /**
     * Vérifier si le joueur qui saisit le score a été sélectionné
     */
    public function isScoreRecorderSelected(): bool
    {
        return $this->score_recorder_id !== null;
    }

    /**
     * Obtenir l'autre joueur (celui qui ne saisit pas le score)
     */
    public function getOtherPlayer(User $user): ?User
    {
        if ($this->player1_id === $user->id) {
            return $this->player2;
        }
        if ($this->player2_id === $user->id) {
            return $this->player1;
        }
        return null;
    }

    /**
     * Vérifier si le match peut être configuré (pas encore de configuration)
     */
    public function canConfigure(): bool
    {
        return !$this->isSetupValid();
    }

    /**
     * Vérifier si le score recorder peut être sélectionné
     */
    public function canSelectScoreRecorder(): bool
    {
        return $this->isSetupValid() && !$this->isScoreRecorderSelected();
    }

    /**
     * Déterminer le gagnant basé sur les scores
     */
    public function determineWinner($result = null)
    {
        if ($this->player1_score === null || $this->player2_score === null) {
            return;
        }

        if ($this->player1_score === $this->player2_score) {
            $this->is_draw = true;
            $this->winner_id = null;
        } else {
            $this->is_draw = false;
            $this->winner_id = $this->player1_score > $this->player2_score
                ? $this->player1_id
                : $this->player2_id;
        }

        if ($this->exists) {
            $this->save();
        }
    }

    /**
     * Réinitialiser la validation des scores
     */
    public function resetValidation()
    {
        $this->player1_score_validated = false;
        $this->player2_score_validated = false;
        $this->status = 'confirmed';
        $this->save();
    }
}
