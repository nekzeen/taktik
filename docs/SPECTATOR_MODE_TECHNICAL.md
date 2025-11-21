# 👁️ MODE SPECTATEUR - DÉTAILS TECHNIQUES

---

## 📋 TABLE DES MATIÈRES

1. [Contrôleur](#contrôleur)
2. [Routes](#routes)
3. [Vues](#vues)
4. [JavaScript](#javascript)
5. [Données JSON](#données-json)
6. [Sécurité](#sécurité)
7. [Checklist Implémentation](#checklist-implémentation)

---

## 🎮 CONTRÔLEUR

### Fichier : `app/Http/Controllers/SpectatorMatchController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\PlayerMatch;
use App\Models\TournamentMatch;
use App\Models\Tournament;
use Illuminate\Http\Request;

class SpectatorMatchController extends Controller
{
    /**
     * Affiche la page spectateur pour un match simple
     */
    public function showPlayerMatch(PlayerMatch $playerMatch)
    {
        // Vérifier que le match est visible
        if (!$this->isMatchVisible($playerMatch)) {
            abort(404, 'Ce match n\'est pas disponible en spectateur');
        }

        // Charger les relations
        $playerMatch->load([
            'creator',
            'opponent',
            'primaryMission',
            'secondaryMission',
            'twistMission',
            'terrainLayout',
            'asymmetricPrimaryMission'
        ]);

        return view('player-matches.spectate', [
            'match' => $playerMatch,
            'matchType' => 'player',
            'apiUrl' => route('api.player-matches.spectator-scores', $playerMatch)
        ]);
    }

    /**
     * Retourne les scores pour polling (API)
     */
    public function getPlayerMatchScores(PlayerMatch $playerMatch)
    {
        // Vérifier que le match est visible
        if (!$this->isMatchVisible($playerMatch)) {
            return response()->json(['error' => 'Match not found'], 404);
        }

        // Charger les scores
        $now = now();
        $delayUntil = $now->copy()->addSeconds(2);

        return response()->json([
            'creator_score' => $playerMatch->creator_score ?? 0,
            'opponent_score' => $playerMatch->opponent_score ?? 0,
            'creator_primary_points' => $playerMatch->creator_primary_points ?? 0,
            'creator_secondary_points' => $playerMatch->creator_secondary_points ?? 0,
            'creator_painting_points' => $playerMatch->creator_painting_points ?? false,
            'opponent_primary_points' => $playerMatch->opponent_primary_points ?? 0,
            'opponent_secondary_points' => $playerMatch->opponent_secondary_points ?? 0,
            'opponent_painting_points' => $playerMatch->opponent_painting_points ?? false,
            'draft_scores' => $playerMatch->draft_scores ?? [],
            'draft_tactical_state_creator' => $playerMatch->draft_tactical_state_creator ?? [],
            'draft_tactical_state_opponent' => $playerMatch->draft_tactical_state_opponent ?? [],
            'updated_at' => $now->toIso8601String(),
            'delay_until' => $delayUntil->toIso8601String()
        ]);
    }

    /**
     * Affiche la page spectateur pour un match de tournoi
     */
    public function showTournamentMatch(Tournament $tournament, TournamentMatch $match)
    {
        // Vérifier que le match appartient au tournoi
        if ($match->tournament_id !== $tournament->id) {
            abort(404, 'Ce match n\'appartient pas à ce tournoi');
        }

        // Vérifier que le match est visible
        if (!$this->isTournamentMatchVisible($match)) {
            abort(404, 'Ce match n\'est pas disponible en spectateur');
        }

        // Charger les relations
        $match->load([
            'player1',
            'player2',
            'primaryMission',
            'secondaryMission',
            'twistMission',
            'terrainLayout',
            'asymmetricPrimaryMission'
        ]);

        return view('tournaments.matches.spectate', [
            'tournament' => $tournament,
            'match' => $match,
            'matchType' => 'tournament',
            'apiUrl' => route('api.tournaments.matches.spectator-scores', [$tournament, $match])
        ]);
    }

    /**
     * Retourne les scores pour polling (API) - Tournoi
     */
    public function getTournamentMatchScores(Tournament $tournament, TournamentMatch $match)
    {
        // Vérifier que le match appartient au tournoi
        if ($match->tournament_id !== $tournament->id) {
            return response()->json(['error' => 'Match not found'], 404);
        }

        // Vérifier que le match est visible
        if (!$this->isTournamentMatchVisible($match)) {
            return response()->json(['error' => 'Match not found'], 404);
        }

        // Charger les scores
        $now = now();
        $delayUntil = $now->copy()->addSeconds(2);

        return response()->json([
            'player1_score' => $match->player1_score ?? 0,
            'player2_score' => $match->player2_score ?? 0,
            'player1_primary_points' => $match->player1_primary_points ?? 0,
            'player1_secondary_points' => $match->player1_secondary_points ?? 0,
            'player1_painting_points' => $match->player1_painting_points ?? false,
            'player2_primary_points' => $match->player2_primary_points ?? 0,
            'player2_secondary_points' => $match->player2_secondary_points ?? 0,
            'player2_painting_points' => $match->player2_painting_points ?? false,
            'draft_scores' => $match->draft_scores ?? [],
            'draft_tactical_state_player1' => $match->draft_tactical_state_player1 ?? [],
            'draft_tactical_state_player2' => $match->draft_tactical_state_player2 ?? [],
            'updated_at' => $now->toIso8601String(),
            'delay_until' => $delayUntil->toIso8601String()
        ]);
    }

    /**
     * Vérifie si un match simple est visible en spectateur
     */
    private function isMatchVisible(PlayerMatch $playerMatch): bool
    {
        // Visible si confirmé ou complété
        return in_array($playerMatch->status, ['confirmed', 'completed']);
    }

    /**
     * Vérifie si un match de tournoi est visible en spectateur
     */
    private function isTournamentMatchVisible(TournamentMatch $match): bool
    {
        // Visible si en cours ou complété
        return in_array($match->status, ['in_progress', 'completed']);
    }
}
```

---

## 🛣️ ROUTES

### Fichier : `routes/web.php`

Ajouter ces routes (publiques, pas d'authentification) :

```php
// Routes spectateur - Matchs simples
Route::get('/player-matches/{playerMatch}/spectate', 
    [SpectatorMatchController::class, 'showPlayerMatch'])
    ->name('player-matches.spectate');

Route::get('/api/player-matches/{playerMatch}/spectator-scores', 
    [SpectatorMatchController::class, 'getPlayerMatchScores'])
    ->name('api.player-matches.spectator-scores');

// Routes spectateur - Matchs tournoi
Route::get('/tournaments/{tournament}/matches/{match}/spectate', 
    [SpectatorMatchController::class, 'showTournamentMatch'])
    ->name('tournaments.matches.spectate');

Route::get('/api/tournaments/{tournament}/matches/{match}/spectator-scores', 
    [SpectatorMatchController::class, 'getTournamentMatchScores'])
    ->name('api.tournaments.matches.spectator-scores');
```

---

## 🎨 VUES

### Fichier 1 : `resources/views/player-matches/spectate.blade.php`

```blade
@extends('layouts.public')

@section('title', 'Mode Spectateur - Match Simple')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
        
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold flex items-center gap-2">
                        👁️ Mode Spectateur
                    </h1>
                    <p class="text-red-100 mt-2">
                        {{ $match->creator->name }} vs {{ $match->opponent->name }}
                    </p>
                </div>
                <a href="{{ route('player-matches.show', $match) }}" 
                   class="bg-white text-red-600 px-4 py-2 rounded-lg font-semibold hover:bg-red-50">
                    ← Retour
                </a>
            </div>
        </div>

        <!-- Scores en temps réel -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8" id="scores-container">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">🎯 Scores en Temps Réel</h2>
            <div class="grid grid-cols-2 gap-6">
                <!-- Créateur -->
                <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4">
                    <h3 class="font-bold text-red-900 mb-3">{{ $match->creator->name }}</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Total:</span>
                            <span class="font-bold text-lg" id="creator-total">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Primaire:</span>
                            <span id="creator-primary">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Secondaire:</span>
                            <span id="creator-secondary">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Peinture:</span>
                            <span id="creator-painting">-</span>
                        </div>
                    </div>
                </div>

                <!-- Adversaire -->
                <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4">
                    <h3 class="font-bold text-blue-900 mb-3">{{ $match->opponent->name }}</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Total:</span>
                            <span class="font-bold text-lg" id="opponent-total">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Primaire:</span>
                            <span id="opponent-primary">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Secondaire:</span>
                            <span id="opponent-secondary">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Peinture:</span>
                            <span id="opponent-painting">-</span>
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-4">
                ⏱️ Mise à jour automatique toutes les 2 secondes (décalage 2s)
            </p>
        </div>

        <!-- Missions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Mission Primaire -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">🎲 Mission Primaire</h2>
                @if($match->primaryMission)
                    <h3 class="font-bold text-red-600 mb-2">{{ $match->primaryMission->name_fr ?? $match->primaryMission->name }}</h3>
                    <p class="text-gray-700 text-sm mb-3">{{ $match->primaryMission->description_fr ?? $match->primaryMission->description }}</p>
                    <div class="bg-gray-50 p-3 rounded max-h-64 overflow-y-auto text-sm text-gray-600">
                        {!! nl2br(e($match->primaryMission->full_text_fr ?? $match->primaryMission->full_text)) !!}
                    </div>
                @else
                    <p class="text-gray-500">Aucune mission primaire</p>
                @endif
            </div>

            <!-- Péripétie -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">🌪️ Péripétie</h2>
                @if($match->twistMission)
                    <h3 class="font-bold text-purple-600 mb-2">{{ $match->twistMission->name_fr ?? $match->twistMission->name }}</h3>
                    <p class="text-gray-700 text-sm mb-3">{{ $match->twistMission->description_fr ?? $match->twistMission->description }}</p>
                    <div class="bg-gray-50 p-3 rounded max-h-64 overflow-y-auto text-sm text-gray-600">
                        {!! nl2br(e($match->twistMission->full_text_fr ?? $match->twistMission->full_text)) !!}
                    </div>
                @else
                    <p class="text-gray-500">Aucune péripétie</p>
                @endif
            </div>
        </div>

        <!-- Missions Secondaires -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">📋 Missions Secondaires</h2>
            @if($match->secondaryMission)
                <div class="space-y-4">
                    <div class="border-l-4 border-green-500 pl-4">
                        <h3 class="font-bold text-green-600">{{ $match->secondaryMission->name_fr ?? $match->secondaryMission->name }}</h3>
                        <p class="text-gray-700 text-sm mt-1">{{ $match->secondaryMission->description_fr ?? $match->secondaryMission->description }}</p>
                        <div class="bg-gray-50 p-3 rounded mt-2 max-h-48 overflow-y-auto text-sm text-gray-600">
                            {!! nl2br(e($match->secondaryMission->full_text_fr ?? $match->secondaryMission->full_text)) !!}
                        </div>
                    </div>
                </div>
                
                <!-- Missions Tactiques -->
                <div class="mt-6 pt-6 border-t-2 border-gray-200">
                    <h3 class="font-bold text-gray-900 mb-3">🎴 Missions Tactiques</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-blue-50 p-3 rounded">
                            <div class="text-sm text-gray-600">En main</div>
                            <div class="text-2xl font-bold text-blue-600" id="tactical-hand">0</div>
                        </div>
                        <div class="bg-yellow-50 p-3 rounded">
                            <div class="text-sm text-gray-600">Défaussées</div>
                            <div class="text-2xl font-bold text-yellow-600" id="tactical-discarded">0</div>
                        </div>
                        <div class="bg-green-50 p-3 rounded">
                            <div class="text-sm text-gray-600">Complétées</div>
                            <div class="text-2xl font-bold text-green-600" id="tactical-completed">0</div>
                        </div>
                    </div>
                </div>
            @else
                <p class="text-gray-500">Aucune mission secondaire</p>
            @endif
        </div>

        <!-- Déploiement et Terrain -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Déploiement -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">🎯 Déploiement</h2>
                <p class="text-lg font-bold text-gray-900 mb-3">
                    {{ ucfirst(str_replace('_', ' ', $match->deployment_mode ?? 'Normal')) }}
                </p>
                <div class="bg-gray-100 rounded h-48 flex items-center justify-center">
                    <p class="text-gray-500">Image de déploiement</p>
                </div>
            </div>

            <!-- Disposition Terrain -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">🗺️ Disposition Terrain</h2>
                @if($match->terrainLayout)
                    <p class="text-lg font-bold text-gray-900 mb-3">{{ $match->terrainLayout->name_fr ?? $match->terrainLayout->name }}</p>
                    @if($match->terrainLayout->image_url)
                        <img src="{{ $match->terrainLayout->image_url }}" 
                             alt="{{ $match->terrainLayout->name_fr ?? $match->terrainLayout->name }}"
                             class="w-full h-48 object-cover rounded">
                    @else
                        <div class="bg-gray-100 rounded h-48 flex items-center justify-center">
                            <p class="text-gray-500">Image non disponible</p>
                        </div>
                    @endif
                @else
                    <p class="text-gray-500">Aucune disposition terrain</p>
                @endif
            </div>
        </div>

    </div>
</div>

<script>
    const apiUrl = '{{ $apiUrl }}';
    let pollInterval;

    // Charger les scores au démarrage
    loadScores();

    // Polling toutes les 2 secondes
    pollInterval = setInterval(loadScores, 2000);

    async function loadScores() {
        try {
            const response = await fetch(apiUrl);
            const data = await response.json();

            if (data.error) {
                console.error('Erreur:', data.error);
                return;
            }

            // Calculer le délai
            const now = new Date();
            const delayUntil = new Date(data.delay_until);
            const wait = Math.max(0, delayUntil - now);

            // Attendre avant d'afficher
            setTimeout(() => {
                updateScores(data);
            }, wait);
        } catch (error) {
            console.error('Erreur lors du chargement des scores:', error);
        }
    }

    function updateScores(data) {
        // Scores créateur
        const creatorTotal = (data.creator_primary_points || 0) + 
                            (data.creator_secondary_points || 0) + 
                            (data.creator_painting_points ? 10 : 0);
        document.getElementById('creator-total').textContent = creatorTotal;
        document.getElementById('creator-primary').textContent = data.creator_primary_points || 0;
        document.getElementById('creator-secondary').textContent = data.creator_secondary_points || 0;
        document.getElementById('creator-painting').textContent = data.creator_painting_points ? '+10' : '-';

        // Scores adversaire
        const opponentTotal = (data.opponent_primary_points || 0) + 
                             (data.opponent_secondary_points || 0) + 
                             (data.opponent_painting_points ? 10 : 0);
        document.getElementById('opponent-total').textContent = opponentTotal;
        document.getElementById('opponent-primary').textContent = data.opponent_primary_points || 0;
        document.getElementById('opponent-secondary').textContent = data.opponent_secondary_points || 0;
        document.getElementById('opponent-painting').textContent = data.opponent_painting_points ? '+10' : '-';

        // Missions tactiques
        const tacticalCreator = data.draft_tactical_state_creator || {};
        document.getElementById('tactical-hand').textContent = (tacticalCreator.hand || []).length;
        document.getElementById('tactical-discarded').textContent = (tacticalCreator.discarded || []).length;
        document.getElementById('tactical-completed').textContent = (tacticalCreator.completed || []).length;
    }

    // Arrêter le polling si l'utilisateur quitte la page
    window.addEventListener('beforeunload', () => {
        clearInterval(pollInterval);
    });
</script>
@endsection
```

### Fichier 2 : `resources/views/tournaments/matches/spectate.blade.php`

Identique au fichier précédent, mais avec :
- "Joueur 1" et "Joueur 2" au lieu de "Créateur" et "Adversaire"
- `$match->player1` et `$match->player2` au lieu de `$match->creator` et `$match->opponent`
- `player1_*` et `player2_*` au lieu de `creator_*` et `opponent_*`

---

## 📱 JAVASCRIPT

Le JavaScript est inclus dans les vues (voir ci-dessus).

**Points clés** :
- Polling toutes les 2 secondes
- Décalage 2 secondes côté serveur
- Calcul automatique des totaux
- Gestion des missions tactiques

---

## 📊 DONNÉES JSON

### Réponse API

```json
{
    "creator_score": 45,
    "opponent_score": 38,
    "creator_primary_points": 25,
    "creator_secondary_points": 15,
    "creator_painting_points": true,
    "opponent_primary_points": 30,
    "opponent_secondary_points": 10,
    "opponent_painting_points": false,
    "draft_scores": {
        "creator_primary_points": "25",
        "creator_secondary_points": "15",
        "creator_painting_points": true,
        "opponent_primary_points": "30",
        "opponent_secondary_points": "10",
        "opponent_painting_points": false,
        "secondary_type": "fixed",
        "fixed_mission_1": 5,
        "fixed_mission_2": 8
    },
    "draft_tactical_state_creator": {
        "hand": ["BREAK_THROUGH", "ASSASSINATE"],
        "discarded": ["SECURE_OBJECTIVE"],
        "completed": []
    },
    "draft_tactical_state_opponent": {
        "hand": ["REPAIR_OBJECTIVE", "RETRIEVE_ARTEFACT", "ENGAGE_ON_ALL_FRONTS"],
        "discarded": ["BRING_IT_DOWN"],
        "completed": ["LINEBREAKER"]
    },
    "updated_at": "2025-11-21T14:30:00Z",
    "delay_until": "2025-11-21T14:30:02Z"
}
```

---

## 🔒 SÉCURITÉ

### Vérifications

1. **Match existe** : `abort(404)` si non trouvé
2. **Match visible** : Vérifier `status` ('confirmed', 'in_progress', 'completed')
3. **Pas d'authentification** : Routes publiques
4. **Données limitées** : Scores et missions uniquement

### Données Protégées

- ❌ Pas d'emails
- ❌ Pas de données personnelles
- ❌ Pas de listes d'armée
- ✅ Scores et missions uniquement

---

## 📝 CHECKLIST IMPLÉMENTATION

### Phase 1 : Matchs Simples
- [ ] Créer `SpectatorMatchController.php`
- [ ] Ajouter routes spectateur
- [ ] Créer vue `player-matches/spectate.blade.php`
- [ ] Tester accès public
- [ ] Tester polling 2 secondes
- [ ] Ajouter bouton "👁️ Spectate" à `player-matches/index.blade.php`

### Phase 2 : Matchs Tournoi
- [ ] Ajouter méthodes tournoi au contrôleur
- [ ] Ajouter routes tournoi
- [ ] Créer vue `tournaments/matches/spectate.blade.php`
- [ ] Tester accès public
- [ ] Tester polling 2 secondes
- [ ] Ajouter bouton "👁️ Spectate" aux cartes tournoi

### Phase 3 : Tests & Optimisations
- [ ] Tester avec plusieurs spectateurs
- [ ] Vérifier performance
- [ ] Vérifier sécurité
- [ ] Vérifier responsive design
- [ ] Compiler Tailwind CSS
- [ ] Vider cache

---

## 🚀 COMMANDES

```bash
# Compiler Tailwind CSS
npm run build

# Vider cache
php artisan cache:clear
php artisan view:cache

# Tester les routes
php artisan route:list | grep spectate
```

---

**Statut** : Détails techniques complets - Prêt pour implémentation
