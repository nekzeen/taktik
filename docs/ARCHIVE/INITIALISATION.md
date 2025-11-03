# Initialisation du projet Warhammer 40k Tournament Manager

## ✅ Étapes complétées

### 1. Environnement
- Laravel 12 déjà installé
- PHP 8.2+
- MySQL configuré
- Node.js 18.20.4 (warning Vite mais fonctionnel)

### 2. Packages installés
```bash
composer require spatie/laravel-permission
composer require spatie/laravel-activitylog
composer require spatie/laravel-backup
composer require laravel/breeze --dev
```

### 3. Authentification
- Laravel Breeze installé avec stack Blade + Alpine.js
- Mode dark activé
- Assets compilés

### 4. Migrations créées et exécutées

#### Tables système (Spatie)
- `permissions` et `roles` (spatie/laravel-permission)
- `activity_log` (spatie/laravel-activitylog)

#### Tables métier
- ✅ `users` (modifiée: phone, consent_at, last_activity_at, soft_deletes)
- ✅ `tournaments`
- ✅ `matches`
- ✅ `factions`
- ✅ `army_lists`
- ✅ `units`
- ✅ `abilities`
- ✅ `wargear`
- ✅ `calendar_slots`
- ✅ `match_requests`
- ✅ `pages`
- ✅ `menus`
- ✅ `translations`
- ✅ `bsdata_imports`

**Note**: Migration `factions` renommée de `2025_10_20_161630` à `2025_10_20_161627` pour résoudre l'ordre de dépendance avec `army_lists`.

### 5. Modèles Eloquent créés

#### Modèles configurés
- ✅ `User` (avec HasRoles, SoftDeletes, LogsActivity, relations)
- ✅ `GameMatch` (table: matches, avec relations et activity log)

#### Modèles créés (à configurer)
- `Tournament`
- `ArmyList`
- `Faction`
- `Unit`
- `Ability`
- `Wargear`
- `CalendarSlot`
- `MatchRequest`
- `Page`
- `Menu`
- `Translation`
- `BsdataImport`

**Note**: Le modèle `Match` a été renommé en `GameMatch` car "Match" est un mot réservé par PHP.

## 📋 Prochaines étapes

### Phase 1 : Compléter les modèles (en cours)
- [ ] Configurer Tournament avec relations et fillable
- [ ] Configurer ArmyList avec relations et fillable
- [ ] Configurer Faction avec relations
- [ ] Configurer Unit avec relations
- [ ] Configurer Ability
- [ ] Configurer Wargear
- [ ] Configurer CalendarSlot avec relations
- [ ] Configurer MatchRequest avec relations
- [ ] Configurer Page
- [ ] Configurer Menu
- [ ] Configurer Translation
- [ ] Configurer BsdataImport

### Phase 2 : Seeders et données de test
- [ ] Créer RoleSeeder (admin, moderator, player, visitor)
- [ ] Créer UserSeeder (admin de test)
- [ ] Créer FactionSeeder (factions de base)
- [ ] Créer TournamentSeeder (tournoi de test)

### Phase 3 : Policies
- [ ] TournamentPolicy
- [ ] ArmyListPolicy
- [ ] GameMatchPolicy
- [ ] PagePolicy
- [ ] MenuPolicy

### Phase 4 : Services
- [ ] BsdataImportService
- [ ] TranslationService
- [ ] TournamentBracketService
- [ ] CalendarService
- [ ] PdfStorageService

### Phase 5 : Controllers
- [ ] Admin/TournamentController
- [ ] Admin/UserController
- [ ] Admin/ArmyListController
- [ ] Admin/PageController
- [ ] Admin/MenuController
- [ ] Admin/TranslationController
- [ ] Player/TournamentController
- [ ] Player/ArmyListController
- [ ] Player/MatchRequestController
- [ ] Player/CalendarController

### Phase 6 : Views
- [ ] Layouts (admin, player, guest)
- [ ] Dashboard admin
- [ ] Dashboard player
- [ ] Pages tournois
- [ ] Pages matchs
- [ ] Agenda
- [ ] Gestion listes d'armées

### Phase 7 : Jobs & Queues
- [ ] ImportBsdataJob
- [ ] TranslateContentJob
- [ ] GenerateTournamentBracketJob
- [ ] SendMatchNotificationJob

### Phase 8 : Tests
- [ ] Tests unitaires services
- [ ] Tests feature controllers
- [ ] Tests policies

## 🔧 Configuration requise

### .env à configurer
```env
APP_NAME="Warhammer 40k Tournament Manager"
APP_LOCALE=fr
APP_FALLBACK_LOCALE=fr

# Base de données (déjà configurée)
DB_CONNECTION=mysql

# Cache et Queue (à configurer pour Redis)
CACHE_STORE=redis
QUEUE_CONNECTION=redis

# Mail (à configurer)
MAIL_MAILER=smtp

# Traduction API (à choisir)
DEEPL_API_KEY=
# ou
GOOGLE_TRANSLATE_API_KEY=
```

### Permissions à créer
```php
// Admin
'manage-users'
'manage-tournaments'
'manage-army-lists'
'manage-translations'
'manage-pages'
'manage-menus'
'import-bsdata'

// Moderator
'validate-army-lists'
'moderate-matches'

// Player
'create-tournaments'
'join-tournaments'
'upload-army-list'
'create-match-request'
'manage-calendar'
```

## 📊 État actuel

**Progression globale**: ~15% (Phase 1 en cours)

- ✅ Infrastructure de base
- ✅ Authentification
- ✅ Base de données (migrations)
- 🔄 Modèles Eloquent (2/13 configurés)
- ⏳ Seeders
- ⏳ Policies
- ⏳ Services
- ⏳ Controllers
- ⏳ Views
- ⏳ Jobs
- ⏳ Tests

## 🐛 Problèmes résolus

1. **Ordre des migrations**: `factions` devait être créée avant `army_lists` → Résolu en renommant le fichier de migration
2. **Nom réservé PHP**: `Match` est réservé → Utilisé `GameMatch` à la place
3. **Node.js version**: Warning Vite mais build réussi → Fonctionnel

## 📝 Notes importantes

- Utiliser `GameMatch::class` au lieu de `Match::class` dans tout le code
- Les rôles Spatie doivent être créés via seeder avant utilisation
- Le chiffrement des données sensibles (phone) sera implémenté via Accessor/Mutator
- Les traductions BSData seront gérées de manière asynchrone via Jobs
