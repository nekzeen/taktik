# 🔍 PROCÉDURE D'AUDIT COMPLET - POUR COMPRENDRE LA LOGIQUE MÉTIER

**Objectif**: Éviter de documenter la structure technique sans comprendre la logique métier réelle

---

## ⚠️ ERREURS À ÉVITER

❌ Lire les migrations en premier
❌ Faire des requêtes SQL pour comprendre les statuts
❌ Supposer que les statuts en base = affichage utilisateur
❌ Ignorer les méthodes des modèles (getStatusLabel(), canJoin(), etc.)
❌ Ne pas lire les contrôleurs
❌ Ne pas vérifier les exemples concrets

---

## ✅ PROCÉDURE CORRECTE D'AUDIT

### ÉTAPE 1 : LIRE LES MODÈLES (PRIORITÉ 1)

**Chercher les méthodes importantes :**

```bash
grep -r "public function" app/Models/ | grep -E "get|can|is|determine"
```

**Exemples à chercher :**
- `getStatusLabel()` - Comment le statut est affiché
- `canJoin()` - Conditions pour rejoindre
- `canSetScore()` - Permissions
- `isAvailable()` - Vérifications de disponibilité
- `determineWinner()` - Logique de calcul

**Exemple trouvé :**
```php
public function getStatusLabel(): string
{
    if ($this->status === 'open' && !$this->is_setup_validated) {
        return 'Configuration en cours';  // ⭐ LOGIQUE MÉTIER !
    }
    return match($this->status) { ... };
}
```

### ÉTAPE 2 : LIRE LES CONTRÔLEURS (PRIORITÉ 2)

**Chercher les filtres et vérifications :**

```bash
grep -r "where\|filter\|can" app/Http/Controllers/
```

**Exemples à chercher :**
- Filtres de liste (quels enregistrements sont affichés)
- Vérifications de permissions
- Conditions pour certaines actions

**Exemple trouvé :**
```php
$availableMatches = PlayerMatch::where('status', 'open')
    ->where('opponent_id', null)
    ->get()
    ->filter(fn($match) => $match->isAvailable())
    ->filter(fn($match) => $match->canJoin($user))  // ⭐ VÉRIFICATION !
```

### ÉTAPE 3 : LIRE LES VUES (PRIORITÉ 3)

**Chercher comment les données sont affichées :**

```bash
find resources/views -name "*.blade.php" | xargs grep -l "status\|label"
```

**Exemples à chercher :**
- Comment les statuts sont affichés
- Quels éléments sont conditionnels
- Quels boutons/actions sont disponibles

### ÉTAPE 4 : VÉRIFIER LES EXEMPLES CONCRETS (PRIORITÉ 4)

**Chercher des données réelles :**

```bash
php artisan tinker
>>> PlayerMatch::find(10)->getStatusLabel()
>>> PlayerMatch::find(10)->canJoin($user)
```

**Tracer le flux complet :**
- Créer un match
- Vérifier son statut
- Vérifier ce que les autres voient
- Accepter une demande
- Vérifier le changement

### ÉTAPE 5 : LIRE LES MIGRATIONS (PRIORITÉ 5)

**Maintenant que vous comprenez la logique :**

```bash
cat database/migrations/*create_player_matches_table.php
```

**Vérifier :**
- Quels champs existent
- Quels champs sont nullable
- Les types de données

### ÉTAPE 6 : DOCUMENTER (PRIORITÉ 6)

**Ordre de documentation :**

1. **Logique métier** (ce que l'utilisateur voit et peut faire)
2. **Statuts réels** (valeurs en base)
3. **Affichage** (getStatusLabel())
4. **Permissions** (canJoin(), canSetScore())
5. **Workflow** (flux complet)
6. **Structure technique** (migrations, tables)

---

## 📋 CHECKLIST D'AUDIT

### Pour chaque entité (Match, Mission, Tournoi, etc.)

- [ ] **Modèles** : Lire toutes les méthodes publiques
- [ ] **Contrôleurs** : Lire les filtres et vérifications
- [ ] **Vues** : Lire comment c'est affiché
- [ ] **Exemples** : Vérifier avec des données réelles
- [ ] **Logique métier** : Comprendre le flux complet
- [ ] **Statuts** : Identifier tous les statuts (réels + affichage)
- [ ] **Permissions** : Identifier toutes les vérifications
- [ ] **Migrations** : Vérifier la structure

---

## 🔍 EXEMPLE : AUDIT DU PLAYER MATCH

### Étape 1 : Modèles
```php
// app/Models/PlayerMatch.php
public function getStatusLabel(): string { ... }  // ⭐ Affichage
public function canJoin(User $user): bool { ... }  // ⭐ Permission
public function isAvailable(): bool { ... }  // ⭐ Vérification
```

### Étape 2 : Contrôleurs
```php
// app/Http/Controllers/PlayerMatchController.php
$availableMatches = PlayerMatch::where('status', 'open')
    ->filter(fn($match) => $match->canJoin($user))  // ⭐ Filtre
```

### Étape 3 : Vues
```blade
<!-- resources/views/player-matches/index.blade.php -->
@foreach($availableMatches as $match)
    {{ $match->getStatusLabel() }}  // ⭐ Affichage
@endforeach
```

### Étape 4 : Exemples Concrets
```
Match 10 (Gaël Morvan):
- status = 'open'
- is_setup_validated = false
- getStatusLabel() = "Configuration en cours"
- canJoin($user) = false
- Affichage: ❌ Invisible
```

### Étape 5 : Migrations
```php
// database/migrations/create_player_matches_table.php
$table->enum('status', ['open', 'confirmed', 'completed', 'cancelled']);
$table->boolean('is_setup_validated')->default(false);
```

### Étape 6 : Documentation
```
LOGIQUE MÉTIER:
- Match créé en "Configuration en cours"
- Après validation: "Ouvert"
- Autres joueurs ne voient que les matchs "Ouvert"

STATUTS:
- status='open' + is_setup_validated=false → "Configuration en cours"
- status='open' + is_setup_validated=true → "Ouvert"

PERMISSIONS:
- canJoin() retourne true SI is_setup_validated=true
```

---

## 🚀 COMMANDES RAPIDES D'AUDIT

```bash
# 1. Lire les modèles
grep -r "public function" app/Models/PlayerMatch.php

# 2. Lire les contrôleurs
grep -r "PlayerMatch" app/Http/Controllers/PlayerMatchController.php

# 3. Lire les vues
find resources/views -name "*player-match*"

# 4. Vérifier les données réelles
php artisan tinker
>>> PlayerMatch::find(10)->toArray()
>>> PlayerMatch::find(10)->getStatusLabel()
>>> PlayerMatch::find(10)->canJoin(Auth::user())

# 5. Lire les migrations
grep -A 20 "create_player_matches_table" database/migrations/*.php
```

---

## 📝 RÉSUMÉ

**Pour comprendre une application :**

1. ✅ Lire les **modèles** (logique métier)
2. ✅ Lire les **contrôleurs** (filtres et permissions)
3. ✅ Lire les **vues** (affichage)
4. ✅ Vérifier les **exemples concrets**
5. ✅ Lire les **migrations** (structure)
6. ✅ Documenter dans cet ordre

**Ne pas :**
- ❌ Commencer par les migrations
- ❌ Supposer les statuts sans lire le code
- ❌ Ignorer les méthodes des modèles
- ❌ Oublier les exemples concrets

---

**Cette procédure doit être appliquée systématiquement pour chaque audit.**

