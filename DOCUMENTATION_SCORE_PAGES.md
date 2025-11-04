# Documentation Complète - Pages de Scoring

## 📋 Vue d'ensemble

Ce document décrit le fonctionnement complet des deux pages de scoring pour les matchs Warhammer 40K :
- **Page Créateur** : `/player-matches/{id}/score` (test-score.blade.php)
- **Page Adversaire** : `/player-matches/{id}/view-score` (view-score.blade.php)

---

## 🎯 Page Créateur : `/player-matches/8/score`

### Fichier : `resources/views/player-matches/test-score.blade.php`

### Fonctionnalités Principales

#### 1. **Formulaire de Scoring**
- Saisie des points pour le créateur et l'adversaire
- Points Primaires : 0-50 (max)
- Points Secondaires : 0-40 (max)
- Peinture : Checkbox (10 points si coché)
- Boutons +/- pour l'incrémentation mobile-friendly

#### 2. **Sauvegarde Automatique des Scores**
- **Déclenchement** : Changement de valeur dans les champs de saisie
- **Délai** : 2 secondes (debouncing)
- **Destination** : Base de données (`draft_scores` colonne JSON)
- **API** : `POST /api/player-matches/{id}/save-draft-scores`
- **Données sauvegardées** :
  ```json
  {
    "creator_primary_points": 40,
    "creator_secondary_points": 20,
    "creator_painting_points": true,
    "opponent_primary_points": 47,
    "opponent_secondary_points": 15,
    "opponent_painting_points": false,
    "secondary_type": "tactical",
    "fixed_mission_1": null,
    "fixed_mission_2": null
  }
  ```

#### 3. **Gestion des Missions Secondaires**

##### Type de Missions
- **Radio Buttons** : "Missions Fixes" ou "Missions Tactiques"
- **Sauvegarde** : Automatique via `saveScoringData()`
- **Chargement** : Au démarrage via `loadSavedData()`

##### Missions Fixes
- **Sélection** : 2 dropdowns pour choisir les missions
- **Validation** : Pas de doublons (warning si identiques)
- **Sauvegarde** : `fixed_mission_1`, `fixed_mission_2` dans `draft_scores`
- **Affichage** : Section "Missions Secondaires Sélectionnées"

##### Missions Tactiques
- **Piocher** : Bouton "🎲 Piocher 2 missions" appelle `drawTacticalMissions()`
- **Défausser** : Bouton "Défausser" appelle `discardMission(missionId)`
- **Terminer** : Bouton "Terminer" appelle `completeMission(missionId)`
- **Remplacer** : Bouton "Générer Remplacement" appelle `generateReplacementMission(waitingId)`
- **Sauvegarde** : Automatique via `saveTacticalState()` après chaque action
- **API** : `POST /api/player-matches/{id}/save-tactical-state/creator`
- **Données** :
  ```json
  {
    "active": [{"id": 86, "name_en": "...", "name_fr": "...", ...}],
    "discarded": [...],
    "completed": [...],
    "waitingReplacement": [{"id": "waiting_...", "status": "waiting"}]
  }
  ```

### Flux de Données

```
Utilisateur saisit scores
    ↓
saveScoringData() déclenché
    ↓
Délai 2 secondes (debouncing)
    ↓
API POST /api/player-matches/{id}/save-draft-scores
    ↓
Base de données : draft_scores (JSON)
    ↓
Chargement au démarrage : loadSavedData()
    ↓
Restauration des valeurs
```

### Chargement au Démarrage

1. `loadSavedData()` → Charge les scores depuis la base
2. `loadTacticalStateFromDb()` → Charge l'état tactique
3. `toggleSecondaryType(secondaryType)` → Affiche la bonne section
4. `updateFixedMissions()` → Met à jour les missions fixes
5. `updateTacticalDisplay()` → Affiche les missions tactiques

### Fonctions Clés

| Fonction | Déclenchement | Action |
|----------|---------------|--------|
| `saveScoringData()` | Changement de valeur | Sauvegarde scores + type + missions fixes |
| `loadSavedData()` | DOMContentLoaded | Charge scores depuis API |
| `saveTacticalState()` | Après action tactique | Sauvegarde état tactique |
| `loadTacticalStateFromDb()` | DOMContentLoaded | Charge état tactique |
| `toggleSecondaryType(type)` | Changement radio | Affiche/cache sections |
| `drawTacticalMissions()` | Clic bouton "Piocher" | Pioche 2 missions aléatoires |
| `discardMission(id)` | Clic bouton "Défausser" | Défausse mission + ajoute à waitingReplacement |
| `completeMission(id)` | Clic bouton "Terminer" | Termine mission + génère remplacement |
| `generateReplacementMission(id)` | Clic bouton "Générer" | Génère nouvelle mission |

---

## 👁️ Page Adversaire : `/player-matches/8/view-score`

### Fichier : `resources/views/player-matches/view-score.blade.php`

### Fonctionnalités Principales

#### 1. **Affichage des Scores Temporaires (Polling)**
- **Affichage** : Section "📊 Scores Temporaires" en haut de page
- **Mise à jour** : Toutes les 2 secondes
- **Données affichées** :
  - Créateur : Primaire, Secondaire, Total (avec peinture)
  - Adversaire : Primaire, Secondaire, Total (avec peinture)
- **Responsive** : 
  - Mobile : Affichage empilé (1 colonne)
  - Desktop : Affichage côte à côte (2 colonnes)

#### 2. **Gestion des Missions Secondaires**
- **Même fonctionnalité** que la page créateur
- **Sauvegarde** : `draft_tactical_state_opponent` (colonne JSON)
- **API** : `POST/GET /api/player-matches/{id}/save-tactical-state/opponent`

### Flux de Données

```
Page Créateur : Sauvegarde scores
    ↓
Base de données : draft_scores
    ↓
Page Adversaire : Polling toutes les 2 secondes
    ↓
API GET /api/player-matches/{id}/get-draft-scores
    ↓
Affichage en temps réel
```

### Fonctions Clés

| Fonction | Déclenchement | Action |
|----------|---------------|--------|
| `updateScoresRealtime()` | Polling (2s) | Récupère scores et affiche |
| `startPolling()` | DOMContentLoaded | Lance polling |
| `stopPolling()` | beforeunload | Arrête polling |
| `loadSavedData()` | DOMContentLoaded | Charge missions fixes |
| `loadTacticalState()` | DOMContentLoaded | Charge missions tactiques |
| `saveTacticalState()` | Après action tactique | Sauvegarde état tactique |

---

## 🗄️ Base de Données

### Table : `player_matches`

| Colonne | Type | Description |
|---------|------|-------------|
| `draft_scores` | JSON | Scores temporaires (créateur + adversaire + type + missions fixes) |
| `draft_tactical_state_creator` | JSON | État tactique du créateur |
| `draft_tactical_state_opponent` | JSON | État tactique de l'adversaire |

### Structure `draft_scores`
```json
{
  "creator_primary_points": 40,
  "creator_secondary_points": 20,
  "creator_painting_points": true,
  "opponent_primary_points": 47,
  "opponent_secondary_points": 15,
  "opponent_painting_points": false,
  "secondary_type": "tactical",
  "fixed_mission_1": 5,
  "fixed_mission_2": 12
}
```

### Structure `draft_tactical_state_creator/opponent`
```json
{
  "active": [
    {
      "id": 86,
      "name_en": "DISPLAY OF MIGHT",
      "name_fr": "L'ÉTALAGE DE PUISSANCE",
      "full_text_en": "...",
      "full_text_fr": "..."
    }
  ],
  "discarded": [...],
  "completed": [...],
  "waitingReplacement": [
    {
      "id": "waiting_1234567890",
      "discardedMissionId": 84,
      "status": "waiting"
    }
  ]
}
```

---

## 🔌 API Endpoints

### Scores

**POST** `/api/player-matches/{id}/save-draft-scores`
- Sauvegarde les scores temporaires
- Body : JSON avec tous les champs de `draft_scores`
- Response : `{"success": true, "message": "Brouillon sauvegardé"}`

**GET** `/api/player-matches/{id}/get-draft-scores`
- Récupère les scores temporaires
- Response : JSON avec tous les champs de `draft_scores`

### Missions Tactiques

**POST** `/api/player-matches/{id}/save-tactical-state/{side}`
- Sauvegarde l'état tactique (side = "creator" ou "opponent")
- Body : JSON avec `active`, `discarded`, `completed`, `waitingReplacement`
- Response : `{"success": true, "message": "État tactique sauvegardé"}`

**GET** `/api/player-matches/{id}/get-tactical-state/{side}`
- Récupère l'état tactique (side = "creator" ou "opponent")
- Response : JSON avec `active`, `discarded`, `completed`, `waitingReplacement`

---

## 🔄 Flux Complet d'une Partie

### Scénario : Créateur entre des scores et pioche des missions tactiques

1. **Créateur accède à `/player-matches/8/score`**
   - `DOMContentLoaded` déclenché
   - `loadSavedData()` → Charge scores depuis DB
   - `loadTacticalStateFromDb()` → Charge missions tactiques
   - `toggleSecondaryType()` → Affiche la bonne section

2. **Créateur entre 40 points primaires**
   - `saveScoringData()` déclenché
   - Attente 2 secondes (debouncing)
   - API POST → Sauvegarde en DB

3. **Créateur coche "Missions Tactiques"**
   - `toggleSecondaryType('tactical')` appelée
   - `saveScoringData()` → Sauvegarde `secondary_type: "tactical"`

4. **Créateur clique "Piocher 2 missions"**
   - `drawTacticalMissions()` appelée
   - 2 missions aléatoires piochées
   - `saveTacticalState()` → Sauvegarde en DB

5. **Adversaire accède à `/player-matches/8/view-score`**
   - `DOMContentLoaded` déclenché
   - `startPolling()` → Lance polling toutes les 2s
   - `updateScoresRealtime()` → Affiche scores du créateur
   - `loadTacticalState()` → Charge missions tactiques

6. **Polling toutes les 2 secondes**
   - `updateScoresRealtime()` appelée
   - API GET → Récupère scores depuis DB
   - Affichage mis à jour en temps réel

---

## 🛠️ Modifications Futures

### Pour ajouter une nouvelle fonctionnalité

1. **Ajouter un champ de scoring**
   - Ajouter input HTML dans le formulaire
   - Ajouter le champ à `saveScoringData()`
   - Ajouter le champ à `loadSavedData()`
   - Ajouter validation dans le contrôleur

2. **Ajouter une nouvelle mission tactique**
   - Ajouter la mission à la table `secondary_missions`
   - Elle sera automatiquement disponible via `allMissions`

3. **Modifier le polling**
   - Changer `setInterval(updateScoresRealtime, 2000)` pour un autre délai
   - Attention : Délai trop court = charge serveur élevée

4. **Ajouter une nouvelle section**
   - Créer une nouvelle fonction `loadXXX()` et `saveXXX()`
   - Ajouter l'appel dans `DOMContentLoaded`
   - Créer l'API endpoint correspondant

---

## ⚠️ Points Importants

### Sécurité
- ✅ CSRF Token requis pour POST
- ✅ Validation côté serveur dans le contrôleur
- ✅ Authentification requise (middleware `auth`)

### Performance
- ✅ Debouncing 2s pour éviter surcharge serveur
- ✅ Polling 2s pour mise à jour temps réel
- ✅ JSON casting pour performances DB

### Compatibilité
- ✅ Responsive design (mobile + desktop)
- ✅ Fallback localStorage si API indisponible
- ✅ Gestion des erreurs réseau

---

## 📞 Contrôleur : `PlayerMatchController`

### Méthodes Principales

```php
// Sauvegarde les scores temporaires
public function saveDraftScores(Request $request, PlayerMatch $playerMatch)

// Récupère les scores temporaires
public function getDraftScores(PlayerMatch $playerMatch)

// Sauvegarde l'état tactique
public function saveTacticalState(Request $request, PlayerMatch $playerMatch, $side)

// Récupère l'état tactique
public function getTacticalState(PlayerMatch $playerMatch, $side)
```

---

## 📝 Notes de Maintenance

- Les données de brouillon restent en DB jusqu'à suppression du match
- Aucun nettoyage automatique n'est effectué
- Les données sont valides pendant la période de disponibilité du match
- Soft delete utilisé pour les matchs supprimés

---

**Dernière mise à jour** : 4 novembre 2025
**Auteur** : Cascade AI
**Version** : 1.0
