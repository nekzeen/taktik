<?php

namespace App\Http\Controllers;

use App\Models\PlayerMatch;
use App\Models\PlayerMatchInvitation;
use App\Models\User;
use App\Notifications\PlayerMatchInvitationCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class PlayerMatchInvitationController extends Controller
{
    public function autocompleteUsers(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 3) {
            return response()->json([]);
        }

        $qLower = mb_strtolower($q);
        $limit = 8;

        $users = User::query()
            ->where('id', '!=', Auth::id())
            ->where(function ($query) use ($qLower) {
                $query
                    ->whereRaw('LOWER(name) LIKE ?', [$qLower . '%'])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$qLower . '%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%' . $qLower . '%'])
                    ->orWhereRaw('LOWER(email) LIKE ?', ['%' . $qLower . '%']);
            })
            ->orderByRaw('CASE WHEN LOWER(name) LIKE ? THEN 0 WHEN LOWER(email) LIKE ? THEN 1 ELSE 2 END', [$qLower . '%', $qLower . '%'])
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'email']);

        return response()->json(
            $users->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'label' => $u->name . ' (' . $u->email . ')',
                'value' => $u->email,
            ])->values()
        );
    }

    public function index(PlayerMatch $playerMatch)
    {
        if (Auth::id() !== $playerMatch->creator_id) {
            abort(403, 'Non autorisé');
        }

        if ($playerMatch->status !== 'open') {
            return redirect()->route('player-matches.index')
                ->with('error', 'Vous ne pouvez gérer les invitations que lorsque le match est ouvert.');
        }

        $invitations = $playerMatch->invitations()
            ->with(['invitedUser', 'invitedBy'])
            ->latest()
            ->get();

        return view('player-matches.invitations', compact('playerMatch', 'invitations'));
    }

    public function store(Request $request, PlayerMatch $playerMatch)
    {
        if (Auth::id() !== $playerMatch->creator_id) {
            abort(403, 'Non autorisé');
        }

        if ($playerMatch->status !== 'open') {
            return redirect()->back()->with('error', 'Vous ne pouvez inviter des joueurs que lorsque le match est ouvert.');
        }

        if ($playerMatch->opponent_id !== null) {
            return redirect()->back()->with('error', 'Ce match a déjà un adversaire.');
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

        if ($invitedUser->id === $playerMatch->creator_id) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas vous inviter vous-même.');
        }

        try {
            DB::transaction(function () use ($playerMatch, $invitedUser, $validated) {
                $invitation = PlayerMatchInvitation::updateOrCreate(
                    [
                        'player_match_id' => $playerMatch->id,
                        'invited_user_id' => $invitedUser->id,
                    ],
                    [
                        'invited_by_id' => Auth::id(),
                        'status' => 'pending',
                        'message' => $validated['message'] ?? null,
                    ]
                );

                // Envoi obligatoire: si l'envoi échoue, la transaction rollback et l'invitation n'est pas enregistrée.
                $invitedUser->notify(new PlayerMatchInvitationCreated($playerMatch, $invitation, Auth::user()));
            });
        } catch (Throwable $e) {
            report($e);

            return redirect()->back()->with('error', 'Impossible d\'envoyer l\'invitation par email. Veuillez réessayer.');
        }

        return redirect()->route('player-matches.show', $playerMatch)
            ->with('success', 'Invitation envoyée par email.');
    }
}
