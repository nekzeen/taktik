# Phase 3 : Implémentation complète - EN COURS 🚧

## Progression : ~30%

### ✅ Complété

#### 1. Request Classes (Validation)
- ✅ `StoreTournamentRequest` - Validation création tournoi
  - Règles : name, description, format, dates, max_players, status
  - Messages personnalisés en français
  - Authorization via Policy
  
- ✅ `UpdateTournamentRequest` - Validation mise à jour tournoi
  - Mêmes règles que Store (dates moins strictes)
  - Authorization via Policy
  
- ✅ `StoreArmyListRequest` - Validation upload liste d'armée
  - Upload PDF (max 10MB)
  - Validation tournament_id, faction_id, points
  - Messages d'erreur personnalisés
  
- ✅ `UpdateArmyListRequest` - Créée (à implémenter)

#### 2. Controllers Admin
- ✅ `Admin\TournamentController` - CRUD complet
  - `index()` : Liste avec pagination, compteurs, relations
  - `create()` : Formulaire création
  - `store()` : Création avec validation + created_by
  - `show()` : Détails avec relations (listes, matchs)
  - `edit()` : Formulaire édition + authorization
  - `update()` : Mise à jour avec validation
  - `destroy()` : Suppression avec authorization
  
- ⏳ `Admin\ArmyListController` - À implémenter
- ⏳ `Admin\UserController` - À implémenter

#### 3. Views Admin
- ✅ `admin/tournaments/index.blade.php` - Liste des tournois
  - Tableau responsive avec Tailwind
  - Badges de statut colorés
  - Actions : Voir, Éditer, Supprimer
  - Pagination
  - Messages de succès
  
- ⏳ `admin/tournaments/create.blade.php` - À créer
- ⏳ `admin/tournaments/edit.blade.php` - À créer
- ⏳ `admin/tournaments/show.blade.php` - À créer
- ⏳ `admin/army-lists/*` - À créer
- ⏳ `admin/users/*` - À créer

### 🚧 En cours

#### Controllers Player
- ⏳ `Player\DashboardController` - À implémenter
- ⏳ `Player\TournamentController` - À implémenter
- ⏳ `Player\ArmyListController` - À implémenter

#### Views Player
- ⏳ `player/dashboard.blade.php` - À créer
- ⏳ `player/tournaments/*` - À créer
- ⏳ `player/army-lists/*` - À créer

### ⏳ À faire

#### 1. Compléter les vues Admin Tournaments
```
admin/tournaments/
├── create.blade.php (formulaire création)
├── edit.blade.php (formulaire édition)
└── show.blade.php (détails + listes + matchs)
```

#### 2. Implémenter Admin\ArmyListController
```php
- index() : Liste toutes les listes
- show() : Détails + PDF viewer
- validate() : Valider une liste
- reject() : Rejeter une liste
- destroy() : Supprimer
```

#### 3. Créer vues Admin ArmyLists
```
admin/army-lists/
├── index.blade.php (liste avec filtres)
└── show.blade.php (détails + PDF + actions validation)
```

#### 4. Implémenter Admin\UserController
```php
- index() : Liste utilisateurs
- create() : Formulaire création
- store() : Création + assignation rôle
- show() : Profil + stats
- edit() : Formulaire édition
- update() : Mise à jour
- destroy() : Suppression (soft delete)
```

#### 5. Implémenter Player Controllers
```php
Player\DashboardController:
- index() : Stats joueur, ses tournois, ses listes

Player\TournamentController:
- index() : Liste tournois publics
- show() : Détails tournoi
- join() : Inscription tournoi

Player\ArmyListController:
- create() : Formulaire upload
- store() : Upload PDF + création
- edit() : Modification (si draft)
- update() : Mise à jour
- destroy() : Suppression
```

#### 6. Créer vues Player
```
player/
├── dashboard.blade.php
├── tournaments/
│   ├── index.blade.php
│   └── show.blade.php
└── army-lists/
    ├── create.blade.php
    └── edit.blade.php
```

#### 7. Composants réutilisables
- ⏳ Form components (input, select, textarea, file)
- ⏳ Status badges component
- ⏳ Alert component
- ⏳ Modal component
- ⏳ Pagination component personnalisé

#### 8. JavaScript/Alpine.js
- ⏳ Confirmation suppression
- ⏳ Upload fichier avec preview
- ⏳ Filtres dynamiques
- ⏳ Recherche en temps réel

## Structure actuelle

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── DashboardController.php ✅
│   │   │   ├── TournamentController.php ✅ (complet)
│   │   │   ├── ArmyListController.php ⏳
│   │   │   └── UserController.php ⏳
│   │   ├── Player/
│   │   │   ├── DashboardController.php ⏳
│   │   │   ├── TournamentController.php ⏳
│   │   │   └── ArmyListController.php ⏳
│   │   └── HomeController.php ✅
│   └── Requests/
│       ├── StoreTournamentRequest.php ✅
│       ├── UpdateTournamentRequest.php ✅
│       ├── StoreArmyListRequest.php ✅
│       └── UpdateArmyListRequest.php ⏳
│
resources/
└── views/
    ├── home.blade.php ✅
    ├── admin/
    │   ├── dashboard.blade.php ✅
    │   └── tournaments/
    │       └── index.blade.php ✅
    └── player/
```

## Prochaines étapes immédiates

### Priorité 1 : Compléter Admin Tournaments
1. ✅ Controller implémenté
2. ✅ Vue index créée
3. ⏳ Vue create (formulaire)
4. ⏳ Vue edit (formulaire)
5. ⏳ Vue show (détails)

### Priorité 2 : Admin ArmyLists
1. ⏳ Implémenter controller
2. ⏳ Vue index
3. ⏳ Vue show avec validation

### Priorité 3 : Player Dashboard & Tournaments
1. ⏳ Player\DashboardController
2. ⏳ Player\TournamentController
3. ⏳ Vues correspondantes

## Fonctionnalités clés à implémenter

### Upload PDF sécurisé
```php
// Dans ArmyListController
$path = $request->file('pdf')->store('army-lists', 'private');
$hash = hash_file('sha256', $request->file('pdf')->path());
```

### Validation liste d'armée
```php
// Admin\ArmyListController
public function validate(ArmyList $armyList)
{
    $armyList->update([
        'status' => 'validated',
        'validated_at' => now(),
        'validated_by' => auth()->id(),
    ]);
}
```

### Inscription tournoi
```php
// Player\TournamentController
public function join(Tournament $tournament)
{
    // Vérifier si déjà inscrit
    // Vérifier places disponibles
    // Créer entrée army_list
}
```

## Notes techniques

### Validation dates
- `start_date` : after_or_equal:today (création)
- `end_date` : after_or_equal:start_date
- `registration_deadline` : before:start_date

### Statuts tournoi
- `draft` : Brouillon
- `open` : Inscriptions ouvertes
- `registration_closed` : Inscriptions fermées
- `in_progress` : En cours
- `completed` : Terminé
- `cancelled` : Annulé

### Statuts army_list
- `draft` : Brouillon
- `pending` : En attente validation
- `validated` : Validée
- `rejected` : Rejetée

### Permissions utilisées
- `manage-tournaments` : CRUD tournois
- `manage-army-lists` : CRUD toutes listes
- `validate-army-lists` : Valider/rejeter
- `upload-army-list` : Upload sa liste
- `join-tournaments` : S'inscrire

## Tests à effectuer

### Admin Tournaments
- [ ] Créer un tournoi
- [ ] Lister les tournois
- [ ] Voir détails tournoi
- [ ] Éditer tournoi
- [ ] Supprimer tournoi
- [ ] Vérifier compteurs (listes, matchs)

### Admin ArmyLists
- [ ] Lister listes en attente
- [ ] Voir PDF liste
- [ ] Valider liste
- [ ] Rejeter liste avec raison

### Player
- [ ] Voir tournois publics
- [ ] S'inscrire à tournoi
- [ ] Upload liste PDF
- [ ] Voir ses listes
- [ ] Modifier liste draft

## Commandes utiles

```bash
# Créer un controller
php artisan make:controller Admin/ResourceController --resource

# Créer une request
php artisan make:request StoreResourceRequest

# Créer un composant Blade
php artisan make:component Forms/Input

# Vider le cache
php artisan optimize:clear

# Tester les routes
php artisan route:list --path=admin

# Lancer le serveur
php artisan serve
```

## État global

**Phase 3 : 30% complétée**

- ✅ Request classes (validation)
- ✅ Admin TournamentController (complet)
- ✅ Vue admin tournaments/index
- 🚧 Vues admin tournaments (create, edit, show)
- ⏳ Admin ArmyListController
- ⏳ Admin UserController
- ⏳ Player Controllers
- ⏳ Player Views
- ⏳ Composants réutilisables

**Temps estimé restant** : 4-6 heures de développement
