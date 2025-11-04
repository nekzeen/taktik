# Référence Rapide - Pages de Scoring

## 📍 Fichiers Clés

| Fichier | Localisation | Description |
|---------|--------------|-------------|
| `test-score.blade.php` | `resources/views/player-matches/` | Page créateur |
| `view-score.blade.php` | `resources/views/player-matches/` | Page adversaire |
| `PlayerMatchController.php` | `app/Http/Controllers/` | Contrôleur API |
| `routes/api.php` | `routes/` | Routes API |

---

## 🔑 Variables Globales JavaScript

### Page Créateur
```javascript
const matchId = 8;                    // ID du match
let scoringAutoSaveTimeout;           // Timeout debouncing scores
let tacticalAutoSaveTimeout;          // Timeout debouncing tactique
let tacticalState = {...};            // État des missions tactiques
const allMissions = [...];            // Toutes les missions disponibles
```

### Page Adversaire
```javascript
const matchId = 8;                    // ID du match
let pollingInterval;                  // Intervalle polling
```

---

## 🎯 Fonctions Principales

### Scores
```javascript
saveScoringData()              // Sauvegarde scores + type + missions fixes
loadSavedData()                // Charge scores depuis API
```

### Missions Tactiques
```javascript
drawTacticalMissions()         // Pioche 2 missions
discardMission(id)             // Défausse une mission
completeMission(id)            // Termine une mission
generateReplacementMission(id) // Génère remplacement
saveTacticalState()            // Sauvegarde état tactique
loadTacticalStateFromDb()      // Charge état tactique
updateTacticalDisplay()        // Met à jour affichage
```

### Missions Fixes
```javascript
updateFixedMissions()          // Valide et sauvegarde missions fixes
```

### Polling (Page Adversaire)
```javascript
updateScoresRealtime()         // Récupère et affiche scores
startPolling()                 // Démarre polling
stopPolling()                  // Arrête polling
```

---

## 📊 Données Sauvegardées

### draft_scores (JSON)
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

### draft_tactical_state_creator/opponent (JSON)
```json
{
  "active": [...],
  "discarded": [...],
  "completed": [...],
  "waitingReplacement": [...]
}
```

---

## 🔌 API Endpoints

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/player-matches/{id}/save-draft-scores` | Sauvegarde scores |
| GET | `/api/player-matches/{id}/get-draft-scores` | Récupère scores |
| POST | `/api/player-matches/{id}/save-tactical-state/{side}` | Sauvegarde tactique |
| GET | `/api/player-matches/{id}/get-tactical-state/{side}` | Récupère tactique |

---

## 🔄 Flux Rapide

### Créateur saisit un score
```
Changement input
    ↓
saveScoringData() (debouncing 2s)
    ↓
POST /api/player-matches/8/save-draft-scores
    ↓
Base de données
```

### Adversaire voit le score
```
Page chargée
    ↓
startPolling()
    ↓
updateScoresRealtime() (toutes les 2s)
    ↓
GET /api/player-matches/8/get-draft-scores
    ↓
Affichage mis à jour
```

### Créateur pioche des missions
```
Clic "Piocher 2 missions"
    ↓
drawTacticalMissions()
    ↓
saveTacticalState() (debouncing 2s)
    ↓
POST /api/player-matches/8/save-tactical-state/creator
    ↓
Base de données
```

---

## 🛠️ Modifications Courantes

### Ajouter un nouveau champ de scoring

1. **Ajouter l'input HTML**
   ```html
   <input type="number" id="new_field" min="0" max="50">
   ```

2. **Ajouter à saveScoringData()**
   ```javascript
   const data = {
       // ...
       new_field: document.getElementById('new_field').value,
   };
   ```

3. **Ajouter à loadSavedData()**
   ```javascript
   document.getElementById('new_field').value = data.new_field || 0;
   ```

4. **Ajouter validation au contrôleur**
   ```php
   'new_field' => 'nullable|integer|min:0|max:50',
   ```

5. **Ajouter event listener**
   ```javascript
   document.getElementById('new_field').addEventListener('change', saveScoringData);
   document.getElementById('new_field').addEventListener('input', saveScoringData);
   ```

### Changer le délai de debouncing

```javascript
// Actuellement 2000ms
clearTimeout(scoringAutoSaveTimeout);
scoringAutoSaveTimeout = setTimeout(() => {
    // Changer 2000 à un autre délai
}, 2000); // ← Modifier ici
```

### Changer le délai de polling

```javascript
// Actuellement 2000ms
pollingInterval = setInterval(updateScoresRealtime, 2000); // ← Modifier ici
```

### Ajouter une nouvelle mission tactique

1. Ajouter dans la table `secondary_missions`
2. Elle sera automatiquement disponible via `allMissions`

---

## 🐛 Debugging Rapide

### Console Logs
```javascript
console.log('Scores:', tacticalState);
console.log('Polling:', pollingInterval);
console.log('Missions:', allMissions);
```

### Vérifier les données en DB
```bash
php artisan tinker
>>> $match = App\Models\PlayerMatch::find(8);
>>> $match->draft_scores;
>>> $match->draft_tactical_state_creator;
```

### Tester l'API
```bash
curl -X GET http://localhost/api/player-matches/8/get-draft-scores
```

### Vérifier les logs
```bash
tail -f storage/logs/laravel.log
```

---

## ✅ Checklist Avant Modification

- [ ] Lire DOCUMENTATION_SCORE_PAGES.md
- [ ] Comprendre le flux de données
- [ ] Vérifier les fichiers concernés
- [ ] Tester en local
- [ ] Vérifier la console (F12)
- [ ] Vérifier les logs Laravel
- [ ] Tester sur mobile
- [ ] Vérifier la sauvegarde en DB

---

## 📞 Ressources

- **Documentation Complète** : `DOCUMENTATION_SCORE_PAGES.md`
- **Architecture** : `ARCHITECTURE_SCORE_SYSTEM.md`
- **Dépannage** : `TROUBLESHOOTING_SCORE_PAGES.md`
- **Référence Rapide** : Ce fichier

---

**Dernière mise à jour** : 4 novembre 2025
**Auteur** : Cascade AI
**Version** : 1.0
