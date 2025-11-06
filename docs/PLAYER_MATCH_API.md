# API Endpoints - Gestion de Fin de Match

## Vue d'ensemble

Les endpoints permettent de :
1. Enregistrer les scores
2. Valider les scores
3. Refuser la validation
4. Récupérer l'état de validation

---

## Endpoints

### 1. POST `/player-matches/{playerMatch}/set-score`

**Description** : Enregistrer les scores du joueur actuel

**Authentification** : Requise (Auth)

**Méthode HTTP** : POST

**Content-Type** : application/json

**Request Body** :
```json
{
    "creator_result": "nul|creator_abandon|opponent_abandon|creator_table_rase|opponent_table_rase",
    "creator_primary_points": 0,
    "creator_secondary_points": 0,
    "creator_painting_points": true,
    "opponent_primary_points": 0,
    "opponent_secondary_points": 0,
    "opponent_painting_points": true
}
```

**Response (200 OK)** :
```json
{
    "success": true,
    "message": "Score enregistré. En attente de la validation de l'autre joueur...",
    "status": "confirmed",
    "creator_validated": true,
    "opponent_validated": false
}
```

**Response (403 Forbidden)** :
```json
{
    "error": "Vous ne pouvez pas enregistrer le score de ce match."
}
```

**Logique** :
- Valide les données
- Calcule les scores totaux
- Marque le joueur actuel comme validé
- Retourne l'état du match

---

### 2. POST `/player-matches/{playerMatch}/validate-opponent-score`

**Description** : Valider le score de l'adversaire et finaliser si les deux ont validé

**Authentification** : Requise (Auth)

**Méthode HTTP** : POST

**Content-Type** : application/json

**Request Body** :
```json
{}
```

**Response (200 OK) - Match finalisé** :
```json
{
    "success": true,
    "message": "Match finalisé avec succès !",
    "status": "completed",
    "creator_validated": true,
    "opponent_validated": true
}
```

**Response (200 OK) - En attente** :
```json
{
    "success": true,
    "message": "Score validé.",
    "status": "confirmed",
    "creator_validated": true,
    "opponent_validated": true
}
```

**Response (403 Forbidden)** :
```json
{
    "error": "Vous ne pouvez pas valider le score de ce match."
}
```

**Logique** :
- Marque le joueur actuel comme validé
- Si les deux ont validé :
  - Change le statut à 'completed'
  - Définit la date/heure de fin
  - Détermine le gagnant
- Retourne l'état du match

---

### 3. POST `/player-matches/{playerMatch}/reject-score-validation`

**Description** : Refuser la validation et réinitialiser

**Authentification** : Requise (Auth)

**Méthode HTTP** : POST

**Content-Type** : application/json

**Request Body** :
```json
{}
```

**Response (200 OK)** :
```json
{
    "success": true,
    "message": "Validation refusée. Veuillez corriger les scores.",
    "status": "confirmed",
    "creator_validated": false,
    "opponent_validated": false
}
```

**Response (403 Forbidden)** :
```json
{
    "error": "Vous ne pouvez pas refuser la validation de ce match."
}
```

**Logique** :
- Réinitialise `creator_score_validated` à false
- Réinitialise `opponent_score_validated` à false
- Remet le statut à 'confirmed'
- Retourne l'état du match

---

### 4. GET `/api/player-matches/{playerMatch}/validation-status`

**Description** : Récupérer l'état actuel de la validation (pour le polling)

**Authentification** : Requise (Auth)

**Méthode HTTP** : GET

**Request Body** : N/A

**Response (200 OK)** :
```json
{
    "creator_validated": true,
    "opponent_validated": false,
    "status": "confirmed",
    "creator_name": "Jean Dupont",
    "opponent_name": "Marie Martin"
}
```

**Logique** :
- Retourne l'état actuel sans modification
- Utilisé par le polling JavaScript

---

## Flux d'Appels API

### Scénario 1: Validation Réussie

```
1. Joueur 1 clique "Fin du match"
   ↓
   POST /set-score
   → creator_validated = true
   → Response: status = "confirmed"
   
2. Polling toutes les 2s
   ↓
   GET /validation-status
   → creator_validated = true, opponent_validated = false
   
3. Joueur 2 reçoit notification
   ↓
   Joueur 2 clique "Confirmer"
   ↓
   POST /validate-opponent-score
   → opponent_validated = true
   → Les deux validés → status = "completed"
   → Response: status = "completed"
   
4. Polling détecte la finalisation
   ↓
   GET /validation-status
   → status = "completed"
   → Redirection vers /player-matches
```

### Scénario 2: Refus de Validation

```
1. Joueur 1 clique "Fin du match"
   ↓
   POST /set-score
   → creator_validated = true
   
2. Polling détecte la validation
   ↓
   GET /validation-status
   → opponent_validated = false
   → Affiche modal
   
3. Joueur 2 clique "Refuser"
   ↓
   POST /reject-score-validation
   → creator_validated = false
   → opponent_validated = false
   → status = "confirmed"
   → Response: status = "confirmed"
   
4. Polling détecte le refus
   ↓
   GET /validation-status
   → creator_validated = false, opponent_validated = false
   → Affiche alerte "Validation refusée"
   → Les deux reviennent à la page de score
```

---

## Codes d'Erreur

| Code | Message | Cause |
|------|---------|-------|
| 200 | OK | Succès |
| 403 | Non autorisé | L'utilisateur n'est pas un des deux joueurs |
| 422 | Validation échouée | Les données ne respectent pas les règles |
| 500 | Erreur serveur | Erreur interne |

---

## Validation des Données

### `creator_result`

Valeurs acceptées :
- `nul` : Match nul
- `creator_abandon` : Le créateur abandonne
- `opponent_abandon` : L'adversaire abandonne
- `creator_table_rase` : Le créateur a table rase
- `opponent_table_rase` : L'adversaire a table rase

### Points

- `creator_primary_points` : 0-20 (entier)
- `creator_secondary_points` : 0-15 (entier)
- `creator_painting_points` : true/false (booléen)
- `opponent_primary_points` : 0-20 (entier)
- `opponent_secondary_points` : 0-15 (entier)
- `opponent_painting_points` : true/false (booléen)

---

## Exemple cURL

### Enregistrer les scores

```bash
curl -X POST http://localhost/player-matches/1/set-score \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your_csrf_token" \
  -H "Authorization: Bearer your_token" \
  -d '{
    "creator_result": "nul",
    "creator_primary_points": 10,
    "creator_secondary_points": 5,
    "creator_painting_points": true,
    "opponent_primary_points": 8,
    "opponent_secondary_points": 4,
    "opponent_painting_points": false
  }'
```

### Valider le score

```bash
curl -X POST http://localhost/player-matches/1/validate-opponent-score \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your_csrf_token" \
  -H "Authorization: Bearer your_token" \
  -d '{}'
```

### Refuser la validation

```bash
curl -X POST http://localhost/player-matches/1/reject-score-validation \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your_csrf_token" \
  -H "Authorization: Bearer your_token" \
  -d '{}'
```

### Récupérer l'état

```bash
curl -X GET http://localhost/api/player-matches/1/validation-status \
  -H "Authorization: Bearer your_token"
```

---

## Implémentation pour Tournois

Les endpoints pour les tournois sont identiques, seules les routes changent :

```php
// Matchs simples
Route::post('/player-matches/{playerMatch}/set-score', ...);
Route::post('/player-matches/{playerMatch}/validate-opponent-score', ...);
Route::post('/player-matches/{playerMatch}/reject-score-validation', ...);
Route::get('/api/player-matches/{playerMatch}/validation-status', ...);

// Tournois
Route::post('/tournament-matches/{tournamentMatch}/set-score', ...);
Route::post('/tournament-matches/{tournamentMatch}/validate-opponent-score', ...);
Route::post('/tournament-matches/{tournamentMatch}/reject-score-validation', ...);
Route::get('/api/tournament-matches/{tournamentMatch}/validation-status', ...);
```

Les contrôleurs peuvent être réutilisés ou dupliqués selon la structure du projet.

---

## Sécurité

### Authentification

Tous les endpoints requièrent une authentification valide :
- Vérifier que l'utilisateur est connecté
- Vérifier que l'utilisateur est l'un des deux joueurs

### CSRF Protection

Tous les POST requièrent un token CSRF valide :
```javascript
const csrfToken = document.querySelector('input[name="_token"]')?.value;
```

### Autorisation

Vérifier que l'utilisateur est autorisé :
```php
if ($playerMatch->creator_id !== $user->id && $playerMatch->opponent_id !== $user->id) {
    return response()->json(['error' => 'Non autorisé'], 403);
}
```

---

## Monitoring

### Logs Recommandés

```php
Log::info('Score enregistré', [
    'match_id' => $playerMatch->id,
    'user_id' => $user->id,
    'creator_score' => $playerMatch->creator_score,
    'opponent_score' => $playerMatch->opponent_score,
]);

Log::info('Validation refusée', [
    'match_id' => $playerMatch->id,
    'user_id' => $user->id,
]);
```

### Métriques

- Nombre de scores enregistrés
- Nombre de validations
- Nombre de refus
- Temps moyen entre enregistrement et validation
- Taux de refus
