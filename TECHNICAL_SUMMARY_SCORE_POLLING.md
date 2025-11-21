# Résumé Technique - Correction de la Mise à Jour Automatique des Scores

**Date** : 21 novembre 2025  
**Auteur** : Cascade  
**Statut** : ✅ Complété

---

## 🎯 Objectif

Corriger la mise à jour automatique des scores sur toutes les pages (créateur, adversaire, spectateur) pour assurer une synchronisation bidirectionnelle en temps réel.

---

## 🔍 Analyse du Problème

### Problème Principal
Les scores de l'adversaire ne se mettaient pas à jour automatiquement sur la page `/player-matches/{id}/score` (page du créateur).

### Cause Racine
- La fonction `loadOpponentScores()` n'existait pas dans `test-score.blade.php`
- Pas de polling pour charger les scores depuis la base de données
- Les champs `opponent_*` n'étaient pas en lecture seule

### Problèmes Secondaires
1. **Spectateur** : API retournait les mauvaises données (colonnes principales au lieu de brouillons)
2. **Total des scores** : Calcul incorrect (concaténation de strings)
3. **Images** : Déploiement déformé, terrain ne s'affichait pas
4. **Missions** : Missions secondaires affichées en spectateur (non désiré)

---

## 💡 Solution Implémentée

### 1. Polling Bidirectionnel

**Créateur** (`/player-matches/{id}/score`):
```javascript
// Polling toutes les 2 secondes
setInterval(() => {
    loadOpponentScores(); // Charge les scores de l'adversaire
}, 2000);

function loadOpponentScores() {
    return fetch(`/api/player-matches/${matchId}/get-draft-scores`)
        .then(response => response.json())
        .then(data => {
            // Met à jour les champs opponent_* (lecture seule)
            document.getElementById('opponent_primary_points').value = 
                data.opponent_primary_points || 0;
            // ...
            updateTotals();
        });
}
```

**Adversaire** (`/player-matches/{id}/score/opponent`):
```javascript
// Même logique mais charge les scores du créateur
// (stockés dans creator_* dans draft_scores)
```

### 2. Champs en Lecture Seule

```blade
<style>
    .opponent-readonly-buttons button {
        display: none !important;
    }
</style>

<input type="number" 
       id="opponent_primary_points" 
       readonly 
       name="opponent_primary_points" 
       ... />

<div class="flex items-center gap-2 opponent-readonly-buttons">
    <button type="button" onclick="return false; // ...">-</button>
    ...
</div>
```

### 3. API Spectateur Corrigée

**Avant** :
```php
return response()->json([
    'creator_score' => $playerMatch->creator_score ?? 0,
    'opponent_score' => $playerMatch->opponent_score ?? 0,
    // Retourne les colonnes principales (scores finalisés)
]);
```

**Après** :
```php
$draftScores = $playerMatch->draft_scores ?? [];

return response()->json([
    'creator_primary_points' => $draftScores['creator_primary_points'] ?? 0,
    'creator_secondary_points' => $draftScores['creator_secondary_points'] ?? 0,
    'creator_painting_points' => $draftScores['creator_painting_points'] ?? false,
    'opponent_primary_points' => $draftScores['opponent_primary_points'] ?? 0,
    'opponent_secondary_points' => $draftScores['opponent_secondary_points'] ?? 0,
    'opponent_painting_points' => $draftScores['opponent_painting_points'] ?? false,
    // Retourne les scores brouillons (temps réel)
]);
```

### 4. Conversion de Types

**Avant** :
```javascript
const creatorTotal = (data.creator_primary_points || 0) + 
                    (data.creator_secondary_points || 0) + 
                    (data.creator_painting_points ? 10 : 0);
// "10" + "5" + 10 = "1055" (concaténation)
```

**Après** :
```javascript
const creatorPrimary = parseInt(data.creator_primary_points) || 0;
const creatorSecondary = parseInt(data.creator_secondary_points) || 0;
const creatorPainting = data.creator_painting_points ? 10 : 0;
const creatorTotal = creatorPrimary + creatorSecondary + creatorPainting;
// 10 + 5 + 10 = 25 (addition correcte)
```

### 5. Images Corrigées

**Déploiement** :
```blade
@php
    $deploymentCard = \App\Models\StrikeForceDeploymentCard::where('name', 'LIKE', '%' . str_replace(' ', '%', $match->deployment_mode) . '%')->first();
@endphp
@if($deploymentCard && $deploymentCard->image_path)
    <img src="{{ asset('storage/' . $deploymentCard->image_path) }}" 
         class="w-full h-48 object-contain rounded">
@endif
```

**Terrain** :
```blade
@if($match->terrainLayout && $match->terrainLayout->image_path)
    <img src="{{ asset('storage/' . $match->terrainLayout->image_path) }}" 
         class="w-full h-48 object-contain rounded">
@endif
```

---

## 📊 Architecture de Synchronisation

```
┌─────────────────────────────────────────────────────────────┐
│                    Base de Données                          │
│                  (player_matches)                           │
│                                                             │
│  draft_scores: {                                           │
│    creator_primary_points: "10",                           │
│    creator_secondary_points: "5",                          │
│    creator_painting_points: true,                          │
│    opponent_primary_points: "10",                          │
│    opponent_secondary_points: "0",                         │
│    opponent_painting_points: true                          │
│  }                                                          │
└─────────────────────────────────────────────────────────────┘
                           ▲
                           │
        ┌──────────────────┼──────────────────┐
        │                  │                  │
        ▼                  ▼                  ▼
    ┌────────┐         ┌────────┐        ┌──────────┐
    │Créateur│         │Adversaire│      │Spectateur│
    │/score  │         │/score/opp│      │/spectate │
    └────────┘         └────────┘        └──────────┘
        │                  │                  │
        │ saveScoringData()│ saveScoringData()│
        │ (creator_*)      │ (opponent_*)     │ (lecture seule)
        │                  │                  │
        └──────────────────┼──────────────────┘
                           │
                    API: save-draft-scores
                           │
                           ▼
                    Mise à jour draft_scores
                           │
        ┌──────────────────┼──────────────────┐
        │                  │                  │
        ▼                  ▼                  ▼
    loadOpponentScores() loadOpponentScores() loadScores()
    (polling 2s)         (polling 2s)         (polling 2s)
        │                  │                  │
        │ API: get-draft-scores              │
        │ API: spectator-scores              │
        │                  │                  │
        ▼                  ▼                  ▼
    Affiche opponent_*  Affiche creator_*  Affiche tous
    (lecture seule)     (lecture seule)     (lecture seule)
```

---

## 🔐 Sécurité

### Contrôle d'Accès
- **Créateur** : Peut modifier `creator_*`, voit `opponent_*` en lecture seule
- **Adversaire** : Peut modifier `opponent_*`, voit `creator_*` en lecture seule
- **Spectateur** : Voit tous les scores en lecture seule

### Vérifications Côté Serveur
```php
// SpectatorMatchController
private function isPlayerMatchVisible(PlayerMatch $playerMatch): bool
{
    // Visible si confirmé ou complété
    return in_array($playerMatch->status, ['confirmed', 'completed']);
}
```

### Vérifications Côté Client
```javascript
// Attribut readonly empêche la modification
<input readonly ... />

// CSS masque les boutons
.opponent-readonly-buttons button { display: none !important; }

// Événements change/input ne modifient pas les champs
```

---

## 🧪 Tests

### Tests Manuels Effectués
1. ✅ Créateur modifie son score → Adversaire voit la mise à jour
2. ✅ Adversaire modifie son score → Créateur voit la mise à jour
3. ✅ Spectateur voit les deux scores en temps réel
4. ✅ Total des scores calculé correctement
5. ✅ Images du déploiement et du terrain s'affichent
6. ✅ Champs `opponent_*` en lecture seule

### Tests Recommandés
```bash
# Compilation
npm run build

# Cache
php artisan cache:clear
php artisan view:cache

# Tests unitaires (à créer)
php artisan test tests/Feature/ScoreSyncTest.php
```

---

## 📝 Points Importants

### Scores Brouillons vs Finalisés
- **Brouillons** : Sauvegardés automatiquement, affichés en temps réel
- **Finalisés** : Remplis quand le match est complété

### Conversion de Types
- Les scores brouillons sont des **strings** dans JSON
- Toujours utiliser `parseInt()` avant d'additionner
- Les booléens sont correctement typés

### Polling
- Intervalle : 2 secondes
- Décalage côté serveur : 2 secondes (pour éviter les affichages prématurés)
- Debouncing : 2 secondes pour la sauvegarde

### Images
- Utiliser `object-contain` pour éviter la déformation
- Utiliser `image_path` avec `asset('storage/' . ...)`
- Vérifier que le fichier existe avant d'afficher

---

## 📦 Fichiers Modifiés

### Vues (3 fichiers)
1. `resources/views/player-matches/test-score.blade.php`
2. `resources/views/player-matches/spectate.blade.php`
3. `resources/views/tournaments/matches/spectate.blade.php`

### Contrôleurs (1 fichier)
1. `app/Http/Controllers/SpectatorMatchController.php`

### Documentation (2 fichiers)
1. `docs/PLAYER_MATCH_SCORING_SYSTEM.md` (mis à jour)
2. `CHANGELOG_SCORE_POLLING_FIX.md` (créé)

---

## 🚀 Déploiement

### Étapes
1. Compiler Tailwind CSS : `npm run build`
2. Vider le cache : `php artisan cache:clear`
3. Compiler les templates : `php artisan view:cache`
4. Tester les pages spectateur et de scoring

### Vérification Post-Déploiement
- [ ] Scores se mettent à jour en temps réel
- [ ] Champs `opponent_*` en lecture seule
- [ ] Images du déploiement et du terrain s'affichent
- [ ] Total des scores correct
- [ ] Pas de missions secondaires en spectateur

---

## 📞 Support

Pour toute question ou problème :
1. Consulter `CHANGELOG_SCORE_POLLING_FIX.md` pour les détails
2. Consulter `docs/PLAYER_MATCH_SCORING_SYSTEM.md` pour la documentation
3. Vérifier la section "Dépannage" dans la documentation

---

**Version** : 1.0  
**Date** : 21 novembre 2025  
**Auteur** : Cascade
