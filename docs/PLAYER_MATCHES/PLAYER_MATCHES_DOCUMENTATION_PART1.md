# 📖 DOCUMENTATION COMPLÈTE - MATCHS SIMPLES (PART 1/2)

**Version**: 1.0 | **Date**: 2025-11-05 | **Statut**: ✅ Documentation de référence

---

## 📋 TABLE DES MATIÈRES

1. Vue d'ensemble
2. Structure de base
3. Flux de vie d'un match
4. Logique métier
5. Modèles et relations

---

## 🎯 VUE D'ENSEMBLE

### Qu'est-ce qu'un match simple ?

Un **match simple (PlayerMatch)** est un match entre deux joueurs créé directement par un utilisateur, sans passer par un tournoi.

### Caractéristiques principales

- ✅ Créé par un utilisateur (créateur)
- ✅ Rejoint par un autre utilisateur (adversaire)
- ✅ Configuration manuelle ou aléatoire
- ✅ Scoring et résultats sauvegardés
- ✅ Système de demandes de participation

---

## 🏗️ STRUCTURE DE BASE

### Table `player_matches` - Colonnes essentielles

```sql
-- Participants
creator_id BIGINT UNSIGNED NOT NULL
opponent_id BIGINT UNSIGNED NULL

-- Statut
status VARCHAR(255) NOT NULL              -- 'open', 'confirmed', 'completed', 'cancelled'
is_setup_validated BOOLEAN DEFAULT FALSE

-- Configuration
primary_mission_id BIGINT UNSIGNED NULL
terrain_layout_id BIGINT UNSIGNED NULL
twist_mission_id BIGINT UNSIGNED NULL
asymmetric_primary_mission_id BIGINT UNSIGNED NULL
deployment_mode VARCHAR(255) NULL

-- État de la configuration
setup_mode VARCHAR(255) NULL              -- 'random' ou 'manual'
is_setup_complete BOOLEAN DEFAULT FALSE

-- Scores
creator_score INT DEFAULT 0
opponent_score INT DEFAULT 0
creator_victory_points INT DEFAULT 0
opponent_victory_points INT DEFAULT 0
winner_id BIGINT UNSIGNED NULL
is_draw BOOLEAN DEFAULT FALSE

-- Brouillons
draft_scores JSON NULL
draft_tactical_state_creator JSON NULL
draft_tactical_state_opponent JSON NULL
```

### Colonnes clés

| Colonne | Type | Description |
|---------|------|-------------|
| `creator_id` | FK | Créateur du match |
| `opponent_id` | FK | Adversaire (NULL si pas rejoint) |
| `status` | ENUM | État: 'open', 'confirmed', 'completed' |
| `is_setup_validated` | BOOL | Configuration validée |
| `deployment_mode` | VARCHAR | Mode de déploiement |
| `draft_scores` | JSON | Brouillon des scores |

---

## 🔄 FLUX DE VIE D'UN MATCH

### État 1: CRÉATION (open + is_setup_validated=false)

```
Créateur crée un match
    ↓
Status: 'open'
is_setup_validated: false
opponent_id: NULL
    ↓
Affichage: "Configuration en cours"
```

**Actions possibles**:
- ✅ Configurer le match (manuel ou aléatoire)
- ✅ Valider la configuration
- ✅ Annuler le match

**Restrictions**:
- ❌ Adversaire ne peut pas rejoindre
- ❌ Scores ne peuvent pas être saisis

---

### État 2: OUVERT (open + is_setup_validated=true)

```
Créateur valide la configuration
    ↓
Status: 'open'
is_setup_validated: true
opponent_id: NULL
    ↓
Affichage: "Ouvert"
```

**Actions possibles**:
- ✅ Autres joueurs peuvent demander à rejoindre
- ✅ Créateur peut accepter/refuser les demandes
- ✅ Créateur peut modifier la configuration

**Restrictions**:
- ❌ Scores ne peuvent pas être saisis
- ❌ Match ne peut pas être joué

---

### État 3: CONFIRMÉ (confirmed)

```
Créateur accepte une demande de participation
    ↓
Status: 'confirmed'
opponent_id: <id du demandeur>
is_setup_validated: true
    ↓
Affichage: "Confirmé"
```

**Actions possibles**:
- ✅ Créateur peut saisir les scores
- ✅ Adversaire peut voir les données
- ✅ Scores sauvegardés progressivement

**Restrictions**:
- ❌ Configuration ne peut pas être modifiée
- ❌ Adversaire ne peut pas rejoindre

---

### État 4: TERMINÉ (completed)

```
Créateur saisit les scores finaux
    ↓
Status: 'completed'
winner_id: <id du gagnant>
    ↓
Affichage: "Terminé"
```

**Actions possibles**:
- ✅ Voir le résumé du match
- ✅ Voir le gagnant
- ✅ Voir les scores finaux

**Restrictions**:
- ❌ Aucune modification possible

---

## 💼 LOGIQUE MÉTIER

### 1. Création d'un match

**Qui peut créer?**
- ✅ Tout utilisateur authentifié

**Conditions**:
- Créateur doit avoir au moins 1 armée configurée
- Faction doit être spécifiée
- Points d'armée doivent être valides

**Résultat**:
```
Status: 'open'
is_setup_validated: false
opponent_id: NULL
```

---

### 2. Validation de la configuration

**Qui peut valider?**
- ✅ Uniquement le créateur

**Conditions**:
- Configuration complète (mission, terrain, péripétie, déploiement)
- Mode de configuration défini (manual ou random)

**Résultat**:
```
Status: 'open'
is_setup_validated: true
```

**Affichage**: Le match passe de "Configuration en cours" à "Ouvert"

---

### 3. Demande de participation

**Qui peut demander?**
- ✅ Tout utilisateur SAUF le créateur

**Conditions**:
- Status = 'open'
- is_setup_validated = true
- opponent_id = NULL
- Match disponible (date pas expirée)

**Résultat**:
- Création d'une `PlayerMatchRequest`
- Notification au créateur

---

### 4. Acceptation d'une demande

**Qui peut accepter?**
- ✅ Uniquement le créateur

**Conditions**:
- Demande existe
- Status = 'open'

**Résultat**:
```
Status: 'confirmed'
opponent_id: <id du demandeur>
PlayerMatchRequest.status: 'accepted'
```

---

### 5. Saisie des scores

**Qui peut saisir?**
- ✅ Uniquement le créateur

**Conditions**:
- Status = 'confirmed'
- opponent_id ≠ NULL

**Processus**:
1. Créateur saisit les scores
2. Scores sauvegardés dans `draft_scores` (brouillon)
3. Adversaire peut voir les scores en temps réel
4. Créateur peut modifier jusqu'à finalisation

---

### 6. Finalisation du match

**Qui peut finaliser?**
- ✅ Uniquement le créateur

**Conditions**:
- Status = 'confirmed'
- Scores saisis

**Résultat**:
```
Status: 'completed'
winner_id: <id du gagnant ou NULL si nul>
is_draw: true/false
played_at: <timestamp>
```

---

## 📊 MODÈLES ET RELATIONS

### Modèle PlayerMatch

**Fichier**: `app/Models/PlayerMatch.php`

**Attributs fillable**:
```php
protected $fillable = [
    'creator_id', 'opponent_id', 'type', 'army_points', 'faction',
    'detachment', 'notes', 'city', 'department', 'availability_type',
    'available_at', 'available_from', 'available_to', 'status',
    'creator_score', 'opponent_score', 'creator_victory_points',
    'opponent_victory_points', 'winner_id', 'is_draw', 'played_at',
    'primary_mission_id', 'secondary_mission_id', 'terrain_layout_id',
    'twist_mission_id', 'asymmetric_primary_mission_id', 'deployment_mode',
    'setup_mode', 'is_setup_complete', 'is_setup_validated',
    'draft_scores', 'draft_tactical_state_creator', 'draft_tactical_state_opponent',
];
```

**Casts**:
```php
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
    'draft_tactical_state_opponent' => 'array',
];
```

### Relations principales

```php
// Créateur du match
public function creator(): BelongsTo
{
    return $this->belongsTo(User::class, 'creator_id');
}

// Adversaire du match
public function opponent(): BelongsTo
{
    return $this->belongsTo(User::class, 'opponent_id');
}

// Gagnant du match
public function winner(): BelongsTo
{
    return $this->belongsTo(User::class, 'winner_id');
}

// Mission primaire
public function primaryMission(): BelongsTo
{
    return $this->belongsTo(PrimaryMission::class);
}

// Disposition de terrain
public function terrainLayout(): BelongsTo
{
    return $this->belongsTo(TerrainLayout::class);
}

// Péripétie
public function twistMission(): BelongsTo
{
    return $this->belongsTo(TwistMission::class);
}

// Mission primaire asymétrique
public function asymmetricPrimaryMission(): BelongsTo
{
    return $this->belongsTo(AsymmetricPrimaryMission::class);
}

// Demandes de participation
public function requests()
{
    return $this->hasMany(PlayerMatchRequest::class);
}
```

### Modèle PlayerMatchRequest

**Fichier**: `app/Models/PlayerMatchRequest.php`

**Attributs**:
```php
protected $fillable = [
    'player_match_id',
    'requester_id',
    'faction',
    'detachment',
    'message',
    'status',              // 'pending', 'accepted', 'rejected'
    'creator_response',
];
```

**Relations**:
```php
public function playerMatch()
{
    return $this->belongsTo(PlayerMatch::class);
}

public function requester()
{
    return $this->belongsTo(User::class, 'requester_id');
}
```

---

## 🛣️ ROUTES PRINCIPALES

**Fichier**: `routes/web.php`

```php
// Affichage
Route::get('/player-matches', [PlayerMatchController::class, 'index'])->name('player-matches.index');
Route::get('/player-matches/{match}', [PlayerMatchController::class, 'show'])->name('player-matches.show');

// Configuration
Route::get('/player-matches/{match}/setup', [MatchSetupController::class, 'showPlayerMatch'])->name('player-matches.setup');
Route::post('/player-matches/{match}/setup', [MatchSetupController::class, 'updatePlayerMatch'])->name('player-matches.setup.update');
Route::post('/player-matches/{match}/randomize', [MatchSetupController::class, 'randomizePlayerMatch'])->name('player-matches.randomize');
Route::post('/player-matches/{match}/validate-setup', [PlayerMatchController::class, 'validateSetup'])->name('player-matches.validate-setup');

// Résumé et scores
Route::get('/player-matches/{match}/summary', [PlayerMatchController::class, 'summary'])->name('player-matches.summary');
Route::post('/player-matches/{match}/set-score', [PlayerMatchController::class, 'setScore'])->name('player-matches.set-score');
Route::post('/player-matches/{match}/complete', [PlayerMatchController::class, 'complete'])->name('player-matches.complete');

// Demandes
Route::post('/player-matches/{match}/request', [PlayerMatchRequestController::class, 'store'])->name('player-matches.request.store');
Route::post('/player-match-requests/{request}/accept', [PlayerMatchRequestController::class, 'accept'])->name('player-match-requests.accept');
Route::post('/player-match-requests/{request}/reject', [PlayerMatchRequestController::class, 'reject'])->name('player-match-requests.reject');
```

---

## 🔐 PERMISSIONS PAR ACTION

| Action | Créateur | Adversaire | Autre |
|--------|----------|-----------|-------|
| Voir le match | ✅ | ✅ | ❌ |
| Configurer | ✅ | ❌ | ❌ |
| Valider config | ✅ | ❌ | ❌ |
| Accepter demande | ✅ | ❌ | ❌ |
| Saisir scores | ✅ | ❌ | ❌ |
| Voir scores | ✅ | ✅ | ❌ |
| Modifier | ✅ | ❌ | ❌ |
| Supprimer | ✅ | ❌ | ❌ |

---

**→ Voir PART 2 pour: Services, Vues, Configuration, Scoring, Checklist**
