# Système Complet des Matchs de Tournoi - Documentation Exhaustive (Partie 1)

## 📋 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture générale](#architecture-générale)
3. [Base de données](#base-de-données)
4. [Modèle TournamentMatch](#modèle-tournamentmatch)
5. [Routes et API](#routes-et-api)
6. [Contrôleur TournamentMatchController](#contrôleur-tournamentmatchcontroller)

---

## Vue d'ensemble

### Objectif
Permettre aux deux joueurs d'un match de tournoi de saisir et valider les scores de manière collaborative, avec une synchronisation en temps réel et une interface intuitive.

### Principes clés
- **Séparation des joueurs** : Chaque joueur a sa propre page de saisie (player1 et player2)
- **Validation bidirectionnelle** : Les deux joueurs doivent valider les scores
- **Temps réel** : Polling toutes les 2 secondes pour détecter les changements
- **Overlay de synchronisation** : Affichage d'un écran noir avec spinner pendant l'attente
- **Refus de validation** : Un joueur peut refuser et demander une correction
- **Sécurité** : Authentification stricte et autorisations par joueur

---

## Architecture générale

```
┌─────────────────────────────────────────────────────────────────┐
│                    Match de Tournoi                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌──────────────────┐  ┌──────────────────┐                     │
│  │   Player 1       │  │   Player 2       │                     │
│  │  /score/player1  │  │  /score/player2  │                     │
│  └────────┬─────────┘  └────────┬─────────┘                     │
│           │                      │                               │
│           └──────────┬───────────┘                               │
│                      │                                           │
│           ┌──────────▼──────────┐                               │
│           │   API Endpoints     │                               │
│           │  /set-score         │                               │
│           │  /validate-score    │                               │
│           │  /reject-score      │                               │
│           │  /validation-status │                               │
│           └──────────┬──────────┘                               │
│                      │                                           │
│           ┌──────────▼──────────┐                               │
│           │  TournamentMatch    │                               │
│           │  (Base de données)  │                               │
│           └─────────────────────┘                               │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## Base de données

### Schéma TournamentMatch

#### Colonnes de scoring (points et validation)

```sql
-- Scores des joueurs (points primaires + secondaires + peinture)
player1_primary_points INT              -- Points primaires du joueur 1
player1_secondary_points INT            -- Points secondaires du joueur 1
player1_painting_points BOOLEAN         -- Bonus peinture du joueur 1 (0 ou 1)
player1_score INT                       -- Score total du joueur 1 (calculé)
player1_victory_points INT              -- Points de victoire du joueur 1

player2_primary_points INT              -- Points primaires du joueur 2
player2_secondary_points INT            -- Points secondaires du joueur 2
player2_painting_points BOOLEAN         -- Bonus peinture du joueur 2 (0 ou 1)
player2_score INT                       -- Score total du joueur 2 (calculé)
player2_victory_points INT              -- Points de victoire du joueur 2

-- Validation des scores
player1_score_validated BOOLEAN         -- Le joueur 1 a-t-il validé ?
player2_score_validated BOOLEAN         -- Le joueur 2 a-t-il validé ?

-- Statut du match
status ENUM('pending', 'in_progress', 'confirmed', 'completed')
  - pending: Match en attente de configuration
  - in_progress: Match en cours
  - confirmed: Scores saisis, en attente de validation
  - completed: Match finalisé

-- Résultat
winner_id INT (nullable)                -- ID du gagnant (null si nul)
is_draw BOOLEAN                         -- Le match est-il nul ?
completed_at TIMESTAMP (nullable)       -- Date de finalisation
```

#### Exemple de données

```json
{
  "player1_id": 1,
  "player2_id": 2,
  "player1_primary_points": 13,
  "player1_secondary_points": 3,
  "player1_painting_points": true,
  "player1_score": 17,
  "player1_victory_points": 17,
  "player1_score_validated": true,
  
  "player2_primary_points": 10,
  "player2_secondary_points": 5,
  "player2_painting_points": false,
  "player2_score": 15,
  "player2_victory_points": 15,
  "player2_score_validated": false,
  
  "status": "confirmed",
  "winner_id": 1,
  "is_draw": false,
  "completed_at": null
}
```

---

## Modèle TournamentMatch

### Propriétés principales

```php
class TournamentMatch extends Model
{
    // Attributs fillable
    protected $fillable = [
        'tournament_id',
        'player1_id', 'player2_id',
        'player1_primary_points', 'player1_secondary_points', 'player1_painting_points',
        'player2_primary_points', 'player2_secondary_points', 'player2_painting_points',
        'player1_score', 'player2_score',
        'player1_victory_points', 'player2_victory_points',
        'player1_score_validated', 'player2_score_validated',
        'status', 'winner_id', 'is_draw',
    ];

    // Casts
    protected $casts = [
        'player1_painting_points' => 'boolean',
        'player2_painting_points' => 'boolean',
        'player1_score_validated' => 'boolean',
        'player2_score_validated' => 'boolean',
        'is_draw' => 'boolean',
        'completed_at' => 'datetime',
    ];
}
```

### Relations

```php
public function tournament(): BelongsTo
public function player1(): BelongsTo      // Utilisateur joueur 1
public function player2(): BelongsTo      // Utilisateur joueur 2
public function player1ArmyList(): BelongsTo
public function player2ArmyList(): BelongsTo
public function winner(): BelongsTo       // Utilisateur gagnant
```

### Méthodes utilitaires

```php
public function isPlayer(User $user): bool              // Vérifier si l'utilisateur est un joueur
public function canEditResult(User $user): bool         // Vérifier si peut éditer
public function getOpponent(User $user): ?User          // Obtenir l'adversaire
public function isSetupValid(): bool                    // Vérifier si config complète
public function determineWinner($result = null)         // Déterminer le gagnant
public function resetValidation()                       // Réinitialiser les validations
```

---

## Routes et API

### Routes Web (formulaires)

```php
// Affichage des scores pour Player 1
GET /tournaments/{tournament}/matches/{match}/score/player1
    → TournamentMatchController::scoreFormPlayer1()
    → Vue: tournaments.matches.score-player1

// Affichage des scores pour Player 2
GET /tournaments/{tournament}/matches/{match}/score/player2
    → TournamentMatchController::scoreFormPlayer2()
    → Vue: tournaments.matches.score-player2

// Visualisation du score (lecture seule)
GET /tournaments/{tournament}/matches/{match}/view-score
    → TournamentMatchController::scoreView()
    → Vue: tournaments.matches.view-score
```

### Routes API (AJAX)

#### 1. Saisie du score

```
POST /tournament-matches/{tournamentMatch}/set-score
Content-Type: application/json
X-CSRF-TOKEN: {token}

Requête:
{
  "player1_primary_points": 13,
  "player1_secondary_points": 3,
  "player1_painting_points": true,
  "player2_primary_points": 10,
  "player2_secondary_points": 5,
  "player2_painting_points": false
}

Réponse:
{
  "success": true,
  "message": "Score enregistré. En attente de la validation de l'autre joueur...",
  "status": "confirmed",
  "player1_validated": true,
  "player2_validated": false
}
```

#### 2. Validation du score

```
POST /tournament-matches/{tournamentMatch}/validate-opponent-score
Content-Type: application/json
X-CSRF-TOKEN: {token}

Requête:
{}

Réponse:
{
  "success": true,
  "message": "Match finalisé avec succès !",
  "status": "completed",
  "player1_validated": true,
  "player2_validated": true
}
```

#### 3. Refus de validation

```
POST /tournament-matches/{tournamentMatch}/reject-score-validation
Content-Type: application/json
X-CSRF-TOKEN: {token}

Requête:
{}

Réponse:
{
  "success": true,
  "message": "Validation refusée. Veuillez corriger les scores.",
  "status": "confirmed",
  "player1_validated": false,
  "player2_validated": false
}
```

#### 4. Vérification du statut de validation (Polling)

```
GET /api/tournament-matches/{tournamentMatch}/validation-status

Réponse:
{
  "player1_validated": true,
  "player2_validated": false,
  "status": "confirmed",
  "player1_name": "Alice",
  "player2_name": "Bob"
}
```

---

## Contrôleur TournamentMatchController

### Méthode setScore()

**Responsabilité** : Enregistrer les scores saisis par un joueur

```php
public function setScore(Request $request, TournamentMatch $tournamentMatch)
{
    // 1. Vérifier que l'utilisateur est l'un des deux joueurs
    $user = Auth::user();
    if ($tournamentMatch->player1_id !== $user->id && $tournamentMatch->player2_id !== $user->id) {
        return response()->json(['error' => 'Non autorisé'], 403);
    }

    // 2. Valider les données
    $validated = $request->validate([
        'player1_primary_points' => 'required|integer|min:0',
        'player1_secondary_points' => 'required|integer|min:0',
        'player1_painting_points' => 'required|boolean',
        'player2_primary_points' => 'required|integer|min:0',
        'player2_secondary_points' => 'required|integer|min:0',
        'player2_painting_points' => 'required|boolean',
    ]);

    // 3. Calculer les scores totaux
    $player1Score = $validated['player1_primary_points'] 
                  + $validated['player1_secondary_points'] 
                  + ($validated['player1_painting_points'] ? 1 : 0);
    
    $player2Score = $validated['player2_primary_points'] 
                  + $validated['player2_secondary_points'] 
                  + ($validated['player2_painting_points'] ? 1 : 0);

    // 4. Mettre à jour les scores
    $tournamentMatch->player1_primary_points = $validated['player1_primary_points'];
    $tournamentMatch->player1_secondary_points = $validated['player1_secondary_points'];
    $tournamentMatch->player1_painting_points = $validated['player1_painting_points'];
    $tournamentMatch->player1_score = $player1Score;

    $tournamentMatch->player2_primary_points = $validated['player2_primary_points'];
    $tournamentMatch->player2_secondary_points = $validated['player2_secondary_points'];
    $tournamentMatch->player2_painting_points = $validated['player2_painting_points'];
    $tournamentMatch->player2_score = $player2Score;

    // 5. Marquer le joueur actuel comme validé
    if ($tournamentMatch->player1_id === $user->id) {
        $tournamentMatch->player1_score_validated = true;
    } else {
        $tournamentMatch->player2_score_validated = true;
    }

    $tournamentMatch->save();

    // 6. Retourner le statut actuel
    return response()->json([
        'success' => true,
        'message' => 'Score enregistré. En attente de la validation de l\'autre joueur...',
        'status' => $tournamentMatch->status,
        'player1_validated' => $tournamentMatch->player1_score_validated,
        'player2_validated' => $tournamentMatch->player2_score_validated,
    ]);
}
```

### Méthode validateOpponentScore()

**Responsabilité** : Valider le score de l'adversaire et finaliser si les deux ont validé

```php
public function validateOpponentScore(Request $request, TournamentMatch $tournamentMatch)
{
    $user = Auth::user();

    // 1. Vérifier l'authentification
    if ($tournamentMatch->player1_id !== $user->id && $tournamentMatch->player2_id !== $user->id) {
        return response()->json(['error' => 'Non autorisé'], 403);
    }

    // 2. Marquer le joueur actuel comme validé
    if ($tournamentMatch->player1_id === $user->id) {
        $tournamentMatch->player1_score_validated = true;
    } else {
        $tournamentMatch->player2_score_validated = true;
    }

    // 3. Si les deux ont validé → finaliser le match
    if ($tournamentMatch->player1_score_validated && $tournamentMatch->player2_score_validated) {
        $tournamentMatch->status = 'completed';
        $tournamentMatch->completed_at = now();
        $tournamentMatch->determineWinner();
    }

    $tournamentMatch->save();

    return response()->json([
        'success' => true,
        'message' => $tournamentMatch->status === 'completed' 
            ? 'Match finalisé avec succès !' 
            : 'Score validé.',
        'status' => $tournamentMatch->status,
        'player1_validated' => $tournamentMatch->player1_score_validated,
        'player2_validated' => $tournamentMatch->player2_score_validated,
    ]);
}
```

### Méthode rejectScoreValidation()

**Responsabilité** : Refuser la validation et réinitialiser les deux validations

```php
public function rejectScoreValidation(Request $request, TournamentMatch $tournamentMatch)
{
    $user = Auth::user();

    // 1. Vérifier l'authentification
    if ($tournamentMatch->player1_id !== $user->id && $tournamentMatch->player2_id !== $user->id) {
        return response()->json(['error' => 'Non autorisé'], 403);
    }

    // 2. Réinitialiser les deux validations
    $tournamentMatch->player1_score_validated = false;
    $tournamentMatch->player2_score_validated = false;
    $tournamentMatch->status = 'confirmed';
    $tournamentMatch->save();

    return response()->json([
        'success' => true,
        'message' => 'Validation refusée. Veuillez corriger les scores.',
        'status' => $tournamentMatch->status,
        'player1_validated' => $tournamentMatch->player1_score_validated,
        'player2_validated' => $tournamentMatch->player2_score_validated,
    ]);
}
```

### Méthode getValidationStatus()

**Responsabilité** : Retourner l'état actuel de la validation (pour le polling)

```php
public function getValidationStatus(TournamentMatch $tournamentMatch)
{
    return response()->json([
        'player1_validated' => $tournamentMatch->player1_score_validated,
        'player2_validated' => $tournamentMatch->player2_score_validated,
        'status' => $tournamentMatch->status,
        'player1_name' => $tournamentMatch->player1->name,
        'player2_name' => $tournamentMatch->player2->name,
    ]);
}
```
