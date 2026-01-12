<?php

namespace App\Http\Controllers;

use App\Mail\TournamentMatchDateAccepted;
use App\Mail\TournamentMatchDateCancelled;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\PlayerAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
            'player1_result' => 'required|string|in:victoire,defaite,abandon,table_rase',
            'player1_victory_points' => 'required|integer|min:0',
            'player2_result' => 'required|string|in:victoire,defaite,abandon,table_rase',
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

        // Vérifier que l'utilisateur est l'un des deux joueurs
        if ($match->player1_id !== $user->id && $match->player2_id !== $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à saisir le score de ce match.');
        }

        $match->load(['primaryMission', 'terrainLayout', 'twistMission', 'player1', 'player2', 'player1ArmyList', 'player2ArmyList']);

        return view('tournaments.matches.score', compact('tournament', 'match'));
    }

    /**
     * Page de saisie du score pour Player 1
     */
    public function scoreFormPlayer1(Tournament $tournament, TournamentMatch $match)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est player1
        if ($match->player1_id !== $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à saisir le score de ce match.');
        }

        $match->load(['primaryMission', 'terrainLayout', 'twistMission', 'player1', 'player2', 'player1ArmyList', 'player2ArmyList']);

        return view('tournaments.matches.score-player1', compact('tournament', 'match'));
    }

    /**
     * Page de saisie du score pour Player 2
     */
    public function scoreFormPlayer2(Tournament $tournament, TournamentMatch $match)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est player2
        if ($match->player2_id !== $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à saisir le score de ce match.');
        }

        $match->load(['primaryMission', 'terrainLayout', 'twistMission', 'player1', 'player2', 'player1ArmyList', 'player2ArmyList']);

        return view('tournaments.matches.score-player2', compact('tournament', 'match'));
    }

    /**
     * Page de visualisation du score (pour voir les scores en temps réel)
     */
    public function scoreView(Tournament $tournament, TournamentMatch $match)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est l'un des deux joueurs
        if ($match->player1_id !== $user->id && $match->player2_id !== $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à visualiser ce match.');
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

        // Vérifier que l'utilisateur est l'un des deux joueurs du match
        if ($match->player1_id !== $user->id && $match->player2_id !== $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à saisir le score de ce match.');
        }

        // Vérifier que le match n'est pas déjà finalisé
        if ($match->status === 'completed') {
            abort(403, 'Ce match est déjà finalisé.');
        }

        // Les joueurs peuvent resoumetttre à tout moment, même si le match est en attente

        $validated = $request->validate([
            'player1_primary_points' => 'required|integer|min:0|max:50',
            'player1_secondary_points' => 'required|integer|min:0|max:40',
            'player1_painting_points' => 'nullable|boolean',
            'player1_result' => 'nullable|string|in:creator_abandon,creator_table_rase',
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

        // Déterminer quel joueur soumet
        $isPlayer1 = $match->player1_id === $user->id;

        // Sauvegarder les scores et marquer le joueur comme ayant validé
        $updateData = [
            'player1_score' => $player1Total,
            'player2_score' => $player2Total,
            'player1_victory_points' => $player1Total,
            'player2_victory_points' => $player2Total,
            'status' => 'confirmed',
        ];

        // Marquer UNIQUEMENT le joueur qui soumet comme validé
        if ($isPlayer1) {
            $updateData['player1_score_validated'] = true;
        } else {
            $updateData['player2_score_validated'] = true;
        }

        // Sauvegarder le résultat spécial (si déclaré) pour finalisation
        if (!empty($validated['player1_result'])) {
            $draft = $match->draft_scores ?? [];
            $draft['special_result'] = $validated['player1_result'];
            $draft['special_result_by'] = $user->id;
            $updateData['draft_scores'] = $draft;
        }

        $match->update($updateData);

        // Rediriger vers la page de score du joueur avec un message
        $scoreRoute = $isPlayer1 
            ? route('tournaments.matches.score', [$tournament, $match, 'player1'])
            : route('tournaments.matches.score', [$tournament, $match, 'player2']);

        return redirect($scoreRoute)
            ->with('success', 'Score enregistré. En attente de validation de l\'autre joueur.');
    }

    public function acceptDate(Tournament $tournament, TournamentMatch $match)
    {
        $user = Auth::user();

        if ($match->tournament_id !== $tournament->id) {
            abort(404);
        }

        if (!$match->isPlayer($user)) {
            abort(403, 'Vous n\'êtes pas un joueur de ce match.');
        }

        if ($match->status === 'completed') {
            return back()->with('error', 'Ce match est déjà terminé.');
        }

        if ($match->scheduled_at) {
            return back()->with('error', 'Ce match est déjà planifié.');
        }

        $opponent = $match->getOpponent($user);
        if (!$opponent) {
            return back()->with('error', 'Adversaire introuvable.');
        }

        $availability = PlayerAvailability::where('tournament_id', $tournament->id)
            ->where('user_id', $opponent->id)
            ->where('type', 'single')
            ->whereNotNull('available_at')
            ->where('available_at', '>=', now())
            ->first();

        if (!$availability) {
            return back()->with('error', 'Aucune disponibilité ponctuelle valide à accepter pour votre adversaire.');
        }

        DB::transaction(function () use ($match, $user, $opponent, $availability, $tournament) {
            $updated = TournamentMatch::whereKey($match->id)
                ->where('tournament_id', $tournament->id)
                ->whereNull('scheduled_at')
                ->update([
                    'scheduled_at' => $availability->available_at,
                    'scheduled_by_user_id' => $user->id,
                    'scheduled_from_user_id' => $opponent->id,
                ]);

            if ($updated !== 1) {
                abort(409, 'Ce match vient d\'être planifié par quelqu\'un d\'autre.');
            }

            PlayerAvailability::where('id', $availability->id)->delete();
        });

        if (!empty($opponent->email)) {
            try {
                Mail::to($opponent->email)->send(new TournamentMatchDateAccepted(
                    tournament: $tournament,
                    match: $match->loadMissing(['player1', 'player2']),
                    proposedBy: $opponent,
                    acceptedBy: $user,
                    scheduledAt: $availability->available_at,
                ));
            } catch (\Throwable $e) {
                Log::error('Erreur envoi email acceptation date match tournoi', [
                    'tournament_id' => $tournament->id,
                    'match_id' => $match->id,
                    'to' => $opponent->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return back()->with('success', 'Date acceptée : le match est maintenant planifié.');
    }

    public function cancelDate(Tournament $tournament, TournamentMatch $match)
    {
        $user = Auth::user();

        if ($match->tournament_id !== $tournament->id) {
            abort(404);
        }

        if (!$match->isPlayer($user)) {
            abort(403, 'Vous n\'êtes pas un joueur de ce match.');
        }

        if ($match->status === 'completed') {
            return back()->with('error', 'Ce match est déjà terminé.');
        }

        if (!$match->scheduled_at) {
            return back()->with('error', 'Ce match n\'est pas planifié.');
        }

        if ((int) $match->scheduled_by_user_id !== (int) $user->id) {
            abort(403, 'Seul le joueur ayant accepté la date peut l\'annuler.');
        }

        $scheduledAt = $match->scheduled_at;
        $proposedById = $match->scheduled_from_user_id;

        if (!$proposedById) {
            return back()->with('error', 'Impossible d\'identifier le joueur ayant proposé la date.');
        }

        $proposedBy = $match->scheduledFrom;
        if (!$proposedBy) {
            return back()->with('error', 'Joueur ayant proposé la date introuvable.');
        }

        DB::transaction(function () use ($match, $tournament, $user) {
            $updated = TournamentMatch::whereKey($match->id)
                ->where('tournament_id', $tournament->id)
                ->whereNotNull('scheduled_at')
                ->where('scheduled_by_user_id', $user->id)
                ->update([
                    'scheduled_at' => null,
                    'scheduled_by_user_id' => null,
                    'scheduled_from_user_id' => null,
                ]);

            if ($updated !== 1) {
                abort(409, 'Ce match vient d\'être modifié par quelqu\'un d\'autre.');
            }
        });

        if ($scheduledAt && $scheduledAt >= now()) {
            PlayerAvailability::firstOrCreate(
                [
                    'tournament_id' => $tournament->id,
                    'user_id' => $proposedBy->id,
                ],
                [
                    'type' => 'single',
                    'available_at' => $scheduledAt,
                    'available_from' => null,
                    'available_to' => null,
                    'notes' => null,
                ]
            );
        }

        if (!empty($proposedBy->email)) {
            try {
                Mail::to($proposedBy->email)->send(new TournamentMatchDateCancelled(
                    tournament: $tournament,
                    match: $match->loadMissing(['player1', 'player2']),
                    proposedBy: $proposedBy,
                    cancelledBy: $user,
                    scheduledAt: $scheduledAt,
                ));
            } catch (\Throwable $e) {
                Log::error('Erreur envoi email annulation date match tournoi', [
                    'tournament_id' => $tournament->id,
                    'match_id' => $match->id,
                    'to' => $proposedBy->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return back()->with('success', 'Date annulée : le match n\'est plus planifié.');
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

    /**
     * Enregistrer les scores du joueur actuel
     */
    public function setScore(Request $request, TournamentMatch $tournamentMatch)
    {
        $user = Auth::user();

        // Vérifier l'authentification
        if ($tournamentMatch->player1_id !== $user->id && $tournamentMatch->player2_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Vérifier que le match n'est pas déjà finalisé
        if ($tournamentMatch->status === 'completed') {
            return response()->json(['error' => 'Ce match est déjà finalisé.'], 403);
        }

        // Valider les données
        $validated = $request->validate([
            'player1_primary_points' => 'required|integer|min:0',
            'player1_secondary_points' => 'required|integer|min:0',
            'player1_painting_points' => 'required|boolean',
            'player2_primary_points' => 'required|integer|min:0',
            'player2_secondary_points' => 'required|integer|min:0',
            'player2_painting_points' => 'required|boolean',
            'player1_result' => 'nullable|string|in:creator_abandon,creator_table_rase',
            'player2_result' => 'nullable|string|in:victoire',
        ]);

        // Calculer les scores totaux
        $player1Score = $validated['player1_primary_points'] + $validated['player1_secondary_points'] + ($validated['player1_painting_points'] ? 1 : 0);
        $player2Score = $validated['player2_primary_points'] + $validated['player2_secondary_points'] + ($validated['player2_painting_points'] ? 1 : 0);

        // Mettre à jour les scores
        $tournamentMatch->player1_primary_points = $validated['player1_primary_points'];
        $tournamentMatch->player1_secondary_points = $validated['player1_secondary_points'];
        $tournamentMatch->player1_painting_points = $validated['player1_painting_points'];
        $tournamentMatch->player1_score = $player1Score;

        $tournamentMatch->player2_primary_points = $validated['player2_primary_points'];
        $tournamentMatch->player2_secondary_points = $validated['player2_secondary_points'];
        $tournamentMatch->player2_painting_points = $validated['player2_painting_points'];
        $tournamentMatch->player2_score = $player2Score;

        // Sauvegarder le résultat spécial dans draft_scores (pour finalisation)
        if (!empty($validated['player1_result'])) {
            $draft = $tournamentMatch->draft_scores ?? [];
            $draft['special_result'] = $validated['player1_result'];
            $draft['special_result_by'] = $user->id;
            $tournamentMatch->draft_scores = $draft;
        }

        // Marquer le joueur actuel comme validé
        if ($tournamentMatch->player1_id === $user->id) {
            $tournamentMatch->player1_score_validated = true;
        } else {
            $tournamentMatch->player2_score_validated = true;
        }

        // Dès qu'un joueur soumet, le match passe en attente de validation
        // (robuste même si le status initial n'est pas exactement 'pending')
        $tournamentMatch->status = 'confirmed';

        $tournamentMatch->save();

        return response()->json([
            'success' => true,
            'message' => 'Score enregistré. En attente de la validation de l\'autre joueur...',
            'status' => $tournamentMatch->status,
            'player1_validated' => $tournamentMatch->player1_score_validated,
            'player2_validated' => $tournamentMatch->player2_score_validated,
        ]);
    }

    /**
     * Valider le score de l'adversaire et finaliser si les deux ont validé
     */
    public function validateOpponentScore(Request $request, TournamentMatch $tournamentMatch)
    {
        $user = Auth::user();

        // Vérifier l'authentification
        if ($tournamentMatch->player1_id !== $user->id && $tournamentMatch->player2_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Marquer comme validé
        if ($tournamentMatch->player1_id === $user->id) {
            $tournamentMatch->player1_score_validated = true;
        } else {
            $tournamentMatch->player2_score_validated = true;
        }

        // Si les deux ont validé → finaliser
        if ($tournamentMatch->player1_score_validated && $tournamentMatch->player2_score_validated) {
            $tournamentMatch->status = 'completed';
            $tournamentMatch->completed_at = now();

            $draft = $tournamentMatch->draft_scores ?? [];
            if (!empty($draft['special_result']) && !empty($draft['special_result_by'])) {
                $specialBy = (int) $draft['special_result_by'];
                $opponentId = $specialBy === (int) $tournamentMatch->player1_id
                    ? (int) $tournamentMatch->player2_id
                    : (int) $tournamentMatch->player1_id;

                $tournamentMatch->is_draw = false;
                $tournamentMatch->winner_id = $opponentId;
            } else {
                $tournamentMatch->determineWinner();
            }
        }

        $tournamentMatch->save();

        return response()->json([
            'success' => true,
            'message' => $tournamentMatch->status === 'completed' 
                ? 'Match finalisé avec succès !' 
                : 'Score validé.',
            'status' => $tournamentMatch->status,
            'player1_validated' => $tournamentMatch->player1_score_validated,
            'player2_validated' => $tournamentMatch->player2_score_validated,
        ]);
    }

    /**
     * Refuser la validation et réinitialiser
     */
    public function rejectScoreValidation(Request $request, TournamentMatch $tournamentMatch)
    {
        $user = Auth::user();

        // Vérifier l'authentification
        if ($tournamentMatch->player1_id !== $user->id && $tournamentMatch->player2_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Réinitialiser les validations
        $tournamentMatch->player1_score_validated = false;
        $tournamentMatch->player2_score_validated = false;
        $tournamentMatch->status = 'confirmed';
        $tournamentMatch->save();

        return response()->json([
            'success' => true,
            'message' => 'Validation refusée. Veuillez corriger les scores.',
            'status' => $tournamentMatch->status,
            'player1_validated' => $tournamentMatch->player1_score_validated,
            'player2_validated' => $tournamentMatch->player2_score_validated,
        ]);
    }

    /**
     * Récupérer l'état actuel de la validation (pour le polling)
     */
    public function getValidationStatus(TournamentMatch $tournamentMatch)
    {
        return response()->json([
            'player1_validated' => $tournamentMatch->player1_score_validated,
            'player2_validated' => $tournamentMatch->player2_score_validated,
            'status' => $tournamentMatch->status,
            'player1_name' => $tournamentMatch->player1->name,
            'player2_name' => $tournamentMatch->player2->name,
        ]);
    }
}
