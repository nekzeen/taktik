<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TournamentMatchController extends Controller
{
    /**
     * Afficher tous les matchs d'un tournoi
     */
    public function index(Tournament $tournament, Request $request)
    {
        $sortBy = $request->get('sort', 'date_asc'); // Par défaut : plus anciens au plus récent
        
        $matches = $tournament->tournamentMatches()
            ->with([
                'player1', 
                'player2', 
                'player1ArmyList.faction', 
                'player2ArmyList.faction', 
                'winner',
                'availabilities' => function ($query) {
                    $query->active()->with('user');
                }
            ])
            ->orderBy('round')
            ->orderBy('table_number')
            ->get();

        // Trier les matchs selon le paramètre
        $userId = auth()->id();
        $sortedMatches = $matches->sortBy(function ($match) use ($userId, $sortBy) {
            // Priorité 1 : Matchs du joueur connecté (0 = en premier)
            $isPlayerMatch = $userId && ($match->player1_id === $userId || $match->player2_id === $userId) ? 0 : 1;
            
            // Priorité 2 : Statut (completed = 1, in_progress = 2, pending = 3)
            $statusOrder = [
                'completed' => 1,
                'in_progress' => 2,
                'pending' => 3,
            ];
            $statusPriority = $statusOrder[$match->status] ?? 4;
            
            // Priorité 3 : Tri par date selon le paramètre
            $datePriority = 0;
            if ($sortBy === 'date_asc') {
                // Plus anciens au plus récent
                $datePriority = $match->round * 1000 + ($match->table_number ?? 0);
            } elseif ($sortBy === 'date_desc') {
                // Plus récents au plus ancien
                $datePriority = (9999 - $match->round) * 1000 + (9999 - ($match->table_number ?? 0));
            }
            
            // Combinaison : matchs du joueur d'abord, puis tri par statut, puis par date
            return ($isPlayerMatch * 100000) + ($statusPriority * 10000) + $datePriority;
        })->groupBy('round');

        // Charger les disponibilités globales actives pour ce tournoi
        $playerAvailabilities = \App\Models\PlayerAvailability::forTournament($tournament->id)
            ->active()
            ->with('user')
            ->get()
            ->keyBy('user_id');

        return view('tournaments.matches.index', [
            'tournament' => $tournament,
            'matches' => $sortedMatches,
            'playerAvailabilities' => $playerAvailabilities,
            'sortBy' => $sortBy
        ]);
    }

    /**
     * Afficher un match spécifique
     */
    public function show(Tournament $tournament, TournamentMatch $match)
    {
        $match->load(['player1', 'player2', 'player1ArmyList', 'player2ArmyList', 'winner']);

        return view('tournaments.matches.show', compact('tournament', 'match'));
    }

    /**
     * Formulaire de saisie du résultat
     */
    public function edit(Tournament $tournament, TournamentMatch $match)
    {
        $user = Auth::user();

        // Vérifier les permissions
        if (!$match->canEditResult($user)) {
            abort(403, 'Vous n\'êtes pas autorisé à éditer ce match.');
        }

        return view('tournaments.matches.edit', compact('tournament', 'match'));
    }

    /**
     * Enregistrer le résultat du match
     */
    public function update(Request $request, Tournament $tournament, TournamentMatch $match)
    {
        $user = Auth::user();

        // Vérifier les permissions
        if (!$match->canEditResult($user)) {
            abort(403, 'Vous n\'êtes pas autorisé à éditer ce match.');
        }

        $validated = $request->validate([
            'player1_score' => 'nullable|integer|min:0',
            'player1_victory_points' => 'nullable|integer|min:0',
            'player2_score' => 'nullable|integer|min:0',
            'player2_victory_points' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'is_draw' => 'boolean',
        ]);

        $match->fill($validated);

        // Déterminer le vainqueur automatiquement
        if ($validated['player1_score'] !== null && $validated['player2_score'] !== null) {
            $match->determineWinner();
            $match->status = 'completed';
            $match->completed_at = now();
        }

        $match->save();

        return redirect()
            ->route('tournaments.matches.show', [$tournament, $match])
            ->with('success', 'Résultat enregistré avec succès !');
    }
}
