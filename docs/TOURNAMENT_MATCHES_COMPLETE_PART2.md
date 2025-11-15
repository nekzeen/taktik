# Système Complet des Matchs de Tournoi - Documentation Exhaustive (Partie 2)

## Flux de saisie des scores

### Étape 1 : Saisie du score (Player 1)

```
Player 1 remplit le formulaire et clique "Fin du match"
    ↓
submitScoreForm() appelée
    ↓
POST /tournament-matches/{match}/set-score
    ↓
setScore() dans le contrôleur
    ↓
- Valide les données
- Calcule les scores totaux
- Sauvegarde les scores
- Marque Player 1 comme validé (player1_score_validated = true)
- Retourne le statut
    ↓
Réponse JSON reçue
    ↓
Afficher l'overlay noir avec spinner
Afficher "En attente de la validation de l'autre joueur..."
Démarrer le polling toutes les 2 secondes
```

### Étape 2 : Polling (Player 1)

```
Toutes les 2 secondes :
    ↓
GET /api/tournament-matches/{match}/validation-status
    ↓
getValidationStatus() retourne l'état actuel
    ↓
Vérifier les changements :
  - Si Player 2 a validé → afficher la modal de validation
  - Si les deux ont validé → rediriger vers la page de résumé
  - Si refusé → retirer l'overlay et réactiver le formulaire
```

### Étape 3 : Validation du score (Player 2)

```
Player 2 reçoit la notification
    ↓
Player 2 voit le formulaire avec les scores de Player 1
    ↓
Player 2 clique "Valider" ou "Refuser"
    ↓
Si "Valider" :
    POST /tournament-matches/{match}/validate-opponent-score
    ↓
    validateOpponentScore() dans le contrôleur
    ↓
    - Marque Player 2 comme validé
    - Si les deux sont validés → finaliser le match
    ↓
    Réponse JSON : status = "completed"
    ↓
    Player 1 détecte via polling que les deux sont validés
    ↓
    Redirection vers la page de résumé

Si "Refuser" :
    POST /tournament-matches/{match}/reject-score-validation
    ↓
    rejectScoreValidation() dans le contrôleur
    ↓
    - Réinitialise les deux validations à false
    ↓
    Réponse JSON : status = "confirmed", player1_validated = false, player2_validated = false
    ↓
    Player 1 détecte via polling que les validations ont été réinitialisées
    ↓
    Retirer l'overlay
    Réactiver le formulaire
    Afficher le message "L'adversaire a refusé la validation"
```

---

## Système de polling

### Initialisation du polling

```javascript
// Après soumission du score
window.validationPollingInterval = setInterval(() => {
    checkValidationStatus();
}, 2000);  // Toutes les 2 secondes
```

### Fonction checkValidationStatus()

```javascript
function checkValidationStatus() {
    const matchId = document.getElementById('match-id').value;
    const csrfToken = document.querySelector('input[name="_token"]')?.value;
    
    fetch(`/api/tournament-matches/${matchId}/validation-status`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
        }
    })
    .then(response => response.json())
    .then(data => {
        // Déterminer le cas et agir en conséquence
        // CAS 1 : Refus de validation
        // CAS 2 : L'autre joueur a validé
        // CAS 3 : Un joueur a validé (en attente)
        // CAS 4 : Les deux ont validé
    })
    .catch(error => console.error('Erreur vérification validation:', error));
}
```

### États du polling

```
État initial :
  player1_validated = false
  player2_validated = false
  status = "confirmed"

↓

Player 1 soumet le score :
  player1_validated = true
  player2_validated = false
  status = "confirmed"
  → Afficher l'overlay

↓

Player 2 valide :
  player1_validated = true
  player2_validated = true
  status = "completed"
  → Finaliser et rediriger

OU

Player 2 refuse :
  player1_validated = false
  player2_validated = false
  status = "confirmed"
  → Retirer l'overlay et réactiver le formulaire
```

---

## JavaScript et Frontend

### Fichiers concernés

- `/resources/views/tournaments/matches/score-player1.blade.php`
- `/resources/views/tournaments/matches/score-player2.blade.php`

### Fonction submitScoreForm()

```javascript
function submitScoreForm(event) {
    event.preventDefault();
    
    const form = document.getElementById('score-form');
    const formData = new FormData(form);
    const csrfToken = document.querySelector('input[name="_token"]')?.value;
    
    // Convertir FormData en objet JSON
    const data = {
        player1_primary_points: parseInt(formData.get('player1_primary_points')) || 0,
        player1_secondary_points: parseInt(formData.get('player1_secondary_points')) || 0,
        player1_painting_points: formData.get('player1_painting_points') === 'on',
        player2_primary_points: parseInt(formData.get('player2_primary_points')) || 0,
        player2_secondary_points: parseInt(formData.get('player2_secondary_points')) || 0,
        player2_painting_points: formData.get('player2_painting_points') === 'on',
    };

    fetch(form.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(data),
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            console.log('✅ Score enregistré:', result.message);
            
            // Afficher l'overlay
            createAndShowOverlay();
            
            // Démarrer le polling
            window.validationPollingInterval = setInterval(() => {
                checkValidationStatus();
            }, 2000);
        } else {
            console.error('❌ Erreur:', result.error);
            alert('Erreur: ' + (result.error || 'Impossible d\'enregistrer le score'));
        }
    })
    .catch(error => {
        console.error('Erreur réseau:', error);
        alert('Erreur réseau: ' + error.message);
    });
}
```

### Fonction validateOpponentScore()

```javascript
function validateOpponentScore() {
    const matchId = document.getElementById('match-id').value;
    const csrfToken = document.querySelector('input[name="_token"]')?.value;
    
    fetch(`/tournament-matches/${matchId}/validate-opponent-score`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({}),
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            console.log('✅ Score validé:', result.message);
            // Le polling détectera le changement
        } else {
            console.error('❌ Erreur:', result.error);
            alert('Erreur: ' + (result.error || 'Impossible de valider le score'));
        }
    })
    .catch(error => {
        console.error('Erreur réseau:', error);
        alert('Erreur réseau: ' + error.message);
    });
}
```

### Fonction rejectValidation()

```javascript
function rejectValidation() {
    const matchId = document.getElementById('match-id').value;
    const csrfToken = document.querySelector('input[name="_token"]')?.value;
    
    fetch(`/tournament-matches/${matchId}/reject-score-validation`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({}),
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            console.log('❌ Validation refusée:', result.message);
            // Le polling détectera le changement
        } else {
            console.error('❌ Erreur:', result.error);
            alert('Erreur: ' + (result.error || 'Impossible de refuser la validation'));
        }
    })
    .catch(error => {
        console.error('Erreur réseau:', error);
        alert('Erreur réseau: ' + error.message);
    });
}
```

### Overlay et UI

```javascript
function createAndShowOverlay() {
    const overlay = document.createElement('div');
    overlay.id = 'validation-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    `;
    
    overlay.innerHTML = `
        <div style="text-align: center; color: white;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">⏳</div>
            <p style="font-size: 1.5rem; margin-bottom: 0.5rem;">En attente de la validation de l'autre joueur...</p>
            <p style="font-size: 1rem; color: #ccc;">Vous pouvez fermer cette page, vous serez notifié quand l'adversaire validera.</p>
        </div>
    `;
    
    document.body.appendChild(overlay);
}
```

---

## Logique métier

### Calcul des scores

```
Score total = Points primaires + Points secondaires + (Bonus peinture ? 1 : 0)

Exemple :
  Points primaires = 13
  Points secondaires = 3
  Bonus peinture = true
  Score total = 13 + 3 + 1 = 17
```

### Détermination du gagnant

```php
public function determineWinner()
{
    if ($this->is_draw) {
        $this->winner_id = null;
    } else {
        $this->winner_id = $this->player1_score > $this->player2_score 
            ? $this->player1_id 
            : $this->player2_id;
    }
    $this->save();
}
```

### Statuts du match

```
pending       → Match en attente de configuration
in_progress   → Match en cours
confirmed     → Scores saisis, en attente de validation
completed     → Match finalisé
```

### Flux de validation

```
1. Player 1 soumet le score
   → player1_score_validated = true
   → status = confirmed
   → Afficher l'overlay

2. Player 2 voit le score et valide
   → player2_score_validated = true
   → Si player1_score_validated = true aussi → status = completed

3. OU Player 2 refuse
   → player1_score_validated = false
   → player2_score_validated = false
   → status = confirmed
   → Retirer l'overlay
```

---

## Sécurité et autorisations

### Authentification

Toutes les routes API nécessitent une authentification :

```php
Route::middleware(['auth'])->group(function () {
    Route::post('/tournament-matches/{tournamentMatch}/set-score', ...);
    Route::post('/tournament-matches/{tournamentMatch}/validate-opponent-score', ...);
    Route::post('/tournament-matches/{tournamentMatch}/reject-score-validation', ...);
    Route::get('/api/tournament-matches/{tournamentMatch}/validation-status', ...);
});
```

### Autorisation

Chaque endpoint vérifie que l'utilisateur est l'un des deux joueurs :

```php
if ($tournamentMatch->player1_id !== $user->id && $tournamentMatch->player2_id !== $user->id) {
    return response()->json(['error' => 'Non autorisé'], 403);
}
```

### Protection CSRF

Tous les formulaires incluent un token CSRF :

```html
<input type="hidden" name="_token" value="{{ csrf_token() }}">
```

Et les requêtes AJAX incluent le token dans les headers :

```javascript
headers: {
    'X-CSRF-TOKEN': csrfToken,
}
```

---

## Cas d'usage pratiques

### Cas 1 : Validation réussie

```
Timeline:
  T0: Player 1 soumet le score
      → player1_validated = true, player2_validated = false
      → Overlay affiché
  
  T2: Polling détecte pas de changement
      → Overlay reste visible
  
  T4: Player 2 accède à la page et valide
      → player1_validated = true, player2_validated = true
      → status = "completed"
  
  T6: Polling détecte que les deux sont validés
      → Retirer l'overlay
      → Rediriger vers la page de résumé
```

### Cas 2 : Refus de validation

```
Timeline:
  T0: Player 1 soumet le score
      → player1_validated = true, player2_validated = false
      → Overlay affiché
  
  T4: Player 2 refuse la validation
      → player1_validated = false, player2_validated = false
      → status = "confirmed"
  
  T6: Polling détecte que les validations ont été réinitialisées
      → Retirer l'overlay
      → Réactiver le formulaire
      → Afficher "L'adversaire a refusé la validation"
```

### Cas 3 : Modification du score

```
Timeline:
  T0: Player 1 soumet le score (13, 3, true) vs (10, 5, false)
      → player1_validated = true
      → Overlay affiché
  
  T4: Player 2 refuse
      → player1_validated = false
  
  T6: Polling détecte le refus
      → Retirer l'overlay
      → Réactiver le formulaire
  
  T8: Player 1 modifie le score (15, 5, true) vs (10, 5, false)
      → Soumet à nouveau
      → player1_validated = true
      → Overlay affiché à nouveau
```

---

## Dépannage

### Le polling ne détecte pas les changements

**Cause probable** : Le `previousValidationState` n'est pas mis à jour après chaque poll

**Solution** : Vérifier que `previousValidationState` est mis à jour à la fin de chaque cas dans `checkValidationStatus()`

### L'overlay ne disparaît pas après refus

**Cause probable** : Le cas 1 (refus) ne s'exécute pas correctement

**Solution** : Vérifier que :
- Les deux validations sont bien réinitialisées à false
- Le status reste "confirmed"
- Le polling détecte le changement (wasValidated = true, nowReset = true)

### Le formulaire ne se réactive pas

**Cause probable** : Les inputs ne sont pas trouvés ou pas déverrouillés correctement

**Solution** : Vérifier que le sélecteur `#score-form` existe et que tous les inputs sont déverrouillés

### Les scores ne s'enregistrent pas

**Cause probable** : Erreur de validation ou données manquantes

**Solution** : Vérifier que :
- Tous les champs requis sont présents
- Les valeurs sont des nombres valides
- Le CSRF token est correct

---

## Résumé des changements récents

### Correction du 7 novembre 2025

**Problème** : L'overlay et le message bleu persistaient après refus de validation

**Cause** : Les `return` dans `checkValidationStatus()` arrêtaient le polling et empêchaient la mise à jour de `previousValidationState`

**Solution** : 
- Enlever tous les `return` et utiliser `else if` à la place
- Mettre à jour `previousValidationState` à la fin de chaque cas
- Ajouter un `else` final pour mettre à jour l'état dans tous les cas

**Fichiers modifiés** :
- `/resources/views/tournaments/matches/score-player1.blade.php`
- `/resources/views/tournaments/matches/score-player2.blade.php`
