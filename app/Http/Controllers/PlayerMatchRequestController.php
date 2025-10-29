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

class PlayerMatchRequestController extends Controller
{
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
