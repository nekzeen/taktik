<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\TournamentMatchController;
use App\Http\Controllers\MatchAvailabilityController;
use App\Http\Controllers\PlayerAvailabilityController;
use App\Http\Controllers\PlayerMatchController;
use App\Http\Controllers\PlayerMatchRequestController;
use App\Http\Controllers\MatchSetupController;
use App\Http\Controllers\SpectatorMatchController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tournaments', [TournamentController::class, 'index'])->name('tournaments.index');
Route::get('/tournaments/create', [TournamentController::class, 'create'])->name('tournaments.create')->middleware(['auth', 'verified']);
Route::post('/tournaments', [TournamentController::class, 'store'])->name('tournaments.store')->middleware(['auth', 'verified']);
Route::get('/tournaments/{tournament}', [TournamentController::class, 'show'])->name('tournaments.show');
Route::get('/tournaments/{tournament}/matches', [TournamentMatchController::class, 'index'])->name('tournaments.matches.index');
Route::get('/tournaments/{tournament}/matches/{match}', [TournamentMatchController::class, 'show'])->name('tournaments.matches.show');
Route::get('/tournaments/{tournament}/availability-calendar', [PlayerAvailabilityController::class, 'calendar'])->name('tournaments.availability-calendar');
Route::get('/tournaments/{tournament}/army-list/{armyList}/view', [TournamentController::class, 'viewArmyListPdfPublic'])->name('tournaments.army-list.view');
Route::get('/tournaments/{tournament}/army-list/{armyList}/download', [TournamentController::class, 'downloadArmyListPdf'])->name('tournaments.army-list.download');
Route::get('/rankings', [HomeController::class, 'rankings'])->name('rankings');
Route::view('/privacy-policy', 'legal.privacy-policy')->name('privacy-policy');
Route::view('/legal-notice', 'legal.legal-notice')->name('legal-notice');
Route::get('/player-matches', [PlayerMatchController::class, 'index'])->name('player-matches.index');

// Auth routes
require __DIR__.'/auth.php';

// API routes publiques pour la sauvegarde des brouillons
Route::post('/api/player-matches/{playerMatch}/save-draft-scores', [PlayerMatchController::class, 'saveDraftScores'])->name('api.player-matches.save-draft-scores');
Route::get('/api/player-matches/{playerMatch}/get-draft-scores', [PlayerMatchController::class, 'getDraftScores'])->name('api.player-matches.get-draft-scores');

// API routes pour la sauvegarde des missions secondaires
Route::post('/api/player-matches/{playerMatch}/save-tactical-state/{side}', [PlayerMatchController::class, 'saveTacticalState'])->name('api.player-matches.save-tactical-state');
Route::get('/api/player-matches/{playerMatch}/get-tactical-state/{side}', [PlayerMatchController::class, 'getTacticalState'])->name('api.player-matches.get-tactical-state');

// API route pour vérifier l'état de validation
Route::get('/api/player-matches/{playerMatch}/validation-status', [PlayerMatchController::class, 'getValidationStatus'])->name('api.player-matches.validation-status');

// API route pour maintenir la session active (keep-alive)
Route::get('/api/keep-alive', function () {
    return response()->json(['status' => 'ok']);
})->name('api.keep-alive');

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tournament management
    Route::get('/tournaments/{tournament}/edit', [TournamentController::class, 'edit'])->name('tournaments.edit');
    Route::put('/tournaments/{tournament}', [TournamentController::class, 'update'])->name('tournaments.update');
    Route::get('/tournaments/{tournament}/register', [TournamentController::class, 'showRegistrationForm'])->name('tournaments.register.form');
    Route::post('/tournaments/{tournament}/register', [TournamentController::class, 'register'])->name('tournaments.register');
    Route::delete('/tournaments/{tournament}/unregister', [TournamentController::class, 'unregister'])->name('tournaments.unregister');
    Route::patch('/tournaments/{tournament}/close', [TournamentController::class, 'close'])->name('tournaments.close');
    Route::delete('/tournaments/{tournament}', [TournamentController::class, 'destroy'])->name('tournaments.destroy');
    
    // Tournament army list management
    Route::get('/tournaments/{tournament}/army-list/{armyList}/edit', [TournamentController::class, 'editArmyList'])->name('tournaments.army-list.edit');
    Route::put('/tournaments/{tournament}/army-list/{armyList}', [TournamentController::class, 'updateArmyList'])->name('tournaments.army-list.update');
    
    // Tournament registration requests management
    Route::get('/tournaments/{tournament}/registrations', [TournamentController::class, 'manageRegistrations'])->name('tournaments.registrations.manage');
    Route::post('/tournaments/{tournament}/army-list/{armyList}/validate', [TournamentController::class, 'validateArmyList'])->name('tournaments.army-list.validate');
    Route::post('/tournaments/{tournament}/army-list/{armyList}/reject', [TournamentController::class, 'rejectArmyList'])->name('tournaments.army-list.reject');
    Route::get('/tournaments/{tournament}/army-list/{armyList}/pdf', [TournamentController::class, 'viewArmyListPdf'])->name('tournaments.army-list.pdf');
    
    // Tournament matches generation
    Route::post('/tournaments/{tournament}/generate-matches', [TournamentController::class, 'generateMatches'])->name('tournaments.generate-matches');

    // Tournament matches - Edit results
    Route::get('/tournaments/{tournament}/matches/{match}/edit', [TournamentMatchController::class, 'edit'])->name('tournaments.matches.edit');
    Route::put('/tournaments/{tournament}/matches/{match}', [TournamentMatchController::class, 'update'])->name('tournaments.matches.update');

    // Tournament matches - Score recorder selection and score entry
    Route::post('/tournaments/{tournament}/matches/{match}/select-score-recorder', [TournamentMatchController::class, 'selectScoreRecorder'])->name('tournaments.matches.select-score-recorder');
    Route::get('/tournaments/{tournament}/matches/{match}/score', [TournamentMatchController::class, 'scoreForm'])->name('tournaments.matches.score');
    Route::get('/tournaments/{tournament}/matches/{match}/score/player1', [TournamentMatchController::class, 'scoreFormPlayer1'])->name('tournaments.matches.score-player1');
    Route::get('/tournaments/{tournament}/matches/{match}/score/player2', [TournamentMatchController::class, 'scoreFormPlayer2'])->name('tournaments.matches.score-player2');
    Route::get('/tournaments/{tournament}/matches/{match}/view-score', [TournamentMatchController::class, 'scoreView'])->name('tournaments.matches.view-score');
    Route::post('/tournaments/{tournament}/matches/{match}/store-score', [TournamentMatchController::class, 'storeScore'])->name('tournaments.matches.store-score');

    // Match setup
    Route::get('/tournaments/{tournament}/matches/{match}/setup', [MatchSetupController::class, 'showTournamentMatch'])->name('tournaments.matches.setup');
    Route::post('/tournaments/{tournament}/matches/{match}/randomize', [MatchSetupController::class, 'randomizeTournamentMatch'])->name('tournaments.matches.randomize');
    Route::post('/tournaments/{tournament}/matches/{match}/setup', [MatchSetupController::class, 'updateTournamentMatch'])->name('tournaments.matches.setup.update');
    Route::get('/tournaments/{tournament}/matches/{match}/summary', [MatchSetupController::class, 'showTournamentSummary'])->name('tournaments.matches.summary');
    Route::post('/tournaments/{tournament}/matches/{match}/reset', [MatchSetupController::class, 'resetTournamentMatch'])->name('tournaments.matches.reset');

    // Match availabilities (deprecated - kept for compatibility)
    Route::post('/tournaments/{tournament}/matches/{match}/availability', [MatchAvailabilityController::class, 'store'])->name('tournaments.matches.availability.store');
    Route::delete('/tournaments/{tournament}/matches/{match}/availability/{availability}', [MatchAvailabilityController::class, 'destroy'])->name('tournaments.matches.availability.destroy');

    // Player availabilities (global for tournament)
    Route::post('/tournaments/{tournament}/availability', [PlayerAvailabilityController::class, 'store'])->name('tournaments.player-availability.store');
    Route::delete('/tournaments/{tournament}/availability', [PlayerAvailabilityController::class, 'destroy'])->name('tournaments.player-availability.destroy');

    // Player matches
    Route::get('/player-matches/create', [PlayerMatchController::class, 'create'])->name('player-matches.create');
    Route::post('/player-matches', [PlayerMatchController::class, 'store'])->name('player-matches.store');
    Route::get('/player-matches/{playerMatch}', [PlayerMatchController::class, 'show'])->name('player-matches.show');
    Route::get('/player-matches/{playerMatch}/edit', [PlayerMatchController::class, 'edit'])->name('player-matches.edit');
    Route::put('/player-matches/{playerMatch}', [PlayerMatchController::class, 'update'])->name('player-matches.update');
    Route::post('/player-matches/{playerMatch}/join', [PlayerMatchController::class, 'join'])->name('player-matches.join');
    Route::get('/player-matches/{playerMatch}/edit-score', [PlayerMatchController::class, 'editScore'])->name('player-matches.edit-score');
    Route::get('/player-matches/{playerMatch}/score', [PlayerMatchController::class, 'testScore'])->name('player-matches.score');
    Route::get('/player-matches/{playerMatch}/score/creator', [PlayerMatchController::class, 'scoreFormCreator'])->name('player-matches.score-creator');
    Route::get('/player-matches/{playerMatch}/score/opponent', [PlayerMatchController::class, 'scoreFormOpponent'])->name('player-matches.score-opponent');
    Route::get('/player-matches/{playerMatch}/view-score', [PlayerMatchController::class, 'viewScore'])->name('player-matches.view-score');
    Route::post('/player-matches/{playerMatch}/set-score', [PlayerMatchController::class, 'setScore'])->name('player-matches.set-score');
    Route::post('/player-matches/{playerMatch}/validate-opponent-score', [PlayerMatchController::class, 'validateOpponentScore'])->name('player-matches.validate-opponent-score');
    Route::post('/player-matches/{playerMatch}/reject-score-validation', [PlayerMatchController::class, 'rejectScoreValidation'])->name('player-matches.reject-score-validation');
    Route::post('/player-matches/{playerMatch}/cancel', [PlayerMatchController::class, 'cancel'])->name('player-matches.cancel');
    Route::delete('/player-matches/{playerMatch}', [PlayerMatchController::class, 'destroy'])->name('player-matches.destroy');

    // Player match setup
    Route::get('/player-matches/{playerMatch}/setup', [MatchSetupController::class, 'showPlayerMatch'])->name('player-matches.setup');
    Route::post('/player-matches/{playerMatch}/randomize', [MatchSetupController::class, 'randomizePlayerMatch'])->name('player-matches.randomize');
    Route::post('/player-matches/{playerMatch}/setup', [MatchSetupController::class, 'updatePlayerMatch'])->name('player-matches.setup.update');
    Route::get('/player-matches/{playerMatch}/summary', [MatchSetupController::class, 'showPlayerSummary'])->name('player-matches.summary');
    Route::post('/player-matches/{playerMatch}/validate-setup', [MatchSetupController::class, 'validatePlayerSetup'])->name('player-matches.validate-setup');
    Route::post('/player-matches/{playerMatch}/reset', [MatchSetupController::class, 'resetPlayerMatch'])->name('player-matches.reset');

    // Player match requests
    Route::get('/player-matches/{playerMatch}/requests', [PlayerMatchRequestController::class, 'index'])->name('player-match-requests.index');
    Route::get('/player-matches/{playerMatch}/request', [PlayerMatchRequestController::class, 'create'])->name('player-match-requests.create');
    Route::post('/player-matches/{playerMatch}/request', [PlayerMatchRequestController::class, 'store'])->name('player-match-requests.store');
    Route::post('/player-match-requests/{playerMatchRequest}/accept', [PlayerMatchRequestController::class, 'accept'])->name('player-match-requests.accept');
    Route::post('/player-match-requests/{playerMatchRequest}/reject', [PlayerMatchRequestController::class, 'reject'])->name('player-match-requests.reject');

    // Dashboard redirect to Filament admin
    Route::get('/dashboard', function () {
        if (auth()->user()->hasRole(['super-admin', 'admin', 'moderator'])) {
            return redirect('/admin');
        }
        return redirect('/');
    })->name('dashboard');
});

// API routes for tournament matches - Draft scores (temporary data)
Route::middleware(['auth'])->group(function () {
    Route::get('/api/tournament-matches/{match}/get-draft-scores', function (\App\Models\TournamentMatch $match) {
        $draftScores = $match->draft_scores ?? [];
        return response()->json($draftScores);
    });

    Route::post('/api/tournament-matches/{match}/save-draft-scores', function (\Illuminate\Http\Request $request, \App\Models\TournamentMatch $match) {
        // Vérifier que l'utilisateur est l'un des deux joueurs
        if (Auth::id() !== $match->player1_id && Auth::id() !== $match->player2_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Sauvegarder dans draft_scores
        $match->update([
            'draft_scores' => $request->all(),
        ]);
        return response()->json(['success' => true]);
    });
});

// API routes for tournament matches - Tactical state (secondary missions)
Route::middleware(['auth'])->group(function () {
    Route::get('/api/tournament-matches/{match}/get-tactical-state/{player}', function (\App\Models\TournamentMatch $match, $player) {
        $column = $player === 'player1' ? 'draft_tactical_state_player1' : 'draft_tactical_state_player2';
        $tacticalState = $match->{$column} ?? [
            'active' => [],
            'discarded' => [],
            'completed' => [],
            'waitingReplacement' => []
        ];
        return response()->json($tacticalState);
    });

    Route::post('/api/tournament-matches/{match}/save-tactical-state/{player}', function (\Illuminate\Http\Request $request, \App\Models\TournamentMatch $match, $player) {
        $column = $player === 'player1' ? 'draft_tactical_state_player1' : 'draft_tactical_state_player2';
        $match->update([
            $column => $request->all(),
        ]);
        return response()->json(['success' => true]);
    });
});

// Routes for tournament match score validation
Route::middleware(['auth'])->group(function () {
    Route::post('/tournament-matches/{tournamentMatch}/set-score', 
        [TournamentMatchController::class, 'setScore']
    )->name('tournament-matches.set-score');
    
    Route::post('/tournament-matches/{tournamentMatch}/validate-opponent-score', 
        [TournamentMatchController::class, 'validateOpponentScore']
    )->name('tournament-matches.validate-opponent-score');
    
    Route::post('/tournament-matches/{tournamentMatch}/reject-score-validation', 
        [TournamentMatchController::class, 'rejectScoreValidation']
    )->name('tournament-matches.reject-score-validation');
    
    Route::get('/api/tournament-matches/{tournamentMatch}/validation-status', 
        [TournamentMatchController::class, 'getValidationStatus']
    );
});

// Routes spectateur - Matchs simples (publiques)
Route::get('/player-matches/{playerMatch}/spectate', 
    [SpectatorMatchController::class, 'showPlayerMatch'])
    ->name('player-matches.spectate');

Route::get('/api/player-matches/{playerMatch}/spectator-scores', 
    [SpectatorMatchController::class, 'getPlayerMatchScores'])
    ->name('api.player-matches.spectator-scores');

// Routes spectateur - Matchs tournoi (publiques)
Route::get('/tournaments/{tournament}/matches/{match}/spectate', 
    [SpectatorMatchController::class, 'showTournamentMatch'])
    ->name('tournaments.matches.spectate');

Route::get('/api/tournaments/{tournament}/matches/{match}/spectator-scores', 
    [SpectatorMatchController::class, 'getTournamentMatchScores'])
    ->name('api.tournaments.matches.spectator-scores');

// Webhooks (sans authentification, protégés par token)
Route::post('/webhooks/missions-update', [WebhookController::class, 'updateMissions'])->name('webhooks.missions-update');
Route::post('/webhooks/missions-validate', [WebhookController::class, 'validateMissions'])->name('webhooks.missions-validate');
