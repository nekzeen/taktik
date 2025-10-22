# 🎮 Warhammer 40,000 Tournament Manager

Application Laravel 12 pour la gestion de tournois Warhammer 40,000 avec interface d'administration Filament.

## 🚀 Démarrage rapide

### Installation
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

### Lancer l'application
```bash
php artisan serve
npm run dev
```

### Accès
- **Site public** : http://localhost
- **Interface admin** : http://localhost/admin
- **Connexion** : admin@wh40k.local / password

## 📚 Documentation

Toute la documentation est disponible dans le dossier `/docs` :

- **[FILAMENT_PRET.md](docs/FILAMENT_PRET.md)** - Guide Filament (interface admin)
- **[projet.md](docs/projet.md)** - Cahier des charges complet
- **[INSTALLATION_COMPLETE.md](docs/INSTALLATION_COMPLETE.md)** - Récapitulatif installation

## 🎯 Fonctionnalités

### Interface Admin (Filament)
- ✅ Gestion des tournois
- ✅ Gestion des listes d'armées
- ✅ Gestion des utilisateurs
- ✅ Gestion des matchs
- ✅ Gestion des factions
- ✅ Dark mode natif
- ✅ Recherche et filtres avancés
- ✅ Export CSV/Excel

### Système de rôles
- **Super Admin** : Accès complet
- **Admin** : Gestion tournois et utilisateurs
- **Moderator** : Validation listes d'armées
- **Player** : Inscription tournois, upload listes
- **Visitor** : Consultation publique

## 🛠️ Technologies

- **Laravel** 12
- **Filament** 3.2 (Interface admin)
- **Livewire** 3 (Composants réactifs)
- **Tailwind CSS** (Design)
- **Spatie Permission** (Rôles & permissions)
- **MySQL** (Base de données)

## 📦 Structure

```
app/
├── Filament/          # Ressources Filament (admin)
├── Models/            # Modèles Eloquent
└── Policies/          # Policies d'autorisation

database/
├── migrations/        # Migrations (17 tables)
└── seeders/          # Seeders (rôles, users)

docs/                  # Documentation complète
```

## 🔐 Comptes de test

```
Super Admin:
- Email: admin@wh40k.local
- Password: password

Admin:
- Email: admin2@wh40k.local
- Password: password

Moderator:
- Email: moderator@wh40k.local
- Password: password

Player:
- Email: player@wh40k.local
- Password: password
```

## 🎨 Interface Admin

L'interface d'administration utilise **Filament**, le framework d'administration le plus moderne pour Laravel :

- Interface professionnelle et intuitive
- Dark mode natif
- 100% responsive
- Recherche et filtres avancés
- Export de données
- Gestion des relations
- Upload de fichiers
- Validation automatique

## 📝 Commandes utiles

```bash
# Créer une ressource Filament
php artisan make:filament-resource ModelName --generate

# Créer un utilisateur admin
php artisan make:filament-user

# Vider les caches
php artisan optimize:clear

# Lancer les tests
php artisan test
```

## 🌐 URLs importantes

- `/` - Page d'accueil publique
- `/admin` - Interface d'administration Filament
- `/login` - Connexion
- `/register` - Inscription

## 📖 Documentation Filament

- Site officiel : https://filamentphp.com
- Documentation : https://filamentphp.com/docs
- Démos : https://demo.filamentphp.com

## 🤝 Support

Pour toute question, consultez la documentation dans `/docs` ou la documentation officielle de Filament.

## 📄 Licence

Ce projet est sous licence MIT.

---

**Développé avec Laravel 12 & Filament 3.2** 🚀
