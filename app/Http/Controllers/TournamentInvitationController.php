<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentInvitation;
use App\Models\User;
use App\Notifications\TournamentInvitationCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Throwable;

class TournamentInvitationController extends Controller
{
    public function store(Request $request, Tournament $tournament)
    {
        Gate::authorize('update', $tournament);

        if (!in_array($tournament->status, ['open', 'registration_open'], true)) {
            return redirect()->back()->with('error', 'Vous ne pouvez inviter des joueurs que lorsque les inscriptions sont ouvertes.');
        }

        $validated = $request->validate([
            'invited_user' => 'required|string|max:255',
            'message' => 'nullable|string|max:500',
        ]);

        $query = trim($validated['invited_user']);

        $invitedUser = null;
        if (str_contains($query, '@')) {
            $invitedUser = User::where('email', $query)->first();
        } else {
            $invitedUser = User::whereRaw('LOWER(name) = ?', [mb_strtolower($query)])->first();
        }

        if (!$invitedUser) {
            return redirect()->back()->with('error', 'Utilisateur introuvable. Essayez avec son email ou son nom exact.');
        }

        if ($invitedUser->id === Auth::id()) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas vous inviter vous-même.');
        }

        $alreadyRegistered = $tournament->armyLists()->where('user_id', $invitedUser->id)->exists();
        if ($alreadyRegistered) {
            return redirect()->back()->with('error', 'Ce joueur est déjà inscrit à ce tournoi.');
        }

        try {
            DB::transaction(function () use ($tournament, $invitedUser, $validated) {
                $invitation = TournamentInvitation::updateOrCreate(
                    [
                        'tournament_id' => $tournament->id,
                        'invited_user_id' => $invitedUser->id,
                    ],
                    [
                        'invited_by_id' => Auth::id(),
                        'status' => 'pending',
                        'message' => $validated['message'] ?? null,
                    ]
                );

                // Envoi obligatoire: si l'envoi échoue, la transaction rollback et l'invitation n'est pas enregistrée.
                $invitedUser->notify(new TournamentInvitationCreated($tournament, $invitation, Auth::user()));
            });
        } catch (Throwable $e) {
            report($e);
            return redirect()->back()->with('error', 'Impossible d\'envoyer l\'invitation par email. Veuillez réessayer.');
        }

        return redirect()->back()->with('success', 'Invitation envoyée par email.');
    }
}
