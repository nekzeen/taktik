# Guide de Dépannage - Pages de Scoring

## 🔍 Problèmes Courants et Solutions

---

## ❌ Les scores ne se sauvegardent pas

### Symptôme
- Les scores changent mais ne sont pas restaurés après rechargement

### Causes Possibles

#### 1. CSRF Token manquant
```javascript
// ❌ Mauvais
fetch('/api/player-matches/8/save-draft-scores', {
    method: 'POST',
    body: JSON.stringify(data)
})

// ✅ Correct
const csrfToken = document.querySelector('input[name="_token"]')?.value;
fetch('/api/player-matches/8/save-draft-scores', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': csrfToken,
    },
    body: JSON.stringify(data)
})
```

#### 2. Debouncing trop long
- Vérifier que le délai est 2000ms (2 secondes)
- Si délai trop long, les scores ne se sauvegardent pas à temps

#### 3. API endpoint incorrect
```bash
# Vérifier l'endpoint
curl -X GET http://localhost/api/player-matches/8/get-draft-scores

# Vérifier la réponse
# Doit retourner : {"creator_primary_points": 40, ...}
```

#### 4. Validation échouée au serveur
```php
// Vérifier les règles de validation
'creator_primary_points' => 'nullable|integer|min:0|max:50',
'creator_secondary_points' => 'nullable|integer|min:0|max:40',
'secondary_type' => 'nullable|in:fixed,tactical',
'fixed_mission_1' => 'nullable|integer',
'fixed_mission_2' => 'nullable|integer',
```

### Solutions

1. **Vérifier la console du navigateur**
   ```javascript
   // Ouvrir DevTools (F12)
   // Aller à Console
   // Vérifier les erreurs
   ```

2. **Vérifier les logs Laravel**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Vérifier la requête réseau**
   - DevTools → Network
   - Chercher `save-draft-scores`
   - Vérifier le status (200 = OK, 422 = validation error)

4. **Tester l'API directement**
   ```bash
   curl -X POST http://localhost/api/player-matches/8/save-draft-scores \
     -H "X-CSRF-TOKEN: token" \
     -H "Content-Type: application/json" \
     -d '{"creator_primary_points": 40}'
   ```

---

## ❌ Les missions tactiques ne s'affichent pas

### Symptôme
- Le bouton "Piocher 2 missions" ne fonctionne pas
- Les missions piochées ne s'affichent pas

### Causes Possibles

#### 1. `allMissions` non défini
```javascript
// ❌ Erreur
console.log(allMissions) // undefined

// ✅ Vérifier que le PHP est correct
// Dans test-score.blade.php avant <script>
@php
    $missionsData = DB::table('secondary_missions')
        ->where('is_active', true)
        ->get()
        ->toArray();
@endphp

<script>
    const allMissions = @json($missionsData);
</script>
```

#### 2. `tacticalState` non initialisé
```javascript
// ❌ Erreur
let tacticalState; // undefined

// ✅ Correct
let tacticalState = {
    active: [],
    discarded: [],
    completed: [],
    waitingReplacement: []
};
```

#### 3. API endpoint incorrect
```bash
# Vérifier l'endpoint
curl -X GET http://localhost/api/player-matches/8/get-tactical-state/creator
```

### Solutions

1. **Vérifier que les missions existent**
   ```bash
   php artisan tinker
   >>> DB::table('secondary_missions')->where('is_active', true)->count()
   ```

2. **Vérifier que allMissions est défini**
   ```javascript
   // Console
   console.log(allMissions)
   // Doit afficher un tableau de missions
   ```

3. **Vérifier que tacticalState est initialisé**
   ```javascript
   // Console
   console.log(tacticalState)
   // Doit afficher { active: [], discarded: [], ... }
   ```

4. **Vérifier les logs de la sauvegarde**
   ```javascript
   // Ajouter des logs dans saveTacticalState()
   console.log('Avant sauvegarde:', tacticalState);
   ```

---

## ❌ Les scores de l'adversaire ne se mettent pas à jour

### Symptôme
- Page adversaire affiche "-" pour tous les scores
- Les scores ne se mettent pas à jour en temps réel

### Causes Possibles

#### 1. Polling non démarré
```javascript
// Vérifier que startPolling() est appelée
document.addEventListener('DOMContentLoaded', function() {
    startPolling(); // ✅ Doit être appelée
});
```

#### 2. API endpoint incorrect
```bash
# Vérifier l'endpoint
curl -X GET http://localhost/api/player-matches/8/get-draft-scores
```

#### 3. Délai de polling trop long
```javascript
// ❌ Trop long
setInterval(updateScoresRealtime, 10000); // 10 secondes

// ✅ Correct
setInterval(updateScoresRealtime, 2000); // 2 secondes
```

#### 4. Éléments HTML manquants
```html
<!-- Vérifier que les éléments existent -->
<p id="creator-primary-display">-</p>
<p id="creator-secondary-display">-</p>
<p id="creator-total-display">-</p>
<p id="opponent-primary-display">-</p>
<p id="opponent-secondary-display">-</p>
<p id="opponent-total-display">-</p>
```

### Solutions

1. **Vérifier que le polling est démarré**
   ```javascript
   // Console
   console.log('Polling interval:', pollingInterval)
   // Doit afficher un nombre > 0
   ```

2. **Vérifier les appels API**
   - DevTools → Network
   - Chercher `get-draft-scores`
   - Vérifier que les appels arrivent toutes les 2s

3. **Vérifier que les éléments existent**
   ```javascript
   // Console
   console.log(document.getElementById('creator-primary-display'))
   // Doit afficher l'élément HTML
   ```

4. **Forcer une mise à jour**
   ```javascript
   // Console
   updateScoresRealtime();
   ```

---

## ❌ Erreur 422 - Validation Failed

### Symptôme
- Erreur "422 Unprocessable Entity"
- Les données ne se sauvegardent pas

### Causes Possibles

#### 1. Valeurs hors limites
```javascript
// ❌ Erreur
creator_primary_points: 100 // Max 50

// ✅ Correct
creator_primary_points: 40 // Entre 0 et 50
```

#### 2. Type de données incorrect
```javascript
// ❌ Erreur
creator_primary_points: "40" // String au lieu de number

// ✅ Correct
creator_primary_points: 40 // Number
```

#### 3. Champ manquant
```javascript
// ❌ Erreur
{
    creator_primary_points: 40
    // secondary_type manquant
}

// ✅ Correct
{
    creator_primary_points: 40,
    secondary_type: "tactical"
}
```

### Solutions

1. **Vérifier la réponse d'erreur**
   - DevTools → Network
   - Cliquer sur `save-draft-scores`
   - Aller à Response
   - Lire le message d'erreur

2. **Vérifier les règles de validation**
   ```php
   // Dans PlayerMatchController
   $validated = $request->validate([
       'creator_primary_points' => 'nullable|integer|min:0|max:50',
       // ...
   ]);
   ```

3. **Vérifier les types de données**
   ```javascript
   // Avant d'envoyer
   console.log(typeof data.creator_primary_points) // Doit être 'number'
   ```

---

## ❌ Erreur 401 - Unauthorized

### Symptôme
- Erreur "401 Unauthorized"
- L'utilisateur n'est pas authentifié

### Causes Possibles

#### 1. Session expirée
- L'utilisateur s'est déconnecté

#### 2. Token CSRF invalide
```javascript
// ❌ Erreur
const csrfToken = "invalid_token";

// ✅ Correct
const csrfToken = document.querySelector('input[name="_token"]')?.value;
```

### Solutions

1. **Vérifier que l'utilisateur est connecté**
   ```php
   // Dans le contrôleur
   if (!Auth::check()) {
       return response()->json(['error' => 'Unauthorized'], 401);
   }
   ```

2. **Rafraîchir la page**
   - La session peut être expirée
   - Rafraîchir la page pour obtenir un nouveau token

3. **Vérifier le token CSRF**
   ```javascript
   // Console
   console.log(document.querySelector('input[name="_token"]')?.value)
   ```

---

## ❌ Les missions fixes ne s'affichent pas

### Symptôme
- Les dropdowns de missions fixes sont vides
- Les missions sélectionnées ne s'affichent pas

### Causes Possibles

#### 1. Pas de missions fixes disponibles
```bash
# Vérifier
php artisan tinker
>>> DB::table('secondary_missions')->where('is_active', true)->count()
```

#### 2. Dropdowns non remplis
```javascript
// Vérifier que les options sont générées
// Dans test-score.blade.php
@foreach($allSecondaryMissions as $mission)
    <option value="{{ $mission->id }}">{{ $mission->name_fr }}</option>
@endforeach
```

#### 3. `updateFixedMissions()` non appelée
```javascript
// Vérifier que la fonction est appelée
// Au changement de dropdown
onchange="updateFixedMissions()"
```

### Solutions

1. **Vérifier les missions en DB**
   ```bash
   php artisan tinker
   >>> DB::table('secondary_missions')->get()
   ```

2. **Vérifier que les options sont générées**
   - Inspecter le HTML (F12)
   - Vérifier que les `<option>` existent

3. **Vérifier que `updateFixedMissions()` est appelée**
   ```javascript
   // Ajouter un log
   function updateFixedMissions() {
       console.log('updateFixedMissions() appelée');
       // ...
   }
   ```

---

## ❌ Erreur CORS

### Symptôme
- Erreur "Access to XMLHttpRequest blocked by CORS policy"
- Les requêtes API échouent

### Causes Possibles

#### 1. Domaine incorrect
- L'API est sur un domaine différent

#### 2. Headers CORS manquants
```php
// Dans le middleware
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-TOKEN');
```

### Solutions

1. **Vérifier que l'API est sur le même domaine**
   ```javascript
   // ❌ Erreur
   fetch('http://api.example.com/...')

   // ✅ Correct
   fetch('/api/player-matches/8/get-draft-scores')
   ```

2. **Vérifier les headers CORS**
   - DevTools → Network
   - Cliquer sur la requête
   - Vérifier Response Headers

---

## ✅ Checklist de Dépannage

- [ ] Vérifier la console du navigateur (F12)
- [ ] Vérifier les logs Laravel (`tail -f storage/logs/laravel.log`)
- [ ] Vérifier les appels API (DevTools → Network)
- [ ] Vérifier que l'utilisateur est connecté
- [ ] Vérifier que le token CSRF est valide
- [ ] Vérifier que les données sont dans les bonnes limites
- [ ] Vérifier que les éléments HTML existent
- [ ] Vérifier que les fonctions JavaScript sont appelées
- [ ] Tester l'API directement avec curl
- [ ] Rafraîchir la page et réessayer

---

## 🔧 Commandes Utiles

### Laravel Tinker
```bash
php artisan tinker

# Vérifier les données
>>> $match = App\Models\PlayerMatch::find(8);
>>> $match->draft_scores;
>>> $match->draft_tactical_state_creator;

# Vérifier les missions
>>> DB::table('secondary_missions')->where('is_active', true)->get();

# Vérifier l'utilisateur
>>> Auth::user();
```

### Logs
```bash
# Afficher les logs en temps réel
tail -f storage/logs/laravel.log

# Chercher une erreur spécifique
grep "save-draft-scores" storage/logs/laravel.log

# Vider les logs
> storage/logs/laravel.log
```

### API Testing
```bash
# Tester l'endpoint GET
curl -X GET http://localhost/api/player-matches/8/get-draft-scores

# Tester l'endpoint POST
curl -X POST http://localhost/api/player-matches/8/save-draft-scores \
  -H "X-CSRF-TOKEN: token" \
  -H "Content-Type: application/json" \
  -d '{"creator_primary_points": 40}'
```

### Database
```bash
# Vérifier les colonnes
php artisan tinker
>>> Schema::getColumnListing('player_matches');

# Vérifier les données
>>> DB::table('player_matches')->where('id', 8)->first();
```

---

**Dernière mise à jour** : 4 novembre 2025
**Auteur** : Cascade AI
**Version** : 1.0
