# Changelog - Correction de la Mise à Jour Automatique des Scores

**Date** : 21 novembre 2025  
**Objectif** : Corriger la mise à jour automatique des scores sur toutes les pages (créateur, adversaire, spectateur)  
**Statut** : ✅ COMPLÉTÉ

---

## 📋 Résumé des Problèmes Identifiés et Résolus

### 1. ❌ Problème : Scores de l'adversaire non mis à jour sur `/player-matches/{id}/score`

**Symptôme** :
- Sur la page `/player-matches/{id}/score` (créateur Nekzeen), les scores de Nek ne se mettaient pas à jour
- Sur la page `/player-matches/{id}/score/opponent` (Nek), les scores de Nekzeen se mettaient à jour correctement

**Cause Racine** :
- La fonction `loadOpponentScores()` n'existait pas dans `test-score.blade.php`
- Pas de polling pour charger les scores de l'adversaire depuis la base de données

**Solution** :
- ✅ Ajout de la fonction `loadOpponentScores()` qui charge les scores depuis l'API `/api/player-matches/{id}/get-draft-scores`
- ✅ Ajout du polling toutes les 2 secondes
- ✅ Les champs `opponent_*` sont maintenant en `readonly` (lecture seule)
- ✅ Les boutons +/- sont masqués pour les scores de l'adversaire

**Fichiers modifiés** :
- `resources/views/player-matches/test-score.blade.php`

**Code ajouté** :
```blade
<style>
    .opponent-readonly-buttons button {
        display: none !important;
    }
</style>
```

```javascript
// Charger les scores de l'adversaire depuis la base de données (pour le polling)
function loadOpponentScores() {
    return fetch(`/api/player-matches/${matchId}/get-draft-scores`)
        .then(response => response.json())
        .then(data => {
            if (data && Object.keys(data).length > 0) {
                console.log('📥 Scores adversaire chargés:', data);
                // Mettre à jour SEULEMENT les scores de l'adversaire
                const opponentPrimaryInput = document.getElementById('opponent_primary_points');
                const opponentSecondaryInput = document.getElementById('opponent_secondary_points');
                const opponentPaintingInput = document.getElementById('opponent_painting_points');
                
                // Vérifier si les valeurs ont changé avant de mettre à jour
                if (opponentPrimaryInput && opponentPrimaryInput.value != data.opponent_primary_points) {
                    opponentPrimaryInput.value = data.opponent_primary_points || 0;
                }
                if (opponentSecondaryInput && opponentSecondaryInput.value != data.opponent_secondary_points) {
                    opponentSecondaryInput.value = data.opponent_secondary_points || 0;
                }
                if (opponentPaintingInput && opponentPaintingInput.checked != (data.opponent_painting_points !== false)) {
                    opponentPaintingInput.checked = data.opponent_painting_points !== false;
                }
                
                updateTotals();
            }
        })
        .catch(error => {
            console.error('Erreur chargement scores adversaire:', error);
        });
}

// Polling automatique toutes les 2 secondes
setInterval(() => {
    loadOpponentScores();
}, 2000);
```

**Attributs HTML modifiés** :
```blade
<!-- Avant -->
<input type="number" id="opponent_primary_points" name="opponent_primary_points" ... />

<!-- Après -->
<input type="number" id="opponent_primary_points" readonly name="opponent_primary_points" ... />
<div class="flex items-center gap-2 opponent-readonly-buttons">
    <button type="button" onclick="return false; // ..." class="...">-</button>
    ...
</div>
```

---

### 2. ❌ Problème : Scores non mis à jour en mode spectateur

**Symptôme** :
- Sur la page spectateur `/player-matches/{id}/spectate`, les scores ne se mettaient pas à jour automatiquement
- Le polling était présent mais retournait les mauvaises données

**Cause Racine** :
- L'API `getPlayerMatchScores()` retournait les colonnes principales (`creator_score`, `opponent_score`) au lieu des scores brouillons (`draft_scores`)
- Les scores brouillons sont les scores en temps réel saisis par les joueurs
- Les colonnes principales ne sont remplies que quand le match est finalisé

**Solution** :
- ✅ Modification de `getPlayerMatchScores()` pour retourner les scores brouillons
- ✅ Modification de `getTournamentMatchScores()` pour retourner les scores brouillons
- ✅ Les scores finalisés sont toujours retournés pour référence

**Fichiers modifiés** :
- `app/Http/Controllers/SpectatorMatchController.php`

**Code modifié** :
```php
// Avant
return response()->json([
    'creator_score' => $playerMatch->creator_score ?? 0,
    'opponent_score' => $playerMatch->opponent_score ?? 0,
    'creator_primary_points' => $playerMatch->creator_primary_points ?? 0,
    // ...
]);

// Après
$draftScores = $playerMatch->draft_scores ?? [];

return response()->json([
    // Retourner les scores brouillons (temps réel)
    'creator_primary_points' => $draftScores['creator_primary_points'] ?? 0,
    'creator_secondary_points' => $draftScores['creator_secondary_points'] ?? 0,
    'creator_painting_points' => $draftScores['creator_painting_points'] ?? false,
    'opponent_primary_points' => $draftScores['opponent_primary_points'] ?? 0,
    'opponent_secondary_points' => $draftScores['opponent_secondary_points'] ?? 0,
    'opponent_painting_points' => $draftScores['opponent_painting_points'] ?? false,
    // Scores finalisés (pour référence)
    'creator_score' => $playerMatch->creator_score ?? 0,
    'opponent_score' => $playerMatch->opponent_score ?? 0,
    // ...
]);
```

---

### 3. ❌ Problème : Total des scores affiché incorrectement en spectateur

**Symptôme** :
- Le total s'affichait comme "10510" au lieu de "25" (10 + 5 + 10)
- Les scores étaient concaténés comme des chaînes de caractères

**Cause Racine** :
- Les scores brouillons sont stockés en tant que **strings** dans la base de données JSON
- Le JavaScript additionnait les strings au lieu de les convertir en nombres

**Solution** :
- ✅ Conversion des scores en nombres avec `parseInt()`
- ✅ Calcul correct du total : `primary + secondary + painting`

**Fichiers modifiés** :
- `resources/views/player-matches/spectate.blade.php`
- `resources/views/tournaments/matches/spectate.blade.php`

**Code modifié** :
```javascript
// Avant
const creatorTotal = (data.creator_primary_points || 0) + 
                    (data.creator_secondary_points || 0) + 
                    (data.creator_painting_points ? 10 : 0);
// Résultat: "10" + "5" + 10 = "1055" (concaténation)

// Après
const creatorPrimary = parseInt(data.creator_primary_points) || 0;
const creatorSecondary = parseInt(data.creator_secondary_points) || 0;
const creatorPainting = data.creator_painting_points ? 10 : 0;
const creatorTotal = creatorPrimary + creatorSecondary + creatorPainting;
// Résultat: 10 + 5 + 10 = 25 (addition correcte)
```

---

### 4. ❌ Problème : Missions secondaires affichées en spectateur

**Symptôme** :
- Les missions secondaires et tactiques s'affichaient sur la page spectateur
- L'utilisateur ne voulait pas les afficher

**Solution** :
- ✅ Suppression de la section "Missions Secondaires"
- ✅ Suppression de la section "Missions Tactiques"

**Fichiers modifiés** :
- `resources/views/player-matches/spectate.blade.php`
- `resources/views/tournaments/matches/spectate.blade.php`

---

### 5. ❌ Problème : Images du déploiement et du terrain ne s'affichaient pas

**Symptôme** :
- L'image du déploiement était déformée
- L'image du terrain ne s'affichait pas du tout

**Cause Racine** :
- Image du déploiement : utilisait `object-cover` qui déformait l'image
- Image du terrain : utilisait `image_url` au lieu de `image_path`

**Solution** :
- ✅ Image du déploiement : `object-cover` → `object-contain`
- ✅ Image du terrain : `image_url` → `image_path` avec `asset('storage/' . ...)`
- ✅ Les deux images utilisent `object-contain` pour un affichage optimal

**Fichiers modifiés** :
- `resources/views/player-matches/spectate.blade.php`
- `resources/views/tournaments/matches/spectate.blade.php`

**Code modifié** :
```blade
<!-- Avant -->
<img src="{{ $match->terrainLayout->image_url }}" class="w-full h-48 object-cover rounded">

<!-- Après -->
@if($match->terrainLayout->image_path)
    <img src="{{ asset('storage/' . $match->terrainLayout->image_path) }}" 
         class="w-full h-48 object-contain rounded">
@endif
```

---

## 📊 Fichiers Modifiés

### Vues Blade
1. **`resources/views/player-matches/test-score.blade.php`**
   - Ajout du style CSS pour masquer les boutons
   - Ajout de l'attribut `readonly` sur les champs `opponent_*`
   - Ajout de la classe `opponent-readonly-buttons`
   - Ajout de la fonction `loadOpponentScores()`
   - Ajout du polling toutes les 2 secondes

2. **`resources/views/player-matches/spectate.blade.php`**
   - Suppression de la section "Missions Secondaires"
   - Suppression de la section "Missions Tactiques"
   - Correction de l'image du déploiement : `object-cover` → `object-contain`
   - Correction de l'image du terrain : `image_url` → `image_path`
   - Ajout de `object-contain` pour les deux images

3. **`resources/views/tournaments/matches/spectate.blade.php`**
   - Suppression de la section "Missions Secondaires"
   - Suppression de la section "Missions Tactiques"
   - Correction de l'image du déploiement : `object-cover` → `object-contain`
   - Correction de l'image du terrain : `image_url` → `image_path`
   - Ajout de `object-contain` pour les deux images

### Contrôleurs
1. **`app/Http/Controllers/SpectatorMatchController.php`**
   - Modification de `getPlayerMatchScores()` pour retourner les scores brouillons
   - Modification de `getTournamentMatchScores()` pour retourner les scores brouillons
   - Ajout de commentaires explicatifs

---

## 🔄 Flux de Mise à Jour Automatique

### Page Créateur (`/player-matches/{id}/score`)
```
Créateur modifie son score
    ↓
saveScoringData() sauvegarde en base (draft_scores)
    ↓
loadOpponentScores() polling toutes les 2s
    ↓
Affiche les scores de l'adversaire depuis draft_scores
```

### Page Adversaire (`/player-matches/{id}/score/opponent`)
```
Adversaire modifie son score
    ↓
saveScoringData() sauvegarde en base (draft_scores)
    ↓
loadOpponentScores() polling toutes les 2s
    ↓
Affiche les scores du créateur depuis draft_scores
```

### Page Spectateur (`/player-matches/{id}/spectate`)
```
Créateur/Adversaire modifie un score
    ↓
saveScoringData() sauvegarde en base (draft_scores)
    ↓
Polling toutes les 2s appelle /api/player-matches/{id}/spectator-scores
    ↓
API retourne les scores brouillons (draft_scores)
    ↓
updateScores() affiche les scores avec parseInt() pour conversion
```

---

## 🔐 Sécurité et Autorisation

### Lecture Seule des Scores de l'Adversaire
- Les champs `opponent_*` ont l'attribut `readonly`
- Les boutons +/- sont masqués avec CSS
- Les événements `change` et `input` ne modifient pas les champs
- Le polling met à jour les champs en lecture seule

### Vérifications Côté Serveur
- `SpectatorMatchController::isPlayerMatchVisible()` vérifie le statut du match
- Seuls les matchs `confirmed` ou `completed` sont visibles en spectateur
- L'API retourne les scores brouillons sans vérification d'authentification (publique)

---

## ✅ Tests Effectués

1. ✅ Compilation Tailwind CSS réussie
2. ✅ Cache vidé et templates compilés
3. ✅ API endpoints retournent les scores brouillons
4. ✅ Polling toutes les 2 secondes fonctionne
5. ✅ Scores de l'adversaire se mettent à jour en temps réel
6. ✅ Total des scores calculé correctement
7. ✅ Images du déploiement et du terrain s'affichent

---

## 📝 Notes Importantes

### Scores Brouillons vs Scores Finalisés
- **Scores brouillons** (`draft_scores`) : Sauvegardés automatiquement, affichés en temps réel
- **Scores finalisés** (`creator_score`, `opponent_score`) : Remplis quand le match est complété

### Conversion de Types
- Les scores brouillons sont stockés en tant que strings dans JSON
- Toujours utiliser `parseInt()` avant d'additionner les scores
- Les booléens (`painting_points`) sont correctement typés

### Images
- Utiliser `object-contain` pour éviter la déformation
- Utiliser `image_path` avec `asset('storage/' . ...)` pour les images stockées
- Utiliser `image_url` pour les URLs externes

---

## 🚀 Déploiement

1. Compiler Tailwind CSS : `npm run build`
2. Vider le cache : `php artisan cache:clear`
3. Compiler les templates : `php artisan view:cache`
4. Tester les pages spectateur et de scoring

---

**Auteur** : Cascade  
**Date** : 21 novembre 2025  
**Version** : 1.0
