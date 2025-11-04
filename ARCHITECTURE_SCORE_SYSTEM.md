# Architecture du Système de Scoring

## 🏗️ Diagramme Global

```
┌─────────────────────────────────────────────────────────────────┐
│                    SYSTÈME DE SCORING W40K                      │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────────────┐              ┌──────────────────────────┐
│   PAGE CRÉATEUR          │              │   PAGE ADVERSAIRE        │
│  /player-matches/8/score │              │ /player-matches/8/view   │
│                          │              │                          │
│  test-score.blade.php    │              │ view-score.blade.php     │
└──────────────┬───────────┘              └──────────────┬───────────┘
               │                                         │
               │ Sauvegarde                              │ Polling
               │ (POST)                                  │ (GET)
               │                                         │
               ▼                                         ▼
        ┌──────────────────────────────────────────────────────┐
        │         API ENDPOINTS                                │
        │  POST /api/player-matches/{id}/save-draft-scores    │
        │  GET  /api/player-matches/{id}/get-draft-scores     │
        │  POST /api/player-matches/{id}/save-tactical-state  │
        │  GET  /api/player-matches/{id}/get-tactical-state   │
        └──────────────┬───────────────────────────────────────┘
                       │
                       ▼
        ┌──────────────────────────────────────────────────────┐
        │         BASE DE DONNÉES                              │
        │  Table: player_matches                               │
        │  - draft_scores (JSON)                               │
        │  - draft_tactical_state_creator (JSON)               │
        │  - draft_tactical_state_opponent (JSON)              │
        └──────────────────────────────────────────────────────┘
```

---

## 📊 Flux de Données - Scores

```
CRÉATEUR                          BASE DE DONNÉES              ADVERSAIRE
─────────────────────────────────────────────────────────────────────────

Saisit 40 points
        │
        ▼
saveScoringData()
        │
        ├─ Debouncing 2s
        │
        ▼
POST /api/player-matches/8/save-draft-scores
        │
        ├─ CSRF Token
        ├─ Validation
        │
        ▼
                    draft_scores = {
                        creator_primary_points: 40,
                        ...
                    }
                                    │
                                    ├─ Polling 2s
                                    │
                                    ▼
                            GET /api/player-matches/8/get-draft-scores
                                    │
                                    ▼
                            updateScoresRealtime()
                                    │
                                    ▼
                            Affichage mis à jour
```

---

## 🎯 Flux de Données - Missions Tactiques

```
CRÉATEUR                          BASE DE DONNÉES              ADVERSAIRE
─────────────────────────────────────────────────────────────────────────

Clique "Piocher 2 missions"
        │
        ▼
drawTacticalMissions()
        │
        ├─ Filtre missions disponibles
        ├─ Pioche 2 aléatoires
        │
        ▼
updateTacticalDisplay()
        │
        ▼
saveTacticalState()
        │
        ├─ Debouncing 2s
        │
        ▼
POST /api/player-matches/8/save-tactical-state/creator
        │
        ├─ CSRF Token
        │
        ▼
                    draft_tactical_state_creator = {
                        active: [mission1, mission2],
                        discarded: [],
                        completed: [],
                        waitingReplacement: []
                    }
                                    │
                                    ├─ DOMContentLoaded
                                    │
                                    ▼
                            GET /api/player-matches/8/get-tactical-state/opponent
                                    │
                                    ▼
                            loadTacticalState()
                                    │
                                    ▼
                            Affichage missions
```

---

## 🔄 Cycle de Vie - Chargement Initial

### Page Créateur
```
DOMContentLoaded
    │
    ├─ loadSavedData()
    │   ├─ GET /api/player-matches/8/get-draft-scores
    │   ├─ Restaure scores
    │   ├─ Restaure missions fixes
    │   └─ Retourne secondary_type
    │
    ├─ loadTacticalStateFromDb()
    │   ├─ GET /api/player-matches/8/get-tactical-state/creator
    │   ├─ Restaure missions tactiques
    │   └─ updateTacticalDisplay()
    │
    ├─ toggleSecondaryType(secondary_type)
    │   └─ Affiche la bonne section
    │
    └─ Ajout des event listeners
        ├─ change/input sur scores
        ├─ change sur missions fixes
        └─ click sur boutons tactiques
```

### Page Adversaire
```
DOMContentLoaded
    │
    ├─ loadSavedData()
    │   └─ Charge missions fixes
    │
    ├─ loadTacticalState()
    │   └─ Charge missions tactiques
    │
    ├─ updateFixedMissions()
    │   └─ Affiche missions fixes
    │
    ├─ setupAutoSave()
    │   └─ Ajoute event listeners
    │
    └─ startPolling()
        ├─ updateScoresRealtime() immédiat
        └─ setInterval(updateScoresRealtime, 2000)
```

---

## 📦 Structure des Données

### draft_scores (JSON)
```
{
  "creator_primary_points": 40,           // 0-50
  "creator_secondary_points": 20,         // 0-40
  "creator_painting_points": true,        // boolean
  "opponent_primary_points": 47,          // 0-50
  "opponent_secondary_points": 15,        // 0-40
  "opponent_painting_points": false,      // boolean
  "secondary_type": "tactical",           // "fixed" ou "tactical"
  "fixed_mission_1": 5,                   // ID mission ou null
  "fixed_mission_2": 12                   // ID mission ou null
}
```

### draft_tactical_state_creator/opponent (JSON)
```
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
  "discarded": [
    {
      "id": 84,
      "name_en": "...",
      "name_fr": "..."
    }
  ],
  "completed": [],
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

## 🔌 Endpoints API

### Scores

```
POST /api/player-matches/{id}/save-draft-scores
├─ Headers: X-CSRF-TOKEN
├─ Body: { creator_primary_points, creator_secondary_points, ... }
└─ Response: { success: true, message: "..." }

GET /api/player-matches/{id}/get-draft-scores
├─ Headers: Authorization
└─ Response: { creator_primary_points, creator_secondary_points, ... }
```

### Missions Tactiques

```
POST /api/player-matches/{id}/save-tactical-state/{side}
├─ {side}: "creator" ou "opponent"
├─ Headers: X-CSRF-TOKEN
├─ Body: { active: [...], discarded: [...], completed: [...], waitingReplacement: [...] }
└─ Response: { success: true, message: "..." }

GET /api/player-matches/{id}/get-tactical-state/{side}
├─ {side}: "creator" ou "opponent"
├─ Headers: Authorization
└─ Response: { active: [...], discarded: [...], completed: [...], waitingReplacement: [...] }
```

---

## 🎨 Composants UI

### Page Créateur

```
┌─────────────────────────────────────────────────────────────┐
│  En-tête : Match entre [Créateur] et [Adversaire]          │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  Formulaire de Scoring                                      │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Créateur                                                ││
│  │ Primaire: [input] +/-  Secondaire: [input] +/-         ││
│  │ Peinture: [checkbox]                                   ││
│  └─────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Adversaire                                              ││
│  │ Primaire: [input] +/-  Secondaire: [input] +/-         ││
│  │ Peinture: [checkbox]                                   ││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  Sélection Missions Secondaires                             │
│  ○ Missions Fixes    ○ Missions Tactiques                  │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  Missions Fixes (si sélectionné)                            │
│  Mission 1: [dropdown]  Mission 2: [dropdown]              │
│  [Affichage des missions sélectionnées]                    │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  Missions Tactiques (si sélectionné)                        │
│  [🎲 Piocher 2 missions]                                   │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Missions Actives                                        ││
│  │ [Mission 1] [Défausser] [Terminer]                     ││
│  │ [Mission 2] [Défausser] [Terminer]                     ││
│  └─────────────────────────────────────────────────────────┘│
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Missions en Attente de Remplacement                     ││
│  │ [Mission Défaussée] [Générer Remplacement]             ││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
```

### Page Adversaire

```
┌─────────────────────────────────────────────────────────────┐
│  En-tête : Visualisation du Match                           │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  📊 Scores Temporaires                                      │
│  ┌──────────────────────┬──────────────────────┐           │
│  │ [Créateur]           │ [Adversaire]         │           │
│  │ Primaire: 40         │ Primaire: 47         │           │
│  │ Secondaire: 20       │ Secondaire: 15       │           │
│  │ Total: 60            │ Total: 62            │           │
│  └──────────────────────┴──────────────────────┘           │
│  ⏱️ Mise à jour en temps réel (toutes les 2 secondes)     │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  Missions et Péripéties (même que page créateur)            │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔐 Sécurité

### Validation
- ✅ CSRF Token requis pour POST
- ✅ Validation côté serveur (min/max)
- ✅ Authentification middleware

### Données
- ✅ JSON casting pour sécurité
- ✅ Pas d'injection SQL
- ✅ Pas d'accès non autorisé

---

## ⚡ Performance

### Debouncing
- Délai 2s pour éviter surcharge serveur
- Évite les appels API inutiles

### Polling
- Intervalle 2s pour mise à jour temps réel
- Arrêt automatique à la fermeture de page

### Caching
- Pas de cache côté client (localStorage supprimé)
- Cache DB via JSON casting

---

## 🐛 Debugging

### Console Logs
- `📥 Scores chargés depuis la base`
- `💾 Scores sauvegardés`
- `📊 Scores mis à jour`
- `📥 Missions tactiques chargées`
- `🎲 Missions piochées`
- `🔄 Polling des scores démarré`

### Network Tab
- Vérifier les appels API
- Vérifier les réponses JSON
- Vérifier les headers CSRF

---

**Dernière mise à jour** : 4 novembre 2025
**Auteur** : Cascade AI
**Version** : 1.0
