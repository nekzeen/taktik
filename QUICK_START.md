# ⚡ Démarrage Rapide

## 🎯 Objectif
Créer des tournois de test pour Nek, Nekzeen et les 10 joueurs de test.

---

## 🚀 Exécution en 3 Étapes

### Étape 1: Exécuter les Migrations
```bash
php artisan migrate
```

### Étape 2: Créer les Tournois de Test
```bash
# Option A: Réinitialiser complètement la BD (recommandé)
php artisan migrate:fresh --seed

# Option B: Créer uniquement les tournois
php artisan seed:test-tournaments

# Option C: Utiliser le script bash
bash setup-test-tournaments.sh
```

### Étape 3: Accéder aux Tournois
```
http://localhost/tournaments
```

---

## 📧 Identifiants de Connexion

| Email | Mot de passe |
|-------|--------------|
| nek@test.fr | password |
| nekzeen@test.fr | password |
| jean.dupont@test.fr | password |
| marie.martin@test.fr | password |
| pierre.durand@test.fr | password |
| sophie.bernard@test.fr | password |
| luc.petit@test.fr | password |
| emma.dubois@test.fr | password |
| thomas.robert@test.fr | password |
| julie.richard@test.fr | password |
| antoine.moreau@test.fr | password |
| camille.simon@test.fr | password |

---

## 📊 Résultat

✅ **12 tournois créés** :
- Nek : créateur d'1 tournoi
- Nekzeen : créateur d'1 tournoi
- 10 joueurs de test : créateurs de 10 tournois

✅ **Chaque tournoi** :
- Créateur : 1 utilisateur
- Inscrits : 11 autres utilisateurs
- Listes d'armée : Pré-validées

---

## 🧪 Tester le Système

### Test 1: Validation Réussie
1. Se connecter avec 2 comptes différents
2. Créer un match
3. Joueur 1 : Enregistrer les scores
4. Joueur 2 : Confirmer la validation
5. ✅ Les deux sont redirigés

### Test 2: Refus de Validation
1. Se connecter avec 2 comptes différents
2. Créer un match
3. Joueur 1 : Enregistrer les scores
4. Joueur 2 : Refuser la validation
5. ✅ Les deux reviennent à la page de score

---

## 📝 Commandes Utiles

### Vérifier les Utilisateurs
```bash
php artisan tinker
>>> User::count()  # Doit afficher 12
>>> User::pluck('name', 'email')
```

### Vérifier les Tournois
```bash
php artisan tinker
>>> Tournament::count()  # Doit afficher 12
>>> Tournament::with('creator')->get()->pluck('name', 'creator.name')
```

### Réinitialiser la BD
```bash
php artisan migrate:fresh --seed
```

---

## 🎯 Prochaines Étapes

1. ✅ Exécuter les migrations
2. ✅ Créer les tournois
3. ✅ Se connecter
4. ✅ Tester le système
5. ✅ Déboguer si nécessaire

---

## 📚 Documentation Complète

- `SETUP_SUMMARY.md` - Résumé complet
- `docs/TEST_TOURNAMENTS_SETUP.md` - Configuration détaillée
- `docs/TOURNAMENT_MATCH_TEST_GUIDE.md` - Guide de test
- `docs/PLAYER_MATCH_END_SYSTEM.md` - Vue d'ensemble du système

---

## ✨ C'est Prêt !

Exécutez simplement :
```bash
php artisan migrate:fresh --seed
```

Et accédez à : `http://localhost/tournaments`

Bon test ! 🎮
