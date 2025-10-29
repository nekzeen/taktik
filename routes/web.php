<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\TournamentMatchController;
use App\Http\Controllers\MatchAvailabilityController;
use App\Http\Controllers\PlayerAvailabilityController;
use App\Http\Controllers\PlayerMatchController;
use App\Http\Controllers\PlayerMatchRequestController;
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
Route::get('/player-matches', [PlayerMatchController::class, 'index'])->name('player-matches.index');
Route::view('/privacy-policy', 'legal.privacy-policy')->name('privacy-policy');
Route::view('/legal-notice', 'legal.legal-notice')->name('legal-notice');

// Auth routes
require __DIR__.'/auth.php';

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
    Route::post('/player-matches/{playerMatch}/set-score', [PlayerMatchController::class, 'setScore'])->name('player-matches.set-score');
    Route::post('/player-matches/{playerMatch}/cancel', [PlayerMatchController::class, 'cancel'])->name('player-matches.cancel');
    Route::delete('/player-matches/{playerMatch}', [PlayerMatchController::class, 'destroy'])->name('player-matches.destroy');

    // Player match requests
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
