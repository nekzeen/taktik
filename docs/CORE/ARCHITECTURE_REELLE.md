# 🏗️ ARCHITECTURE RÉELLE DE L'APPLICATION

## 📊 Vue d'Ensemble

Application Warhammer 40K Tournament Manager - Gestion complète des tournois, matchs joueurs et système de traductions multilingues.

### 🎯 Objectifs Principaux

1. **Gestion des Tournois** - Créer et gérer des tournois avec matchs appairés
2. **Gestion des Matchs Joueurs** - Matchs simples entre joueurs (sans tournoi)
3. **Système de Missions** - Missions primaires, secondaires, péripéties
4. **Traductions Multilingues** - Support FR, DE, ES, IT via DeepL
5. **Gestion des Utilisateurs** - Inscription, profils, disponibilités
6. **Système de Score** - Calcul automatique des points de victoire

---

## 📈 Statistiques Actuelles

| Ressource | Nombre |
|-----------|--------|
| Utilisateurs | 4 |
| Tournois | 1 |
| Matchs de Tournoi | 4 |
| Matchs Joueurs | 4 |
| Demandes de Matchs | 2 |
| Missions Primaires | 10 |
| Missions Secondaires | 19 |
| Péripéties | 9 |
| Traductions | 233 |
| Entrées Glossaire | 47 |
| Détachements | 227 |
| Factions | 26 |
| Listes d'Armée | 3 |

---

## 🗄️ STRUCTURE DE LA BASE DE DONNÉES

### 1. Gestion des Utilisateurs

#### Table: `users`
```sql
id | name | email | password | role | faction_id | detachment_id | created_at | updated_at
```

**Rôles disponibles:**
- `admin` - Administrateur système
- `user` - Utilisateur standard
- `tournament_organizer` - Organisateur de tournoi

**Relations:**
- `belongsTo(Faction)` - Faction préférée
- `belongsTo(Detachment)` - Détachement préféré
- `hasMany(Tournament)` - Tournois créés
- `hasMany(PlayerMatch)` - Matchs joueurs créés
- `hasMany(PlayerMatchRequest)` - Demandes de matchs
- `hasMany(PlayerAvailability)` - Disponibilités

---

### 2. Gestion des Tournois

#### Table: `tournaments`
```sql
id | name | description | organizer_id | start_date | end_date | 
max_players | current_round | status | created_at | updated_at
```

**Statuts:**
- `draft` - Brouillon
- `registration_open` - Inscriptions ouvertes
- `registration_closed` - Inscriptions fermées
- `in_progress` - En cours
- `completed` - Terminé

**Relations:**
- `belongsTo(User, 'organizer_id')` - Organisateur
- `hasMany(TournamentMatch)` - Matchs du tournoi
- `hasMany(TournamentMissionPool)` - Pools de missions

#### Table: `tournament_matches`
```sql
id | tournament_id | player1_id | player2_id | round | table_number |
primary_mission_id | secondary_mission_id | twist_mission_id |
player1_score | player2_score | winner_id | status | created_at | updated_at
```

**Statuts:**
- `scheduled` - Programmé
- `in_progress` - En cours
- `completed` - Terminé

**Relations:**
- `belongsTo(Tournament)` - Tournoi
- `belongsTo(User, 'player1_id')` - Joueur 1
- `belongsTo(User, 'player2_id')` - Joueur 2
- `belongsTo(PrimaryMission)` - Mission primaire
- `belongsTo(SecondaryMission)` - Mission secondaire
- `belongsTo(TwistMission)` - Péripétie

---

### 3. Gestion des Matchs Joueurs

#### Table: `player_matches`
```sql
id | creator_id | type | points | location | faction_id | detachment_id |
status | created_at | updated_at
```

**Types de matchs:**
- `casual` - Casual
- `competitive` - Compétitif
- `narrative` - Narratif

**Statuts:**
- `open` - Ouvert
- `in_progress` - En cours
- `completed` - Terminé
- `cancelled` - Annulé

**Relations:**
- `belongsTo(User, 'creator_id')` - Créateur
- `belongsTo(Faction)` - Faction du créateur
- `belongsTo(Detachment)` - Détachement du créateur
- `hasMany(PlayerMatchRequest)` - Demandes de participation
- `hasMany(PlayerAvailability)` - Disponibilités

#### Table: `player_match_requests`
```sql
id | player_match_id | player_id | faction_id | detachment_id |
status | created_at | updated_at
```

**Statuts:**
- `pending` - En attente
- `accepted` - Acceptée
- `rejected` - Rejetée
- `cancelled` - Annulée

**Relations:**
- `belongsTo(PlayerMatch)` - Match joueur
- `belongsTo(User, 'player_id')` - Demandeur
- `belongsTo(Faction)` - Faction proposée
- `belongsTo(Detachment)` - Détachement proposé

---

### 4. Gestion des Missions

#### Table: `primary_missions`
```sql
id | name | description | full_text | when_condition | timing |
max_vp | slug | edition | source | is_active | created_at | updated_at
```

**Relations:**
- `hasMany(PrimaryMissionSection)` - Sections de la mission
- `hasMany(Translation)` - Traductions

#### Table: `secondary_missions`
```sql
id | name | description | full_text | when_drawn | when_condition |
scoring_conditions | max_vp | slug | edition | source | is_active | created_at | updated_at
```

**Relations:**
- `hasMany(Translation)` - Traductions

#### Table: `twist_missions`
```sql
id | name | description | full_text | when_drawn | effect | timing |
max_vp | slug | edition | source | is_active | created_at | updated_at
```

**Relations:**
- `hasMany(Translation)` - Traductions

---

### 5. Système de Traductions

#### Table: `translations`
```sql
id | source_text | translated_text | locale | resource_type | resource_id |
field | status | reviewed_by | reviewed_at | created_at | updated_at
```

**Locales supportées:**
- `fr` - Français
- `de` - Allemand
- `es` - Espagnol
- `it` - Italien

**Statuts:**
- `pending` - En attente
- `auto` - Automatique (DeepL)
- `reviewed` - Révisée
- `approved` - Approuvée

**Resource Types:**
- `PrimaryMission`
- `SecondaryMission`
- `TwistMission`
- `Detachment`
- `Faction`
- `Unit`
- `Ability`
- `Wargear`

#### Table: `warhammer_glossary`
```sql
id | english_term | category | context | french_translation |
german_translation | spanish_translation | italian_translation |
description | example | status | usage_count | created_at | updated_at
```

**Catégories:**
- `ability` - Capacité
- `keyword` - Mot-clé
- `unit` - Unité
- `condition` - Condition
- `action` - Action
- `general` - Général

**Contextes:**
- `primary_mission`
- `secondary_mission`
- `twist_mission`
- `deployment_zone`
- `general`

---

### 6. Gestion des Détachements et Factions

#### Table: `detachments`
```sql
id | name | faction_id | bsdata_id | description | rules |
is_active | created_at | updated_at
```

**Relations:**
- `belongsTo(Faction)` - Faction
- `hasMany(User)` - Utilisateurs
- `hasMany(PlayerMatch)` - Matchs joueurs
- `hasMany(PlayerMatchRequest)` - Demandes de matchs
- `hasMany(Translation)` - Traductions

#### Table: `factions`
```sql
id | name | description | color_code | is_active | created_at | updated_at
```

**Relations:**
- `hasMany(Detachment)` - Détachements
- `hasMany(User)` - Utilisateurs
- `hasMany(PlayerMatch)` - Matchs joueurs

#### Table: `bsdata_detachments`
```sql
id | name | faction_id | bsdata_id | data | last_sync | created_at | updated_at
```

**Relations:**
- `belongsTo(Faction)` - Faction

---

### 7. Gestion des Disponibilités

#### Table: `player_availabilities`
```sql
id | player_id | player_match_id | day | time_slot | available | created_at | updated_at
```

**Relations:**
- `belongsTo(User, 'player_id')` - Joueur
- `belongsTo(PlayerMatch)` - Match joueur

#### Table: `calendar_slots`
```sql
id | date | time_slot | available_slots | created_at | updated_at
```

---

## 🔄 FLUX DE DONNÉES PRINCIPAUX

### 1. Flux Tournoi

```
Créer Tournoi
    ↓
Ouvrir Inscriptions
    ↓
Joueurs s'inscrivent
    ↓
Fermer Inscriptions
    ↓
Générer Appairements (Round 1)
    ↓
Assigner Missions
    ↓
Joueurs jouent et saisissent scores
    ↓
Valider Résultats
    ↓
Générer Appairements (Round 2+)
    ↓
Répéter jusqu'à fin
    ↓
Clôturer Tournoi
```

### 2. Flux Match Joueur

```
Créer Match Joueur
    ↓
Définir Paramètres (points, faction, détachement)
    ↓
Attendre Demandes de Participation
    ↓
Accepter/Rejeter Demandes
    ↓
Joueurs se rencontrent
    ↓
Saisir Résultats
    ↓
Clôturer Match
```

### 3. Flux Traduction

```
Créer Ressource (Mission, Détachement, etc.)
    ↓
Observer détecte la création
    ↓
Créer entrées Translation (status: pending)
    ↓
Appeler DeepL API
    ↓
Mettre à jour translations (status: auto)
    ↓
Appliquer Glossaire Warhammer
    ↓
Propager à toutes les traductions du système
    ↓
Marquer comme reviewed/approved
```

---

## 🔐 Sécurité et Permissions

### Rôles et Permissions

| Action | Admin | Organisateur | Utilisateur |
|--------|-------|--------------|-------------|
| Créer Tournoi | ✅ | ✅ | ❌ |
| Modifier Tournoi | ✅ | ✅ (propre) | ❌ |
| Créer Match Joueur | ✅ | ✅ | ✅ |
| Modifier Détachements | ✅ | ❌ | ❌ |
| Modifier Traductions | ✅ | ❌ | ❌ |
| Voir Admin Panel | ✅ | ❌ | ❌ |

### Authentification

- **Laravel Sanctum** - API tokens
- **Session** - Web authentication
- **Middleware** - Route protection

---

## 📦 Dépendances Principales

```json
{
  "laravel/framework": "^12.0",
  "filament/filament": "^3.0",
  "spatie/laravel-permission": "^6.0",
  "deepl-php/deepl-php": "^1.0",
  "livewire/livewire": "^3.0"
}
```

---

## 🚀 Commandes Artisan Principales

```bash
# Migrations
php artisan migrate
php artisan migrate:fresh --seed

# Traductions
php artisan missions:translate --locale=fr
php artisan missions:translate-secondary --locale=fr
php artisan missions:translate-twist --locale=fr

# Import Wahapedia
php artisan missions:import-xml
php artisan missions:import-secondary-xml

# Cache
php artisan cache:clear
php artisan view:cache

# Admin Filament
php artisan make:filament-resource ResourceName
```

---

## 📝 Notes Importantes

1. **Deux types de matchs** - Tournoi (appairés) et Joueurs (libres)
2. **Traductions automatiques** - DeepL + Glossaire Warhammer
3. **Observer Pattern** - Automatise les traductions et propagation
4. **Missions dynamiques** - Importées depuis Wahapedia
5. **Système de scoring** - Points de victoire calculés automatiquement

---

**Dernière mise à jour** : 3 novembre 2025
**Version** : 2.0 (Réelle)

