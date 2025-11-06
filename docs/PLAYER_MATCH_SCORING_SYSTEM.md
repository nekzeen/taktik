# Système de Scoring des Matchs Simples (PlayerMatch)

## 📋 Table des Matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture](#architecture)
3. [Base de Données](#base-de-données)
4. [Modèles et Relations](#modèles-et-relations)
5. [Routes et API](#routes-et-api)
6. [Contrôleurs](#contrôleurs)
7. [Vues et Interface](#vues-et-interface)
8. [JavaScript et Temps Réel](#javascript-et-temps-réel)
9. [CSS et Styling](#css-et-styling)
10. [Flux Utilisateur](#flux-utilisateur)
11. [Sécurité et Autorisation](#sécurité-et-autorisation)

---

## Vue d'ensemble

Le système de scoring des matchs simples permet à deux joueurs de saisir les résultats d'un match de manière collaborative et en temps réel. Chaque joueur accède à sa propre page de saisie où il peut modifier ses scores tout en voyant les scores de l'adversaire mis à jour en temps réel.

### Caractéristiques Principales

- **Pages séparées par joueur** : Créateur et adversaire ont des pages dédiées
- **Temps réel** : Polling automatique toutes les 2 secondes
- **Missions complètes** : Primaire, secondaire, péripétie avec textes EN/FR
- **Résultats spéciaux** : Nul, abandon, table rase
- **Missions secondaires** : Fixes ou tactiques avec gestion complète
- **Autorisation stricte** : Chaque joueur ne peut modifier que ses propres scores
- **Sauvegarde automatique** : Brouillons sauvegardés en base de données

---

## Architecture

```
Utilisateur 1 (Créateur)          Utilisateur 2 (Adversaire)
        │                                  │
        ▼                                  ▼
score-creator.blade.php      score-opponent.blade.php
        │                                  │
        └──────────────┬───────────────────┘
                       ▼
            API Endpoints (AJAX)
            - save-draft-scores
            - get-draft-scores
            - save-tactical-state
            - get-tactical-state
                       │
                       ▼
            PlayerMatchController
                       │
                       ▼
            PlayerMatch Model
                       │
                       ▼
            Base de Données (player_matches)
```

---

## Base de Données

### Colonnes Principales pour le Scoring

| Colonne | Type | Description |
|---------|------|-------------|
| `creator_score` | INT | Score total du créateur |
| `opponent_score` | INT | Score total de l'adversaire |
| `creator_primary_points` | INT | Points primaires du créateur (0-50) |
| `creator_secondary_points` | INT | Points secondaires du créateur (0-40) |
| `creator_painting_points` | BOOLEAN | Points peinture du créateur (+10) |
| `opponent_primary_points` | INT | Points primaires de l'adversaire (0-50) |
| `opponent_secondary_points` | INT | Points secondaires de l'adversaire (0-40) |
| `opponent_painting_points` | BOOLEAN | Points peinture de l'adversaire (+10) |
| `draft_scores` | JSON | Brouillon des scores (sauvegarde automatique) |
| `draft_tactical_state_creator` | JSON | État des missions tactiques du créateur |
| `draft_tactical_state_opponent` | JSON | État des missions tactiques de l'adversaire |
| `status` | ENUM | État du match (open, confirmed, completed, cancelled) |
| `winner_id` | BIGINT | ID du gagnant (NULL si nul) |
| `is_draw` | BOOLEAN | Indique si le match est un nul |

### Structure JSON: draft_scores

```json
{
    "creator_primary_points": 25,
    "creator_secondary_points": 15,
    "creator_painting_points": true,
    "opponent_primary_points": 30,
    "opponent_secondary_points": 10,
    "opponent_painting_points": false,
    "secondary_type": "fixed",
    "fixed_mission_1": 5,
    "fixed_mission_2": 8
}
```

---

## Modèles et Relations

### PlayerMatch Model

```php
class PlayerMatch extends Model {
    protected $fillable = [
        'creator_id', 'opponent_id', 'type', 'army_points',
        'faction', 'detachment', 'notes', 'city', 'department',
        'availability_type', 'available_at', 'available_from', 'available_to',
        'status', 'creator_score', 'opponent_score',
        'creator_primary_points', 'creator_secondary_points', 'creator_painting_points',
        'opponent_primary_points', 'opponent_secondary_points', 'opponent_painting_points',
        'creator_victory_points', 'opponent_victory_points',
        'winner_id', 'is_draw', 'played_at',
        'primary_mission_id', 'secondary_mission_id', 'terrain_layout_id', 'twist_mission_id',
        'asymmetric_primary_mission_id', 'deployment_mode', 'setup_mode',
        'is_setup_complete', 'is_setup_validated',
        'draft_scores', 'draft_tactical_state_creator', 'draft_tactical_state_opponent'
    ];
    
    protected $casts = [
        'available_at' => 'datetime',
        'available_from' => 'datetime',
        'available_to' => 'datetime',
        'played_at' => 'datetime',
        'is_draw' => 'boolean',
        'is_setup_complete' => 'boolean',
        'is_setup_validated' => 'boolean',
        'draft_scores' => 'array',
        'draft_tactical_state_creator' => 'array',
        'draft_tactical_state_opponent' => 'array'
    ];
    
    // Relations
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'creator_id'); }
    public function opponent(): BelongsTo { return $this->belongsTo(User::class, 'opponent_id'); }
    public function winner(): BelongsTo { return $this->belongsTo(User::class, 'winner_id'); }
    public function primaryMission(): BelongsTo { return $this->belongsTo(PrimaryMission::class); }
    public function secondaryMission(): BelongsTo { return $this->belongsTo(SecondaryMission::class); }
    public function terrainLayout(): BelongsTo { return $this->belongsTo(TerrainLayout::class); }
    public function twistMission(): BelongsTo { return $this->belongsTo(TwistMission::class); }
}
```

### Relations

```
PlayerMatch
├── creator (User) - Joueur qui a créé le match
├── opponent (User) - Joueur adversaire
├── winner (User) - Gagnant du match
├── primaryMission (PrimaryMission) - Mission primaire
├── secondaryMission (SecondaryMission) - Mission secondaire
├── terrainLayout (TerrainLayout) - Disposition de terrain
└── twistMission (TwistMission) - Péripétie
```

---

## Routes et API

### Routes Web

```php
Route::get('/player-matches/{playerMatch}/score/creator', 
    [PlayerMatchController::class, 'scoreFormCreator'])
    ->name('player-matches.score-creator');

Route::get('/player-matches/{playerMatch}/score/opponent', 
    [PlayerMatchController::class, 'scoreFormOpponent'])
    ->name('player-matches.score-opponent');

Route::post('/player-matches/{playerMatch}/set-score', 
    [PlayerMatchController::class, 'setScore'])
    ->name('player-matches.set-score');
```

### Routes API

```php
Route::post('/api/player-matches/{playerMatch}/save-draft-scores', 
    [PlayerMatchController::class, 'saveDraftScores'])
    ->name('api.player-matches.save-draft-scores');

Route::get('/api/player-matches/{playerMatch}/get-draft-scores', 
    [PlayerMatchController::class, 'getDraftScores'])
    ->name('api.player-matches.get-draft-scores');

Route::post('/api/player-matches/{playerMatch}/save-tactical-state/{side}', 
    [PlayerMatchController::class, 'saveTacticalState'])
    ->name('api.player-matches.save-tactical-state');

Route::get('/api/player-matches/{playerMatch}/get-tactical-state/{side}', 
    [PlayerMatchController::class, 'getTacticalState'])
    ->name('api.player-matches.get-tactical-state');
```

---

## Contrôleurs

### Méthodes Principales

#### scoreFormCreator()
- **Autorisation** : Seul le créateur peut accéder
- **Charge** : primaryMission, terrainLayout, twistMission, creator, opponent
- **Vue** : player-matches.score-creator

#### scoreFormOpponent()
- **Autorisation** : Seul l'adversaire peut accéder
- **Charge** : primaryMission, terrainLayout, twistMission, creator, opponent
- **Vue** : player-matches.score-opponent

#### saveDraftScores()
- **Validation** : Points (0-50 primaires, 0-40 secondaires), booléens peinture
- **Sauvegarde** : JSON dans colonne `draft_scores`
- **Réponse** : JSON success/error

#### getDraftScores()
- **Retour** : JSON des brouillons ou valeurs par défaut
- **Accessible** : Polling toutes les 2 secondes

#### setScore()
- **Validation** : Tous les paramètres requis
- **Calcul** : Totaux automatiques
- **Finalisation** : Status = 'completed', winner déterminé
- **Réponse** : JSON success/error

---

## Vues et Interface

### Fichiers

- `resources/views/player-matches/score-creator.blade.php` - Page créateur
- `resources/views/player-matches/score-opponent.blade.php` - Page adversaire

### Structure Commune

**Colonne Gauche (2/3)** :
- Mission Primaire (EN/FR, texte complet)
- Mission Secondaire (EN/FR, texte complet)
- Péripétie (EN/FR, texte complet)
- Missions Secondaires Sélectionnées (fixes ou tactiques)

**Colonne Droite (1/3)** :
- Résultat du match (radio buttons)
- Type de missions secondaires (fixed/tactical)
- Missions Fixes (2 sélects)
- Missions Tactiques (piochage, défausse, terminées)
- Points Créateur (éditables)
- Points Adversaire (lecture seule)
- Bouton "Fin du match"

---

## JavaScript et Temps Réel

### Mécanisme de Polling

```javascript
// Polling automatique toutes les 2 secondes
setInterval(() => {
    loadOpponentScores();
}, 2000);
```

### Fonctions Clés

**loadSavedData()** - Charge tous les scores au démarrage
**loadOpponentScores()** - Charge SEULEMENT les scores de l'adversaire (polling)
**saveScoringData()** - Sauvegarde avec debounce (2 secondes)
**updateTotals()** - Calcule les totaux automatiquement
**validateForm()** - Valide les limites de points
**incrementPoints()** / **decrementPoints()** - Boutons +/-

### Debouncing

```javascript
let saveScoringTimeout;

function saveScoringData() {
    clearTimeout(saveScoringTimeout);
    saveScoringTimeout = setTimeout(() => {
        // Appel API
    }, 2000);
}
```

---

## CSS et Styling

### Classes Principales

```css
/* Masquer les boutons pour les scores en lecture seule */
.opponent-readonly-buttons button {
    display: none !important;
}

.creator-readonly-buttons button {
    display: none !important;
}

/* Couleurs des sections */
.bg-red-50 { /* Scores du créateur */ }
.bg-blue-50 { /* Scores de l'adversaire */ }

/* Responsive */
.lg:col-span-2 { /* Missions sur desktop */ }
.lg:col-span-1 { /* Formulaire sur desktop */ }
```

### Tailwind Utilities

- `grid grid-cols-1 lg:grid-cols-3` - Layout responsive
- `space-y-4` - Espacement vertical
- `border-2 border-red-200` - Bordures colorées
- `max-h-96 overflow-y-auto` - Scrolling pour textes longs
- `flex items-center gap-2` - Boutons +/-

---

## Flux Utilisateur

### Étapes Complètes

1. **Accès** : Utilisateur clique sur "Saisir le score"
2. **Chargement** : Page charge les missions et brouillons existants
3. **Saisie** : Utilisateur remplit les scores
4. **Sauvegarde Auto** : Scores sauvegardés toutes les 2 secondes
5. **Polling** : Scores de l'adversaire mis à jour toutes les 2 secondes
6. **Finalisation** : Utilisateur clique "Fin du match"
7. **Validation** : Scores finalisés, match marqué comme completed

---

## Sécurité et Autorisation

### Vérifications

- **scoreFormCreator()** : `Auth::id() === $playerMatch->creator_id`
- **scoreFormOpponent()** : `Auth::id() === $playerMatch->opponent_id`
- **setScore()** : Utilisateur est creator OU opponent

### Protections

- Scores adversaire en `readonly`
- Boutons +/- masqués pour scores non-éditables
- Validation côté serveur de tous les paramètres
- CSRF token requis pour toutes les requêtes POST

---

## Calcul des Scores

### Formule

```
Score Total = Points Primaires + Points Secondaires + Points Peinture

Où :
- Points Primaires : 0-50
- Points Secondaires : 0-40
- Points Peinture : 0 ou 10 (booléen)

Exemple :
Score = 25 + 15 + 10 = 50 points
```

### Détermination du Gagnant

- **Normal** : Score le plus élevé gagne
- **Nul** : Scores égaux = match nul
- **Abandon** : Autre joueur gagne
- **Table Rase** : Autre joueur gagne

---

## Fichiers Impliqués

```
app/
├── Http/Controllers/PlayerMatchController.php
├── Models/PlayerMatch.php
└── Services/PlayerMatchService.php

routes/
└── web.php

resources/views/player-matches/
├── score-creator.blade.php
├── score-opponent.blade.php
└── index.blade.php

database/migrations/
└── 2025_11_06_add_detailed_scores_to_player_matches.php
```

---

## Dépannage

### Problème : Scores ne se sauvegardent pas
- Vérifier le token CSRF
- Vérifier la console pour erreurs API
- Vérifier les permissions utilisateur

### Problème : Polling ne fonctionne pas
- Vérifier que l'intervalle est défini (2000ms)
- Vérifier que l'API retourne les bonnes données
- Vérifier la connexion réseau

### Problème : Boutons +/- visibles pour adversaire
- Vérifier la classe CSS `opponent-readonly-buttons`
- Vérifier que le CSS est compilé (npm run build)

---

**Dernière mise à jour** : 6 novembre 2025
**Version** : 1.0
**Statut** : Production
