# 🎮 Warhammer 40K Tournament Manager

Application Laravel 12 pour la gestion complète de tournois **Warhammer 40,000** avec système de matchs, validation de listes d'armées et interface d'administration Filament.

## 📋 Table des matières

- [Fonctionnalités](#-fonctionnalités)
- [Installation](#-installation)
- [Architecture](#-architecture)
- [Gestion des Tournois](#-gestion-des-tournois)
- [Système de Matchs](#-système-de-matchs)
- [Rôles et Permissions](#-rôles-et-permissions)
- [API et Routes](#-api-et-routes)
- [Commandes Utiles](#-commandes-utiles)
- [Support](#-support)

---

## 🎯 Fonctionnalités

### 🏆 Gestion des Tournois

- ✅ **Création de tournois** avec formats multiples (Élimination, Suisse, Ligue)
- ✅ **Formats d'armée** : Incursion (1000 pts), Force de Frappe (2000 pts), Offensive (3000 pts)
- ✅ **Limitation des tournois ouverts** selon le rôle :
  - Super-admin : Pas de limite
  - Admin : Max 10 tournois ouverts
  - Player : Max 1 tournoi ouvert
- ✅ **Statuts de tournoi** : Open, In Progress, Completed, Cancelled
- ✅ **Dates de tournoi** : Date de début et fin
- ✅ **Nombre de participants** : Min/Max configurable
- ✅ **Tri intelligent** : Tournois créés par l'utilisateur en premier

### 📝 Gestion des Inscriptions

- ✅ **Inscription des joueurs** avec upload de liste d'armée (PDF)
- ✅ **Validation des listes** par le créateur du tournoi
- ✅ **Statuts de liste** : Pending, Validated, Rejected
- ✅ **Raison de rejet** optionnelle
- ✅ **Email de confirmation** automatique lors de la validation
- ✅ **Génération automatique des matchs** à chaque validation (min 2 participants)

### ⚔️ Système de Matchs

- ✅ **Génération automatique** selon le format du tournoi :
  - **Élimination** : Appairage simple du premier round
  - **Suisse** : 3 rounds avec appairage aléatoire
  - **Ligue** : Round-robin complet (chaque joueur vs tous les autres)
- ✅ **Saisie des résultats** par les deux joueurs ou l'organisateur
- ✅ **Calcul automatique du vainqueur** selon les scores
- ✅ **Support des matchs nuls**
- ✅ **Points de victoire** optionnels
- ✅ **Notes sur les matchs**
- ✅ **Prévention des doublons** lors de la régénération
- ✅ **Conservation des matchs complétés**

### 👥 Gestion des Utilisateurs

- ✅ **5 rôles** : Super Admin, Admin, Moderator, Player, Visitor
- ✅ **Permissions granulaires** avec Spatie Permission
- ✅ **Profils utilisateurs** avec avatar
- ✅ **Historique des actions** (audit logs)

### 🎨 Interface Admin (Filament)

- ✅ Interface moderne et intuitive
- ✅ Dark mode natif
- ✅ 100% responsive
- ✅ Recherche et filtres avancés
- ✅ Export CSV/Excel
- ✅ Gestion des relations
- ✅ Upload de fichiers
- ✅ Validation automatique

---

## 🚀 Installation

### Prérequis

- PHP 8.3+
- Composer
- Node.js 18+
- MySQL 8.0+
- Git

### Étapes d'installation

```bash
# 1. Cloner le repository
git clone https://github.com/votre-repo/wh40k-tournament-manager.git
cd wh40k-tournament-manager

# 2. Installer les dépendances PHP
composer install

# 3. Installer les dépendances Node
npm install

# 4. Copier le fichier d'environnement
cp .env.example .env

# 5. Générer la clé d'application
php artisan key:generate

# 6. Configurer la base de données dans .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=wh40k_tournament
# DB_USERNAME=root
# DB_PASSWORD=

# 7. Exécuter les migrations
php artisan migrate

# 8. Seeder les données initiales (rôles, utilisateurs de test)
php artisan db:seed

# 9. Compiler les assets
npm run build

# 10. Lancer l'application
php artisan serve
```

### Accès à l'application

- **Site public** : http://localhost:8000
- **Interface admin** : http://localhost:8000/admin
- **Mailhog** (emails) : http://localhost:1025

---

## 🏗️ Architecture

### Structure du projet

```
app/
 Filament/                      # Interface d'administration
   └── Resources/                 # Ressources Filament
 Http/
   ├── Controllers/               # Contrôleurs
 TournamentController.php   │   ├
   │   ├── TournamentMatchController.php
   │   └── ...
   └── Requests/                  # Form Requests
 Models/                        # Modèles Eloquent
   ├── Tournament.php
   ├── TournamentMatch.php
   ├── ArmyList.php
   ├── User.php
   └── ...
 Policies/                      # Policies d'autorisation
   ├── TournamentPolicy.php
   └── ...
 Services/                      # Services métier
   ├── TournamentMatchGenerator.php
   └── ArmyListAnalyzer.php
 Notifications/                 # Notifications
    └── ArmyListValidated.php

database/
 migrations/                    # Migrations (17 tables)
 seeders/                       # Seeders

resources/
 views/
   ├── tournaments/               # Vues tournois
   ├── matches/                   # Vues matchs
   └── layouts/                   # Layouts
 css/                           # Styles Tailwind

routes/
 web.php                        # Routes web
 api.php                        # Routes API (future)
```

### Base de données

**17 tables principales** :

| Table | Description |
|-------|-------------|
| `tournaments` | Tournois |
| `tournament_matches` | Matchs du tournoi |
| `army_lists` | Listes d'armées des joueurs |
| `users` | Utilisateurs |
| `roles` | Rôles (Spatie) |
| `permissions` | Permissions (Spatie) |
| `factions` | Factions Warhammer |
| `detachments` | Détachements |
| `player_matches` | Matchs entre joueurs (casual) |
| `player_availabilities` | Disponibilités des joueurs |
| `match_availabilities` | Disponibilités pour les matchs |
| `audit_logs` | Historique des actions |
| Et 5 autres tables de liaison |

---

## 🏆 Gestion des Tournois

### Créer un tournoi

1. Aller sur `/admin/tournaments`
2. Cliquer sur "Créer"
3. Remplir les informations :
   - Nom du tournoi
   - Format (Élimination, Suisse, Ligue)
   - Taille d'armée (Incursion, Force de Frappe, Offensive)
   - Dates
   - Nombre de participants (min/max)
4. Valider

### Gérer les inscriptions

1. Aller sur le détail du tournoi
2. Cliquer sur "Gérer les inscriptions"
3. Voir les demandes en attente
4. Pour chaque demande :
   - Voir le PDF de la liste d'armée
   - Valider ou rejeter
   - Ajouter une raison de rejet (optionnel)

### Générer les matchs

**Automatique** : À chaque validation d'un participant (min 2)

**Manuel** : Bouton "Générer les matchs" sur la page du tournoi

---

## ⚔️ Système de Matchs

### Formats de tournoi

#### 🎯 Élimination
- Premier round : Appairage simple
- Les gagnants avancent
- Idéal pour les petits tournois

#### 🎲 Suisse
- 3 rounds
- Appairage aléatoire à chaque round
- Tous les joueurs jouent tous les rounds
- Idéal pour les tournois moyens

#### 🏅 Ligue
- Round-robin complet
- Chaque joueur joue contre tous les autres
- Idal pour les petits tournois compétitifs

### Saisir un résultat

1. Aller sur la page du match
2. Cliquer sur "Saisir le résultat"
3. Entrer les scores des deux joueurs
4. Optionnel : Points de victoire, notes
5. Cocher "Match nul" si applicable
6. Valider

**Le vainqueur est calculé automatiquement** selon les scores.

---

## 👥 Rôles et Permissions

### 🔴 Super Admin
- Accès complet à toutes les fonctionnalités
- Gestion des utilisateurs et rôles
- Gestion des tournois sans limite
- Validation des listes d'armées
- Modération des matchs

### 🟠 Admin
- Gestion des tournois (max 10 ouverts)
- Gestion des utilisateurs
- Validation des listes d'armées
- Modération des matchs
- Pas d'accès aux paramètres système

### 🟢 Moderator
- Validation des listes d'armées
- Modération des matchs
- Consultation des tournois
- Pas de création de tournois

### 🔵 Player
- Création de tournois (max 1 ouvert)
- Inscription aux tournois
- Upload de listes d'armées
- Saisie des résultats de matchs
- Gestion de son profil

### ⚪ Visitor
- Consultation publique des tournois
- Consultation des matchs
- Pas d'action possible

---

## 🔗 API et Routes

### Routes Tournois

```
GET    /tournaments                    # Liste des tournois
GET    /tournaments/{id}               # Détail du tournoi
POST   /tournaments                    # Créer un tournoi
PUT    /tournaments/{id}               # Modifier un tournoi
DELETE /tournaments/{id}               # Supprimer un tournoi
```

### Routes Inscriptions

```
POST   /tournaments/{id}/register      # S'inscrire
DELETE /tournaments/{id}/unregister    # Se désinscrire
GET    /tournaments/{id}/registrations # Gérer les inscriptions
POST   /tournaments/{id}/army-list/{id}/validate   # Valider une liste
POST   /tournaments/{id}/army-list/{id}/reject     # Rejeter une liste
GET    /tournaments/{id}/army-list/{id}/pdf        # Voir le PDF
```

### Routes Matchs

```
GET    /tournaments/{id}/matches              # Liste des matchs
GET    /tournaments/{id}/matches/{id}         # Détail du match
GET    /tournaments/{id}/matches/{id}/edit    # Formulaire résultat
PUT    /tournaments/{id}/matches/{id}         # Enregistrer résultat
POST   /tournaments/{id}/generate-matches     # Gnérer les matchs
```

---

## 🛠️ Commandes Utiles

```bash
# Créer un utilisateur admin
php artisan make:filament-user

# Vider les caches
php artisan optimize:clear

# Lancer les migrations
php artisan migrate

# Seeder les données
php artisan db:seed

# Lancer les tests
php artisan test

# Générer une clé d'application
php artisan key:generate

# Publier les assets Filament
php artisan filament:install

# Créer une notification
php artisan make:notification NotificationName

# Créer un service
php artisan make:class Services/ServiceName
```

---

## 📧 Notifications

### Email de validation

Quand un joueur est accepté, il reçoit un email avec :
- Confirmation de validation
- Détails de son inscription
- Informations du tournoi
- Lien pour consulter le tournoi

**Configuration** : Fichier `.env`
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=votre_username
MAIL_PASSWORD=votre_password
MAIL_FROM_ADDRESS=noreply@wh40k.local
```

---

## � Comptes de test

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

Player 1:
- Email: emma@wh40k.local
- Password: password

Player 2:
- Email: luc@wh40k.local
- Password: password
```

---

## 🛠️ Technologies

| Technologie | Version | Usage |
|-------------|---------|-------|
| Laravel | 12 | Framework principal |
| Filament | 3.2 | Interface admin |
| Livewire | 3 | Composants réactifs |
| Tailwind CSS | 3 | Design |
| Spatie Permission | 6 | Rôles & permissions |
| MySQL | 8.0+ | Base de données |
| PHP | 8.3+ | Langage |

---

## 📝 Variables d'environnement

```env
APP_NAME="WH40K Tournament Manager"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wh40k_tournament
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@wh40k.local

FILAMENT_THEME=light
```

---

## 🤝 Support

### Documentation
- [Filament](https://filamentphp.com/docs)
- [Laravel](https://laravel.com/docs)
- [Tailwind CSS](https://tailwindcss.com/docs)

### Issues
Pour signaler un bug ou proposer une fonctionnalité, ouvrez une issue sur GitHub.

---

## 📄 Licence

Ce projet est sous licence MIT.

---

**Développé avec ❤️ pour la communauté Warhammer 40K**

