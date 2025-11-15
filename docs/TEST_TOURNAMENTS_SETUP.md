# Configuration des Tournois de Test

## 📋 Vue d'ensemble

Ce guide explique comment créer des tournois de test pour :
- **Nek** (nek@test.fr)
- **Nekzeen** (nekzeen@test.fr)
- **10 joueurs de test** (jean.dupont@test.fr, marie.martin@test.fr, etc.)

Chaque utilisateur sera créateur d'un tournoi et tous les autres utilisateurs seront inscrits dans ce tournoi.

---

## 🚀 Installation Rapide

### Option 1: Exécuter le Seeder Complet

```bash
# Réinitialiser la base de données et exécuter tous les seeders
php artisan migrate:fresh --seed
```

### Option 2: Exécuter Uniquement les Tournois de Test

```bash
# Exécuter uniquement le seeder de tournois de test
php artisan seed:test-tournaments
```

### Option 3: Exécuter Manuellement

```bash
# Exécuter le seeder spécifique
php artisan db:seed --class=TournamentTestSeeder
```

---

## 📊 Résultat Attendu

### Utilisateurs Créés

| Email | Nom | Rôle | Mot de passe |
|-------|-----|------|--------------|
| nek@test.fr | Nek | player | password |
| nekzeen@test.fr | Nekzeen | player | password |
| jean.dupont@test.fr | Jean Dupont | player | password |
| marie.martin@test.fr | Marie Martin | player | password |
| pierre.durand@test.fr | Pierre Durand | player | password |
| sophie.bernard@test.fr | Sophie Bernard | player | password |
| luc.petit@test.fr | Luc Petit | player | password |
| emma.dubois@test.fr | Emma Dubois | player | password |
| thomas.robert@test.fr | Thomas Robert | player | password |
| julie.richard@test.fr | Julie Richard | player | password |
| antoine.moreau@test.fr | Antoine Moreau | player | password |
| camille.simon@test.fr | Camille Simon | player | password |

### Tournois Créés

**12 tournois au total** :

1. **Tournoi Nek** (créateur: Nek)
   - Inscrits : Nekzeen + 10 joueurs de test
   
2. **Tournoi Nekzeen** (créateur: Nekzeen)
   - Inscrits : Nek + 10 joueurs de test
   
3. **Tournoi Jean Dupont** (créateur: Jean Dupont)
   - Inscrits : Nek, Nekzeen + 9 autres joueurs
   
4. **Tournoi Marie Martin** (créateur: Marie Martin)
   - Inscrits : Nek, Nekzeen + 9 autres joueurs
   
... et ainsi de suite pour chaque joueur

### Caractéristiques des Tournois

- **Statut** : Open (ouvert)
- **Max joueurs** : 8
- **Points** : 2000
- **Location** : En ligne
- **Date de début** : +7 jours
- **Date de fin** : +8 jours
- **Listes d'armée** : Pré-validées pour les tests

---

## 🧪 Test des Tournois

### Accéder aux Tournois

1. Aller sur `http://localhost/tournaments`
2. Se connecter avec l'un des comptes de test
3. Voir les tournois créés et les tournois auxquels on est inscrit

### Tester la Gestion de Fin de Match

1. Créer un match dans un tournoi
2. Accéder à la page de scoring
3. Tester le système de validation :
   - ✅ Enregistrer les scores
   - ✅ Valider mutuellement
   - ✅ Refuser la validation
   - ✅ Redirection après finalisation

### Tester les Permissions

1. Essayer d'accéder à un tournoi créé par quelqu'un d'autre
2. Vérifier que les permissions sont correctes
3. Vérifier que seuls les joueurs inscrits peuvent participer

---

## 📝 Fichiers Modifiés/Créés

### Seeders
- ✅ `database/seeders/TournamentTestSeeder.php` - Crée les tournois de test
- ✅ `database/seeders/DatabaseSeeder.php` - Modifié pour inclure le nouveau seeder

### Commandes
- ✅ `app/Console/Commands/SeedTestTournaments.php` - Commande pour exécuter le seeder

---

## 🔍 Vérification

### Vérifier les Utilisateurs

```bash
php artisan tinker
>>> User::all()->pluck('name', 'email')
```

### Vérifier les Tournois

```bash
php artisan tinker
>>> Tournament::with('creator')->get()->map(fn($t) => "{$t->name} (créateur: {$t->creator->name})")
```

### Vérifier les Inscriptions

```bash
php artisan tinker
>>> $tournament = Tournament::find(1)
>>> $tournament->armyLists()->with('user')->get()->pluck('user.name')
```

---

## 🐛 Troubleshooting

### Erreur: "Aucune faction trouvée"

**Cause** : Les factions n'ont pas été créées

**Solution** : Exécuter d'abord les migrations et les seeders de factions

```bash
php artisan migrate
php artisan db:seed --class=FactionSeeder  # Si ce seeder existe
```

### Erreur: "Utilisateurs de test non trouvés"

**Cause** : Le seeder `TestPlayersSeeder` n'a pas été exécuté

**Solution** : Exécuter le seeder complet

```bash
php artisan migrate:fresh --seed
```

### Les tournois ne s'affichent pas

**Cause** : Les utilisateurs ne sont pas connectés

**Solution** : Se connecter avec l'un des comptes de test

---

## 📚 Ressources Additionnelles

- Documentation complète : `docs/PLAYER_MATCH_END_SYSTEM.md`
- Guide de test : `docs/TOURNAMENT_MATCH_TEST_GUIDE.md`
- Implémentation tournois : `docs/TOURNAMENT_MATCH_IMPLEMENTATION.md`

---

## ✅ Checklist

- [ ] Exécuter la migration
- [ ] Exécuter le seeder de tournois de test
- [ ] Vérifier que les utilisateurs sont créés
- [ ] Vérifier que les tournois sont créés
- [ ] Vérifier que les inscriptions sont correctes
- [ ] Se connecter avec un compte de test
- [ ] Accéder à un tournoi
- [ ] Tester la gestion de fin de match
- [ ] Tester les permissions

---

## 🎯 Prochaines Étapes

1. **Exécuter le seeder** : `php artisan seed:test-tournaments`
2. **Se connecter** : Utiliser l'un des comptes de test
3. **Tester** : Créer un match et tester la validation
4. **Déboguer** : Vérifier les logs si des erreurs surviennent

Bon test ! 🚀
