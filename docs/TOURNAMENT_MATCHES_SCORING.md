# Documentation : Système de Sauvegarde des Scores pour les Matchs de Tournoi

## 📋 Table des matières
1. [Architecture Générale](#architecture-générale)
2. [Base de Données](#base-de-données)
3. [API Endpoints](#api-endpoints)
4. [Logique Métier](#logique-métier)
5. [Flux de Données](#flux-de-données)

---

## Architecture Générale

Le système fonctionne selon ce modèle :

```
Page de Saisie (score.blade.php)
    ↓ saveScoringData() à chaque changement
API POST /api/tournament-matches/{match}/save-draft-scores
    ↓ Validation et sauvegarde
Base de Données (draft_scores JSON)
    ↓ Polling toutes les 2 secondes
API GET /api/tournament-matches/{match}/get-draft-scores
    ↓ Retourne les données
Page de Visualisation (view-score.blade.php)
    ↓ Affichage en temps réel
```

---

## Base de Données

### Colonnes Ajoutées à `tournament_matches`

```sql
-- Scores temporaires (JSON)
ALTER TABLE tournament_matches ADD COLUMN draft_scores JSON NULLABLE;

-- État tactique joueur 1 (JSON)
ALTER TABLE tournament_matches ADD COLUMN draft_tactical_state_player1 JSON NULLABLE;

-- État tactique joueur 2 (JSON)
ALTER TABLE tournament_matches ADD COLUMN draft_tactical_state_player2 JSON NULLABLE;
```

### Modèle TournamentMatch

```php
class TournamentMatch extends Model
{
    protected $fillable = [
        'draft_scores',
        'draft_tactical_state_player1',
        'draft_tactical_state_player2',
    ];

    protected $casts = [
        'draft_scores' => 'array',
        'draft_tactical_state_player1' => 'array',
        'draft_tactical_state_player2' => 'array',
    ];
}
```

### Format des Données

**draft_scores :**
```json
{
  "player1_primary_points": "50",
  "player1_secondary_points": "25",
  "player1_painting_points": true,
  "player2_primary_points": "30",
  "player2_secondary_points": "15",
  "player2_painting_points": false,
  "secondary_type": "tactical",
  "fixed_mission_1": null,
  "fixed_mission_2": null
}
```

**draft_tactical_state_player1 :**
```json
{
  "active": [
    { "id": 1, "name_en": "Mission", "name_fr": "Mission FR" }
  ],
  "discarded": [],
  "completed": [],
  "waitingReplacement": []
}
```

---

## API Endpoints

### 1. POST : Sauvegarder les Scores

**Route :** `POST /api/tournament-matches/{match}/save-draft-scores`

```php
Route::post('/api/tournament-matches/{match}/save-draft-scores', function (\Illuminate\Http\Request $request, \App\Models\TournamentMatch $match) {
    $match->update([
        'draft_scores' => $request->all(),
    ]);
    return response()->json(['success' => true]);
});
```

**Requête JavaScript :**
```javascript
fetch(`/api/tournament-matches/${matchId}/save-draft-scores`, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
    },
    body: JSON.stringify({
        player1_primary_points: 50,
        player1_secondary_points: 25,
        player1_painting_points: true,
        player2_primary_points: 30,
        player2_secondary_points: 15,
        player2_painting_points: false,
    }),
})
```

### 2. GET : Charger les Scores

**Route :** `GET /api/tournament-matches/{match}/get-draft-scores`

```php
Route::get('/api/tournament-matches/{match}/get-draft-scores', function (\App\Models\TournamentMatch $match) {
    return response()->json($match->draft_scores ?? []);
});
```

### 3. POST : Sauvegarder l'État Tactique

**Route :** `POST /api/tournament-matches/{match}/save-tactical-state/{player}`

```php
Route::post('/api/tournament-matches/{match}/save-tactical-state/{player}', function (\Illuminate\Http\Request $request, \App\Models\TournamentMatch $match, $player) {
    $column = $player === 'player1' ? 'draft_tactical_state_player1' : 'draft_tactical_state_player2';
    $match->update([$column => $request->all()]);
    return response()->json(['success' => true]);
});
```

### 4. GET : Charger l'État Tactique

**Route :** `GET /api/tournament-matches/{match}/get-tactical-state/{player}`

```php
Route::get('/api/tournament-matches/{match}/get-tactical-state/{player}', function (\App\Models\TournamentMatch $match, $player) {
    $column = $player === 'player1' ? 'draft_tactical_state_player1' : 'draft_tactical_state_player2';
    return response()->json($match->{$column} ?? ['active' => [], 'discarded' => [], 'completed' => [], 'waitingReplacement' => []]);
});
```

---

## Logique Métier

### Validation des Scores

```javascript
function validateForm() {
    let isValid = true;

    const player1Primary = parseInt(document.getElementById('player1_primary_points').value) || 0;
    const player1Secondary = parseInt(document.getElementById('player1_secondary_points').value) || 0;

    // Validation : max 50 pour primaire
    const player1PrimaryError = document.getElementById('player1_primary_error');
    if (player1Primary > 50) {
        player1PrimaryError.classList.remove('hidden');
        isValid = false;
    } else {
        player1PrimaryError.classList.add('hidden');
    }

    // Validation : max 40 pour secondaire
    const player1SecondaryError = document.getElementById('player1_secondary_error');
    if (player1Secondary > 40) {
        player1SecondaryError.classList.remove('hidden');
        isValid = false;
    } else {
        player1SecondaryError.classList.add('hidden');
    }

    return isValid;
}
```

### Calcul des Totaux

```javascript
function updateTotals() {
    // Player 1
    const p1Primary = parseInt(document.getElementById('player1_primary_points').value) || 0;
    const p1Secondary = parseInt(document.getElementById('player1_secondary_points').value) || 0;
    const p1Painting = document.getElementById('player1_painting_points').checked ? 10 : 0;
    const p1Total = p1Primary + p1Secondary + p1Painting;
    document.getElementById('player1_total').textContent = p1Total;

    // Player 2
    const p2Primary = parseInt(document.getElementById('player2_primary_points').value) || 0;
    const p2Secondary = parseInt(document.getElementById('player2_secondary_points').value) || 0;
    const p2Painting = document.getElementById('player2_painting_points').checked ? 10 : 0;
    const p2Total = p2Primary + p2Secondary + p2Painting;
    document.getElementById('player2_total').textContent = p2Total;
}
```

**Règles :**
- Primaire : 0-50 points
- Secondaire : 0-40 points
- Peinture : 0 ou 10 points
- **Total = Primaire + Secondaire + Peinture**

---

## Flux de Données

### Flux 1 : Saisie et Sauvegarde

```
1. Utilisateur modifie un score
2. Événement 'input' ou 'change' déclenché
3. updateTotals() appelée
4. validateForm() appelée
5. saveScoringData() appelée
6. Debouncing 2 secondes
7. Fetch POST à l'API
8. Données sauvegardées en base (draft_scores)
9. Réponse { success: true }
```

### Flux 2 : Affichage en Temps Réel

```
1. Page de visualisation chargée
2. startPolling() appelée
3. updateScoresRealtime() appelée immédiatement
4. Fetch GET à l'API
5. Données reçues et affichées
6. setInterval toutes les 2 secondes
7. Scores mis à jour en temps réel
```

### Flux 3 : Missions Tactiques

```
1. Utilisateur sélectionne missions tactiques
2. saveTacticalStateToDb() appelée
3. Debouncing 2 secondes
4. Fetch POST à l'API
5. État sauvegardé en base (draft_tactical_state_player1)
6. Au rechargement : loadTacticalStateFromDb() restaure l'état
```

---

## Fonctions JavaScript Clés

### saveScoringData()

Sauvegarde automatique des scores avec debouncing 2 secondes.

```javascript
let scoringAutoSaveTimeout;

function saveScoringData() {
    const data = {
        player1_primary_points: document.getElementById('player1_primary_points').value,
        player1_secondary_points: document.getElementById('player1_secondary_points').value,
        player1_painting_points: document.getElementById('player1_painting_points').checked,
        player2_primary_points: document.getElementById('player2_primary_points').value,
        player2_secondary_points: document.getElementById('player2_secondary_points').value,
        player2_painting_points: document.getElementById('player2_painting_points').checked,
    };

    clearTimeout(scoringAutoSaveTimeout);
    scoringAutoSaveTimeout = setTimeout(() => {
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
            if (result.success) console.log('✓ Scores sauvegardés');
        });
    }, 2000);
}
```

### incrementPoints() et decrementPoints()

```javascript
function incrementPoints(fieldId, max) {
    const input = document.getElementById(fieldId);
    let value = parseInt(input.value) || 0;
    if (value < max) {
        input.value = value + 1;
        updateTotals();
        validateForm();
        saveScoringData();
    }
}

function decrementPoints(fieldId, max) {
    const input = document.getElementById(fieldId);
    let value = parseInt(input.value) || 0;
    if (value > 0) {
        input.value = value - 1;
        updateTotals();
        validateForm();
        saveScoringData();
    }
}
```

### updateScoresRealtime()

Polling toutes les 2 secondes pour afficher les scores en temps réel.

```javascript
function updateScoresRealtime() {
    fetch(`/api/tournament-matches/${matchId}/get-draft-scores`)
        .then(response => response.json())
        .then(data => {
            if (data && Object.keys(data).length > 0) {
                const p1Primary = parseInt(data.player1_primary_points) || 0;
                const p1Secondary = parseInt(data.player1_secondary_points) || 0;
                const p1Painting = data.player1_painting_points ? 10 : 0;
                const p1Total = p1Primary + p1Secondary + p1Painting;

                document.getElementById('creator-primary-display').textContent = p1Primary;
                document.getElementById('creator-secondary-display').textContent = p1Secondary;
                document.getElementById('creator-total-display').textContent = p1Total;
            }
        });
}

function startPolling() {
    updateScoresRealtime();
    setInterval(updateScoresRealtime, 2000);
}
```

---

## Debugging

### Ouvrir la Console

Appuyez sur `F12` et allez dans l'onglet "Console"

### Logs à Vérifier

```
✓ DOMContentLoaded appelé
✓ Event listeners attachés avec succès
✓ saveScoringData() appelée
✓ Données à sauvegarder: {...}
✓ Envoi des données à l'API...
✓ Réponse de l'API: { success: true }
✓ Scores sauvegardés en base
```

### Vérifier les Requêtes Réseau

1. Ouvrez F12 → Network
2. Modifiez un score
3. Cherchez la requête POST à `/api/tournament-matches/{match}/save-draft-scores`
4. Vérifiez que la réponse est `{ "success": true }`

---

## Résumé

✅ **Sauvegarde Automatique** : Les scores sont sauvegardés en base de données lors de chaque modification
✅ **Affichage en Temps Réel** : Les scores s'affichent en temps réel sur la page de visualisation (polling 2s)
✅ **Missions Tactiques** : L'état tactique est sauvegardé et restauré au rechargement
✅ **Validation** : Les scores sont validés (max 50 primaire, max 40 secondaire)
✅ **Débouncing** : Les appels API sont limités (2 secondes) pour éviter les surcharges
