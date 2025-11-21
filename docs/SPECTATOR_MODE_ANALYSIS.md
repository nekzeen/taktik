# 👁️ MODE SPECTATEUR - ANALYSE COMPLÈTE

**Date**: 21 novembre 2025  
**Demande**: Implémenter un mode spectateur pour matchs simples et tournois  
**Statut**: Analyse en cours - Aucune implémentation effectuée  

---

## 🎯 OBJECTIF

Permettre aux **joueurs non-participants** et **visiteurs non-inscrits** de suivre un match en cours avec :
- ✅ Résultats en temps réel (décalage 2 secondes)
- ✅ Mission primaire et péripétie
- ✅ Missions secondaires actives (fixes ou tactiques)
- ✅ Déploiement (nom + image)
- ✅ Disposition de terrain (nom + image)

---

## 📊 RÉSUMÉ EXÉCUTIF

### ✅ FAISABLE
- Architecture existante supporte déjà le polling temps réel (2 secondes)
- Données complètes disponibles en base de données
- Système de missions et déploiement déjà implémenté
- Images terrain et déploiement déjà stockées

### ⚠️ DÉFIS
- Sécurité : Accès public vs joueurs authentifiés
- Performance : Polling pour potentiellement N spectateurs
- Données : Décalage 2 secondes à implémenter

### 🚀 COMPLEXITÉ
- **Faible** : Réutilise 80% du code existant
- **Temps estimé** : 1-2 jours (2 pages + 2 endpoints API)

---

## 🏗️ ARCHITECTURE ACTUELLE

### Matchs Simples (PlayerMatch)

**Modèle** :
```php
PlayerMatch {
    creator_id, opponent_id,
    primary_mission_id, secondary_mission_id, twist_mission_id,
    terrain_layout_id, deployment_mode, asymmetric_primary_mission_id,
    creator_score, opponent_score,
    creator_primary_points, creator_secondary_points, creator_painting_points,
    opponent_primary_points, opponent_secondary_points, opponent_painting_points,
    draft_scores, draft_tactical_state_creator, draft_tactical_state_opponent,
    status (open|confirmed|completed|cancelled)
}
```

**Relations** :
- `creator()` → User
- `opponent()` → User
- `primaryMission()` → PrimaryMission
- `secondaryMission()` → SecondaryMission
- `twistMission()` → TwistMission
- `terrainLayout()` → TerrainLayout
- `asymmetricPrimaryMission()` → AsymmetricPrimaryMission

**Pages Actuelles** :
- `/player-matches` - Liste (index)
- `/player-matches/{id}` - Détails (show)
- `/player-matches/{id}/score/creator` - Scoring créateur
- `/player-matches/{id}/score/opponent` - Scoring adversaire

**Données de Scoring** :
```json
draft_scores: {
    "creator_primary_points": 25,
    "creator_secondary_points": 15,
    "creator_painting_points": true,
    "opponent_primary_points": 30,
    "opponent_secondary_points": 10,
    "opponent_painting_points": false,
    "secondary_type": "fixed|tactical",
    "fixed_mission_1": 5,
    "fixed_mission_2": 8,
    "tactical_deck": [...],
    "tactical_discarded": [...],
    "tactical_completed": [...]
}
```

---

### Matchs de Tournoi (TournamentMatch)

**Modèle** :
```php
TournamentMatch {
    tournament_id, player1_id, player2_id,
    primary_mission_id, secondary_mission_id, twist_mission_id,
    terrain_layout_id, deployment_mode, asymmetric_primary_mission_id,
    player1_score, player2_score,
    player1_primary_points, player1_secondary_points, player1_painting_points,
    player2_primary_points, player2_secondary_points, player2_painting_points,
    draft_scores, draft_tactical_state_player1, draft_tactical_state_player2,
    status (pending|in_progress|completed|cancelled)
}
```

**Relations** :
- `tournament()` → Tournament
- `player1()` → User
- `player2()` → User
- `primaryMission()` → PrimaryMission
- `secondaryMission()` → SecondaryMission
- `twistMission()` → TwistMission
- `terrainLayout()` → TerrainLayout
- `asymmetricPrimaryMission()` → AsymmetricPrimaryMission

**Pages Actuelles** :
- `/tournaments/{id}/matches` - Liste des matchs
- `/tournaments/{id}/matches/{matchId}` - Détails du match
- `/tournaments/{id}/matches/{matchId}/score/player1` - Scoring joueur 1
- `/tournaments/{id}/matches/{matchId}/score/player2` - Scoring joueur 2

---

## 🔍 ANALYSE DÉTAILLÉE

### 1. DONNÉES DISPONIBLES

#### Pour Matchs Simples
```
✅ Créateur : User.name, User.avatar
✅ Adversaire : User.name, User.avatar
✅ Mission Primaire : PrimaryMission.name_fr, description_fr, full_text_fr
✅ Péripétie : TwistMission.name_fr, description_fr, full_text_fr
✅ Missions Secondaires : SecondaryMission.name_fr, description_fr (fixes)
✅ Missions Tactiques : draft_tactical_state_creator/opponent (JSON)
✅ Déploiement : deployment_mode (enum)
✅ Disposition Terrain : TerrainLayout.name_fr, image_url
✅ Scores : creator_score, opponent_score (polling toutes les 2s)
✅ Détails Scores : draft_scores (JSON avec détails)
```

#### Pour Matchs de Tournoi
```
✅ Joueur 1 : User.name, User.avatar
✅ Joueur 2 : User.name, User.avatar
✅ Mission Primaire : PrimaryMission.name_fr, description_fr, full_text_fr
✅ Péripétie : TwistMission.name_fr, description_fr, full_text_fr
✅ Missions Secondaires : SecondaryMission.name_fr, description_fr (fixes)
✅ Missions Tactiques : draft_tactical_state_player1/player2 (JSON)
✅ Déploiement : deployment_mode (enum)
✅ Disposition Terrain : TerrainLayout.name_fr, image_url
✅ Scores : player1_score, player2_score (polling toutes les 2s)
✅ Détails Scores : draft_scores (JSON avec détails)
```

### 2. SÉCURITÉ & ACCÈS

#### Matchs Simples
- **Status 'confirmed'** : Visible à tous (spectateurs + visiteurs)
- **Status 'completed'** : Visible à tous (historique)
- **Status 'open'** : Visible à tous (avant confirmation)
- **Scores** : Visibles uniquement après `status = 'confirmed'`

#### Matchs de Tournoi
- **Status 'in_progress'** : Visible à tous (spectateurs + visiteurs)
- **Status 'completed'** : Visible à tous (historique)
- **Status 'pending'** : Visible à tous (avant démarrage)
- **Scores** : Visibles uniquement après `status = 'in_progress'`

### 3. DÉCALAGE 2 SECONDES

**Implémentation** :
```javascript
// Endpoint API retourne les scores avec timestamp
GET /api/player-matches/{id}/spectator-scores
{
    "creator_score": 45,
    "opponent_score": 38,
    "updated_at": "2025-11-21 14:30:00",
    "delay_until": "2025-11-21 14:30:02"  // +2 secondes
}

// Frontend attend avant d'afficher
const response = await fetch(...);
const now = new Date();
const delayUntil = new Date(response.delay_until);
const wait = Math.max(0, delayUntil - now);
setTimeout(() => updateScores(), wait);
```

---

## 📋 FICHIERS À CRÉER/MODIFIER

### 1. CONTRÔLEURS

#### Nouveau : `SpectatorMatchController.php`
```php
class SpectatorMatchController extends Controller {
    // Matchs simples
    public function showPlayerMatch(PlayerMatch $playerMatch)
    public function getPlayerMatchScores(PlayerMatch $playerMatch)
    
    // Matchs tournoi
    public function showTournamentMatch(Tournament $tournament, TournamentMatch $match)
    public function getTournamentMatchScores(Tournament $tournament, TournamentMatch $match)
}
```

### 2. ROUTES

```php
// Routes publiques (pas d'authentification requise)
Route::get('/player-matches/{playerMatch}/spectate', 
    [SpectatorMatchController::class, 'showPlayerMatch'])
    ->name('player-matches.spectate');

Route::get('/api/player-matches/{playerMatch}/spectator-scores', 
    [SpectatorMatchController::class, 'getPlayerMatchScores'])
    ->name('api.player-matches.spectator-scores');

Route::get('/tournaments/{tournament}/matches/{match}/spectate', 
    [SpectatorMatchController::class, 'showTournamentMatch'])
    ->name('tournaments.matches.spectate');

Route::get('/api/tournaments/{tournament}/matches/{match}/spectator-scores', 
    [SpectatorMatchController::class, 'getTournamentMatchScores'])
    ->name('api.tournaments.matches.spectator-scores');
```

### 3. VUES

#### Matchs Simples
- `resources/views/player-matches/spectate.blade.php` - Page spectateur

#### Matchs Tournoi
- `resources/views/tournaments/matches/spectate.blade.php` - Page spectateur

### 4. JAVASCRIPT

Réutiliser le système de polling existant (2 secondes) avec décalage.

---

## 🎨 LAYOUT PAGE SPECTATEUR

### Matchs Simples

```
┌─────────────────────────────────────────────────────────────┐
│  👁️ MODE SPECTATEUR - Match Simple                          │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │ 🎯 SCORES EN TEMPS RÉEL (Décalage 2s)                  │ │
│  ├─────────────────────────────────────────────────────────┤ │
│  │ Créateur: 45 pts  |  Adversaire: 38 pts                │ │
│  │ • Primaire: 25    |  • Primaire: 30                    │ │
│  │ • Secondaire: 15  |  • Secondaire: 10                  │ │
│  │ • Peinture: +5    |  • Peinture: -                     │ │
│  └─────────────────────────────────────────────────────────┘ │
│                                                               │
│  ┌──────────────────────┐  ┌──────────────────────┐          │
│  │ 🎲 MISSION PRIMAIRE  │  │ 🌪️ PÉRIPÉTIE         │          │
│  ├──────────────────────┤  ├──────────────────────┤          │
│  │ LINCHPIN             │  │ AMBUSH               │          │
│  │ [Texte complet...]   │  │ [Texte complet...]   │          │
│  └──────────────────────┘  └──────────────────────┘          │
│                                                               │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │ 📋 MISSIONS SECONDAIRES EN COURS                        │ │
│  ├─────────────────────────────────────────────────────────┤ │
│  │ ✓ BREAK THROUGH (Fixe)                                 │ │
│  │   [Texte complet...]                                    │ │
│  │                                                          │ │
│  │ ✓ ASSASSINATE (Fixe)                                   │ │
│  │   [Texte complet...]                                    │ │
│  │                                                          │ │
│  │ 🎴 Missions Tactiques:                                 │ │
│  │   • En main: 3 cartes                                   │ │
│  │   • Défaussées: 2 cartes                                │ │
│  │   • Complétées: 1 carte                                 │ │
│  └─────────────────────────────────────────────────────────┘ │
│                                                               │
│  ┌──────────────────────┐  ┌──────────────────────┐          │
│  │ 🎯 DÉPLOIEMENT       │  │ 🗺️ DISPOSITION       │          │
│  ├──────────────────────┤  ├──────────────────────┤          │
│  │ Hammer and Anvil     │  │ Terrain 1            │          │
│  │ [Image]              │  │ [Image]              │          │
│  └──────────────────────┘  └──────────────────────┘          │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

### Matchs Tournoi

Identique, mais avec "Joueur 1" et "Joueur 2" au lieu de "Créateur" et "Adversaire".

---

## 🔗 INTÉGRATION AUX LISTES

### Matchs Simples (`/player-matches`)

**Colonne Actions** :
```
Avant: [Voir] [Configurer] [Saisir score]
Après: [Voir] [👁️ Spectate] [Configurer] [Saisir score]
```

**Condition d'affichage** :
- Bouton visible si `status = 'confirmed'` ou `status = 'completed'`
- Accessible à tous (pas d'authentification requise)

### Matchs Tournoi (Cartes)

**Bouton Spectateur** :
```
Avant: [Voir] [Configurer] [Saisir score]
Après: [Voir] [👁️ Spectate] [Configurer] [Saisir score]
```

**Condition d'affichage** :
- Bouton visible si `status = 'in_progress'` ou `status = 'completed'`
- Accessible à tous (pas d'authentification requise)

---

## 📊 DONNÉES TEMPS RÉEL

### Polling Toutes les 2 Secondes

**Endpoint** :
```
GET /api/player-matches/{id}/spectator-scores
GET /api/tournaments/{tournament}/matches/{match}/spectator-scores
```

**Réponse** :
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
    "draft_scores": {...},
    "draft_tactical_state_creator": {...},
    "draft_tactical_state_opponent": {...},
    "updated_at": "2025-11-21 14:30:00",
    "delay_until": "2025-11-21 14:30:02"
}
```

---

## 🔒 SÉCURITÉ

### Accès Public
- ✅ Pas d'authentification requise
- ✅ Vérifier que le match existe
- ✅ Vérifier que le match est `confirmed` ou `in_progress`
- ✅ Afficher les scores uniquement si le match est en cours

### Données Sensibles
- ❌ Pas d'accès aux emails
- ❌ Pas d'accès aux données personnelles
- ❌ Pas d'accès aux listes d'armée
- ✅ Afficher uniquement les scores et missions

---

## 📈 PERFORMANCE

### Optimisations
- ✅ Eager loading des relations (creator, opponent, missions)
- ✅ Cache des données statiques (missions, terrains)
- ✅ Polling côté client (pas de websocket)
- ✅ Décalage 2 secondes côté serveur

### Considérations
- Potentiellement N spectateurs par match
- Chaque spectateur fait un polling toutes les 2 secondes
- Impact minimal (requête GET légère)

---

## 🚀 PLAN D'IMPLÉMENTATION

### Phase 1 : Matchs Simples (1 jour)
1. Créer `SpectatorMatchController`
2. Créer endpoint API `/api/player-matches/{id}/spectator-scores`
3. Créer vue `player-matches/spectate.blade.php`
4. Ajouter routes
5. Ajouter bouton "👁️ Spectate" à la liste

### Phase 2 : Matchs Tournoi (1 jour)
1. Créer endpoints API pour tournois
2. Créer vue `tournaments/matches/spectate.blade.php`
3. Ajouter routes
4. Ajouter bouton "👁️ Spectate" aux cartes

### Phase 3 : Tests & Optimisations (0.5 jour)
1. Tester polling 2 secondes
2. Tester accès public
3. Tester avec plusieurs spectateurs

---

## ✅ CHECKLIST AVANT IMPLÉMENTATION

- [ ] Vérifier que les données sont complètes en base
- [ ] Vérifier que les images terrain existent
- [ ] Vérifier que les traductions FR existent
- [ ] Vérifier que le polling 2 secondes fonctionne
- [ ] Vérifier que les statuts match sont corrects
- [ ] Tester l'accès public (sans authentification)
- [ ] Tester le décalage 2 secondes
- [ ] Tester avec plusieurs spectateurs simultanés

---

## 📝 NOTES

- Réutiliser le système de polling existant (déjà implémenté pour les joueurs)
- Réutiliser les vues de scoring (adapter pour lecture seule)
- Réutiliser les styles Tailwind existants
- Pas de modification de la base de données requise
- Pas de modification des modèles existants requise

---

**Statut** : Analyse complète - Prêt pour implémentation
**Complexité** : Faible
**Temps estimé** : 1-2 jours
**Dépendances** : Aucune
