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
                'player1ArmyList' => function ($query) {
                    $query->where('status', '!=', 'rejected');
                },
                'player1ArmyList.faction', 
                'player2ArmyList' => function ($query) {
                    $query->where('status', '!=', 'rejected');
                },
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

        // Vérifier si l'utilisateur est inscrit au tournoi (et non rejeté)
        $userIsRegistered = false;
        if (auth()->check()) {
            $userIsRegistered = $tournament->armyLists()
                ->where('user_id', auth()->id())
                ->where('status', '!=', 'rejected')
                ->exists();
        }

        return view('tournaments.matches.index', [
            'tournament' => $tournament,
            'matches' => $sortedMatches,
            'playerAvailabilities' => $playerAvailabilities,
            'sortBy' => $sortBy,
            'userIsRegistered' => $userIsRegistered
        ]);
    }

    /**
     * Afficher un match spécifique
     */
    public function show(Tournament $tournament, TournamentMatch $match)
    {
        $match->load([
            'player1', 
            'player2', 
            'player1ArmyList', 
            'player2ArmyList', 
            'winner',
            'primaryMission',
            'terrainLayout',
            'twistMission'
        ]);

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
            'player1_result' => 'required|string|in:victoire,defaite,abandon,table_rase,nul',
            'player1_victory_points' => 'required|integer|min:0',
            'player2_result' => 'required|string|in:victoire,defaite,abandon,table_rase,nul',
            'player2_victory_points' => 'required|integer|min:0',
            'notes' => 'nullable|string',
            'is_draw' => 'boolean',
        ]);

        // Calculer les scores en fonction des résultats
        $validated['player1_score'] = $this->calculateScore($validated['player1_result'], $validated['player2_result']);
        $validated['player2_score'] = $this->calculateScore($validated['player2_result'], $validated['player1_result']);

        $match->fill($validated);

        // Déterminer le vainqueur automatiquement
        $match->determineWinner();
        $match->status = 'completed';
        $match->completed_at = now();

        $match->save();

        return redirect()
            ->route('tournaments.matches.show', [$tournament, $match])
            ->with('success', 'Résultat enregistré avec succès !');
    }

    private function calculateScore($playerResult, $opponentResult)
    {
        // Nul = 1 point aux deux joueurs
        if ($playerResult === 'nul' && $opponentResult === 'nul') {
            return 1;
        }

        // Abandon ou Table rase = 0 points
        if ($playerResult === 'abandon' || $playerResult === 'table_rase') {
            return 0;
        }

        // Défaite = 0 points
        if ($playerResult === 'defaite') {
            return 0;
        }

        // Victoire = 3 points
        if ($playerResult === 'victoire') {
            return 3;
        }

        return 0;
    }

    /**
     * Sélectionner le joueur qui saisit le score
     */
    public function selectScoreRecorder(Tournament $tournament, TournamentMatch $match, Request $request)
    {
        $user = Auth::user();

        // Vérifier les permissions (joueurs du match, créateur du tournoi ou super-admin)
        $isPlayer = $match->isPlayer($user);
        $isCreator = $tournament->created_by === $user->id;
        $isSuperAdmin = $user->hasRole('super-admin');

        if (!$isPlayer && !$isCreator && !$isSuperAdmin) {
            abort(403, 'Vous n\'êtes pas autorisé à sélectionner le joueur.');
        }

        // Vérifier que le match peut avoir un score recorder sélectionné
        if (!$match->canSelectScoreRecorder()) {
            return back()->with('error', 'Ce match ne peut pas avoir de joueur sélectionné pour saisir le score.');
        }

        $validated = $request->validate([
            'score_recorder_id' => 'required|in:' . $match->player1_id . ',' . $match->player2_id,
        ]);

        $match->update([
            'score_recorder_id' => $validated['score_recorder_id'],
            'score_recorder_selected_at' => now(),
        ]);

        return back()->with('success', 'Joueur sélectionné avec succès !');
    }

    /**
     * Page de saisie du score (pour le joueur sélectionné)
     */
    public function scoreForm(Tournament $tournament, TournamentMatch $match)
    {
        $user = Auth::user();

        // Vérifier que le match a un score recorder sélectionné
        if (!$match->isScoreRecorderSelected()) {
            abort(403, 'Aucun joueur n\'a été sélectionné pour saisir le score.');
        }

        // Vérifier que l'utilisateur est le score recorder
        if ($match->score_recorder_id !== $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à saisir le score de ce match.');
        }

        $match->load(['primaryMission', 'terrainLayout', 'twistMission', 'player1', 'player2', 'player1ArmyList', 'player2ArmyList']);

        return view('tournaments.matches.score', compact('tournament', 'match'));
    }

    /**
     * Page de visualisation du score (pour le joueur non sélectionné)
     */
    public function scoreView(Tournament $tournament, TournamentMatch $match)
    {
        $user = Auth::user();

        // Vérifier que le match a un score recorder sélectionné
        if (!$match->isScoreRecorderSelected()) {
            abort(403, 'Aucun joueur n\'a été sélectionné pour saisir le score.');
        }

        // Vérifier que l'utilisateur est un des joueurs
        if (!$match->isPlayer($user)) {
            abort(403, 'Vous n\'êtes pas autorisé à visualiser ce match.');
        }

        // Vérifier que l'utilisateur n'est pas le score recorder
        if ($match->score_recorder_id === $user->id) {
            abort(403, 'Vous êtes le joueur qui saisit le score.');
        }

        $match->load(['primaryMission', 'terrainLayout', 'twistMission', 'player1', 'player2', 'player1ArmyList', 'player2ArmyList']);

        return view('tournaments.matches.view-score', compact('tournament', 'match'));
    }

    /**
     * Enregistrer le score du match de tournoi
     */
    public function storeScore(Request $request, Tournament $tournament, TournamentMatch $match)
    {
        $user = Auth::user();

        // Log pour déboguer
        \Log::info('storeScore appelé', [
            'match_id' => $match->id,
            'user_id' => $user->id,
            'request_data' => $request->all(),
        ]);

        // Vérifier que le match a un score recorder sélectionné
        if (!$match->isScoreRecorderSelected()) {
            abort(403, 'Aucun joueur n\'a été sélectionné pour saisir le score.');
        }

        // Vérifier que l'utilisateur est le score recorder
        if ($match->score_recorder_id !== $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à saisir le score de ce match.');
        }

        $validated = $request->validate([
            'player1_primary_points' => 'required|integer|min:0|max:50',
            'player1_secondary_points' => 'required|integer|min:0|max:40',
            'player1_painting_points' => 'nullable|boolean',
            'player1_result' => 'required|string|in:nul,player1_abandon,player2_abandon,player1_table_rase,player2_table_rase',
            'player2_primary_points' => 'required|integer|min:0|max:50',
            'player2_secondary_points' => 'required|integer|min:0|max:40',
            'player2_painting_points' => 'nullable|boolean',
        ]);

        // Calculer les points totaux
        $player1Total = $validated['player1_primary_points'] 
            + $validated['player1_secondary_points'] 
            + ($validated['player1_painting_points'] ? 10 : 0);
        
        $player2Total = $validated['player2_primary_points'] 
            + $validated['player2_secondary_points'] 
            + ($validated['player2_painting_points'] ? 10 : 0);

        // Vérifier si un résultat spécial est sélectionné
        $hasSpecialResult = in_array($validated['player1_result'], 
            ['nul', 'player1_abandon', 'player2_abandon', 'player1_table_rase', 'player2_table_rase']);

        $match->update([
            'player1_score' => $player1Total,
            'player2_score' => $player2Total,
            'player1_victory_points' => $player1Total,
            'player2_victory_points' => $player2Total,
            'player1_primary_points' => $validated['player1_primary_points'],
            'player1_secondary_points' => $validated['player1_secondary_points'],
            'player1_painting_points' => $validated['player1_painting_points'] ?? false,
            'player2_primary_points' => $validated['player2_primary_points'],
            'player2_secondary_points' => $validated['player2_secondary_points'],
            'player2_painting_points' => $validated['player2_painting_points'] ?? false,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Déterminer le gagnant
        if ($hasSpecialResult) {
            $this->determineWinnerFromSpecialResult($match, $validated['player1_result']);
        } else {
            $match->determineWinner();
        }

        $match->save();

        return redirect()->route('tournaments.matches.show', [$tournament, $match])
            ->with('success', 'Score enregistré avec succès !');
    }

    /**
     * Déterminer le gagnant à partir d'un résultat spécial
     */
    private function determineWinnerFromSpecialResult(TournamentMatch $match, string $result): void
    {
        if ($result === 'nul') {
            $match->is_draw = true;
            $match->winner_id = null;
        } elseif ($result === 'player1_abandon' || $result === 'player1_table_rase') {
            $match->is_draw = false;
            $match->winner_id = $match->player2_id;
        } elseif ($result === 'player2_abandon' || $result === 'player2_table_rase') {
            $match->is_draw = false;
            $match->winner_id = $match->player1_id;
        }
    }
}
