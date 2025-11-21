<?php

namespace App\Http\Controllers;

use App\Models\PlayerMatch;
use App\Models\TournamentMatch;
use App\Models\Tournament;
use Illuminate\Http\Request;

class SpectatorMatchController extends Controller
{
    /**
     * Affiche la page spectateur pour un match simple
     */
    public function showPlayerMatch(PlayerMatch $playerMatch)
    {
        // Vérifier que le match est visible
        if (!$this->isPlayerMatchVisible($playerMatch)) {
            abort(404, 'Ce match n\'est pas disponible en spectateur');
        }

        // Charger les relations
        $playerMatch->load([
            'creator',
            'opponent',
            'primaryMission',
            'secondaryMission',
            'twistMission',
            'terrainLayout',
            'asymmetricPrimaryMission'
        ]);

        return view('player-matches.spectate', [
            'match' => $playerMatch,
            'matchType' => 'player',
            'apiUrl' => route('api.player-matches.spectator-scores', $playerMatch)
        ]);
    }

    /**
     * Retourne les scores pour polling (API)
     * Retourne les scores brouillons (draft_scores) pour la mise à jour en temps réel
     */
    public function getPlayerMatchScores(PlayerMatch $playerMatch)
    {
        // Vérifier que le match est visible
        if (!$this->isPlayerMatchVisible($playerMatch)) {
            return response()->json(['error' => 'Match not found'], 404);
        }

        // Charger les scores brouillons
        $draftScores = $playerMatch->draft_scores ?? [];
        
        // Charger les scores
        $now = now();
        $delayUntil = $now->copy()->addSeconds(2);

        return response()->json([
            // Retourner les scores brouillons (temps réel)
            'creator_primary_points' => $draftScores['creator_primary_points'] ?? 0,
            'creator_secondary_points' => $draftScores['creator_secondary_points'] ?? 0,
            'creator_painting_points' => $draftScores['creator_painting_points'] ?? false,
            'opponent_primary_points' => $draftScores['opponent_primary_points'] ?? 0,
            'opponent_secondary_points' => $draftScores['opponent_secondary_points'] ?? 0,
            'opponent_painting_points' => $draftScores['opponent_painting_points'] ?? false,
            // Scores finalisés (pour référence)
            'creator_score' => $playerMatch->creator_score ?? 0,
            'opponent_score' => $playerMatch->opponent_score ?? 0,
            // États tactiques
            'draft_tactical_state_creator' => $playerMatch->draft_tactical_state_creator ?? [],
            'draft_tactical_state_opponent' => $playerMatch->draft_tactical_state_opponent ?? [],
            'updated_at' => $now->toIso8601String(),
            'delay_until' => $delayUntil->toIso8601String()
        ]);
    }

    /**
     * Affiche la page spectateur pour un match de tournoi
     */
    public function showTournamentMatch(Tournament $tournament, TournamentMatch $match)
    {
        // Vérifier que le match appartient au tournoi
        if ($match->tournament_id !== $tournament->id) {
            abort(404, 'Ce match n\'appartient pas à ce tournoi');
        }

        // Vérifier que le match est visible
        if (!$this->isTournamentMatchVisible($match)) {
            abort(404, 'Ce match n\'est pas disponible en spectateur');
        }

        // Charger les relations
        $match->load([
            'player1',
            'player2',
            'primaryMission',
            'secondaryMission',
            'twistMission',
            'terrainLayout',
            'asymmetricPrimaryMission'
        ]);

        return view('tournaments.matches.spectate', [
            'tournament' => $tournament,
            'match' => $match,
            'matchType' => 'tournament',
            'apiUrl' => route('api.tournaments.matches.spectator-scores', [$tournament, $match])
        ]);
    }

    /**
     * Retourne les scores pour polling (API) - Tournoi
     * Retourne les scores brouillons (draft_scores) pour la mise à jour en temps réel
     */
    public function getTournamentMatchScores(Tournament $tournament, TournamentMatch $match)
    {
        // Vérifier que le match appartient au tournoi
        if ($match->tournament_id !== $tournament->id) {
            return response()->json(['error' => 'Match not found'], 404);
        }

        // Vérifier que le match est visible
        if (!$this->isTournamentMatchVisible($match)) {
            return response()->json(['error' => 'Match not found'], 404);
        }

        // Charger les scores brouillons
        $draftScores = $match->draft_scores ?? [];
        
        // Charger les scores
        $now = now();
        $delayUntil = $now->copy()->addSeconds(2);

        return response()->json([
            // Retourner les scores brouillons (temps réel)
            'player1_primary_points' => $draftScores['player1_primary_points'] ?? 0,
            'player1_secondary_points' => $draftScores['player1_secondary_points'] ?? 0,
            'player1_painting_points' => $draftScores['player1_painting_points'] ?? false,
            'player2_primary_points' => $draftScores['player2_primary_points'] ?? 0,
            'player2_secondary_points' => $draftScores['player2_secondary_points'] ?? 0,
            'player2_painting_points' => $draftScores['player2_painting_points'] ?? false,
            // Scores finalisés (pour référence)
            'player1_score' => $match->player1_score ?? 0,
            'player2_score' => $match->player2_score ?? 0,
            // États tactiques
            'draft_tactical_state_player1' => $match->draft_tactical_state_player1 ?? [],
            'draft_tactical_state_player2' => $match->draft_tactical_state_player2 ?? [],
            'updated_at' => $now->toIso8601String(),
            'delay_until' => $delayUntil->toIso8601String()
        ]);
    }

    /**
     * Vérifie si un match simple est visible en spectateur
     */
    private function isPlayerMatchVisible(PlayerMatch $playerMatch): bool
    {
        // Visible si confirmé ou complété
        return in_array($playerMatch->status, ['confirmed', 'completed']);
    }

    /**
     * Vérifie si un match de tournoi est visible en spectateur
     */
    private function isTournamentMatchVisible(TournamentMatch $match): bool
    {
        // Visible si en cours ou complété
        return in_array($match->status, ['in_progress', 'completed']);
    }
}
