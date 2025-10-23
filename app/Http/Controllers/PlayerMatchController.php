<?php

namespace App\Http\Controllers;

use App\Models\PlayerMatch;
use App\Models\PlayerMatchRequest;
use App\Models\User;
use App\Models\Faction;
use App\Models\BsdataDetachment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlayerMatchController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $user = Auth::user();
        $sortBy = $request->get('sort', 'date_asc'); // Par défaut : plus anciens au plus récent

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

        // Mes matchs proposés
        $myProposedMatches = $user ? PlayerMatch::where('creator_id', $user->id)
            ->with(['opponent', 'requests'])
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
        $factions = Faction::whereHas('detachments')->orderBy('name_fr')->get();
        return view('player-matches.create', compact('factions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:competitive,narrative',
            'army_points' => 'required|integer|min:500|max:5000',
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
        return view('player-matches.show', compact('playerMatch', 'requests', 'userRequest'));
    }

    public function edit(PlayerMatch $playerMatch)
    {
        $this->authorize('update', $playerMatch);
        $factions = Faction::whereHas('detachments')->orderBy('name_fr')->get();
        $detachments = BsdataDetachment::where('faction_id', $playerMatch->faction)->orderBy('name')->get();

        return view('player-matches.edit', compact('playerMatch', 'factions', 'detachments'));
    }

    public function update(Request $request, PlayerMatch $playerMatch)
    {
        $this->authorize('update', $playerMatch);

        $validated = $request->validate([
            'type' => 'required|in:competitive,narrative',
            'army_points' => 'required|integer|min:500|max:5000',
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

    public function setScore(Request $request, PlayerMatch $playerMatch)
    {
        $user = Auth::user();

        if (!$playerMatch->canSetScore($user)) {
            return redirect()->back()
                ->with('error', 'Vous ne pouvez pas définir le score de ce match.');
        }

        $validated = $request->validate([
            'creator_score' => 'required|integer|min:0',
            'opponent_score' => 'required|integer|min:0',
        ]);

        $playerMatch->update([
            'creator_score' => $validated['creator_score'],
            'opponent_score' => $validated['opponent_score'],
            'played_at' => now(),
        ]);

        $playerMatch->determineWinner();
        $playerMatch->update(['status' => 'completed']);

        return redirect()->route('player-matches.show', $playerMatch)
            ->with('success', 'Score enregistré avec succès !');
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
