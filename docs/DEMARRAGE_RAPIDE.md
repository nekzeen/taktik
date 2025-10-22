# 🚀 DÉMARRAGE RAPIDE

## ⚡ En 3 minutes

### 1. Lancer l'application
```bash
cd /var/www/clients/client2/web12/web
php artisan serve
```

### 2. Accéder à l'admin
```
URL: http://localhost/admin
Email: admin@wh40k.local
Password: password
```

### 3. C'est tout ! ✅

---

## 🎯 Que faire ensuite ?

### **Explorer l'interface**
- Cliquer sur "Tournaments" dans la sidebar
- Créer un nouveau tournoi
- Tester les filtres et la recherche
- Essayer le dark mode (icône lune en haut à droite)

### **Gérer les utilisateurs**
- Aller sur "Users"
- Voir la liste des utilisateurs
- Créer un nouvel utilisateur
- Assigner des rôles

### **Valider des listes d'armées**
- Aller sur "Army Lists"
- Filtrer par status "pending"
- Valider ou rejeter une liste

---

## 📚 Documentation

- **Guide complet** : [FILAMENT_PRET.md](FILAMENT_PRET.md)
- **Index** : [INDEX.md](INDEX.md)
- **Projet** : [projet.md](projet.md)

---

## 🔑 Comptes de test

```
Super Admin:
admin@wh40k.local / password

Admin:
admin2@wh40k.local / password

Moderator:
moderator@wh40k.local / password

Player:
player@wh40k.local / password
```

---

## 🛠️ Commandes utiles

```bash
# Vider les caches
php artisan optimize:clear

# Créer une ressource Filament
php artisan make:filament-resource ModelName --generate

# Créer un utilisateur admin
php artisan make:filament-user

# Reset la base de données
php artisan migrate:fresh --seed
```

---

## ❓ Problèmes ?

### **Erreur 500**
```bash
php artisan optimize:clear
chmod -R 775 storage bootstrap/cache
```

### **Page blanche**
```bash
php artisan filament:clear-cached-components
php artisan view:clear
```

### **Accès refusé**
Vérifier que l'utilisateur a le rôle admin :
```bash
php artisan tinker
>>> $user = User::where('email', 'admin@wh40k.local')->first();
>>> $user->assignRole('super-admin');
```

---

## 🎉 C'est parti !

Vous êtes prêt à utiliser votre interface d'administration professionnelle !

**Bon développement ! 🚀**
