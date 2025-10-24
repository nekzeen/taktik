<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TournamentController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        
        $tournaments = Tournament::with(['armyLists', 'creator'])
            ->where('status', '!=', 'draft')
            ->orderByRaw('CASE WHEN created_by = ? THEN 0 ELSE 1 END, start_date DESC', [$userId])
            ->paginate(12);

        $canCreateTournament = auth()->check() ? auth()->user()->can('create', Tournament::class) : false;

        return view('tournaments.index', compact('tournaments', 'canCreateTournament'));
    }

    public function create()
    {
        Gate::authorize('create', Tournament::class);
        return view('tournaments.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Tournament::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'format' => 'required|in:elimination,swiss,league',
            'army_size' => 'required|in:incursion,strike_force,onslaught',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'registration_deadline' => 'nullable|date_format:Y-m-d H:i',
            'max_players' => 'nullable|integer|min:2',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'open';

        $tournament = Tournament::create($validated);

        return redirect()->route('tournaments.show', $tournament)
            ->with('success', 'Tournoi créé avec succès !');
    }

    public function show(Tournament $tournament)
    {
        // Charger uniquement les listes d'armée non rejetées
        $tournament->load([
            'armyLists' => function ($query) {
                $query->where('status', '!=', 'rejected');
            },
            'armyLists.user',
            'armyLists.faction',
            'tournamentMatches'
        ]);
        $rankings = $this->calculateRankings($tournament);
        
        // Vérifier si l'utilisateur est inscrit au tournoi (et non rejeté)
        $userIsRegistered = false;
        if (auth()->check()) {
            $userIsRegistered = $tournament->armyLists()
                ->where('user_id', auth()->id())
                ->where('status', '!=', 'rejected')
                ->exists();
        }
        
        return view('tournaments.show', compact('tournament', 'rankings', 'userIsRegistered'));
    }

    public function edit(Tournament $tournament)
    {
        Gate::authorize('update', $tournament);
        return view('tournaments.edit', compact('tournament'));
    }

    public function update(Request $request, Tournament $tournament)
    {
        Gate::authorize('update', $tournament);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'format' => 'required|in:elimination,swiss,league',
            'army_size' => 'required|in:incursion,strike_force,onslaught',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'registration_deadline' => 'nullable|date_format:Y-m-d H:i',
            'max_players' => 'nullable|integer|min:2',
        ]);

        $tournament->update($validated);

        return redirect()->route('tournaments.show', $tournament)
            ->with('success', 'Le tournoi a été mis à jour avec succès !');
    }

    private function calculateRankings(Tournament $tournament)
    {
        $players = [];
        $armyLists = $tournament->armyLists()->where('status', 'validated')->with(['user', 'faction'])->get();

        foreach ($armyLists as $armyList) {
            $userId = $armyList->user_id;
            $players[$userId] = [
                'user' => $armyList->user,
                'faction' => $armyList->faction->name ?? 'Unknown',
                'points' => 0,
                'wins' => 0,
                'draws' => 0,
                'losses' => 0,
                'matches_played' => 0,
            ];
        }

        $matches = $tournament->tournamentMatches()
            ->where('status', '!=', 'pending')
            ->get();

        foreach ($matches as $match) {
            if (isset($players[$match->player1_id])) {
                $players[$match->player1_id]['matches_played']++;
                if ($match->winner_id === $match->player1_id) {
                    $players[$match->player1_id]['wins']++;
                    $players[$match->player1_id]['points'] += 3;
                } elseif ($match->is_draw) {
                    $players[$match->player1_id]['draws']++;
                    $players[$match->player1_id]['points'] += 1;
                } else {
                    $players[$match->player1_id]['losses']++;
                }
            }

            if (isset($players[$match->player2_id])) {
                $players[$match->player2_id]['matches_played']++;
                if ($match->winner_id === $match->player2_id) {
                    $players[$match->player2_id]['wins']++;
                    $players[$match->player2_id]['points'] += 3;
                } elseif ($match->is_draw) {
                    $players[$match->player2_id]['draws']++;
                    $players[$match->player2_id]['points'] += 1;
                } else {
                    $players[$match->player2_id]['losses']++;
                }
            }
        }

        usort($players, function($a, $b) {
            return $b['points'] - $a['points'];
        });

        return $players;
    }

    public function showRegistrationForm(Tournament $tournament)
    {
        if ($tournament->status !== 'open') {
            return redirect()->route('tournaments.show', $tournament)
                ->with('error', 'Les inscriptions sont fermées pour ce tournoi.');
        }

        if ($tournament->registration_deadline && $tournament->registration_deadline < now()) {
            return redirect()->route('tournaments.show', $tournament)
                ->with('error', 'La date limite d\'inscription est dépassée.');
        }

        $factions = \App\Models\Faction::with('detachments')
            ->whereHas('detachments')
            ->orderBy('name_fr')
            ->get();
        return view('tournaments.register', compact('tournament', 'factions'));
    }

    public function register(Request $request, Tournament $tournament)
    {
        if ($tournament->status !== 'open') {
            return back()->with('error', 'Les inscriptions sont fermées pour ce tournoi.');
        }

        if ($tournament->registration_deadline && $tournament->registration_deadline < now()) {
            return back()->with('error', 'La date limite d\'inscription est dépassée.');
        }

        $existingArmyList = $tournament->armyLists()
            ->where('user_id', auth()->id())
            ->first();

        if ($existingArmyList) {
            return back()->with('error', 'Vous êtes déjà inscrit à ce tournoi.');
        }

        $validated = $request->validate([
            'faction_id' => 'required|exists:factions,id',
            'detachment' => 'required|string|max:255',
            'pdf' => 'required|file|mimes:pdf|max:5120',
        ]);

        $pdfPath = $request->file('pdf')->store('army-lists', 'public');
        $pdfHash = hash_file('sha256', $request->file('pdf')->getRealPath());

        $armyList = $tournament->armyLists()->create([
            'user_id' => auth()->id(),
            'faction_id' => $validated['faction_id'],
            'detachment' => $validated['detachment'],
            'pdf_path' => $pdfPath,
            'pdf_size' => $request->file('pdf')->getSize(),
            'pdf_hash' => $pdfHash,
            'status' => 'pending',
        ]);

        return redirect()->route('tournaments.show', $tournament)
            ->with('success', 'Vous avez été inscrit au tournoi. Votre liste d\'armée est en attente de validation.');
    }

    public function editArmyList(Tournament $tournament, \App\Models\ArmyList $armyList)
    {
        if ($armyList->user_id !== auth()->id()) {
            abort(403);
        }

        if ($armyList->status === 'validated') {
            return redirect()->route('tournaments.show', $tournament)
                ->with('error', 'Vous ne pouvez pas modifier une liste validée.');
        }

        $factions = \App\Models\Faction::with('detachments')->orderBy('name_fr')->get();
        return view('tournaments.army-list.edit', compact('tournament', 'armyList', 'factions'));
    }

    public function updateArmyList(Request $request, Tournament $tournament, \App\Models\ArmyList $armyList)
    {
        if ($armyList->user_id !== auth()->id()) {
            abort(403);
        }

        if ($armyList->status === 'validated') {
            return back()->with('error', 'Vous ne pouvez pas modifier une liste validée.');
        }

        $validated = $request->validate([
            'faction_id' => 'required|exists:factions,id',
            'detachment' => 'required|string|max:255',
            'pdf' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        if ($request->hasFile('pdf')) {
            if ($armyList->pdf_path && \Storage::disk('public')->exists($armyList->pdf_path)) {
                \Storage::disk('public')->delete($armyList->pdf_path);
            }

            $pdfPath = $request->file('pdf')->store('army-lists', 'public');
            $pdfHash = hash_file('sha256', $request->file('pdf')->getRealPath());

            $armyList->update([
                'pdf_path' => $pdfPath,
                'pdf_size' => $request->file('pdf')->getSize(),
                'pdf_hash' => $pdfHash,
            ]);
        }

        $armyList->update([
            'faction_id' => $validated['faction_id'],
            'detachment' => $validated['detachment'],
        ]);

        return redirect()->route('tournaments.show', $tournament)
            ->with('success', 'Votre liste d\'armée a été mise à jour.');
    }

    public function unregister(Tournament $tournament)
    {
        $armyList = $tournament->armyLists()
            ->where('user_id', auth()->id())
            ->first();

        if (!$armyList) {
            return back()->with('error', 'Vous n\'êtes pas inscrit à ce tournoi.');
        }

        $hasPlayedMatches = TournamentMatch::where('tournament_id', $tournament->id)
            ->where(function ($query) {
                $query->where('player1_id', auth()->id())
                    ->orWhere('player2_id', auth()->id());
            })
            ->where('status', '!=', 'pending')
            ->exists();

        if ($hasPlayedMatches) {
            return back()->with('error', 'Vous ne pouvez pas vous désinscrire car vous avez déjà joué des matchs.');
        }

        if ($armyList->pdf_path && \Storage::disk('public')->exists($armyList->pdf_path)) {
            \Storage::disk('public')->delete($armyList->pdf_path);
        }

        $armyList->delete();

        return redirect()->route('tournaments.show', $tournament)
            ->with('success', 'Vous avez été désinscrit du tournoi avec succès.');
    }

    public function close(Tournament $tournament)
    {
        Gate::authorize('update', $tournament);

        $tournament->update(['status' => 'completed']);

        return redirect()->route('tournaments.show', $tournament)
            ->with('success', 'Le tournoi a été fermé avec succès.');
    }

    public function destroy(Tournament $tournament)
    {
        Gate::authorize('delete', $tournament);

        $tournament->delete();

        return redirect()->route('tournaments.index')
            ->with('success', 'Le tournoi a été supprimé avec succès.');
    }

    public function manageRegistrations(Tournament $tournament)
    {
        Gate::authorize('update', $tournament);

        $pendingArmyLists = $tournament->armyLists()
            ->where('status', 'pending')
            ->with(['user', 'faction'])
            ->get();

        $validatedArmyLists = $tournament->armyLists()
            ->where('status', 'validated')
            ->with(['user', 'faction'])
            ->get();

        $rejectedArmyLists = $tournament->armyLists()
            ->where('status', 'rejected')
            ->with(['user', 'faction'])
            ->get();

        return view('tournaments.manage-registrations', compact(
            'tournament',
            'pendingArmyLists',
            'validatedArmyLists',
            'rejectedArmyLists'
        ));
    }

    public function validateArmyList(Tournament $tournament, \App\Models\ArmyList $armyList)
    {
        Gate::authorize('update', $tournament);

        if ($armyList->tournament_id !== $tournament->id) {
            abort(404);
        }

        $armyList->update([
            'status' => 'validated',
            'validated_at' => now(),
            'validated_by' => auth()->id(),
        ]);

        // Régénérer les matchs automatiquement
        $generator = new \App\Services\TournamentMatchGenerator();
        $result = $generator->generateWithoutDeletingCompleted($tournament);

        // Envoyer une notification au joueur
        $armyList->user->notify(new \App\Notifications\ArmyListValidated($armyList, $tournament));

        return back()->with('success', 'La liste d\'armée de ' . $armyList->user->name . ' a été validée. ' . $result['message']);
    }

    public function rejectArmyList(Request $request, Tournament $tournament, \App\Models\ArmyList $armyList)
    {
        Gate::authorize('update', $tournament);

        if ($armyList->tournament_id !== $tournament->id) {
            abort(404);
        }

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $armyList->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', 'La liste d\'armée de ' . $armyList->user->name . ' a été rejetée.');
    }

    public function viewArmyListPdf(Tournament $tournament, \App\Models\ArmyList $armyList)
    {
        Gate::authorize('update', $tournament);

        if ($armyList->tournament_id !== $tournament->id) {
            abort(404);
        }

        if (!$armyList->pdf_path || !\Storage::disk('public')->exists($armyList->pdf_path)) {
            return back()->with('error', 'Le fichier PDF n\'existe pas.');
        }

        return \Storage::disk('public')->response($armyList->pdf_path);
    }

    public function viewArmyListPdfPublic(Tournament $tournament, \App\Models\ArmyList $armyList)
    {
        // Vérifier que l'utilisateur est connecté
        if (!auth()->check()) {
            abort(403);
        }

        // Vérifier que la liste d'armée appartient au tournoi
        if ($armyList->tournament_id !== $tournament->id) {
            abort(404);
        }

        // Vérifier que l'utilisateur est inscrit au tournoi
        $userArmyList = $tournament->armyLists()
            ->where('user_id', auth()->id())
            ->first();

        if (!$userArmyList) {
            abort(403);
        }

        // Vérifier que la liste d'armée est validée
        if ($armyList->status !== 'validated') {
            abort(403);
        }

        if (!$armyList->pdf_path || !\Storage::disk('public')->exists($armyList->pdf_path)) {
            abort(404);
        }

        return \Storage::disk('public')->response($armyList->pdf_path);
    }

    public function downloadArmyListPdf(Tournament $tournament, \App\Models\ArmyList $armyList)
    {
        // Vérifier que l'utilisateur est connecté
        if (!auth()->check()) {
            abort(403);
        }

        // Vérifier que la liste d'armée appartient au tournoi
        if ($armyList->tournament_id !== $tournament->id) {
            abort(404);
        }

        // Vérifier que l'utilisateur est inscrit au tournoi
        $userArmyList = $tournament->armyLists()
            ->where('user_id', auth()->id())
            ->first();

        if (!$userArmyList) {
            abort(403);
        }

        // Vérifier que la liste d'armée est validée
        if ($armyList->status !== 'validated') {
            abort(403);
        }

        if (!$armyList->pdf_path || !\Storage::disk('public')->exists($armyList->pdf_path)) {
            abort(404);
        }

        return \Storage::disk('public')->download($armyList->pdf_path, $armyList->user->name . ' - ' . ($armyList->faction->name ?? 'Liste') . '.pdf');
    }

    public function generateMatches(Tournament $tournament)
    {
        Gate::authorize('update', $tournament);

        $generator = new \App\Services\TournamentMatchGenerator();
        $result = $generator->generate($tournament);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        } else {
            return back()->with('error', $result['message']);
        }
    }
}
