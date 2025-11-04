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
    public function index(Request $request)
    {
        $user = Auth::user();
        $sortBy = $request->get('sort', 'date_asc'); // Par défaut : plus anciens au plus récent

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

        return view('player-matches.index', compact(
            'availableMatches',
            'myProposedMatches',
            'myConfirmedMatches',
            'userRequests',
            'sortBy'
        ));
    }

    public function create()
    {
        $factions = Faction::orderBy('name')
            ->get()
            ->filter(function ($faction) {
                // Utiliser name_fr si non-vide, sinon name
                $name = !empty(trim($faction->name_fr)) ? $faction->name_fr : $faction->name;
                return !empty(trim($name));
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
            'department' => 'required|string|max:255',
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
        
        // Calculer les ratios de victoire pour le créateur et l'adversaire
        $creatorStats = $this->calculatePlayerStats($playerMatch->creator_id);
        $opponentStats = $playerMatch->opponent ? $this->calculatePlayerStats($playerMatch->opponent_id) : null;
        
        return view('player-matches.show', compact('playerMatch', 'requests', 'userRequest', 'creatorStats', 'opponentStats'));
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

        if (!$playerMatch->canSetScore($user)) {
            return redirect()->back()
                ->with('error', 'Vous ne pouvez pas définir le score de ce match.');
        }

        $validated = $request->validate([
            'creator_result' => 'required|string|in:nul,creator_abandon,opponent_abandon,creator_table_rase,opponent_table_rase',
            'creator_primary_points' => 'required|integer|min:0|max:50',
            'creator_secondary_points' => 'required|integer|min:0|max:40',
            'creator_painting_points' => 'nullable|boolean',
            'opponent_result' => 'required|string|in:nul,abandon,victoire,table_rase',
            'opponent_primary_points' => 'required|integer|min:0|max:50',
            'opponent_secondary_points' => 'required|integer|min:0|max:40',
            'opponent_painting_points' => 'nullable|boolean',
        ]);
        
        // Calculer les points totaux (missions + peinture)
        $creatorTotal = $validated['creator_primary_points'] 
            + $validated['creator_secondary_points'] 
            + ($validated['creator_painting_points'] ? 10 : 0);
        
        $opponentTotal = $validated['opponent_primary_points'] 
            + $validated['opponent_secondary_points'] 
            + ($validated['opponent_painting_points'] ? 10 : 0);

        // Vérifier si un résultat spécial est sélectionné
        $hasSpecialResult = in_array($validated['creator_result'], 
            ['nul', 'creator_abandon', 'opponent_abandon', 'creator_table_rase', 'opponent_table_rase']);

        if ($hasSpecialResult) {
            // Si un résultat spécial est coché, les points ne sont pas utilisés pour déterminer le gagnant
            // Mais on les enregistre quand même
            $playerMatch->update([
                'creator_score' => $creatorTotal,
                'opponent_score' => $opponentTotal,
                'creator_victory_points' => $creatorTotal,
                'opponent_victory_points' => $opponentTotal,
                'played_at' => now(),
            ]);
            
            // Déterminer le gagnant en passant le résultat du créateur
            $playerMatch->determineWinner($validated['creator_result']);
        } else {
            // Si aucun résultat spécial n'est coché, les points déterminent le gagnant
            $playerMatch->update([
                'creator_score' => $creatorTotal,
                'opponent_score' => $opponentTotal,
                'creator_victory_points' => $creatorTotal,
                'opponent_victory_points' => $opponentTotal,
                'played_at' => now(),
            ]);
            
            // Déterminer le gagnant basé sur les points (pas de résultat spécial)
            $playerMatch->determineWinner(null);
        }

        $playerMatch->update(['status' => 'completed']);
        $playerMatch->save();

        return redirect()->route('player-matches.show', $playerMatch)
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

}
