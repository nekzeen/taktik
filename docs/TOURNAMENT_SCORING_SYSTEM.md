# Système de Scoring pour Matchs de Tournoi - Documentation Complète

## 📋 Table des matières
1. [Vue d'ensemble](#vue-densemble)
2. [Architecture](#architecture)
3. [Base de données](#base-de-données)
4. [Logique métier](#logique-métier)
5. [Flux utilisateur](#flux-utilisateur)
6. [API et endpoints](#api-et-endpoints)
7. [Frontend - Pages de scoring](#frontend---pages-de-scoring)
8. [Système de mise à jour en temps réel](#système-de-mise-à-jour-en-temps-réel)
9. [Sécurité et autorisation](#sécurité-et-autorisation)
10. [Guide d'implémentation pour matchs simples](#guide-dimplémentation-pour-matchs-simples)

---

## Vue d'ensemble

### Objectif
Permettre à chaque joueur d'un match de tournoi de saisir et modifier **uniquement son propre score**, avec une visualisation en temps réel des scores de l'adversaire.

### Principes clés
- **Séparation des responsabilités** : Chaque joueur a sa propre page de scoring
- **Temps réel** : Les scores se mettent à jour automatiquement toutes les 2 secondes
- **Sécurité** : Authentification et autorisation strictes
- **Sauvegarde temporaire** : Les scores sont sauvegardés en base de données (colonne `draft_scores`), pas en localStorage
- **Pas de conflit** : Le polling ne recharge que les scores de l'adversaire, pas ceux de l'utilisateur

---

## Architecture

### Composants principaux

```
┌─────────────────────────────────────────────────────────────┐
│                    Matchs de Tournoi                         │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌──────────────────┐  ┌──────────────────┐                 │
│  │   Player 1       │  │   Player 2       │                 │
│  │  /score/player1  │  │  /score/player2  │                 │
│  └────────┬─────────┘  └────────┬─────────┘                 │
│           │                      │                           │
│           └──────────┬───────────┘                           │
│                      │                                       │
│           ┌──────────▼──────────┐                           │
│           │   API Endpoints     │                           │
│           │  /save-draft-scores │                           │
│           │  /get-draft-scores  │                           │
│           └──────────┬──────────┘                           │
│                      │                                       │
│           ┌──────────▼──────────┐                           │
│           │  TournamentMatch    │                           │
│           │  (draft_scores)     │                           │
│           └─────────────────────┘                           │
│                                                               │
│  ┌──────────────────┐  ┌──────────────────┐                 │
│  │  View Score      │  │  View Score      │                 │
│  │   (Player 1)     │  │   (Player 2)     │                 │
│  │ /view-score      │  │ /view-score      │                 │
│  └──────────────────┘  └──────────────────┘                 │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

### Stack technologique
- **Backend** : Laravel 12
- **ORM** : Eloquent
- **Frontend** : Blade + JavaScript vanilla
- **Base de données** : MySQL (colonnes JSON pour draft_scores)
- **Communication** : Fetch API + JSON

---

## Base de données

### Schéma TournamentMatch

#### Colonnes de scoring
```sql
-- Scores finaux (sauvegardés après validation)
player1_primary_points INT
player1_secondary_points INT
player1_painting_points BOOLEAN
player1_score INT (total)
player1_victory_points INT

player2_primary_points INT
player2_secondary_points INT
player2_painting_points BOOLEAN
player2_score INT (total)
player2_victory_points INT

-- Scores temporaires (en cours de saisie)
draft_scores JSON
{
  "player1_primary_points": "13",
  "player1_secondary_points": "3",
  "player1_painting_points": true,
  "player2_primary_points": "12",
  "player2_secondary_points": "4",
  "player2_painting_points": true,
  "secondary_type": "tactical",
  "fixed_mission_1": null,
  "fixed_mission_2": null
}

-- État des missions tactiques
draft_tactical_state_player1 JSON
draft_tactical_state_player2 JSON
{
  "active": [...],
  "discarded": [...],
  "completed": [...],
  "waitingReplacement": [...]
}

-- Métadonnées
score_recorder_id INT (DEPRECATED - non utilisé dans le nouveau système)
score_recorder_selected_at DATETIME (DEPRECATED)
status ENUM('pending', 'in_progress', 'completed')
```

### Modèle Eloquent

```php
class TournamentMatch extends Model {
    protected $casts = [
        'draft_scores' => 'array',
        'draft_tactical_state_player1' => 'array',
        'draft_tactical_state_player2' => 'array',
        'player1_painting_points' => 'boolean',
        'player2_painting_points' => 'boolean',
    ];
}
```

**Important** : Les colonnes `draft_scores` et `draft_tactical_state_*` sont castées en `array`, ce qui permet à Eloquent de les convertir automatiquement en JSON lors de la sauvegarde et en array lors de la lecture.

---

## Logique métier

### Calcul des points

#### Points primaires (max 50)
- Saisis par le joueur
- Basés sur les objectifs primaires du match

#### Points secondaires (max 40)
- Saisis par le joueur
- Basés sur les missions secondaires (fixes ou tactiques)

#### Points peinture (+10)
- Booléen (oui/non)
- Accordé si l'armée est bien peinte

#### Total
```
Total = Points primaires + Points secondaires + (Points peinture ? 10 : 0)
```

### Missions tactiques vs Missions fixes

#### Missions fixes
- Prédéfinies au démarrage du match
- Sélection de 2 missions parmi une liste
- Points fixes pour chaque mission

#### Missions tactiques
- Tirées dynamiquement pendant le match
- Peuvent être défaussées (coûtent des points)
- Peuvent être complétées (gratuites)
- État : `active`, `discarded`, `completed`, `waitingReplacement`

---

## Flux utilisateur

### Scénario complet

```
1. AFFICHAGE DE LA LISTE DES MATCHS
   └─ Page: /tournaments/{id}/matches
   └─ Affiche tous les matchs du tournoi
   └─ Boutons: "Voir config" (si setup valide), "Saisir le score" (si joueur du match)

2. ACCÈS À LA PAGE DE SCORING
   ├─ Player 1 clique sur "Saisir le score"
   │  └─ Redirection vers: /tournaments/{id}/matches/{id}/score/player1
   │  └─ Vérification: Auth::id() === match->player1_id
   │  └─ Affichage: Ses scores (modifiables) + Scores de player2 (readonly)
   │
   └─ Player 2 clique sur "Saisir le score"
      └─ Redirection vers: /tournaments/{id}/matches/{id}/score/player2
      └─ Vérification: Auth::id() === match->player2_id
      └─ Affichage: Ses scores (modifiables) + Scores de player1 (readonly)

3. MODIFICATION DES SCORES
   ├─ Player 1 modifie son score primaire (ex: 21 → 25)
   ├─ Événement: onchange/oninput sur l'input
   ├─ Appel: saveScoringData()
   └─ Requête: POST /api/tournament-matches/{id}/save-draft-scores
      └─ Payload: { player1_primary_points: 25, ... }
      └─ Réponse: { success: true }
      └─ Sauvegarde en DB: draft_scores.player1_primary_points = 25

4. MISE À JOUR EN TEMPS RÉEL
   ├─ Polling automatique toutes les 2 secondes
   ├─ Appel: loadOpponentScores()
   ├─ Requête: GET /api/tournament-matches/{id}/get-draft-scores
   ├─ Réponse: { player1_primary_points: 25, player2_primary_points: 12, ... }
   ├─ Mise à jour DOM: document.getElementById('player2_primary_points').value = 12
   └─ Recalcul: updateTotals()

5. VISUALISATION EN TEMPS RÉEL
   ├─ Player 2 accède à: /tournaments/{id}/matches/{id}/view-score
   ├─ Affichage: Tous les scores (readonly)
   ├─ Polling: Mise à jour automatique toutes les 2 secondes
   └─ Affichage du score de player1 modifié (25)

6. FINALISATION DU MATCH
   ├─ Les deux joueurs cliquent sur "Fin du match"
   ├─ Validation: Tous les scores sont remplis
   ├─ Requête: POST /tournaments/{id}/matches/{id}/store-score
   ├─ Sauvegarde: draft_scores → player1_score, player2_score, etc.
   ├─ Détermination du gagnant
   └─ Statut: pending → completed
```

---

## API et endpoints

### Routes

```php
// routes/web.php

// Saisie du score par joueur
Route::get('/tournaments/{tournament}/matches/{match}/score/player1', 
    [TournamentMatchController::class, 'scoreFormPlayer1'])
    ->name('tournaments.matches.score-player1');

Route::get('/tournaments/{tournament}/matches/{match}/score/player2', 
    [TournamentMatchController::class, 'scoreFormPlayer2'])
    ->name('tournaments.matches.score-player2');

// Visualisation du score
Route::get('/tournaments/{tournament}/matches/{match}/view-score', 
    [TournamentMatchController::class, 'scoreView'])
    ->name('tournaments.matches.view-score');

// API pour sauvegarder les scores temporaires
Route::post('/api/tournament-matches/{match}/save-draft-scores', 
    function (Request $request, TournamentMatch $match) {
        // Vérification: utilisateur est l'un des deux joueurs
        if (Auth::id() !== $match->player1_id && Auth::id() !== $match->player2_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Sauvegarde
        $match->update(['draft_scores' => $request->all()]);
        return response()->json(['success' => true]);
    });

// API pour charger les scores temporaires
Route::get('/api/tournament-matches/{match}/get-draft-scores', 
    function (TournamentMatch $match) {
        // Vérification: utilisateur est l'un des deux joueurs
        if (Auth::id() !== $match->player1_id && Auth::id() !== $match->player2_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        return response()->json($match->draft_scores ?? []);
    });

// Finalisation du score
Route::post('/tournaments/{tournament}/matches/{match}/store-score', 
    [TournamentMatchController::class, 'storeScore'])
    ->name('tournaments.matches.store-score');
```

### Contrôleur

```php
class TournamentMatchController {
    
    /**
     * Page de saisie pour Player 1
     */
    public function scoreFormPlayer1(Tournament $tournament, TournamentMatch $match)
    {
        $user = Auth::user();
        
        // Vérification: utilisateur est player1
        if ($match->player1_id !== $user->id) {
            abort(403, 'Non autorisé');
        }
        
        // Chargement des relations
        $match->load(['primaryMission', 'terrainLayout', 'twistMission', 
                      'player1', 'player2', 'player1ArmyList', 'player2ArmyList']);
        
        return view('tournaments.matches.score-player1', compact('tournament', 'match'));
    }
    
    /**
     * Page de saisie pour Player 2
     */
    public function scoreFormPlayer2(Tournament $tournament, TournamentMatch $match)
    {
        $user = Auth::user();
        
        // Vérification: utilisateur est player2
        if ($match->player2_id !== $user->id) {
            abort(403, 'Non autorisé');
        }
        
        $match->load(['primaryMission', 'terrainLayout', 'twistMission', 
                      'player1', 'player2', 'player1ArmyList', 'player2ArmyList']);
        
        return view('tournaments.matches.score-player2', compact('tournament', 'match'));
    }
    
    /**
     * Page de visualisation du score
     */
    public function scoreView(Tournament $tournament, TournamentMatch $match)
    {
        $user = Auth::user();
        
        // Vérification: utilisateur est l'un des deux joueurs
        if ($match->player1_id !== $user->id && $match->player2_id !== $user->id) {
            abort(403, 'Non autorisé');
        }
        
        $match->load(['primaryMission', 'terrainLayout', 'twistMission', 
                      'player1', 'player2', 'player1ArmyList', 'player2ArmyList']);
        
        return view('tournaments.matches.view-score', compact('tournament', 'match'));
    }
}
```

---

## Frontend - Pages de scoring

### Structure des pages

#### score-player1.blade.php
- Affiche les scores de player1 (modifiables)
- Affiche les scores de player2 (readonly)
- Boutons +/- masqués pour player2
- Event listeners seulement sur player1

#### score-player2.blade.php
- Affiche les scores de player1 (readonly)
- Affiche les scores de player2 (modifiables)
- Boutons +/- masqués pour player1
- Event listeners seulement sur player2

#### view-score.blade.php
- Affiche les scores des deux joueurs (readonly)
- Mise à jour automatique toutes les 2 secondes
- Accessible par les deux joueurs

### Inputs et contrôles

```html
<!-- Inputs modifiables (player1 dans score-player1) -->
<input type="number" id="player1_primary_points" 
       name="player1_primary_points" 
       min="0" max="50" 
       value="0"
       onchange="updateTotals(); validateForm(); saveScoringData()"
       oninput="updateTotals(); validateForm()">

<!-- Boutons +/- -->
<button type="button" 
        onclick="incrementPoints('player1_primary_points', 50)"
        class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 rounded text-sm">
    +
</button>

<!-- Inputs readonly (player2 dans score-player1) -->
<input type="number" id="player2_primary_points" 
       readonly 
       name="player2_primary_points" 
       min="0" max="50" 
       value="0"
       class="...">

<!-- Boutons +/- masqués pour player2 -->
<style>
    .player2-readonly-buttons button {
        display: none !important;
    }
</style>
```

### JavaScript - Fonctions principales

#### saveScoringData()
```javascript
function saveScoringData() {
    clearTimeout(saveScoringTimeout);
    saveScoringTimeout = setTimeout(() => {
        const data = {
            player1_primary_points: document.getElementById('player1_primary_points').value,
            player1_secondary_points: document.getElementById('player1_secondary_points').value,
            player1_painting_points: document.getElementById('player1_painting_points').checked,
            player2_primary_points: document.getElementById('player2_primary_points').value,
            player2_secondary_points: document.getElementById('player2_secondary_points').value,
            player2_painting_points: document.getElementById('player2_painting_points').checked,
            secondary_type: document.querySelector('input[name="secondary_type"]:checked')?.value,
            fixed_mission_1: document.querySelector('select[name="fixed_mission_1"]')?.value,
            fixed_mission_2: document.querySelector('select[name="fixed_mission_2"]')?.value,
        };
        
        fetch(`/api/tournament-matches/${matchId}/save-draft-scores`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(data),
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                console.log('✅ Scores sauvegardés');
            }
        })
        .catch(error => console.error('Erreur:', error));
    }, 2000); // Debouncing 2 secondes
}
```

#### loadOpponentScores()
```javascript
function loadOpponentScores() {
    fetch(`/api/tournament-matches/${matchId}/get-draft-scores`)
        .then(response => response.json())
        .then(data => {
            if (data && Object.keys(data).length > 0) {
                // Ne recharger que les scores de l'adversaire
                document.getElementById('player2_primary_points').value = data.player2_primary_points || 0;
                document.getElementById('player2_secondary_points').value = data.player2_secondary_points || 0;
                document.getElementById('player2_painting_points').checked = data.player2_painting_points !== false;
                
                updateTotals();
            }
        })
        .catch(error => console.error('Erreur:', error));
}
```

#### Polling automatique
```javascript
// Démarrer le polling au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Charger les données initiales
    loadSavedData();
    
    // Polling automatique toutes les 2 secondes
    setInterval(() => {
        loadOpponentScores();
    }, 2000);
});
```

#### updateTotals()
```javascript
function updateTotals() {
    // Player 1
    const player1Primary = parseInt(document.getElementById('player1_primary_points').value) || 0;
    const player1Secondary = parseInt(document.getElementById('player1_secondary_points').value) || 0;
    const player1Painting = document.getElementById('player1_painting_points').checked ? 10 : 0;
    const player1Total = player1Primary + player1Secondary + player1Painting;
    document.getElementById('player1_total').textContent = player1Total;
    
    // Player 2
    const player2Primary = parseInt(document.getElementById('player2_primary_points').value) || 0;
    const player2Secondary = parseInt(document.getElementById('player2_secondary_points').value) || 0;
    const player2Painting = document.getElementById('player2_painting_points').checked ? 10 : 0;
    const player2Total = player2Primary + player2Secondary + player2Painting;
    document.getElementById('player2_total').textContent = player2Total;
}
```

---

## Système de mise à jour en temps réel

### Mécanisme de polling

#### Problème initial
Le polling rechargeait TOUS les scores, y compris ceux que l'utilisateur était en train de modifier, ce qui causait un "rollback" des modifications.

#### Solution
Créer deux fonctions distinctes :
- `loadSavedData()` : Charge TOUS les scores (au démarrage)
- `loadOpponentScores()` : Charge SEULEMENT les scores de l'adversaire (polling)

#### Implémentation
```javascript
// Au démarrage
loadSavedData().then(secondaryType => {
    // Charger les missions tactiques
    loadTacticalStateFromDb();
});

// Polling automatique
setInterval(() => {
    loadOpponentScores(); // Ne recharge que player2 dans score-player1
}, 2000);
```

### Fréquence de mise à jour
- **Polling** : Toutes les 2 secondes
- **Debouncing de sauvegarde** : 2 secondes après la dernière modification
- **Raison** : Éviter les appels API excessifs et les conflits de sauvegarde

---

## Sécurité et autorisation

### Niveaux de sécurité

#### 1. Authentification
```php
// Middleware 'auth' sur toutes les routes
Route::middleware('auth')->group(function () {
    // Routes protégées
});
```

#### 2. Autorisation au niveau du contrôleur
```php
public function scoreFormPlayer1(Tournament $tournament, TournamentMatch $match)
{
    $user = Auth::user();
    
    // Vérification: utilisateur est player1
    if ($match->player1_id !== $user->id) {
        abort(403, 'Non autorisé');
    }
}
```

#### 3. Autorisation au niveau de l'API
```php
Route::post('/api/tournament-matches/{match}/save-draft-scores', 
    function (Request $request, TournamentMatch $match) {
        // Vérification: utilisateur est l'un des deux joueurs
        if (Auth::id() !== $match->player1_id && Auth::id() !== $match->player2_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $match->update(['draft_scores' => $request->all()]);
        return response()->json(['success' => true]);
    });
```

#### 4. Contrôles frontend
- Inputs `readonly` pour les scores non modifiables
- Boutons +/- masqués avec CSS
- Event listeners seulement sur les inputs modifiables

### Prévention des abus

| Attaque | Prévention |
|---------|-----------|
| Accès non autorisé à `/score/player1` | Vérification `Auth::id() === match->player1_id` |
| Modification des scores d'un autre joueur | Inputs `readonly` + API check |
| Injection de données | Validation `$request->validate()` |
| CSRF | Token CSRF dans les headers |
| Modification du DOM | Vérification côté serveur |

---

## Guide d'implémentation pour matchs simples

### Étapes de migration

#### 1. Analyser le modèle PlayerMatch
```bash
# Vérifier les colonnes existantes
php artisan tinker
>>> PlayerMatch::first()->getAttributes()
```

#### 2. Créer les migrations nécessaires
```php
// Si les colonnes n'existent pas
Schema::table('player_matches', function (Blueprint $table) {
    $table->json('draft_scores')->nullable();
    $table->json('draft_tactical_state_creator')->nullable();
    $table->json('draft_tactical_state_opponent')->nullable();
});
```

#### 3. Mettre à jour le modèle PlayerMatch
```php
class PlayerMatch extends Model {
    protected $casts = [
        'draft_scores' => 'array',
        'draft_tactical_state_creator' => 'array',
        'draft_tactical_state_opponent' => 'array',
    ];
}
```

#### 4. Créer les routes
```php
// Copier les routes de TournamentMatch
Route::get('/player-matches/{match}/score/creator', 
    [PlayerMatchController::class, 'scoreFormCreator'])
    ->name('player-matches.score-creator');

Route::get('/player-matches/{match}/score/opponent', 
    [PlayerMatchController::class, 'scoreFormOpponent'])
    ->name('player-matches.score-opponent');
```

#### 5. Créer les contrôleurs
```php
class PlayerMatchController {
    public function scoreFormCreator(PlayerMatch $match)
    {
        if (Auth::id() !== $match->creator_id) {
            abort(403);
        }
        return view('player-matches.score-creator', compact('match'));
    }
    
    public function scoreFormOpponent(PlayerMatch $match)
    {
        if (Auth::id() !== $match->opponent_id) {
            abort(403);
        }
        return view('player-matches.score-opponent', compact('match'));
    }
}
```

#### 6. Créer les vues
- Copier `score-player1.blade.php` → `score-creator.blade.php`
- Copier `score-player2.blade.php` → `score-opponent.blade.php`
- Remplacer `player1` par `creator` et `player2` par `opponent`

#### 7. Créer les API endpoints
```php
Route::post('/api/player-matches/{match}/save-draft-scores', 
    function (Request $request, PlayerMatch $match) {
        if (Auth::id() !== $match->creator_id && Auth::id() !== $match->opponent_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $match->update(['draft_scores' => $request->all()]);
        return response()->json(['success' => true]);
    });
```

#### 8. Mettre à jour la page d'index
- Remplacer les boutons "Saisir le score" par les nouvelles routes
- Afficher les boutons pour `creator` et `opponent`

#### 9. Tester complètement
- Accès non autorisé
- Modification des scores
- Mise à jour en temps réel
- Finalisation du match

---

## Checklist de vérification

### Avant le déploiement
- [ ] Authentification fonctionne
- [ ] Autorisation stricte (403 si non autorisé)
- [ ] Inputs readonly pour l'adversaire
- [ ] Boutons +/- masqués pour l'adversaire
- [ ] Sauvegarde automatique fonctionne
- [ ] Polling met à jour les scores de l'adversaire
- [ ] Pas de rollback des modifications
- [ ] Finalisation du match fonctionne
- [ ] Calcul des totaux correct
- [ ] Validation des points (max 50 et 40)
- [ ] Pas de fuite de données
- [ ] Performance acceptable (polling 2 secondes)

### Tests de sécurité
- [ ] Impossible de modifier les scores d'un autre joueur
- [ ] Impossible d'accéder à `/score/player1` en tant que player2
- [ ] Impossible de sauvegarder des scores invalides
- [ ] Impossible de contourner les vérifications frontend

---

## Fichiers clés

| Fichier | Rôle |
|---------|------|
| `app/Models/TournamentMatch.php` | Modèle avec colonnes JSON |
| `app/Http/Controllers/TournamentMatchController.php` | Contrôleur avec 3 méthodes |
| `routes/web.php` | Routes et API endpoints |
| `resources/views/tournaments/matches/score-player1.blade.php` | Vue pour player1 |
| `resources/views/tournaments/matches/score-player2.blade.php` | Vue pour player2 |
| `resources/views/tournaments/matches/view-score.blade.php` | Vue de visualisation |
| `resources/views/tournaments/matches/index.blade.php` | Liste des matchs |

---

## Conclusion

Ce système de scoring pour matchs de tournoi est :
- ✅ **Sécurisé** : Authentification et autorisation strictes
- ✅ **Performant** : Polling optimisé, debouncing de sauvegarde
- ✅ **Intuitif** : Chaque joueur voit ses scores modifiables et ceux de l'adversaire en readonly
- ✅ **Temps réel** : Mise à jour automatique toutes les 2 secondes
- ✅ **Maintenable** : Code bien structuré et documenté

Il peut être adapté pour les matchs simples en remplaçant `player1/player2` par `creator/opponent`.
