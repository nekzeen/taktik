<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\PlayerAvailability;
use App\Notifications\PlayerAvailabilityCreatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlayerAvailabilityController extends Controller
{
    /**
     * Enregistrer ou mettre à jour la disponibilité globale d'un joueur
     */
    public function store(Request $request, Tournament $tournament)
    {
        $user = Auth::user();

        // Validation
        $validated = $request->validate([
            'type' => 'required|in:single,period',
            'available_at' => 'required_if:type,single|nullable|date|after:now',
            'available_from' => 'required_if:type,period|nullable|date|after:now',
            'available_to' => 'required_if:type,period|nullable|date|after:available_from',
            'notes' => 'nullable|string|max:500',
        ]);

        // Créer ou mettre à jour la disponibilité (upsert)
        $availability = PlayerAvailability::updateOrCreate(
            [
                'tournament_id' => $tournament->id,
                'user_id' => $user->id,
            ],
            [
                'type' => $validated['type'],
                'available_at' => $validated['type'] === 'single' ? $validated['available_at'] : null,
                'available_from' => $validated['type'] === 'period' ? $validated['available_from'] : null,
                'available_to' => $validated['type'] === 'period' ? $validated['available_to'] : null,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        // Notifier uniquement à la création (première définition de disponibilité)
        if ($availability->wasRecentlyCreated) {
            $matches = $tournament->tournamentMatches()
                ->where('status', '!=', 'completed')
                ->whereNotNull('player1_id')
                ->whereNotNull('player2_id')
                ->where(function ($q) use ($user) {
                    $q->where('player1_id', $user->id)
                        ->orWhere('player2_id', $user->id);
                })
                ->with(['player1', 'player2'])
                ->get();

            $opponents = $matches
                ->map(function ($match) use ($user) {
                    if ($match->player1_id === $user->id) {
                        return $match->player2;
                    }
                    if ($match->player2_id === $user->id) {
                        return $match->player1;
                    }
                    return null;
                })
                ->filter()
                ->filter(fn($opponent) => $opponent->id !== $user->id)
                ->unique('id')
                ->values();

            foreach ($opponents as $opponent) {
                $opponent->notify(new PlayerAvailabilityCreatedNotification($availability, $tournament, $user));
            }
        }

        return back()->with('success', 'Votre disponibilité a été enregistrée pour tous vos matchs de ce tournoi.');
    }

    /**
     * Supprimer la disponibilité globale d'un joueur
     */
    public function destroy(Tournament $tournament)
    {
        $user = Auth::user();

        PlayerAvailability::where('tournament_id', $tournament->id)
            ->where('user_id', $user->id)
            ->delete();

        return back()->with('success', 'Votre disponibilité a été supprimée.');
    }

    /**
     * Afficher l'agenda général de toutes les disponibilités du tournoi
     */
    public function calendar(Tournament $tournament)
    {
        // Récupérer toutes les disponibilités actives du tournoi
        $availabilities = PlayerAvailability::forTournament($tournament->id)
            ->active()
            ->with('user')
            ->orderBy('available_at')
            ->orderBy('available_from')
            ->get();

        // Grouper par type pour un affichage organisé
        $singleAvailabilities = $availabilities->where('type', 'single')->sortBy('available_at');
        $periodAvailabilities = $availabilities->where('type', 'period')->sortBy('available_from');

        return view('tournaments.availability-calendar', [
            'tournament' => $tournament,
            'singleAvailabilities' => $singleAvailabilities,
            'periodAvailabilities' => $periodAvailabilities,
            'allAvailabilities' => $availabilities
        ]);
    }
}
