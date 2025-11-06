# JavaScript - Gestion de Fin de Match

## Vue d'ensemble

Le JavaScript gère :
1. **Soumission du formulaire** avec validation
2. **Polling** pour détecter les changements
3. **Affichage des modals** de validation
4. **Gestion des alertes** sans boucle infinie

---

## Variables Globales

```javascript
const matchId = {{ $playerMatch->id }};
let rejectionAlertShown = false;  // Flag pour éviter les alertes en boucle
```

---

## Fonction 1: `submitScoreForm(event)`

**Objectif** : Envoyer les scores au serveur

```javascript
function submitScoreForm(event) {
    event.preventDefault();
    
    const form = document.getElementById('score-form');
    const formData = new FormData(form);
    const csrfToken = document.querySelector('input[name="_token"]')?.value;
    
    // Convertir FormData en JSON
    const data = {
        creator_result: formData.get('creator_result'),
        creator_primary_points: parseInt(formData.get('creator_primary_points')) || 0,
        creator_secondary_points: parseInt(formData.get('creator_secondary_points')) || 0,
        creator_painting_points: formData.get('creator_painting_points') === 'on',
        opponent_primary_points: parseInt(formData.get('opponent_primary_points')) || 0,
        opponent_secondary_points: parseInt(formData.get('opponent_secondary_points')) || 0,
        opponent_painting_points: formData.get('opponent_painting_points') === 'on',
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
            
            const waitingAlert = document.getElementById('waiting-alert');
            if (waitingAlert) {
                waitingAlert.classList.remove('hidden');
            }
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

**Étapes** :
1. Récupère les données du formulaire
2. Convertit en JSON
3. Envoie via fetch POST
4. Affiche le message d'attente si succès

---

## Fonction 2: `validateOpponentScore()`

**Objectif** : Valider le score de l'adversaire

```javascript
function validateOpponentScore() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
        console.error('CSRF token non trouvé');
        return;
    }

    fetch(`{{ route('player-matches.validate-opponent-score', $playerMatch) }}`, {
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
            const validationAlert = document.getElementById('validation-alert');
            if (validationAlert) {
                if (result.status === 'completed') {
                    // Match finalisé
                    validationAlert.innerHTML = `
                        <div class="bg-green-50 border-2 border-green-200 rounded-lg p-4">
                            <p class="text-green-900 font-semibold">✅ Match finalisé!</p>
                            <p class="text-green-700 text-sm">Les deux joueurs ont validé le résultat.</p>
                        </div>
                    `;
                    // Rediriger après 2 secondes
                    setTimeout(() => {
                        window.location.href = '{{ route("player-matches.index") }}';
                    }, 2000);
                }
            }
        }
    })
    .catch(error => console.error('Erreur validation:', error));
}
```

**Étapes** :
1. Envoie POST à `/validate-opponent-score`
2. Si succès et status = 'completed' :
   - Affiche message vert
   - Redirige après 2s

---

## Fonction 3: `rejectValidation()`

**Objectif** : Refuser la validation

```javascript
function rejectValidation() {
    const csrfToken = document.querySelector('input[name="_token"]')?.value;
    if (!csrfToken) {
        console.error('CSRF token non trouvé');
        return;
    }

    fetch(`{{ route('player-matches.reject-score-validation', $playerMatch) }}`, {
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
            const validationAlert = document.getElementById('validation-alert');
            if (validationAlert) {
                validationAlert.classList.add('hidden');
            }
            // Afficher un message de confirmation
            alert('Validation refusée. Vous pouvez corriger les scores.');
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

**Étapes** :
1. Envoie POST à `/reject-score-validation`
2. Ferme la modal
3. Affiche alerte de confirmation

---

## Fonction 4: `checkValidationStatus()` - Polling

**Objectif** : Vérifier l'état de validation toutes les 2 secondes

```javascript
let rejectionAlertShown = false;  // Flag pour éviter les alertes en boucle

function checkValidationStatus() {
    fetch(`/api/player-matches/${matchId}/validation-status`)
        .then(response => response.json())
        .then(data => {
            const validationAlert = document.getElementById('validation-alert');
            const waitingAlert = document.getElementById('waiting-alert');
            
            // CAS 1: Refus de validation
            if (!data.creator_validated && !data.opponent_validated && data.status === 'confirmed') {
                console.log('❌ Validation refusée par l\'adversaire');
                if (validationAlert) {
                    validationAlert.classList.add('hidden');
                }
                if (waitingAlert) {
                    waitingAlert.classList.add('hidden');
                }
                // Afficher un message d'alerte une seule fois
                if (!rejectionAlertShown) {
                    rejectionAlertShown = true;
                    alert('L\'adversaire a refusé la validation. Vous pouvez corriger les scores.');
                }
                return;
            }
            
            // CAS 2: L'adversaire a validé
            if (data.opponent_validated && !data.creator_validated && validationAlert) {
                validationAlert.classList.remove('hidden');
                console.log('✅ L\'adversaire a validé le score!');
            }
            
            // CAS 3: Les deux ont validé
            if (data.creator_validated && data.opponent_validated && data.status === 'completed') {
                console.log('✅ Match finalisé!');
                if (validationAlert) {
                    validationAlert.innerHTML = `
                        <div class="bg-green-50 border-2 border-green-200 rounded-lg p-4">
                            <p class="text-green-900 font-semibold">✅ Match finalisé!</p>
                            <p class="text-green-700 text-sm">Les deux joueurs ont validé le résultat.</p>
                        </div>
                    `;
                    // Rediriger après 2 secondes
                    setTimeout(() => {
                        window.location.href = '{{ route("player-matches.index") }}';
                    }, 2000);
                }
            }
            
            // Réinitialiser le flag si les validations reprennent
            if (data.creator_validated || data.opponent_validated) {
                rejectionAlertShown = false;
            }
        })
        .catch(error => console.error('Erreur vérification validation:', error));
}
```

**Étapes** :
1. Fetch `/api/player-matches/{id}/validation-status`
2. Détecte 3 cas :
   - **Refus** : Masque les alertes, affiche message une fois
   - **Validation adversaire** : Affiche modal
   - **Deux validations** : Affiche message vert, redirige
3. Réinitialise le flag si nouvelles validations

---

## Initialisation

```javascript
// Lancer le polling au chargement
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded appelé');
    
    // Vérifier l'état initial
    checkValidationStatus();
    
    // Lancer le polling toutes les 2 secondes
    setInterval(() => {
        checkValidationStatus();
    }, 2000);
    
    validateForm();
});
```

---

## Points Clés

### 1. Flag `rejectionAlertShown`

**Problème** : L'alerte s'affichait en boucle toutes les 2 secondes

**Solution** : Flag qui se met à true après la première alerte

```javascript
if (!rejectionAlertShown) {
    rejectionAlertShown = true;
    alert('...');
}

// Réinitialiser si les validations reprennent
if (data.creator_validated || data.opponent_validated) {
    rejectionAlertShown = false;
}
```

### 2. CSRF Token

**Deux façons de l'obtenir** :

```javascript
// Depuis un input hidden
const csrfToken = document.querySelector('input[name="_token"]')?.value;

// Depuis une meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
```

### 3. Conversion FormData en JSON

```javascript
const formData = new FormData(form);
const data = {
    creator_result: formData.get('creator_result'),
    creator_primary_points: parseInt(formData.get('creator_primary_points')) || 0,
    // ...
};
```

### 4. Polling Interval

```javascript
// Toutes les 2 secondes
setInterval(() => {
    checkValidationStatus();
}, 2000);
```

---

## Debugging

### Console Logs

```javascript
console.log('✅ Score enregistré:', result.message);
console.log('❌ Validation refusée par l\'adversaire');
console.log('✅ L\'adversaire a validé le score!');
console.log('✅ Match finalisé!');
console.error('Erreur:', error);
```

### Vérifier l'État

Ouvrir la console et exécuter :

```javascript
// Vérifier l'état actuel
fetch(`/api/player-matches/${matchId}/validation-status`)
    .then(r => r.json())
    .then(d => console.log(d));
```

---

## Adaptation pour Tournois

Le code est identique, seules les routes changent :

```javascript
// Matchs simples
fetch(`{{ route('player-matches.validate-opponent-score', $playerMatch) }}`, ...)

// Tournois
fetch(`{{ route('tournament-matches.validate-opponent-score', $tournamentMatch) }}`, ...)
```
