<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service pour la gestion des tournois
 * 
 * Centralise la logique métier liée aux tournois
 */
class TournamentService
{
    /**
     * Obtenir les tournois disponibles pour un utilisateur
     */
    public function getAvailableTournaments(User $user = null): Collection
    {
        return Tournament::where('status', 'registration_open')
            ->with(['organizer'])
            ->get();
    }

    /**
     * Obtenir les tournois d'un utilisateur
     */
    public function getUserTournaments(User $user): Collection
    {
        return Tournament::where('organizer_id', $user->id)
            ->with(['matches', 'missionPools'])
            ->get();
    }

    /**
     * Obtenir les matchs d'un tournoi
     */
    public function getTournamentMatches(Tournament $tournament): Collection
    {
        return TournamentMatch::where('tournament_id', $tournament->id)
            ->with(['player1', 'player2', 'primaryMission', 'secondaryMission', 'twistMission'])
            ->get();
    }

    /**
     * Obtenir les matchs d'un tournoi par round
     */
    public function getTournamentMatchesByRound(Tournament $tournament, int $round): Collection
    {
        return TournamentMatch::where('tournament_id', $tournament->id)
            ->where('round', $round)
            ->with(['player1', 'player2', 'primaryMission', 'secondaryMission'])
            ->get();
    }

    /**
     * Vérifier si un utilisateur peut s'inscrire à un tournoi
     */
    public function canRegister(Tournament $tournament, User $user): bool
    {
        return $tournament->status === 'registration_open'
            && $tournament->players()->where('user_id', $user->id)->count() === 0;
    }

    /**
     * Vérifier si un utilisateur est inscrit à un tournoi
     */
    public function isRegistered(Tournament $tournament, User $user): bool
    {
        return $tournament->players()->where('user_id', $user->id)->exists();
    }

    /**
     * Obtenir le classement d'un tournoi
     */
    public function getTournamentRanking(Tournament $tournament): array
    {
        $players = $tournament->players;
        $ranking = [];

        foreach ($players as $player) {
            $matches = TournamentMatch::where('tournament_id', $tournament->id)
                ->where(function ($q) use ($player) {
                    $q->where('player1_id', $player->id)
                        ->orWhere('player2_id', $player->id);
                })
                ->where('status', 'completed')
                ->get();

            $wins = $matches->filter(fn($m) => $m->winner_id === $player->id)->count();
            $losses = $matches->filter(fn($m) => $m->winner_id !== $player->id && $m->winner_id !== null)->count();
            $draws = $matches->filter(fn($m) => $m->is_draw)->count();

            $totalPoints = ($wins * 3) + $draws;

            $ranking[] = [
                'player' => $player,
                'matches' => $matches->count(),
                'wins' => $wins,
                'losses' => $losses,
                'draws' => $draws,
                'points' => $totalPoints,
            ];
        }

        // Trier par points (décroissant)
        usort($ranking, fn($a, $b) => $b['points'] <=> $a['points']);

        return $ranking;
    }

    /**
     * Obtenir le nombre de joueurs inscrits
     */
    public function getRegisteredPlayersCount(Tournament $tournament): int
    {
        return $tournament->players()->count();
    }

    /**
     * Vérifier si le tournoi est plein
     */
    public function isFull(Tournament $tournament): bool
    {
        return $this->getRegisteredPlayersCount($tournament) >= $tournament->max_players;
    }

    /**
     * Obtenir le label du statut
     */
    public function getStatusLabel(Tournament $tournament): string
    {
        return match($tournament->status) {
            'draft' => 'Brouillon',
            'registration_open' => 'Inscriptions ouvertes',
            'registration_closed' => 'Inscriptions fermées',
            'in_progress' => 'En cours',
            'completed' => 'Terminé',
            default => 'Inconnu',
        };
    }

    /**
     * Obtenir le nombre de rounds
     */
    public function getRoundsCount(Tournament $tournament): int
    {
        return TournamentMatch::where('tournament_id', $tournament->id)
            ->max('round') ?? 0;
    }

    /**
     * Obtenir le round actuel
     */
    public function getCurrentRound(Tournament $tournament): int
    {
        return $tournament->current_round ?? 1;
    }
}
