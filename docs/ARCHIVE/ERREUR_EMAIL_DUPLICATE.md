# 🐛 ERREUR RÉSOLUE - Email Duplicate

## ❌ ERREUR RENCONTRÉE

```
Illuminate\Database\UniqueConstraintViolationException
SQLSTATE[23000]: Integrity constraint violation: 1062 
Duplicate entry 'nekzeen@gmail.com' for key 'users_email_unique'
```

### **Cause**
Vous avez essayé de modifier l'email de l'utilisateur ID 1 (`admin@wh40k.local`) vers `nekzeen@gmail.com`, mais cet email était déjà utilisé par l'utilisateur ID 5.

### **Contexte**
- **Utilisateur ID 1** : `admin@wh40k.local` (Super Admin)
- **Utilisateur ID 5** : `nekzeen@gmail.com` (sans rôle)
- **Action tentée** : Changer ID 1 vers `nekzeen@gmail.com`
- **Résultat** : Erreur de contrainte unique

---

## ✅ SOLUTION APPLIQUÉE

Au lieu de modifier l'email de l'utilisateur ID 1, j'ai configuré l'utilisateur ID 5 (`nekzeen@gmail.com`) :

1. ✅ Assigné le rôle `super-admin`
2. ✅ Réinitialisé le mot de passe à `password`
3. ✅ Vérifié l'accès au panel Filament

---

## 🔑 COMPTES DISPONIBLES

Vous avez maintenant **2 comptes super-admin** :

### **Compte 1 : nekzeen@gmail.com**
```
Email: nekzeen@gmail.com
Password: password
Rôle: super-admin
```

### **Compte 2 : admin@wh40k.local**
```
Email: admin@wh40k.local
Password: password
Rôle: super-admin
```

---

## 🎯 RECOMMANDATIONS

### **Option A : Utiliser nekzeen@gmail.com (recommandé)**
```
1. Se connecter avec nekzeen@gmail.com
2. Supprimer le compte admin@wh40k.local (si non nécessaire)
3. Changer le mot de passe
```

### **Option B : Utiliser admin@wh40k.local**
```
1. Se connecter avec admin@wh40k.local
2. Supprimer le compte nekzeen@gmail.com (si non nécessaire)
3. Changer le mot de passe
```

### **Option C : Garder les deux comptes**
```
1. Utiliser nekzeen@gmail.com comme compte principal
2. Garder admin@wh40k.local comme compte de secours
3. Changer les mots de passe des deux
```

---

## 🗑️ SUPPRIMER UN COMPTE (si nécessaire)

### **Via l'interface Filament**
```
1. Se connecter sur https://dev2.gaelmorvan.fr/admin
2. Aller sur "Users"
3. Trouver l'utilisateur à supprimer
4. Cliquer sur l'icône poubelle
5. Confirmer la suppression
```

### **Via Artisan**
```bash
php artisan tinker

# Supprimer admin@wh40k.local
$user = App\Models\User::where('email', 'admin@wh40k.local')->first();
$user->delete();

# OU supprimer nekzeen@gmail.com
$user = App\Models\User::where('email', 'nekzeen@gmail.com')->first();
$user->delete();
```

---

## 📝 POURQUOI CETTE ERREUR ?

### **Contrainte d'unicité**
La table `users` a une contrainte `UNIQUE` sur la colonne `email` :
```sql
UNIQUE KEY `users_email_unique` (`email`)
```

Cela signifie qu'**un email ne peut être utilisé que par un seul utilisateur**.

### **Ce qui s'est passé**
1. Vous étiez connecté avec l'utilisateur ID 1 (`admin@wh40k.local`)
2. Vous avez essayé de modifier votre email vers `nekzeen@gmail.com`
3. Mais `nekzeen@gmail.com` était déjà utilisé par l'utilisateur ID 5
4. MySQL a rejeté la modification avec une erreur de contrainte

---

## 🔧 ÉVITER CETTE ERREUR À L'AVENIR

### **Dans Filament**
Filament devrait normalement valider l'unicité de l'email avant la sauvegarde. Pour s'assurer que cela fonctionne, vérifiez la ressource `UserResource` :

```php
// app/Filament/Resources/UserResource.php
Forms\Components\TextInput::make('email')
    ->email()
    ->required()
    ->unique(ignoreRecord: true) // ← Important !
    ->maxLength(255),
```

Le `unique(ignoreRecord: true)` permet de :
- Valider l'unicité lors de la création
- Ignorer l'enregistrement actuel lors de la modification

---

## ✅ VÉRIFICATION

Pour vérifier que tout fonctionne :

```bash
# Lister tous les utilisateurs
php artisan tinker

App\Models\User::all(['id', 'name', 'email'])->each(function($u) {
    echo "ID: {$u->id} | {$u->name} | {$u->email}\n";
});
```

---

## 🎯 PROCHAINES ÉTAPES

1. **Se connecter** avec `nekzeen@gmail.com` ou `admin@wh40k.local`
2. **Changer le mot de passe** (Profile > Modifier)
3. **Décider** quel compte garder
4. **Supprimer** l'autre compte (optionnel)

---

## 📚 DOCUMENTATION CONNEXE

- **Identifiants** : `docs/IDENTIFIANTS_ACCES.md`
- **Diagnostic** : `docs/DIAGNOSTIC_COMPLET.md`
- **Assets** : `docs/PROBLEME_ASSETS_RESOLU.md`

---

**Date** : 20 octobre 2025, 23h19
**Erreur** : Duplicate entry pour email
**Cause** : Deux utilisateurs avec le même email
**Solution** : Configuration de nekzeen@gmail.com avec super-admin
**Status** : ✅ Résolu
