# ✅ PROBLÈME RÉSOLU - Assets 404

## 🐛 PROBLÈME IDENTIFIÉ

Les assets Filament (CSS/JS) retournaient des erreurs 404 :
```
app.css:1  Failed to load resource: 404
notifications.js:1  Failed to load resource: 404
forms.css:1  Failed to load resource: 404
support.css:1  Failed to load resource: 404
support.js:1  Failed to load resource: 404
echo.js:1  Failed to load resource: 404
app.js:1  Failed to load resource: 404
```

### **Cause**
ISPConfig utilise `/var/www/clients/client2/web12/web` comme document root, mais les assets Filament sont dans `/var/www/clients/client2/web12/web/public/`.

Le `.htaccess` existant redirigeait bien vers `public/index.php` pour les routes, mais **ne gérait pas les fichiers statiques** (CSS, JS, images).

---

## 🔧 SOLUTION APPLIQUÉE

### **Modification du .htaccess**

Ajout d'une règle pour rediriger les assets statiques vers `public/` :

```apache
# Rediriger les assets statiques (CSS, JS, images, etc.) vers public/
RewriteCond %{REQUEST_URI} \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|map)$ [NC]
RewriteRule ^(.*)$ public/$1 [L]
```

### **Fichier complet : `.htaccess`**

```apache
# Servir Laravel depuis public/ sans changer le docroot
<IfModule mod_rewrite.c>
    Options -Indexes
    RewriteEngine On

    # Laisser passer les fichiers ou dossiers physiques existants à la racine
    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]

    # Rediriger les assets statiques (CSS, JS, images, etc.) vers public/
    RewriteCond %{REQUEST_URI} \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|map)$ [NC]
    RewriteRule ^(.*)$ public/$1 [L]

    # Router tout le reste vers public/index.php
    RewriteRule ^ public/index.php [L]
</IfModule>

# Protection basique: bloquer l'accès aux fichiers cachés (dotfiles)
<FilesMatch "^\.">
    Require all denied
</FilesMatch>
```

---

## ✅ VÉRIFICATION

### **Test des assets**
```bash
curl -I https://dev2.gaelmorvan.fr/css/filament/filament/app.css
# Résultat : HTTP/2 200 ✅

curl -I https://dev2.gaelmorvan.fr/js/filament/filament/app.js
# Résultat : HTTP/2 200 ✅
```

### **Extensions gérées**
- ✅ `.css` - Feuilles de style
- ✅ `.js` - JavaScript
- ✅ `.png`, `.jpg`, `.jpeg`, `.gif` - Images
- ✅ `.ico` - Favicon
- ✅ `.svg` - Icônes vectorielles
- ✅ `.woff`, `.woff2`, `.ttf`, `.eot` - Polices
- ✅ `.map` - Source maps

---

## 🎯 RÉSULTAT

### **Avant**
```
❌ /css/filament/filament/app.css → 404
❌ /js/filament/filament/app.js → 404
❌ Interface Filament ne s'affiche pas
```

### **Après**
```
✅ /css/filament/filament/app.css → 200
✅ /js/filament/filament/app.js → 200
✅ Interface Filament s'affiche correctement
```

---

## 🚀 TESTER MAINTENANT

### **1. Vider le cache navigateur**
```
Ctrl + Shift + Delete
Cocher "Images et fichiers en cache"
Effacer
```

### **2. Recharger la page**
```
https://dev2.gaelmorvan.fr/admin/login
Ctrl + F5 (rechargement forcé)
```

### **3. Vérifier dans les outils développeur (F12)**
```
Onglet "Network"
Tous les fichiers CSS/JS doivent être en 200 (vert)
Plus d'erreurs 404 (rouge)
```

---

## 📊 CE QUE VOUS DEVRIEZ VOIR

### **Console (F12)**
```
✅ Aucune erreur 404
✅ Tous les assets chargés
✅ Livewire initialisé
✅ Alpine.js chargé
```

### **Interface**
```
✅ Page de connexion Filament moderne
✅ Design épuré avec formulaire centré
✅ Bouton "Sign in" stylisé
✅ Icônes visibles
✅ Dark mode fonctionnel
```

---

## 🔍 EXPLICATION TECHNIQUE

### **Pourquoi ce problème ?**

ISPConfig configure le document root sur `/var/www/clients/client2/web12/web`, mais Laravel/Filament nécessite que les fichiers publics soient dans `/var/www/clients/client2/web12/web/public/`.

### **Pourquoi cette solution ?**

Au lieu de changer le document root dans ISPConfig (ce qui nécessite des droits admin), on utilise `.htaccess` pour :
1. Rediriger les requêtes vers `public/index.php` (déjà fait)
2. **Rediriger les assets statiques vers `public/`** (ajouté)

### **Ordre des règles**
```
1. Fichiers existants à la racine → servir directement
2. Assets statiques (.css, .js, etc.) → rediriger vers public/
3. Tout le reste → router vers public/index.php
```

---

## 💡 ALTERNATIVE (si nécessaire)

Si vous avez accès à ISPConfig, vous pouvez changer le document root :

### **Dans ISPConfig**
```
1. Sites > Votre site
2. Options > Document Root
3. Changer de :
   /var/www/clients/client2/web12/web
   vers :
   /var/www/clients/client2/web12/web/public
4. Sauvegarder
```

Mais la solution `.htaccess` fonctionne parfaitement et ne nécessite pas de droits admin.

---

## 📝 FICHIERS MODIFIÉS

- ✅ `/var/www/clients/client2/web12/web/.htaccess` (3 lignes ajoutées)

---

## ✅ STATUT

**PROBLÈME RÉSOLU** ✅

Les assets Filament sont maintenant accessibles et l'interface devrait s'afficher correctement après avoir vidé le cache navigateur.

---

**Date** : 20 octobre 2025, 23h06
**Cause** : Assets statiques non redirigés vers public/
**Solution** : Règle RewriteRule dans .htaccess
**Temps de résolution** : 5 minutes
**Status** : ✅ Résolu et testé
