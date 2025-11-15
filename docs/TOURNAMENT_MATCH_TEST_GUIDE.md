# Guide de Test - Gestion de Fin de Match Tournois

## ✅ Implémentation Complète

### Fichiers Modifiés
- ✅ `app/Models/TournamentMatch.php` - Modèle avec méthodes
- ✅ `app/Http/Controllers/TournamentMatchController.php` - 4 méthodes ajoutées
- ✅ `routes/web.php` - 4 routes ajoutées
- ✅ `database/migrations/2025_11_07_000001_add_score_validation_to_tournament_matches.php` - Migration
- ✅ `resources/views/tournaments/matches/score-player1.blade.php` - Vue modifiée
- ✅ `resources/views/tournaments/matches/score-player2.blade.php` - Vue modifiée

---

## 🧪 Plan de Test

### Phase 1: Préparation
- [ ] Exécuter la migration
- [ ] Vérifier que les colonnes existent en base de données
- [ ] Vérifier que les routes sont enregistrées

### Phase 2: Test Unitaire
- [ ] Tester `setScore()` - Enregistrer les scores
- [ ] Tester `validateOpponentScore()` - Valider
- [ ] Tester `rejectScoreValidation()` - Refuser
- [ ] Tester `getValidationStatus()` - Polling

### Phase 3: Test d'Intégration
- [ ] Scénario 1: Validation réussie
- [ ] Scénario 2: Refus de validation
- [ ] Scénario 3: Permissions (non-joueurs)

### Phase 4: Test UI/UX
- [ ] Modal de validation s'affiche correctement
- [ ] Message d'attente s'affiche correctement
- [ ] Redirection fonctionne
- [ ] Alertes s'affichent une seule fois

---

## 🚀 Étapes de Test Réelles

### Étape 1: Exécuter la Migration

```bash
php artisan migrate
```

**Vérification** :
```bash
php artisan tinker
>>> DB::table('tournament_matches')->first()
# Vérifier que les colonnes player1_score_validated et player2_score_validated existent
```

### Étape 2: Vérifier les Routes

```bash
php artisan route:list | grep tournament-matches
```

**Résultat attendu** :
```
POST   /tournament-matches/{tournamentMatch}/set-score
POST   /tournament-matches/{tournamentMatch}/validate-opponent-score
POST   /tournament-matches/{tournamentMatch}/reject-score-validation
GET    /api/tournament-matches/{tournamentMatch}/validation-status
```

### Étape 3: Test Scénario 1 - Validation Réussie

#### Préparation
1. Créer un tournoi avec 2 joueurs
2. Créer un match entre les 2 joueurs
3. Ouvrir deux navigateurs (ou onglets privés) :
   - **Navigateur 1** : Joueur 1
   - **Navigateur 2** : Joueur 2

#### Étapes
1. **Joueur 1** : Accéder à `/tournaments/{id}/matches/{match}/score/player1`
2. **Joueur 1** : Remplir les scores et cliquer "Fin du match"
3. **Vérification Joueur 1** :
   - ✅ Message bleu "⏳ En attente de validation..." apparaît
   - ✅ Pas de redirection immédiate
4. **Joueur 2** : Accéder à `/tournaments/{id}/matches/{match}/score/player2`
5. **Vérification Joueur 2** :
   - ✅ Modal rouge "🔔 Joueur 1 a validé le score!" apparaît
6. **Joueur 2** : Cliquer "✓ Confirmer"
7. **Vérification Joueur 2** :
   - ✅ Modal devient verte "✅ Match finalisé!"
   - ✅ Redirection vers `/tournaments/{id}/matches` après 2s
8. **Vérification Joueur 1** :
   - ✅ Modal verte apparaît
   - ✅ Redirection vers `/tournaments/{id}/matches` après 2s

#### Vérification Base de Données
```bash
php artisan tinker
>>> $match = TournamentMatch::find(1)
>>> $match->player1_score_validated  # true
>>> $match->player2_score_validated  # true
>>> $match->status                    # 'completed'
>>> $match->winner_id                 # ID du gagnant
```

---

### Étape 4: Test Scénario 2 - Refus de Validation

#### Préparation
Même que Scénario 1

#### Étapes
1. **Joueur 1** : Remplir les scores et cliquer "Fin du match"
2. **Joueur 2** : Voir la modal et cliquer "✗ Refuser"
3. **Vérification Joueur 2** :
   - ✅ Modal se ferme
   - ✅ Alerte : "Validation refusée. Vous pouvez corriger les scores."
4. **Vérification Joueur 1** :
   - ✅ Message bleu disparaît
   - ✅ Alerte : "L'adversaire a refusé la validation. Vous pouvez corriger les scores."
   - ✅ Alerte n'apparaît qu'une seule fois (pas de boucle)
5. **Les deux joueurs** : Peuvent modifier les scores et réessayer

#### Vérification Base de Données
```bash
php artisan tinker
>>> $match = TournamentMatch::find(1)
>>> $match->player1_score_validated  # false
>>> $match->player2_score_validated  # false
>>> $match->status                    # 'confirmed'
```

---

### Étape 5: Test Scénario 3 - Permissions

#### Test 1: Non-joueur essaie d'enregistrer les scores

```bash
# En tant que joueur non impliqué
curl -X POST http://localhost/tournament-matches/1/set-score \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: token" \
  -d '{"player1_primary_points": 10, ...}'
```

**Résultat attendu** :
```json
{
    "error": "Non autorisé"
}
```

#### Test 2: Non-joueur essaie de valider

```bash
curl -X POST http://localhost/tournament-matches/1/validate-opponent-score \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: token" \
  -d '{}'
```

**Résultat attendu** :
```json
{
    "error": "Non autorisé"
}
```

---

## 📊 Checklist de Vérification

### Base de Données
- [ ] Migration exécutée sans erreur
- [ ] Colonnes `player1_score_validated` et `player2_score_validated` existent
- [ ] Valeurs par défaut à `false`

### Routes
- [ ] 4 routes enregistrées
- [ ] Routes protégées par `auth` middleware
- [ ] Noms de routes corrects

### Contrôleur
- [ ] `setScore()` enregistre les scores
- [ ] `validateOpponentScore()` valide et finalise
- [ ] `rejectScoreValidation()` réinitialise
- [ ] `getValidationStatus()` retourne l'état correct

### Vues
- [ ] Modal de validation s'affiche
- [ ] Message d'attente s'affiche
- [ ] Formulaire intercepte la soumission
- [ ] JavaScript exécute correctement

### Polling
- [ ] Polling toutes les 2 secondes
- [ ] Détecte les changements d'état
- [ ] Affiche les alertes correctement
- [ ] Pas de boucle infinie d'alertes

### Redirections
- [ ] Redirection après validation réussie
- [ ] Redirection après refus
- [ ] Redirection vers la bonne page

### Sécurité
- [ ] Authentification requise
- [ ] Autorisation vérifiée (joueurs du match)
- [ ] CSRF token validé
- [ ] Données validées

---

## 🐛 Debugging

### Vérifier les Logs

```bash
tail -f storage/logs/laravel.log
```

### Vérifier la Console du Navigateur

Ouvrir DevTools (F12) → Console

**Logs attendus** :
```
✅ Score enregistré: Score enregistré. En attente de la validation de l'autre joueur...
✅ L'adversaire a validé le score!
✅ Match finalisé!
❌ Validation refusée par l'adversaire
```

### Vérifier les Requêtes Réseau

DevTools → Network

**Requêtes attendues** :
```
POST /tournament-matches/1/set-score
POST /tournament-matches/1/validate-opponent-score
POST /tournament-matches/1/reject-score-validation
GET /api/tournament-matches/1/validation-status
```

---

## ✅ Critères de Succès

- ✅ Les deux joueurs peuvent enregistrer leurs scores
- ✅ Les deux joueurs peuvent valider mutuellement
- ✅ Les deux joueurs peuvent refuser la validation
- ✅ Le match se finalise quand les deux valident
- ✅ Les deux joueurs sont redirigés après finalisation
- ✅ Les alertes s'affichent correctement
- ✅ Pas d'alertes en boucle
- ✅ Les permissions sont vérifiées
- ✅ Les données sont validées
- ✅ Les CSRF tokens sont vérifiés

---

## 📝 Notes

### Points Importants
1. **Polling** : Toutes les 2 secondes, pas plus rapide pour éviter les surcharges
2. **Flag** : `rejectionAlertShown` évite les alertes en boucle
3. **Redirection** : Après 2 secondes pour laisser le temps de voir le message
4. **Permissions** : Vérifiées côté serveur, pas côté client
5. **Validation** : Données validées avant traitement

### Améliorations Futures
- Ajouter des notifications en temps réel (WebSocket)
- Ajouter un système de timeout (ex: 5 minutes)
- Ajouter des logs d'audit
- Ajouter des emails de notification
- Ajouter une page d'historique des validations

---

## 🎯 Résumé

Le système de gestion de fin de match pour les tournois est maintenant **complètement implémenté** et prêt pour les tests réels !

**Prochaines étapes** :
1. Exécuter la migration
2. Tester les 3 scénarios
3. Vérifier les permissions
4. Vérifier la base de données
5. Valider le flux complet
