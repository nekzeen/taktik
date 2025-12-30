<?php

namespace App\Http\Controllers;

use App\Models\PlayerMatch;
use App\Models\PlayerMatchRequest;
use App\Models\User;
use App\Models\Faction;
use App\Models\BsdataDetachment;
use App\Services\ArmyPointsService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlayerMatchController extends Controller
{
    use AuthorizesRequests;

    private function ensureUserIsMatchPlayer(PlayerMatch $playerMatch): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(401);
        }

        if ($playerMatch->creator_id !== $user->id && $playerMatch->opponent_id !== $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à ces données.');
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $sortBy = $request->get('sort', 'date_desc'); // Par défaut : plus récents au plus anciens

        // Supprimer les matchs expirés
        PlayerMatch::where('status', 'open')
            ->where('opponent_id', null)
            ->get()
            ->each(function ($match) {
                if (!$match->isAvailable()) {
                    $match->forceDelete();
                }
            });

        // Matchs disponibles (créés par d'autres joueurs)
        $availableMatches = PlayerMatch::where('status', 'open')
            ->where('opponent_id', null)
            ->with(['creator'])
            ->get()
            ->filter(fn($match) => $match->isAvailable())
            ->filter(fn($match) => !$user || $match->creator_id !== $user->id)
            ->sort(function ($a, $b) use ($sortBy) {
                // Récupérer la date de disponibilité
                $dateA = $a->availability_type === 'single' ? $a->available_at : $a->available_from;
                $dateB = $b->availability_type === 'single' ? $b->available_at : $b->available_from;
                
                if ($sortBy === 'date_asc') {
                    return $dateA <=> $dateB; // Plus anciens au plus récent
                } else {
                    return $dateB <=> $dateA; // Plus récents au plus ancien
                }
            })
            ->values();

        // Charger les demandes de l'utilisateur pour chaque match disponible
        $userRequests = $user ? $user->playerMatchRequests()->pluck('player_match_id', 'status')->toArray() : [];

        // Mes matchs proposés (exclure les expirés)
        $myProposedMatches = $user ? PlayerMatch::where('creator_id', $user->id)
            ->with(['opponent', 'requests'])
            ->get()
            ->filter(fn($match) => $match->isAvailable() || $match->status !== 'open' || $match->opponent_id !== null)
            ->filter(fn($match) => $match->status !== 'completed')
            ->sort(function ($a, $b) use ($sortBy) {
                // Récupérer la date de disponibilité
                $dateA = $a->availability_type === 'single' ? $a->available_at : $a->available_from;
                $dateB = $b->availability_type === 'single' ? $b->available_at : $b->available_from;
                
                if ($sortBy === 'date_asc') {
                    return $dateA <=> $dateB; // Plus anciens au plus récent
                } else {
                    return $dateB <=> $dateA; // Plus récents au plus ancien
                }
            })
            ->values() : collect();

        // Mes matchs confirmés
        $myConfirmedMatches = $user ? PlayerMatch::where(function ($q) use ($user) {
            $q->where('creator_id', $user->id)
                ->orWhere('opponent_id', $user->id);
        })
            ->where('status', 'confirmed')
            ->with(['creator', 'opponent'])
            ->get()
            ->sort(function ($a, $b) use ($sortBy) {
                // Récupérer la date de disponibilité
                $dateA = $a->availability_type === 'single' ? $a->available_at : $a->available_from;
                $dateB = $b->availability_type === 'single' ? $b->available_at : $b->available_from;
                
                if ($sortBy === 'date_asc') {
                    return $dateA <=> $dateB; // Plus anciens au plus récent
                } else {
                    return $dateB <=> $dateA; // Plus récents au plus ancien
                }
            })
            ->values() : collect();

        // Matchs en cours (confirmés, accessibles en spectateur)
        $ongoingMatches = PlayerMatch::where('status', 'confirmed')
            ->with(['creator', 'opponent', 'primaryMission', 'secondaryMission', 'terrainLayout', 'twistMission'])
            ->get()
            ->filter(function ($match) use ($user) {
                // Exclure les matchs où l'utilisateur est joueur
                if ($user && ($match->creator_id === $user->id || $match->opponent_id === $user->id)) {
                    return false;
                }
                return true;
            })
            ->sort(function ($a, $b) use ($sortBy) {
                // Récupérer la date de disponibilité
                $dateA = $a->availability_type === 'single' ? $a->available_at : $a->available_from;
                $dateB = $b->availability_type === 'single' ? $b->available_at : $b->available_from;
                
                if ($sortBy === 'date_asc') {
                    return $dateA <=> $dateB; // Plus anciens au plus récent
                } else {
                    return $dateB <=> $dateA; // Plus récents au plus ancien
                }
            })
            ->values();

        // Historique des matchs terminés
        $completedMatches = $user ? PlayerMatch::where(function ($q) use ($user) {
            $q->where('creator_id', $user->id)
                ->orWhere('opponent_id', $user->id);
        })
            ->where('status', 'completed')
            ->with(['creator', 'opponent', 'primaryMission', 'secondaryMission', 'terrainLayout', 'twistMission'])
            ->orderBy('played_at', 'desc')
            ->get() : collect();

        return view('player-matches.index', compact(
            'availableMatches',
            'myProposedMatches',
            'myConfirmedMatches',
            'ongoingMatches',
            'completedMatches',
            'userRequests',
            'sortBy'
        ));
    }

    public function create()
    {
        $factions = Faction::orderBy('name')
            ->get()
            ->filter(function ($faction) {
                return !empty(trim((string) $faction->name));
            })
            ->values();
        
        $armyPointsOptions = ArmyPointsService::getArmyPointsOptions();
        
        return view('player-matches.create', compact('factions', 'armyPointsOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:competitive,narrative',
            'army_points' => 'required|in:1000,1500,2000,3000,3000+',
            'faction' => 'required|string|max:255',
            'detachment' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'city' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'availability_type' => 'required|in:single,period',
            'available_at' => 'required_if:availability_type,single|nullable|date_format:Y-m-d\\TH:i|after:now',
            'available_from' => 'required_if:availability_type,period|nullable|date_format:Y-m-d\\TH:i|after:now',
            'available_to' => 'required_if:availability_type,period|nullable|date_format:Y-m-d\\TH:i|after:available_from',
        ]);

        $match = PlayerMatch::create([
            'creator_id' => Auth::id(),
            ...$validated,
        ]);

        return redirect()->route('player-matches.index')
            ->with('success', 'Match proposé avec succès !');
    }

    public function show(PlayerMatch $playerMatch)
    {
        $requests = $playerMatch->requests()->with('requester')->get();
        $userRequest = $playerMatch->requests()->where('requester_id', Auth::id())->first();

        $invitations = $playerMatch->invitations()
            ->with(['invitedUser', 'invitedBy'])
            ->latest()
            ->get();
        
        // Calculer les ratios de victoire pour le créateur et l'adversaire
        $creatorStats = $this->calculatePlayerStats($playerMatch->creator_id);
        $opponentStats = $playerMatch->opponent ? $this->calculatePlayerStats($playerMatch->opponent_id) : null;
        
        return view('player-matches.show', compact('playerMatch', 'requests', 'userRequest', 'invitations', 'creatorStats', 'opponentStats'));
    }
    
    private function calculatePlayerStats($userId)
    {
        $totalMatches = PlayerMatch::where('status', 'completed')
            ->where(function ($query) use ($userId) {
                $query->where('creator_id', $userId)
                      ->orWhere('opponent_id', $userId);
            })
            ->count();
        
        $wins = PlayerMatch::where('status', 'completed')
            ->where(function ($query) use ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->where('creator_id', $userId)
                      ->where('creator_score', '>', \DB::raw('opponent_score'));
                })->orWhere(function ($q) use ($userId) {
                    $q->where('opponent_id', $userId)
                      ->where('opponent_score', '>', \DB::raw('creator_score'));
                });
            })
            ->count();
        
        $winRatio = $totalMatches > 0 ? round(($wins / $totalMatches) * 100, 1) : 0;
        
        return [
            'total_matches' => $totalMatches,
            'wins' => $wins,
            'win_ratio' => $winRatio,
        ];
    }

    private function getTranslation($englishTerm)
    {
        $glossary = \DB::table('warhammer_glossary')
            ->where('english_term', $englishTerm)
            ->first();
        
        return $glossary ? $glossary->french_translation : null;
    }

    public function edit(PlayerMatch $playerMatch)
    {
        $this->authorize('update', $playerMatch);
        
        $factions = Faction::orderBy('name_fr')->get();
        $detachments = BsdataDetachment::where('faction_id', $playerMatch->faction)->orderBy('name')->get();
        $armyPointsOptions = ArmyPointsService::getArmyPointsOptions();

        return view('player-matches.edit', compact('playerMatch', 'factions', 'detachments', 'armyPointsOptions'));
    }

    public function update(Request $request, PlayerMatch $playerMatch)
    {
        $this->authorize('update', $playerMatch);

        $validated = $request->validate([
            'type' => 'required|in:competitive,narrative',
            'army_points' => 'required|in:1000,1500,2000,3000,3000+',
            'faction' => 'required|string|max:255',
            'detachment' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'city' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'availability_type' => 'required|in:single,period',
            'available_at' => 'required_if:availability_type,single|nullable|date_format:Y-m-d\\TH:i|after:now',
            'available_from' => 'required_if:availability_type,period|nullable|date_format:Y-m-d\\TH:i|after:now',
            'available_to' => 'required_if:availability_type,period|nullable|date_format:Y-m-d\\TH:i|after:available_from',
        ]);

        $playerMatch->update($validated);

        return redirect()->route('player-matches.show', $playerMatch)
            ->with('success', 'Match modifié avec succès !');
    }

    public function join(PlayerMatch $playerMatch)
    {
        $user = Auth::user();

        if (!$playerMatch->canJoin($user)) {
            return redirect()->back()
                ->with('error', 'Impossible de rejoindre ce match.');
        }

        $playerMatch->update([
            'opponent_id' => $user->id,
            'status' => 'confirmed',
        ]);

        return redirect()->route('player-matches.show', $playerMatch)
            ->with('success', 'Vous avez rejoint le match !');
    }

    public function editScore(PlayerMatch $playerMatch)
    {
        // Redirection vers la page de scoring (testScore)
        return redirect()->route('player-matches.score', $playerMatch);
    }

    public function testScore(PlayerMatch $playerMatch)
    {
        return view('player-matches.test-score', compact('playerMatch'));
    }

    public function viewScore(PlayerMatch $playerMatch)
    {
        // Vérifier que l'utilisateur est l'adversaire (non-créateur)
        if (Auth::id() === $playerMatch->creator_id) {
            return redirect()->route('player-matches.score', $playerMatch);
        }

        return view('player-matches.view-score', compact('playerMatch'));
    }

    public function setScore(Request $request, PlayerMatch $playerMatch)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est l'un des deux joueurs
        if ($playerMatch->creator_id !== $user->id && $playerMatch->opponent_id !== $user->id) {
            return response()->json(['error' => 'Vous ne pouvez pas définir le score de ce match.'], 403);
        }

        $validated = $request->validate([
            'creator_result' => 'required|string|in:normal,creator_abandon,opponent_abandon,creator_table_rase,opponent_table_rase',
            'creator_primary_points' => 'required|integer|min:0|max:50',
            'creator_secondary_points' => 'required|integer|min:0|max:40',
            'creator_painting_points' => 'nullable|boolean',
            'creator_score' => 'nullable|integer|min:0',
            'opponent_primary_points' => 'required|integer|min:0|max:50',
            'opponent_secondary_points' => 'required|integer|min:0|max:40',
            'opponent_painting_points' => 'nullable|boolean',
            'opponent_score' => 'nullable|integer|min:0',
        ]);
        
        // Calculer les points totaux (missions + peinture)
        $creatorTotal = $validated['creator_score'] ?? (
            $validated['creator_primary_points'] 
            + $validated['creator_secondary_points'] 
            + ($validated['creator_painting_points'] ? 10 : 0)
        );
        
        $opponentTotal = $validated['opponent_score'] ?? (
            $validated['opponent_primary_points'] 
            + $validated['opponent_secondary_points'] 
            + ($validated['opponent_painting_points'] ? 10 : 0)
        );

        // Vérifier si un résultat spécial est sélectionné
        $creatorResult = $validated['creator_result'];
        $hasSpecialResult = in_array($creatorResult, 
            ['creator_abandon', 'opponent_abandon', 'creator_table_rase', 'opponent_table_rase']);

        // Sauvegarder les scores détaillés
        $playerMatch->update([
            'creator_primary_points' => $validated['creator_primary_points'],
            'creator_secondary_points' => $validated['creator_secondary_points'],
            'creator_painting_points' => $validated['creator_painting_points'] ?? false,
            'creator_score' => $creatorTotal,
            'creator_victory_points' => $creatorTotal,
            'opponent_primary_points' => $validated['opponent_primary_points'],
            'opponent_secondary_points' => $validated['opponent_secondary_points'],
            'opponent_painting_points' => $validated['opponent_painting_points'] ?? false,
            'opponent_score' => $opponentTotal,
            'opponent_victory_points' => $opponentTotal,
            'creator_result' => $creatorResult,
            'played_at' => now(),
        ]);

        // Marquer le score comme validé par le joueur actuel
        if ($playerMatch->creator_id === $user->id) {
            $playerMatch->creator_score_validated = true;
        } else {
            $playerMatch->opponent_score_validated = true;
        }

        // Vérifier si les deux joueurs ont validé
        if ($playerMatch->creator_score_validated && $playerMatch->opponent_score_validated) {
            // Les deux ont validé → finaliser le match
            $playerMatch->status = 'completed';
            
            // Déterminer le gagnant
            if ($hasSpecialResult) {
                $playerMatch->determineWinner($creatorResult);
            } else {
                $playerMatch->determineWinner(null);
            }
        }

        $playerMatch->save();

        return response()->json([
            'success' => true, 
            'message' => $playerMatch->status === 'completed' 
                ? 'Résultat enregistré avec succès !' 
                : 'Score enregistré. En attente de la validation de l\'autre joueur...',
            'status' => $playerMatch->status,
            'creator_validated' => $playerMatch->creator_score_validated,
            'opponent_validated' => $playerMatch->opponent_score_validated,
        ]);
    }

    public function validateOpponentScore(Request $request, PlayerMatch $playerMatch)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est l'un des deux joueurs
        if ($playerMatch->creator_id !== $user->id && $playerMatch->opponent_id !== $user->id) {
            return response()->json(['error' => 'Vous ne pouvez pas valider le score de ce match.'], 403);
        }

        // Marquer le score comme validé par le joueur actuel
        if ($playerMatch->creator_id === $user->id) {
            $playerMatch->creator_score_validated = true;
        } else {
            $playerMatch->opponent_score_validated = true;
        }

        // Vérifier si les deux joueurs ont validé
        if ($playerMatch->creator_score_validated && $playerMatch->opponent_score_validated) {
            // Les deux ont validé → finaliser le match
            $playerMatch->status = 'completed';
            
            // Déterminer le gagnant en tenant compte d'un éventuel résultat spécial
            $playerMatch->determineWinner($playerMatch->creator_result);
        }

        $playerMatch->save();

        return response()->json([
            'success' => true,
            'message' => $playerMatch->status === 'completed' 
                ? 'Match finalisé avec succès !' 
                : 'Score validé.',
            'status' => $playerMatch->status,
            'creator_validated' => $playerMatch->creator_score_validated,
            'opponent_validated' => $playerMatch->opponent_score_validated,
        ]);
    }

    public function getValidationStatus(PlayerMatch $playerMatch)
    {
        $this->ensureUserIsMatchPlayer($playerMatch);

        return response()->json([
            'creator_validated' => $playerMatch->creator_score_validated,
            'opponent_validated' => $playerMatch->opponent_score_validated,
            'status' => $playerMatch->status,
            'creator_name' => $playerMatch->creator->name,
            'opponent_name' => $playerMatch->opponent->name,
        ]);
    }

    public function rejectScoreValidation(Request $request, PlayerMatch $playerMatch)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est l'un des deux joueurs
        if ($playerMatch->creator_id !== $user->id && $playerMatch->opponent_id !== $user->id) {
            return response()->json(['error' => 'Vous ne pouvez pas refuser la validation de ce match.'], 403);
        }

        // Réinitialiser les validations
        $playerMatch->creator_score_validated = false;
        $playerMatch->opponent_score_validated = false;
        $playerMatch->status = 'confirmed';
        $playerMatch->save();

        return response()->json([
            'success' => true,
            'message' => 'Validation refusée. Veuillez corriger les scores.',
            'status' => $playerMatch->status,
            'creator_validated' => $playerMatch->creator_score_validated,
            'opponent_validated' => $playerMatch->opponent_score_validated,
        ]);
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

    private function normalizeResult($result)
    {
        // Convertir les résultats du créateur en résultats normalisés
        if ($result === 'creator_abandon') {
            return 'abandon';
        } elseif ($result === 'opponent_abandon') {
            return 'victoire';
        } elseif ($result === 'creator_table_rase') {
            return 'table_rase';
        } elseif ($result === 'opponent_table_rase') {
            return 'victoire';
        }
        return $result;
    }

    public function cancel(PlayerMatch $playerMatch)
    {
        $this->authorize('delete', $playerMatch);

        $playerMatch->update(['status' => 'cancelled']);

        return redirect()->route('player-matches.index')
            ->with('success', 'Match annulé.');
    }

    public function destroy(PlayerMatch $playerMatch)
    {
        $this->authorize('delete', $playerMatch);

        $playerMatch->delete();

        return redirect()->route('player-matches.index')
            ->with('success', 'Match supprimé.');
    }

    // Sauvegarder les scores brouillon en base de données
    public function saveDraftScores(Request $request, PlayerMatch $playerMatch)
    {
        $this->ensureUserIsMatchPlayer($playerMatch);

        try {
            $validated = $request->validate([
                'creator_primary_points' => 'nullable|integer|min:0|max:50',
                'creator_secondary_points' => 'nullable|integer|min:0|max:40',
                'creator_painting_points' => 'nullable|boolean',
                'opponent_primary_points' => 'nullable|integer|min:0|max:50',
                'opponent_secondary_points' => 'nullable|integer|min:0|max:40',
                'opponent_painting_points' => 'nullable|boolean',
                'secondary_type' => 'nullable|in:fixed,tactical',
                'fixed_mission_1' => 'nullable|integer',
                'fixed_mission_2' => 'nullable|integer',
            ]);

            // Le cast 'array' gérera automatiquement la conversion JSON
            $playerMatch->update([
                'draft_scores' => $validated,
            ]);

            return response()->json(['success' => true, 'message' => 'Brouillon sauvegardé']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // Récupérer les scores brouillon depuis la base de données
    public function getDraftScores(PlayerMatch $playerMatch)
    {
        $this->ensureUserIsMatchPlayer($playerMatch);

        try {
            // Le cast 'array' retournera automatiquement un tableau
            $draftScores = $playerMatch->draft_scores;

            return response()->json($draftScores ?? [
                'creator_primary_points' => 0,
                'creator_secondary_points' => 0,
                'creator_painting_points' => true,
                'opponent_primary_points' => 0,
                'opponent_secondary_points' => 0,
                'opponent_painting_points' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Sauvegarder l'état tactique (missions secondaires)
    public function saveTacticalState(Request $request, PlayerMatch $playerMatch, $side)
    {
        $this->ensureUserIsMatchPlayer($playerMatch);

        try {
            $validated = $request->validate([
                'active' => 'nullable|array',
                'discarded' => 'nullable|array',
                'completed' => 'nullable|array',
                'waitingReplacement' => 'nullable|array',
            ]);

            $column = $side === 'creator' ? 'draft_tactical_state_creator' : 'draft_tactical_state_opponent';
            
            $playerMatch->update([
                $column => $validated,
            ]);

            return response()->json(['success' => true, 'message' => 'État tactique sauvegardé']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // Récupérer l'état tactique (missions secondaires)
    public function getTacticalState(PlayerMatch $playerMatch, $side)
    {
        $this->ensureUserIsMatchPlayer($playerMatch);

        try {
            $column = $side === 'creator' ? 'draft_tactical_state_creator' : 'draft_tactical_state_opponent';
            $tacticalState = $playerMatch->{$column};

            return response()->json($tacticalState ?? [
                'active' => [],
                'discarded' => [],
                'completed' => [],
                'waitingReplacement' => [],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Page de saisie du score pour le créateur
     */
    public function scoreFormCreator(PlayerMatch $playerMatch)
    {
        $user = Auth::user();

        // Vérification: utilisateur est le créateur
        if ($playerMatch->creator_id !== $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à saisir le score de ce match.');
        }

        $playerMatch->load(['primaryMission', 'terrainLayout', 'twistMission', 'creator', 'opponent']);

        return view('player-matches.score-creator', compact('playerMatch'));
    }

    /**
     * Page de saisie du score pour l'adversaire
     */
    public function scoreFormOpponent(PlayerMatch $playerMatch)
    {
        $user = Auth::user();

        // Vérification: utilisateur est l'adversaire
        if ($playerMatch->opponent_id !== $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à saisir le score de ce match.');
        }

        $playerMatch->load(['primaryMission', 'terrainLayout', 'twistMission', 'creator', 'opponent']);

        return view('player-matches.score-opponent', compact('playerMatch'));
    }

}
