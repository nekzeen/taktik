# Documentation Technique Avancée - Système de Scoring PlayerMatch

## 📊 Flux de Données Détaillé

### 1. Chargement Initial de la Page

```
GET /player-matches/{id}/score/creator
    ↓
PlayerMatchController::scoreFormCreator()
    ↓
Vérification: Auth::id() === creator_id
    ↓
Charge relations: primaryMission, terrainLayout, twistMission, creator, opponent
    ↓
Retourne view('player-matches.score-creator', compact('playerMatch'))
    ↓
Blade rend la page avec:
  - Missions (EN/FR)
  - Formulaire de scoring
  - Données JSON pour JavaScript
    ↓
JavaScript DOMContentLoaded:
  - loadSavedData() → GET /api/player-matches/{id}/get-draft-scores
  - Restaure les brouillons
  - Démarre le polling
```

### 2. Saisie des Scores (Utilisateur)

```
Utilisateur modifie creator_primary_points
    ↓
Event: change + input
    ↓
JavaScript:
  - updateTotals() → Calcule creator_total
  - validateForm() → Vérifie limites
  - saveScoringData() → Debounce 2s
    ↓
Après 2 secondes:
  POST /api/player-matches/{id}/save-draft-scores
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
    ↓
PlayerMatchController::saveDraftScores()
  - Valide les données
  - Sauvegarde dans draft_scores (JSON)
  - Retourne: {"success": true}
    ↓
JavaScript reçoit la réponse
  - Log: "✅ Scores sauvegardés"
```

### 3. Polling Temps Réel (Toutes les 2 secondes)

```
setInterval(() => { loadOpponentScores() }, 2000)
    ↓
GET /api/player-matches/{id}/get-draft-scores
    ↓
PlayerMatchController::getDraftScores()
  - Retourne: $playerMatch->draft_scores (JSON)
    ↓
JavaScript reçoit les données:
  - Met à jour opponent_primary_points
  - Met à jour opponent_secondary_points
  - Met à jour opponent_painting_points
  - Recalcule opponent_total
    ↓
Affichage mis à jour en temps réel
  (SANS modifier creator_primary_points)
```

### 4. Finalisation du Match

```
Utilisateur clique "Fin du match"
    ↓
JavaScript: finishMatch()
  - Confirmation: confirm('Êtes-vous sûr ?')
  - Collecte tous les scores
  - POST /player-matches/{id}/set-score
  {
    "creator_result": "normal",
    "creator_primary_points": 25,
    "creator_secondary_points": 15,
    "creator_painting_points": true,
    "creator_score": 50,
    "opponent_primary_points": 30,
    "opponent_secondary_points": 10,
    "opponent_painting_points": false,
    "opponent_score": 40
  }
    ↓
PlayerMatchController::setScore()
  - Valide tous les paramètres
  - Calcule les totaux
  - Détermine le gagnant
  - Sauvegarde:
    * creator_score = 50
    * opponent_score = 40
    * winner_id = creator_id
    * is_draw = false
    * status = 'completed'
    * played_at = now()
    ↓
Retourne: {"success": true}
    ↓
JavaScript redirige vers player-matches.show
```

---

## 🔐 Sécurité Détaillée

### Authentification

```php
// Dans scoreFormCreator()
$user = Auth::user();
if ($playerMatch->creator_id !== $user->id) {
    abort(403, 'Vous n\'êtes pas autorisé...');
}

// Dans scoreFormOpponent()
$user = Auth::user();
if ($playerMatch->opponent_id !== $user->id) {
    abort(403, 'Vous n\'êtes pas autorisé...');
}

// Dans setScore()
if ($playerMatch->creator_id !== $user->id && 
    $playerMatch->opponent_id !== $user->id) {
    return response()->json(['error' => '...'], 403);
}
```

### Validation des Données

```php
$validated = $request->validate([
    'creator_primary_points' => 'nullable|integer|min:0|max:50',
    'creator_secondary_points' => 'nullable|integer|min:0|max:40',
    'creator_painting_points' => 'nullable|boolean',
    'opponent_primary_points' => 'nullable|integer|min:0|max:50',
    'opponent_secondary_points' => 'nullable|integer|min:0|max:40',
    'opponent_painting_points' => 'nullable|boolean',
    'secondary_type' => 'nullable|in:fixed,tactical',
    'fixed_mission_1' => 'nullable|integer',
    'fixed_mission_2' => 'nullable|integer',
]);
```

### Protection CSRF

```javascript
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

fetch('/api/player-matches/{id}/save-draft-scores', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,  // ← Token CSRF
    },
    body: JSON.stringify(data),
});
```

---

## 📱 Interface Utilisateur Détaillée

### Layout Responsive

```html
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
    <!-- Colonne gauche: 2/3 sur desktop, 100% sur mobile -->
    <div class="lg:col-span-2 space-y-4 lg:space-y-6 order-2 lg:order-1">
        <!-- Missions et Péripéties -->
    </div>
    
    <!-- Colonne droite: 1/3 sur desktop, 100% sur mobile -->
    <div class="lg:col-span-1 order-1 lg:order-2">
        <!-- Formulaire de scoring -->
    </div>
</div>
```

### Sections Collapsibles

```javascript
// Mission Primaire
<button onclick="document.getElementById('primary-content').classList.toggle('hidden')">
    <h3>Mission Primaire</h3>
    <span id="primary-toggle"></span>
</button>

// Affichage/Masquage du texte complet
<button onclick="document.getElementById('primary-en').classList.toggle('hidden')">EN</button>
<button onclick="document.getElementById('primary-fr').classList.toggle('hidden')">FR</button>
```

### Boutons +/-

```html
<div class="flex items-center gap-2">
    <button onclick="decrementPoints('creator_primary_points', 50)">-</button>
    <input type="number" id="creator_primary_points" min="0" max="50">
    <button onclick="incrementPoints('creator_primary_points', 50)">+</button>
</div>

<!-- Pour l'adversaire (lecture seule) -->
<div class="flex items-center gap-2 opponent-readonly-buttons">
    <!-- Les boutons sont masqués par CSS -->
    <button>-</button>
    <input type="number" id="opponent_primary_points" readonly>
    <button>+</button>
</div>
```

### Affichage des Totaux

```html
<div class="bg-white border-2 border-red-300 p-3 rounded-lg">
    <p class="text-sm text-red-900">
        <span class="font-bold">Total:</span> 
        <span id="creator_total" class="font-bold text-lg text-red-700">10</span> 
        points
    </p>
</div>
```

---

## 🔄 Gestion des Missions Secondaires

### Missions Fixes

```javascript
function updateFixedMissions() {
    const mission1 = document.querySelector('select[name="fixed_mission_1"]').value;
    const mission2 = document.querySelector('select[name="fixed_mission_2"]').value;
    
    // Vérifier que les deux missions sont différentes
    if (mission1 && mission2 && mission1 === mission2) {
        document.getElementById('fixed-warning').classList.remove('hidden');
    } else {
        document.getElementById('fixed-warning').classList.add('hidden');
    }
    
    // Sauvegarder
    saveScoringData();
}
```

### Missions Tactiques

```javascript
let tacticalState = {
    active: [],           // Missions en jeu
    discarded: [],        // Missions défaussées
    completed: [],        // Missions terminées
    waitingReplacement: [] // En attente de remplacement
};

function drawTacticalMissions() {
    // Piocher 2 missions aléatoires
    // Mettre à jour tacticalState.active
    // Sauvegarder via saveTacticalStateToDb()
}

function saveTacticalStateToDb() {
    fetch(`/api/player-matches/${matchId}/save-tactical-state/creator`, {
        method: 'POST',
        body: JSON.stringify(tacticalState),
    });
}
```

---

## 📊 Calcul des Scores

### Formule Complète

```javascript
function updateTotals() {
    // Créateur
    const creatorPrimary = parseInt(document.getElementById('creator_primary_points').value) || 0;
    const creatorSecondary = parseInt(document.getElementById('creator_secondary_points').value) || 0;
    const creatorPainting = document.getElementById('creator_painting_points').checked ? 10 : 0;
    const creatorTotal = creatorPrimary + creatorSecondary + creatorPainting;
    document.getElementById('creator_total').textContent = creatorTotal;
    
    // Adversaire
    const opponentPrimary = parseInt(document.getElementById('opponent_primary_points').value) || 0;
    const opponentSecondary = parseInt(document.getElementById('opponent_secondary_points').value) || 0;
    const opponentPainting = document.getElementById('opponent_painting_points').checked ? 10 : 0;
    const opponentTotal = opponentPrimary + opponentSecondary + opponentPainting;
    document.getElementById('opponent_total').textContent = opponentTotal;
}
```

### Validation

```javascript
function validateForm() {
    const creatorPrimary = parseInt(document.getElementById('creator_primary_points').value) || 0;
    const creatorSecondary = parseInt(document.getElementById('creator_secondary_points').value) || 0;
    
    // Afficher les erreurs si dépassement
    document.getElementById('creator_primary_error').classList.toggle('hidden', creatorPrimary <= 50);
    document.getElementById('creator_secondary_error').classList.toggle('hidden', creatorSecondary <= 40);
}
```

---

## 🎨 CSS Personnalisé

### Masquage des Boutons

```css
/* Dans score-creator.blade.php */
<style>
    .opponent-readonly-buttons button {
        display: none !important;
    }
</style>

/* Dans score-opponent.blade.php */
<style>
    .creator-readonly-buttons button {
        display: none !important;
    }
</style>
```

### Couleurs Thématiques

```css
/* Créateur (Rouge) */
.bg-red-50 { background-color: #fef2f2; }
.border-red-200 { border-color: #fecaca; }
.text-red-900 { color: #7c2d12; }

/* Adversaire (Bleu) */
.bg-blue-50 { background-color: #eff6ff; }
.border-blue-200 { border-color: #bfdbfe; }
.text-blue-900 { color: #1e3a8a; }

/* Missions (Blanc) */
.bg-white { background-color: #ffffff; }
.border-gray-200 { border-color: #e5e7eb; }
```

---

## 🔍 Débogage

### Logs JavaScript

```javascript
// Au chargement
console.log('DOMContentLoaded appelé');

// Lors du chargement des scores
console.log('📥 Scores chargés depuis la base:', data);

// Lors du polling
console.log('🔄 Mise à jour scores adversaire:', data);

// Lors de la sauvegarde
console.log('✅ Scores sauvegardés');

// Erreurs
console.error('Erreur chargement scores:', error);
```

### Inspection des Données

```javascript
// Vérifier les données du formulaire
console.log('creator_primary_points:', document.getElementById('creator_primary_points').value);

// Vérifier l'état tactique
console.log('tacticalState:', tacticalState);

// Vérifier le token CSRF
console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.content);
```

---

## 📈 Performance

### Optimisations

- **Debouncing** : Sauvegarde après 2 secondes d'inactivité
- **Polling** : Toutes les 2 secondes (pas plus fréquent)
- **Chargement** : Seuls les scores de l'adversaire sont rechargés
- **JSON** : Utilisation de colonnes JSON pour les brouillons

### Métriques

- Temps de réponse API : < 100ms
- Temps de polling : 2000ms
- Temps de debounce : 2000ms
- Taille des requêtes : ~500 bytes

---

## 🚀 Déploiement

### Checklist Pré-Déploiement

- [ ] Routes enregistrées dans web.php
- [ ] Contrôleurs implémentés
- [ ] Vues créées (score-creator.blade.php, score-opponent.blade.php)
- [ ] Migrations exécutées (colonnes de scoring)
- [ ] CSS compilé (npm run build)
- [ ] Tests d'autorisation effectués
- [ ] Tests de polling effectués
- [ ] Tests de sauvegarde effectués

### Commandes

```bash
# Exécuter les migrations
php artisan migrate

# Compiler les assets
npm run build

# Vider le cache des routes
php artisan route:cache

# Vider le cache des vues
php artisan view:clear
```

---

**Dernière mise à jour** : 6 novembre 2025
**Version** : 1.0
**Auteur** : Système de Scoring PlayerMatch
