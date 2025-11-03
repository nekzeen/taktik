<?php

namespace App\Http\Controllers;

use App\Models\PlayerMatch;
use App\Models\PlayerMatchRequest;
use App\Models\Faction;
use App\Models\BsdataDetachment;
use App\Notifications\PlayerMatchRequestCreated;
use App\Notifications\PlayerMatchRequestRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlayerMatchRequestController extends Controller
{
    public function index(PlayerMatch $playerMatch)
    {
        // Vérifier que l'utilisateur est le créateur du match
        if (Auth::id() !== $playerMatch->creator_id) {
            abort(403, 'Non autorisé');
        }

        // Récupérer les demandes en attente avec les détails du requester
        $pendingRequests = $playerMatch->requests()
            ->where('status', 'pending')
            ->with('requester')
            ->get();

        // Calculer le ratio de victoire pour chaque requester
        foreach ($pendingRequests as $request) {
            $requesterId = $request->requester_id;
            
            $totalMatches = PlayerMatch::where('status', 'completed')
                ->where(function ($query) use ($requesterId) {
                    $query->where('creator_id', $requesterId)
                          ->orWhere('opponent_id', $requesterId);
                })
                ->count();
            
            $wins = PlayerMatch::where('status', 'completed')
                ->where(function ($query) use ($requesterId) {
                    $query->where(function ($q) use ($requesterId) {
                        $q->where('creator_id', $requesterId)
                          ->where('creator_score', '>', DB::raw('opponent_score'));
                    })->orWhere(function ($q) use ($requesterId) {
                        $q->where('opponent_id', $requesterId)
                          ->where('opponent_score', '>', DB::raw('creator_score'));
                    });
                })
                ->count();

            $request->requester->win_ratio = $totalMatches > 0 ? round(($wins / $totalMatches) * 100, 1) : 0;
            $request->requester->total_matches = $totalMatches;
        }

        return view('player-match-requests.index', compact('playerMatch', 'pendingRequests'));
    }

    public function create(PlayerMatch $playerMatch)
    {
        if (Auth::id() === $playerMatch->creator_id) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas répondre à votre propre match');
        }

        $factions = Faction::orderBy('name')
            ->get()
            ->filter(function ($faction) {
                // Utiliser name_fr si non-vide, sinon name
                $name = !empty(trim($faction->name_fr)) ? $faction->name_fr : $faction->name;
                return !empty(trim($name));
            })
            ->values();
        return view('player-match-requests.create', compact('playerMatch', 'factions'));
    }

    public function store(Request $request, PlayerMatch $playerMatch)
    {
        if (Auth::id() === $playerMatch->creator_id) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas répondre à votre propre match');
        }

        $validated = $request->validate([
            'faction' => 'required|string|max:255',
            'detachment' => 'required|string|max:255',
            'message' => 'nullable|string|max:1000',
        ]);

        $request = PlayerMatchRequest::updateOrCreate(
            [
                'player_match_id' => $playerMatch->id,
                'requester_id' => Auth::id(),
            ],
            [
                'faction' => $validated['faction'],
                'detachment' => $validated['detachment'],
                'message' => $validated['message'],
                'status' => 'pending',
            ]
        );

        // Envoyer une notification au créateur du match
        $playerMatch->creator->notify(new PlayerMatchRequestCreated($playerMatch, $request, Auth::user()));

        return redirect()->route('player-matches.show', $playerMatch)
            ->with('success', 'Votre demande a été envoyée au créateur du match');
    }

    public function accept(PlayerMatchRequest $request)
    {
        $request->load('playerMatch');
        $match = $request->playerMatch;

        if (!$match) {
            return redirect()->back()->with('error', 'Match non trouvé');
        }

        if (Auth::id() !== $match->creator_id) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé');
        }

        $request->update(['status' => 'accepted']);
        $match->update([
            'opponent_id' => $request->requester_id,
            'status' => 'confirmed',
        ]);

        return redirect()->route('player-matches.show', $match)
            ->with('success', 'Demande acceptée ! Le match est maintenant confirmé.');
    }

    public function reject(Request $httpRequest, PlayerMatchRequest $matchRequest)
    {
        $matchRequest->load('playerMatch');
        $match = $matchRequest->playerMatch;

        if (!$match) {
            return redirect()->back()->with('error', 'Match non trouvé');
        }

        if (Auth::id() !== $match->creator_id) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé');
        }

        $validated = $httpRequest->validate([
            'creator_response' => 'nullable|string|max:500',
        ]);

        $matchRequest->update([
            'status' => 'rejected',
            'creator_response' => $validated['creator_response'],
        ]);

        // Envoyer une notification au demandeur
        $matchRequest->requester->notify(new PlayerMatchRequestRejected($match, $matchRequest, Auth::user()));

        return redirect()->route('player-matches.show', $match)
            ->with('success', 'Demande refusée.');
    }
}
