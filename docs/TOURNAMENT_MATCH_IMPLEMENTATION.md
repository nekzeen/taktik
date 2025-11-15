# Implémentation - Gestion de Fin de Match pour Tournois

## ✅ Étapes Complétées

### 1. Base de Données ✅
- **Migration créée** : `2025_11_07_000001_add_score_validation_to_tournament_matches.php`
- **Colonnes ajoutées** :
  - `player1_score_validated` (boolean, default false)
  - `player2_score_validated` (boolean, default false)

### 2. Modèle TournamentMatch ✅
- **Fillable** : Ajout de `player1_score_validated` et `player2_score_validated`
- **Casts** : Ajout des casts booléens
- **Méthodes** :
  - `determineWinner()` - Détermine le gagnant basé sur les scores
  - `resetValidation()` - Réinitialise les validations

### 3. Contrôleur TournamentMatchController ✅
- **Méthode 1** : `setScore()` - Enregistrer les scores
- **Méthode 2** : `validateOpponentScore()` - Valider et finaliser
- **Méthode 3** : `rejectScoreValidation()` - Refuser la validation
- **Méthode 4** : `getValidationStatus()` - Récupérer l'état (polling)

### 4. Routes ✅
```php
POST /tournament-matches/{tournamentMatch}/set-score
POST /tournament-matches/{tournamentMatch}/validate-opponent-score
POST /tournament-matches/{tournamentMatch}/reject-score-validation
GET /api/tournament-matches/{tournamentMatch}/validation-status
```

---

## 📝 À Faire : Vues Blade

### Structure des Vues

Les vues de scoring pour les tournois doivent être adaptées des matchs simples :

**Fichiers à créer/modifier** :
- `resources/views/tournaments/matches/score-player1.blade.php`
- `resources/views/tournaments/matches/score-player2.blade.php`

### Éléments à Ajouter

#### 1. Modal de Validation (Identique aux matchs simples)

```blade
<!-- Modal de validation de l'adversaire - Modal centré -->
<div id="validation-alert" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-red-50 border-4 border-red-300 rounded-xl p-8 max-w-md mx-4 shadow-2xl">
        <div class="text-center">
            <p class="text-4xl mb-4">🔔</p>
            <p class="text-red-900 font-bold text-xl mb-2">{{ $match->player2->name }} a validé le score!</p>
            <p class="text-red-700 text-base mb-6">Veuillez confirmer pour finaliser le match.</p>
            <div class="flex gap-3">
                <button type="button" onclick="validateOpponentScore()" 
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg text-base transition">
                    ✓ Confirmer
                </button>
                <button type="button" onclick="rejectValidation()" 
                    class="flex-1 bg-gray-400 hover:bg-gray-500 text-white font-bold py-3 px-6 rounded-lg text-base transition">
                    ✗ Refuser
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Message d'attente de validation -->
<div id="waiting-alert" class="hidden mb-6">
    <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4">
        <p class="text-blue-900 font-semibold">⏳ Score enregistré. En attente de la validation de l'autre joueur...</p>
        <p class="text-blue-700 text-sm mt-1">Vous pouvez fermer cette page, vous serez notifié quand l'adversaire validera.</p>
    </div>
</div>
```

#### 2. Formulaire de Scoring

```blade
<form id="score-form" action="{{ route('tournament-matches.set-score', $match) }}" 
      method="POST" onsubmit="submitScoreForm(event); return false;">
    @csrf
    
    <!-- Résultat du match -->
    <div class="space-y-2 mb-4">
        <label class="block text-sm font-semibold text-gray-900">Résultat du match *</label>
        <div class="space-y-1">
            <label class="flex items-center">
                <input type="radio" name="player_result" value="nul" class="mr-2">
                <span class="text-sm text-gray-700">Nul</span>
            </label>
            <label class="flex items-center">
                <input type="radio" name="player_result" value="player1_abandon" class="mr-2">
                <span class="text-sm text-gray-700">{{ $match->player1->name }} abandonne</span>
            </label>
            <!-- Autres options... -->
        </div>
    </div>

    <!-- Points -->
    <div class="space-y-2 mb-4">
        <label class="block text-sm font-semibold text-gray-900">Points Primaires</label>
        <input type="number" name="player1_primary_points" id="player1_primary_points" 
               class="w-full px-3 py-2 border border-gray-300 rounded" value="0">
    </div>

    <!-- Bouton de soumission -->
    <button type="submit" id="submit_btn" 
            class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700">
        Fin du match
    </button>
</form>
```

#### 3. JavaScript (Identique aux matchs simples, adapter les IDs)

```javascript
const matchId = {{ $match->id }};
let rejectionAlertShown = false;

// Soumettre le formulaire
function submitScoreForm(event) {
    event.preventDefault();
    
    const form = document.getElementById('score-form');
    const formData = new FormData(form);
    const csrfToken = document.querySelector('input[name="_token"]')?.value;
    
    const data = {
        player_result: formData.get('player_result'),
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
            const waitingAlert = document.getElementById('waiting-alert');
            if (waitingAlert) {
                waitingAlert.classList.remove('hidden');
            }
        }
    });
}

// Valider le score de l'adversaire
function validateOpponentScore() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch(`{{ route('tournament-matches.validate-opponent-score', $match) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({}),
    })
    .then(response => response.json())
    .then(result => {
        if (result.success && result.status === 'completed') {
            setTimeout(() => {
                window.location.href = '{{ route("tournaments.show", $match->tournament) }}';
            }, 2000);
        }
    });
}

// Refuser la validation
function rejectValidation() {
    const csrfToken = document.querySelector('input[name="_token"]')?.value;
    
    fetch(`{{ route('tournament-matches.reject-score-validation', $match) }}`, {
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
            document.getElementById('validation-alert').classList.add('hidden');
            alert('Validation refusée. Vous pouvez corriger les scores.');
        }
    });
}

// Polling pour vérifier l'état de validation
let rejectionAlertShown = false;

function checkValidationStatus() {
    fetch(`/api/tournament-matches/${matchId}/validation-status`)
        .then(response => response.json())
        .then(data => {
            const validationAlert = document.getElementById('validation-alert');
            const waitingAlert = document.getElementById('waiting-alert');
            
            // Si refus
            if (!data.player1_validated && !data.player2_validated && data.status === 'confirmed') {
                if (!rejectionAlertShown) {
                    rejectionAlertShown = true;
                    alert('L\'adversaire a refusé la validation. Vous pouvez corriger les scores.');
                }
                if (waitingAlert) waitingAlert.classList.add('hidden');
                return;
            }

            // Si l'adversaire a validé
            if (data.player2_validated && !data.player1_validated) {
                validationAlert.classList.remove('hidden');
            }

            // Si les deux ont validé
            if (data.player1_validated && data.player2_validated && data.status === 'completed') {
                setTimeout(() => {
                    window.location.href = '{{ route("tournaments.show", $match->tournament) }}';
                }, 2000);
            }

            // Réinitialiser le flag
            if (data.player1_validated || data.player2_validated) {
                rejectionAlertShown = false;
            }
        });
}

// Lancer le polling
setInterval(() => {
    checkValidationStatus();
}, 2000);

// Initialisation au chargement
document.addEventListener('DOMContentLoaded', function() {
    checkValidationStatus();
});
```

---

## 🔄 Différences Clés Tournois vs Matchs Simples

### Noms des Colonnes

| Matchs Simples | Tournois |
|---|---|
| `creator_score_validated` | `player1_score_validated` |
| `opponent_score_validated` | `player2_score_validated` |
| `creator_score` | `player1_score` |
| `opponent_score` | `player2_score` |

### Noms des Routes

| Matchs Simples | Tournois |
|---|---|
| `player-matches.set-score` | `tournament-matches.set-score` |
| `player-matches.validate-opponent-score` | `tournament-matches.validate-opponent-score` |
| `player-matches.reject-score-validation` | `tournament-matches.reject-score-validation` |

### Redirection Finale

| Matchs Simples | Tournois |
|---|---|
| `/player-matches` | `/tournaments/{id}` |

### Données Supplémentaires (Tournois)

Les tournois incluent :
- Missions primaires/secondaires
- Zones de déploiement
- Terrains
- Points de victoire
- Numéro de table
- Numéro de round

---

## 📋 Checklist d'Implémentation

### Vues Blade

- [ ] Créer/modifier `score-player1.blade.php`
  - [ ] Ajouter modal de validation
  - [ ] Ajouter message d'attente
  - [ ] Ajouter formulaire de scoring
  - [ ] Ajouter JavaScript

- [ ] Créer/modifier `score-player2.blade.php`
  - [ ] Ajouter modal de validation
  - [ ] Ajouter message d'attente
  - [ ] Ajouter formulaire de scoring
  - [ ] Ajouter JavaScript

### Tests

- [ ] Tester l'enregistrement des scores
- [ ] Tester la validation réussie
- [ ] Tester le refus de validation
- [ ] Tester le polling
- [ ] Tester les redirections
- [ ] Tester les permissions (non-joueurs)

### Sécurité

- [ ] Vérifier l'authentification
- [ ] Vérifier l'autorisation (joueurs du match)
- [ ] Vérifier les CSRF tokens
- [ ] Vérifier la validation des données

---

## 🚀 Prochaines Étapes

1. **Adapter les vues** : Copier la structure des matchs simples
2. **Tester le système** : Vérifier tous les flux
3. **Documenter** : Ajouter des commentaires dans le code
4. **Monitorer** : Ajouter des logs pour le debugging

---

## 📚 Références

- Documentation complète : `docs/PLAYER_MATCH_END_SYSTEM.md`
- JavaScript détaillé : `docs/PLAYER_MATCH_JAVASCRIPT.md`
- API endpoints : `docs/PLAYER_MATCH_API.md`
