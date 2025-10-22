<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\MatchAvailability;
use App\Notifications\MatchAvailabilityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MatchAvailabilityController extends Controller
{
    /**
     * Enregistrer une nouvelle disponibilité
     */
    public function store(Request $request, Tournament $tournament, TournamentMatch $match)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est un joueur du match
        if (!$match->isPlayer($user)) {
            abort(403, 'Vous n\'êtes pas un joueur de ce match.');
        }

        // Vérifier que le match n'est pas terminé
        if ($match->status === 'completed') {
            return back()->with('error', 'Vous ne pouvez pas définir de disponibilité pour un match terminé.');
        }

        // Validation
        $validated = $request->validate([
            'type' => 'required|in:single,period',
            'available_at' => 'required_if:type,single|nullable|date|after:now',
            'available_from' => 'required_if:type,period|nullable|date|after:now',
            'available_to' => 'required_if:type,period|nullable|date|after:available_from',
            'notes' => 'nullable|string|max:500',
        ]);

        // Supprimer les anciennes disponibilités de l'utilisateur pour ce match
        $match->availabilities()->where('user_id', $user->id)->delete();

        // Créer la nouvelle disponibilité
        $availability = $match->availabilities()->create([
            'user_id' => $user->id,
            'type' => $validated['type'],
            'available_at' => $validated['type'] === 'single' ? $validated['available_at'] : null,
            'available_from' => $validated['type'] === 'period' ? $validated['available_from'] : null,
            'available_to' => $validated['type'] === 'period' ? $validated['available_to'] : null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Notifier l'adversaire
        $opponent = $match->getOpponent($user);
        if ($opponent) {
            $opponent->notify(new MatchAvailabilityNotification($availability, $match, $user));
            $availability->update([
                'opponent_notified' => true,
                'notified_at' => now(),
            ]);
        }

        return back()->with('success', 'Votre disponibilité a été enregistrée et votre adversaire a été notifié par email.');
    }

    /**
     * Supprimer une disponibilité
     */
    public function destroy(Tournament $tournament, TournamentMatch $match, MatchAvailability $availability)
    {
        $user = Auth::user();

        // Vérifier que c'est bien la disponibilité de l'utilisateur
        if ($availability->user_id !== $user->id) {
            abort(403, 'Vous ne pouvez pas supprimer cette disponibilité.');
        }

        $availability->delete();

        return back()->with('success', 'Votre disponibilité a été supprimée.');
    }
}
