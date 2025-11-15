<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\GameMatch;
use App\Models\TournamentMatch;
use App\Models\PlayerMatch;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Tournois en cours et à venir
        $tournaments = Tournament::whereIn('status', ['open', 'in_progress'])
            ->orderBy('start_date')
            ->take(6)
            ->get();

        // Prochains matchs
        $upcomingMatches = GameMatch::where('status', 'confirmed')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', now())
            ->with(['player1', 'player2', 'tournament'])
            ->orderBy('scheduled_at')
            ->take(10)
            ->get();

        return view('home', compact('tournaments', 'upcomingMatches'));
    }

    public function rankings()
    {
        // Récupérer tous les tournois complétés
        $tournaments = Tournament::where('status', 'completed')
            ->with(['tournamentMatches.player1', 'tournamentMatches.player2'])
            ->orderBy('end_date', 'desc')
            ->get();

        // Calculer les classements globaux
        $globalRankings = $this->calculateGlobalRankings();

        // Classements par tournoi
        $tournamentRankings = [];
        foreach ($tournaments as $tournament) {
            $tournamentRankings[$tournament->id] = $this->calculateTournamentRankings($tournament);
        }

        return view('rankings', compact('tournaments', 'globalRankings', 'tournamentRankings'));
    }

    private function calculateGlobalRankings()
    {
        $rankings = [];

        // Récupérer tous les matchs de tournoi complétés
        $tournamentMatches = TournamentMatch::where('status', 'completed')
            ->with(['player1', 'player2'])
            ->get();

        foreach ($tournamentMatches as $match) {
            // Joueur 1
            if (!isset($rankings[$match->player1_id])) {
                $rankings[$match->player1_id] = [
                    'user' => $match->player1,
                    'wins' => 0,
                    'losses' => 0,
                    'draws' => 0,
                    'points' => 0,
                ];
            }

            // Joueur 2
            if (!isset($rankings[$match->player2_id])) {
                $rankings[$match->player2_id] = [
                    'user' => $match->player2,
                    'wins' => 0,
                    'losses' => 0,
                    'draws' => 0,
                    'points' => 0,
                ];
            }

            // Déterminer le résultat
            if ($match->is_draw) {
                $rankings[$match->player1_id]['draws']++;
                $rankings[$match->player2_id]['draws']++;
                $rankings[$match->player1_id]['points'] += 1;
                $rankings[$match->player2_id]['points'] += 1;
            } elseif ($match->winner_id === $match->player1_id) {
                $rankings[$match->player1_id]['wins']++;
                $rankings[$match->player2_id]['losses']++;
                $rankings[$match->player1_id]['points'] += 3;
            } else {
                $rankings[$match->player2_id]['wins']++;
                $rankings[$match->player1_id]['losses']++;
                $rankings[$match->player2_id]['points'] += 3;
            }
        }

        // Récupérer tous les matchs de joueur complétés
        $playerMatches = PlayerMatch::where('status', 'completed')
            ->with(['creator', 'opponent'])
            ->get();

        foreach ($playerMatches as $match) {
            // Créateur
            if (!isset($rankings[$match->creator_id])) {
                $rankings[$match->creator_id] = [
                    'user' => $match->creator,
                    'wins' => 0,
                    'losses' => 0,
                    'draws' => 0,
                    'points' => 0,
                ];
            }

            // Adversaire
            if (!isset($rankings[$match->opponent_id])) {
                $rankings[$match->opponent_id] = [
                    'user' => $match->opponent,
                    'wins' => 0,
                    'losses' => 0,
                    'draws' => 0,
                    'points' => 0,
                ];
            }

            // Déterminer le résultat basé sur winner_id (respecte les abandons)
            if ($match->is_draw) {
                $rankings[$match->creator_id]['draws']++;
                $rankings[$match->opponent_id]['draws']++;
                $rankings[$match->creator_id]['points'] += 1;
                $rankings[$match->opponent_id]['points'] += 1;
            } elseif ($match->winner_id === $match->creator_id) {
                $rankings[$match->creator_id]['wins']++;
                $rankings[$match->opponent_id]['losses']++;
                $rankings[$match->creator_id]['points'] += 3;
            } else {
                $rankings[$match->opponent_id]['wins']++;
                $rankings[$match->creator_id]['losses']++;
                $rankings[$match->opponent_id]['points'] += 3;
            }
        }

        // Trier par points (décroissant), puis par nombre de victoires
        usort($rankings, function ($a, $b) {
            if ($b['points'] !== $a['points']) {
                return $b['points'] - $a['points'];
            }
            return $b['wins'] - $a['wins'];
        });

        return $rankings;
    }

    private function calculateTournamentRankings(Tournament $tournament)
    {
        $matches = $tournament->tournamentMatches()
            ->where('status', 'completed')
            ->with(['player1', 'player2'])
            ->get();

        $rankings = [];

        foreach ($matches as $match) {
            // Joueur 1
            if (!isset($rankings[$match->player1_id])) {
                $rankings[$match->player1_id] = [
                    'user' => $match->player1,
                    'wins' => 0,
                    'losses' => 0,
                    'draws' => 0,
                    'points' => 0,
                ];
            }

            // Joueur 2
            if (!isset($rankings[$match->player2_id])) {
                $rankings[$match->player2_id] = [
                    'user' => $match->player2,
                    'wins' => 0,
                    'losses' => 0,
                    'draws' => 0,
                    'points' => 0,
                ];
            }

            // Déterminer le résultat
            if ($match->is_draw) {
                $rankings[$match->player1_id]['draws']++;
                $rankings[$match->player2_id]['draws']++;
                $rankings[$match->player1_id]['points'] += 1;
                $rankings[$match->player2_id]['points'] += 1;
            } elseif ($match->winner_id === $match->player1_id) {
                $rankings[$match->player1_id]['wins']++;
                $rankings[$match->player2_id]['losses']++;
                $rankings[$match->player1_id]['points'] += 3;
            } else {
                $rankings[$match->player2_id]['wins']++;
                $rankings[$match->player1_id]['losses']++;
                $rankings[$match->player2_id]['points'] += 3;
            }
        }

        // Trier par points (décroissant), puis par nombre de victoires
        usort($rankings, function ($a, $b) {
            if ($b['points'] !== $a['points']) {
                return $b['points'] - $a['points'];
            }
            return $b['wins'] - $a['wins'];
        });

        return $rankings;
    }
}
