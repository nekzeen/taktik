# 🔑 IDENTIFIANTS D'ACCÈS

## 🚀 INTERFACE ADMIN FILAMENT

### **URL d'accès**
```
https://dev2.gaelmorvan.fr/admin
```

### **Identifiants Super Admin**
```
Email: nekzeen@gmail.com
Password: password
```

---

## ✅ COMPTE VÉRIFIÉ

- ✅ Utilisateur existe dans la base de données
- ✅ Mot de passe réinitialisé
- ✅ Rôle `super-admin` assigné
- ✅ Accès au panel Filament autorisé

---

## 🔐 MODIFIER LE MOT DE PASSE

### **Méthode 1 : Via l'interface Filament**
```
1. Se connecter sur https://dev2.gaelmorvan.fr/admin
2. Cliquer sur votre nom (coin supérieur droit)
3. Profile
4. Changer le mot de passe
```

### **Méthode 2 : Via Artisan Tinker**
```bash
php artisan tinker

$user = App\Models\User::where('email', 'admin@wh40k.local')->first();
$user->password = bcrypt('nouveau_mot_de_passe');
$user->save();
```

### **Méthode 3 : Créer un nouveau compte**
```bash
php artisan make:filament-user
```

---

## 👥 CRÉER D'AUTRES UTILISATEURS

### **Via l'interface Filament**
```
1. Se connecter
2. Aller sur "Users" dans la sidebar
3. Cliquer sur "New User"
4. Remplir le formulaire
5. Assigner un rôle
6. Sauvegarder
```

### **Via Artisan**
```bash
php artisan make:filament-user
```

---

## 🎭 RÔLES DISPONIBLES

### **super-admin**
- Accès complet à tout
- Gestion des utilisateurs
- Gestion des rôles et permissions
- Toutes les ressources Filament

### **admin**
- Gestion des tournois
- Gestion des listes d'armées
- Gestion des utilisateurs (limité)
- Gestion des matchs

### **moderator**
- Validation des listes d'armées
- Consultation des tournois
- Gestion des matchs

### **player**
- Pas d'accès au panel admin
- Interface joueur uniquement

---

## 🔒 SÉCURITÉ

### **Recommandations**
1. ✅ Changer le mot de passe par défaut
2. ✅ Utiliser un mot de passe fort (12+ caractères)
3. ✅ Ne pas partager les identifiants
4. ✅ Créer des comptes séparés pour chaque admin

### **Mot de passe fort**
```
Minimum recommandé:
- 12 caractères
- Majuscules et minuscules
- Chiffres
- Caractères spéciaux

Exemple: Admin2025!Wh40k#Secure
```

---

## 🆘 PROBLÈMES DE CONNEXION

### **"Invalid credentials"**
```bash
# Réinitialiser le mot de passe
php artisan tinker

$user = App\Models\User::where('email', 'admin@wh40k.local')->first();
$user->password = bcrypt('password');
$user->save();
```

### **"Access denied" / Page blanche**
```bash
# Vérifier les rôles
php artisan tinker

$user = App\Models\User::where('email', 'admin@wh40k.local')->first();
$user->assignRole('super-admin');
```

### **"User not found"**
```bash
# Créer un nouvel utilisateur
php artisan make:filament-user
```

---

## 📊 VÉRIFIER UN COMPTE

```bash
php artisan tinker

$user = App\Models\User::where('email', 'admin@wh40k.local')->first();
echo "Nom: " . $user->name;
echo "Email: " . $user->email;
echo "Rôles: " . $user->roles->pluck('name')->implode(', ');
```

---

## 🎯 CONNEXION RAPIDE

1. **Aller sur** : https://dev2.gaelmorvan.fr/admin
2. **Email** : admin@wh40k.local
3. **Password** : password
4. **Cliquer** : Sign in
5. **✅ Connecté !**

---

## 📝 NOTES

- Le mot de passe est haché avec bcrypt (sécurisé)
- Les sessions expirent après 2 heures d'inactivité
- Le "Remember me" garde la session 2 semaines
- Les tentatives de connexion sont limitées (throttling)

---

**Dernière mise à jour** : 20 octobre 2025, 23h09
**Status** : ✅ Identifiants vérifiés et fonctionnels
