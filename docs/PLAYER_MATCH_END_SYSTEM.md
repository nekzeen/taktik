# Système de Gestion de Fin de Match - Matchs Simples

## 📋 Table des Matières
1. [Vue d'ensemble](#vue-densemble)
2. [Architecture](#architecture)
3. [Base de Données](#base-de-données)
4. [Modèles](#modèles)
5. [Contrôleurs](#contrôleurs)
6. [Routes](#routes)
7. [Flux Complet](#flux-complet)
8. [Implémentation pour Tournois](#implémentation-pour-tournois)

---

## Vue d'ensemble

Le système de gestion de fin de match permet à deux joueurs de :
1. **Saisir les scores** de manière indépendante
2. **Valider les scores** mutuellement
3. **Refuser la validation** si les scores ne correspondent pas
4. **Finaliser le match** quand les deux ont validé

### Caractéristiques Clés
- ✅ Validation à deux joueurs (double confirmation)
- ✅ Possibilité de refuser et corriger les scores
- ✅ Polling en temps réel pour détecter les changements
- ✅ Messages d'alerte modaux visuels
- ✅ Redirection automatique après finalisation

---

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    JOUEUR 1 (Créateur)                      │
│  /player-matches/{id}/score/creator                         │
└─────────────────────────────────────────────────────────────┘
                            ↕
                    [API REST - Endpoints]
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                    JOUEUR 2 (Adversaire)                    │
│  /player-matches/{id}/score/opponent                        │
└─────────────────────────────────────────────────────────────┘
                            ↕
                    [Base de Données]
                            ↕
                    [PlayerMatch Model]
```

### Composants Principaux

| Composant | Fichier | Responsabilité |
|-----------|---------|-----------------|
| **Modèle** | `app/Models/PlayerMatch.php` | Gestion des données du match |
| **Contrôleur** | `app/Http/Controllers/PlayerMatchController.php` | Logique métier |
| **Routes** | `routes/web.php` | Endpoints HTTP |
| **Vues** | `resources/views/player-matches/score-*.blade.php` | Interface utilisateur |
| **JavaScript** | Inline dans les vues | Logique client et polling |

---

## Base de Données

### Colonnes Critiques pour la Fin de Match

| Colonne | Type | Description |
|---------|------|-------------|
| `status` | ENUM | État du match (open, confirmed, completed) |
| `creator_score_validated` | BOOLEAN | Le créateur a validé son score |
| `opponent_score_validated` | BOOLEAN | L'adversaire a validé son score |
| `creator_score` | INT | Score final du créateur |
| `opponent_score` | INT | Score final de l'adversaire |
| `winner_id` | BIGINT | ID du gagnant (NULL si nul) |
| `is_draw` | BOOLEAN | Match nul |
| `played_at` | TIMESTAMP | Date/heure de fin du match |

### Migration

```php
// database/migrations/XXXX_XX_XX_XXXXXX_add_validation_to_player_matches.php
Schema::table('player_matches', function (Blueprint $table) {
    $table->boolean('creator_score_validated')->default(false);
    $table->boolean('opponent_score_validated')->default(false);
});
```

---

## Modèles

### PlayerMatch.php

```php
class PlayerMatch extends Model
{
    protected $fillable = [
        'creator_score_validated',
        'opponent_score_validated',
        // ... autres champs ...
    ];

    protected $casts = [
        'creator_score_validated' => 'boolean',
        'opponent_score_validated' => 'boolean',
        'is_draw' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function opponent()
    {
        return $this->belongsTo(User::class, 'opponent_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function determineWinner($result = null)
    {
        if ($this->is_draw) {
            $this->winner_id = null;
        } else {
            $this->winner_id = $this->creator_score > $this->opponent_score 
                ? $this->creator_id 
                : $this->opponent_id;
        }
        $this->save();
    }

    public function resetValidation()
    {
        $this->creator_score_validated = false;
        $this->opponent_score_validated = false;
        $this->status = 'confirmed';
        $this->save();
    }
}
```

---

## Contrôleurs

### PlayerMatchController.php - Méthodes Principales

#### 1. `setScore()` - Enregistrer les scores

```php
public function setScore(Request $request, PlayerMatch $playerMatch)
{
    $user = Auth::user();
    
    if ($playerMatch->creator_id !== $user->id && $playerMatch->opponent_id !== $user->id) {
        return response()->json(['error' => 'Non autorisé'], 403);
    }

    $validated = $request->validate([
        'creator_result' => 'required|in:nul,creator_abandon,opponent_abandon,creator_table_rase,opponent_table_rase',
        'creator_primary_points' => 'required|integer|min:0',
        'creator_secondary_points' => 'required|integer|min:0',
        'creator_painting_points' => 'required|boolean',
        'opponent_primary_points' => 'required|integer|min:0',
        'opponent_secondary_points' => 'required|integer|min:0',
        'opponent_painting_points' => 'required|boolean',
    ]);

    $creatorScore = $this->calculateScore(
        $validated['creator_result'],
        $validated['creator_primary_points'],
        $validated['creator_secondary_points'],
        $validated['creator_painting_points']
    );

    if ($playerMatch->creator_id === $user->id) {
        $playerMatch->creator_score = $creatorScore;
        $playerMatch->creator_score_validated = true;
    } else {
        $playerMatch->opponent_score = $creatorScore;
        $playerMatch->opponent_score_validated = true;
    }

    $playerMatch->save();

    return response()->json([
        'success' => true,
        'message' => 'Score enregistré. En attente de la validation de l\'autre joueur...',
        'status' => $playerMatch->status,
        'creator_validated' => $playerMatch->creator_score_validated,
        'opponent_validated' => $playerMatch->opponent_score_validated,
    ]);
}
```

#### 2. `validateOpponentScore()` - Valider et finaliser

```php
public function validateOpponentScore(Request $request, PlayerMatch $playerMatch)
{
    $user = Auth::user();

    if ($playerMatch->creator_id !== $user->id && $playerMatch->opponent_id !== $user->id) {
        return response()->json(['error' => 'Non autorisé'], 403);
    }

    if ($playerMatch->creator_id === $user->id) {
        $playerMatch->creator_score_validated = true;
    } else {
        $playerMatch->opponent_score_validated = true;
    }

    if ($playerMatch->creator_score_validated && $playerMatch->opponent_score_validated) {
        $playerMatch->status = 'completed';
        $playerMatch->played_at = now();
        $playerMatch->determineWinner();
    }

    $playerMatch->save();

    return response()->json([
        'success' => true,
        'message' => $playerMatch->status === 'completed' ? 'Match finalisé !' : 'Score validé.',
        'status' => $playerMatch->status,
        'creator_validated' => $playerMatch->creator_score_validated,
        'opponent_validated' => $playerMatch->opponent_score_validated,
    ]);
}
```

#### 3. `rejectScoreValidation()` - Refuser la validation

```php
public function rejectScoreValidation(Request $request, PlayerMatch $playerMatch)
{
    $user = Auth::user();

    if ($playerMatch->creator_id !== $user->id && $playerMatch->opponent_id !== $user->id) {
        return response()->json(['error' => 'Non autorisé'], 403);
    }

    $playerMatch->creator_score_validated = false;
    $playerMatch->opponent_score_validated = false;
    $playerMatch->status = 'confirmed';
    $playerMatch->save();

    return response()->json([
        'success' => true,
        'message' => 'Validation refusée. Veuillez corriger les scores.',
        'status' => $playerMatch->status,
        'creator_validated' => $playerMatch->creator_score_validated,
        'opponent_validated' => $playerMatch->opponent_score_validated,
    ]);
}
```

#### 4. `getValidationStatus()` - Récupérer l'état

```php
public function getValidationStatus(PlayerMatch $playerMatch)
{
    return response()->json([
        'creator_validated' => $playerMatch->creator_score_validated,
        'opponent_validated' => $playerMatch->opponent_score_validated,
        'status' => $playerMatch->status,
        'creator_name' => $playerMatch->creator->name,
        'opponent_name' => $playerMatch->opponent->name,
    ]);
}
```

---

## Routes

### routes/web.php

```php
Route::middleware(['auth'])->group(function () {
    // Pages de saisie des scores
    Route::get('/player-matches/{playerMatch}/score/creator', 
        [PlayerMatchController::class, 'scoreFormCreator']
    )->name('player-matches.score-creator');
    
    Route::get('/player-matches/{playerMatch}/score/opponent', 
        [PlayerMatchController::class, 'scoreFormOpponent']
    )->name('player-matches.score-opponent');

    // Endpoints pour la gestion des scores
    Route::post('/player-matches/{playerMatch}/set-score', 
        [PlayerMatchController::class, 'setScore']
    )->name('player-matches.set-score');
    
    Route::post('/player-matches/{playerMatch}/validate-opponent-score', 
        [PlayerMatchController::class, 'validateOpponentScore']
    )->name('player-matches.validate-opponent-score');
    
    Route::post('/player-matches/{playerMatch}/reject-score-validation', 
        [PlayerMatchController::class, 'rejectScoreValidation']
    )->name('player-matches.reject-score-validation');
    
    // API pour le polling
    Route::get('/api/player-matches/{playerMatch}/validation-status', 
        [PlayerMatchController::class, 'getValidationStatus']
    );
});
```

---

## Flux Complet

### Scénario 1: Validation Réussie

```
Joueur 1 clique "Fin du match"
    ↓
POST /set-score (Joueur 1)
    ↓
creator_score_validated = true
    ↓
Message bleu : "⏳ En attente de validation..."
    ↓
Polling détecte : opponent_validated = false
    ↓
Joueur 2 reçoit notification
    ↓
Joueur 2 clique "Confirmer"
    ↓
POST /validate-opponent-score (Joueur 2)
    ↓
opponent_score_validated = true
    ↓
Les deux validés → status = 'completed'
    ↓
Modal vert : "✅ Match finalisé!"
    ↓
Redirection vers /player-matches après 2s
```

### Scénario 2: Refus de Validation

```
Joueur 1 clique "Fin du match"
    ↓
POST /set-score (Joueur 1)
    ↓
Message bleu : "⏳ En attente..."
    ↓
Joueur 2 reçoit notification
    ↓
Joueur 2 clique "Refuser"
    ↓
POST /reject-score-validation (Joueur 2)
    ↓
creator_score_validated = false
opponent_score_validated = false
status = 'confirmed'
    ↓
Polling détecte le refus
    ↓
Les deux reviennent à la page de score
    ↓
Message : "Validation refusée. Vous pouvez corriger les scores."
```

---

## Implémentation pour Tournois

### Fichiers à Adapter

1. **Modèle** : `app/Models/TournamentMatch.php`
   - Ajouter les colonnes : `creator_score_validated`, `opponent_score_validated`
   - Ajouter les méthodes : `determineWinner()`, `resetValidation()`

2. **Contrôleur** : `app/Http/Controllers/TournamentMatchController.php`
   - Copier les méthodes : `setScore()`, `validateOpponentScore()`, `rejectScoreValidation()`, `getValidationStatus()`
   - Adapter les noms de routes et modèles

3. **Routes** : `routes/web.php`
   - Ajouter les routes pour les tournois (remplacer `player-matches` par `tournament-matches`)

4. **Vues** : `resources/views/tournaments/matches/score-*.blade.php`
   - Copier la structure des vues des matchs simples
   - Adapter les variables et routes

5. **JavaScript** : Même logique, adapter les IDs et routes

### Différences Clés pour Tournois

- Ajouter la gestion des **missions de tournoi** (primaire, secondaire, péripétie)
- Ajouter la gestion des **zones de déploiement**
- Ajouter la gestion des **terrains**
- Gérer les **points de victoire** spécifiques aux tournois

---

## Checklist d'Implémentation

- [ ] Ajouter les colonnes de validation en base de données
- [ ] Ajouter les méthodes au modèle
- [ ] Ajouter les contrôleurs
- [ ] Ajouter les routes
- [ ] Créer les vues
- [ ] Ajouter le JavaScript
- [ ] Tester le flux complet
- [ ] Tester les cas d'erreur
- [ ] Documenter les endpoints API
