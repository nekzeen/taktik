# 🔍 AUDIT COMPLET ET EXHAUSTIF DE L'APPLICATION

**Date**: 3 novembre 2025
**Statut**: Audit complet de toutes les tables, modèles et fonctionnalités

---

## 📊 TOUTES LES TABLES DE LA BASE DE DONNÉES (74 migrations)

### 1. GESTION DES UTILISATEURS ET AUTHENTIFICATION

#### `users` (4 enregistrements)
```sql
id | name | email | password | email_verified_at | remember_token |
faction_id | detachment_id | created_at | updated_at
```
- Rôles: admin, user, tournament_organizer
- Relations: faction, detachment, tournaments, matches, requests

#### `roles` (4 enregistrements)
```sql
id | name | guard_name | created_at | updated_at
```
- Rôles: admin, user, tournament_organizer, guest

#### `permissions` (5 enregistrements)
```sql
id | name | guard_name | created_at | updated_at
```

#### `model_has_roles`
- Liaison users ↔ roles

#### `model_has_permissions`
- Liaison users ↔ permissions

#### `role_has_permissions`
- Liaison roles ↔ permissions

#### `password_reset_tokens`
- Réinitialisation de mot de passe

#### `sessions` (2 enregistrements)
- Sessions utilisateur

---

### 2. GESTION DES TOURNOIS

#### `tournaments` (1 enregistrement)
```sql
id | name | description | organizer_id | start_date | end_date |
max_players | current_round | status | army_size | created_at | updated_at
```
- Statuts: draft, registration_open, registration_closed, in_progress, completed
- Relations: organizer (User), matches (TournamentMatch), missionPools

#### `tournament_matches` (4 enregistrements)
```sql
id | tournament_id | player1_id | player2_id | round | table_number |
primary_mission_id | secondary_mission_id | twist_mission_id |
player1_score | player2_score | winner_id | status |
deployment_mode | setup_mode | army_points | created_at | updated_at
```
- Statuts: scheduled, in_progress, completed
- Relations: tournament, player1, player2, primaryMission, secondaryMission, twistMission

#### `tournament_mission_pools` (40 enregistrements)
```sql
id | tournament_id | name | description | created_at | updated_at
```
- Relations: tournament, secondaryMissions, terrainLayouts

#### `tournament_mission_pool_secondary_missions`
- Liaison pools ↔ secondary missions

#### `tournament_mission_pool_terrain_layouts` (202 enregistrements)
- Liaison pools ↔ terrain layouts

---

### 3. GESTION DES MATCHS JOUEURS

#### `player_matches` (4 enregistrements)
```sql
id | creator_id | opponent_id | type | army_points | faction | detachment |
notes | city | department | availability_type | available_at | available_from |
available_to | status | creator_score | creator_victory_points | opponent_score |
opponent_victory_points | primary_mission_id | secondary_mission_id |
terrain_layout_id | twist_mission_id | asymmetric_primary_mission_id |
deployment_mode | setup_mode | is_setup_complete | is_setup_validated |
winner_id | is_draw | played_at | created_at | updated_at | deleted_at
```
- Types: competitive, narrative
- Statuts: open, confirmed, completed, cancelled
- Setup modes: random, manual
- Relations: creator, opponent, missions, terrain, twist

#### `player_match_requests` (2 enregistrements)
```sql
id | player_match_id | player_id | faction_id | detachment_id |
status | created_at | updated_at
```
- Statuts: pending, accepted, rejected, cancelled
- Relations: playerMatch, player, faction, detachment

#### `player_availabilities` (1 enregistrement)
```sql
id | player_id | player_match_id | day | time_slot | available | created_at | updated_at
```

---

### 4. MISSIONS WARHAMMER

#### `primary_missions` (10 enregistrements)
```sql
id | name | description | full_text | when_condition | timing |
max_vp | slug | edition | source | is_active | created_at | updated_at
```
- Relations: sections, translations, tournamentMatches

#### `primary_mission_sections` (26 enregistrements)
```sql
id | primary_mission_id | type | order | content | created_at | updated_at
```
- Types: action, setup, scoring, condition

#### `secondary_missions` (19 enregistrements)
```sql
id | name | description | full_text | when_drawn | when_condition |
scoring_conditions | max_vp | slug | edition | source | is_active |
created_at | updated_at
```
- Relations: translations, playerMatches, tournamentMatches

#### `secondary_mission_sections` (0 enregistrements)
```sql
id | secondary_mission_id | type | order | content | created_at | updated_at
```

#### `twist_missions` (9 enregistrements)
```sql
id | name | description | full_text | when_drawn | effect | timing |
max_vp | slug | edition | source | is_active | created_at | updated_at
```
- Relations: translations, playerMatches, tournamentMatches

#### `asymmetric_primary_missions` (5 enregistrements)
```sql
id | name | description | full_text | created_at | updated_at
```

---

### 5. CARTES DE DÉPLOIEMENT

#### `strike_force_deployment_cards` (6 enregistrements)
```sql
id | name | description | created_at | updated_at
```

#### `incursion_deployment_cards` (6 enregistrements)
```sql
id | name | description | created_at | updated_at
```

#### `asymmetric_warfare_deployment_cards` (5 enregistrements)
```sql
id | name | description | created_at | updated_at
```

#### `terrain_layouts` (8 enregistrements)
```sql
id | name | description | created_at | updated_at
```

---

### 6. DONNÉES WARHAMMER 40K

#### `factions` (26 enregistrements)
```sql
id | name | description | color_code | is_active | created_at | updated_at
```
- Relations: detachments, users, playerMatches

#### `detachments` (227 enregistrements)
```sql
id | name | faction_id | bsdata_id | description | rules | is_active |
created_at | updated_at
```
- Relations: faction, abilities, users, playerMatches, translations

#### `detachment_abilities` (243 enregistrements)
```sql
id | detachment_id | ability_id | created_at | updated_at
```

#### `abilities` (1 enregistrement)
```sql
id | name | description | created_at | updated_at
```

#### `army_lists` (3 enregistrements)
```sql
id | user_id | name | description | faction_id | detachment_id |
pdf_path | created_at | updated_at
```

#### `bsdata_detachments` (227 enregistrements)
```sql
id | name | faction_id | bsdata_id | data | last_sync | created_at | updated_at
```

#### `bsdata_imports` (0 enregistrements)
```sql
id | import_type | status | data | created_at | updated_at
```

#### `bsdata_units` (0 enregistrements)
```sql
id | bsdata_id | name | faction_id | data | created_at | updated_at
```

---

### 7. DONNÉES DATASHEETS (IMPORT BSDATA)

#### `datasheets` (1669 enregistrements)
```sql
id | name | faction_id | data | created_at | updated_at
```
- Données complètes des datasheets Warhammer 40K

#### `datasheet_ability` (0 enregistrements)
- Liaison datasheets ↔ abilities

#### `stratagems` (1284 enregistrements)
```sql
id | name | faction_id | data | created_at | updated_at
```
- Stratagèmes Warhammer 40K

#### `units` (0 enregistrements)
```sql
id | name | faction_id | data | created_at | updated_at
```

#### `wargear` (0 enregistrements)
```sql
id | name | data | created_at | updated_at
```

#### `weapons` (0 enregistrements)
```sql
id | name | data | created_at | updated_at
```

---

### 8. SYSTÈME DE TRADUCTIONS

#### `translations` (233 enregistrements)
```sql
id | source_text | translated_text | locale | resource_type | resource_id |
field | status | reviewed_by | reviewed_at | created_at | updated_at
```
- Locales: fr, de, es, it
- Statuts: pending, auto, reviewed, approved
- Resource types: PrimaryMission, SecondaryMission, TwistMission, Detachment, Faction, etc.

#### `warhammer_glossary` (47 entrées)
```sql
id | english_term | category | context | french_translation |
german_translation | spanish_translation | italian_translation |
description | example | status | usage_count | created_at | updated_at
```
- Catégories: ability, keyword, unit, condition, action, general
- Contextes: primary_mission, secondary_mission, twist_mission, deployment_zone, general

---

### 9. AUTRES TABLES

#### `calendar_slots` (0 enregistrements)
```sql
id | date | time_slot | available_slots | created_at | updated_at
```

#### `match_availabilities` (0 enregistrements)
```sql
id | match_id | available_date | created_at | updated_at
```

#### `match_requests` (0 enregistrements)
```sql
id | match_id | player_id | status | created_at | updated_at
```

#### `matches` (0 enregistrements)
```sql
id | tournament_id | player1_id | player2_id | status | created_at | updated_at
```

#### `pages` (0 enregistrements)
```sql
id | title | slug | content | created_at | updated_at
```

#### `menus` (0 enregistrements)
```sql
id | name | items | created_at | updated_at
```

---

### 10. SYSTÈME

#### `migrations` (74 enregistrements)
- Historique des migrations

#### `activity_log` (32 enregistrements)
```sql
id | log_name | description | subject_type | subject_id | causer_type |
causer_id | properties | created_at | updated_at
```

#### `cache` (0 enregistrements)
- Cache Laravel

#### `cache_locks` (0 enregistrements)
- Verrous de cache

#### `jobs` (25 enregistrements)
- Files d'attente de jobs

#### `job_batches` (0 enregistrements)
- Lots de jobs

#### `failed_jobs` (0 enregistrements)
- Jobs échoués

---

## 🎯 RÉSUMÉ DES DONNÉES RÉELLES

### Données Principales
- **Utilisateurs**: 4
- **Tournois**: 1
- **Matchs de Tournoi**: 4
- **Matchs Joueurs**: 4
- **Demandes de Matchs**: 2
- **Disponibilités Joueurs**: 1

### Missions et Cartes
- **Missions Primaires**: 10
- **Sections Missions Primaires**: 26
- **Missions Secondaires**: 19
- **Péripéties**: 9
- **Missions Asymétriques**: 5
- **Cartes Strike Force**: 6
- **Cartes Incursion**: 6
- **Cartes Asymmetric Warfare**: 5
- **Terrains**: 8

### Données Warhammer
- **Factions**: 26
- **Détachements**: 227
- **Capacités de Détachements**: 243
- **Datasheets**: 1669
- **Stratagèmes**: 1284
- **Listes d'Armée**: 3

### Traductions
- **Traductions**: 233
- **Entrées Glossaire**: 47

### Pools de Missions
- **Pools de Missions**: 40
- **Liaisons Pool-Terrain**: 202

---

## 🔄 FLUX DE DONNÉES COMPLETS

### 1. Flux Tournoi Complet

```
1. CRÉATION TOURNOI
   - Organisateur crée tournoi
   - Statut: draft

2. OUVERTURE INSCRIPTIONS
   - Statut: registration_open
   - Joueurs s'inscrivent

3. FERMETURE INSCRIPTIONS
   - Statut: registration_closed

4. CRÉATION POOLS DE MISSIONS
   - 40 pools créés
   - Chaque pool a des missions secondaires
   - Chaque pool a des terrains (202 liaisons)

5. APPAIRAGE ROUND 1
   - Création TournamentMatch
   - Assignation missions primaire/secondaire/péripétie
   - Assignation terrain
   - Assignation deployment_mode
   - Statut: scheduled

6. JOUEURS JOUENT
   - Saisissent scores
   - Statut: in_progress → completed

7. APPAIRAGE ROUND 2+
   - Répéter le processus

8. CLÔTURE
   - Statut: completed
```

### 2. Flux Match Joueur Complet

```
1. CRÉATION (status: open)
   - Joueur crée PlayerMatch
   - Définit type, points, localisation, faction, détachement
   - Définit disponibilité (date fixe ou période)

2. CONFIGURATION (status: open → confirmed)
   - Choisit missions (primaire, secondaire, asymétrique)
   - Choisit terrain
   - Choisit péripétie (optionnel)
   - Choisit deployment_mode
   - Choisit setup_mode (random ou manual)
   - Marque is_setup_complete = true
   - Valide is_setup_validated = true
   - Statut: confirmed

3. DEMANDES DE PARTICIPATION
   - Autres joueurs créent PlayerMatchRequest
   - Proposent faction/détachement
   - Statut: pending

4. ACCEPTATION
   - Créateur accepte une demande
   - opponent_id défini
   - Autres demandes: rejected

5. MATCH EN COURS
   - Joueurs jouent
   - Saisissent scores (creator_score, opponent_score)
   - Saisissent victory points

6. CLÔTURE (status: completed)
   - winner_id déterminé
   - is_draw défini
   - played_at enregistré
```

---

## 🎮 MODES DE JEU DÉCOUVERTS

### Setup Modes
- `random` - Setup aléatoire
- `manual` - Setup manuel

### Deployment Modes
- Strike Force
- Incursion
- Asymmetric Warfare

### Match Types
- `competitive` - Compétitif
- `narrative` - Narratif

### Availability Types
- `single` - Date fixe unique
- `period` - Période (de/à)

---

## 📚 MODÈLES ELOQUENT (35 modèles)

1. User
2. Tournament
3. TournamentMatch
4. TournamentMissionPool
5. PlayerMatch
6. PlayerMatchRequest
7. PlayerAvailability
8. PrimaryMission
9. PrimaryMissionSection
10. SecondaryMission
11. SecondaryMissionSection
12. TwistMission
13. AsymmetricPrimaryMission
14. StrikeForceDeploymentCard
15. IncursionDeploymentCard
16. AsymmetricWarfareDeploymentCard
17. TerrainLayout
18. Faction
19. Detachment
20. DetachmentAbility
21. Ability
22. ArmyList
23. BsdataDetachment
24. BsdataImport
25. BsdataUnit
26. Translation
27. WarhammerGlossary
28. CalendarSlot
29. MatchAvailability
30. MatchRequest
31. Menu
32. Page
33. Unit
34. Wargear
35. GameMatch

---

## 🎮 CONTRÔLEURS (20 contrôleurs)

1. AuthenticatedSessionController
2. ConfirmablePasswordController
3. EmailVerificationNotificationController
4. EmailVerificationPromptController
5. HomeController
6. MatchAvailabilityController
7. MatchSetupController
8. NewPasswordController
9. PasswordController
10. PasswordResetLinkController
11. PlayerAvailabilityController
12. PlayerMatchController
13. PlayerMatchRequestController
14. ProfileController
15. RegisteredUserController
16. ThemeController
17. TournamentController
18. TournamentMatchController
19. VerifyEmailController
20. WebhookController

---

## 🚨 DÉCOUVERTES IMPORTANTES

### 1. Données Warhammer Complètes
- **1669 datasheets** importées
- **1284 stratagèmes** importés
- **227 détachements** avec capacités

### 2. Système de Missions Complexe
- **Missions primaires** avec sections (26 sections)
- **Missions secondaires** (19)
- **Péripéties** (9)
- **Missions asymétriques** (5)

### 3. Cartes de Déploiement Multiples
- Strike Force (6 cartes)
- Incursion (6 cartes)
- Asymmetric Warfare (5 cartes)

### 4. Pools de Missions
- **40 pools** créés
- **202 liaisons** pool-terrain
- Chaque pool a ses propres missions et terrains

### 5. Setup et Deployment Modes
- Setup: random ou manual
- Deployment: Strike Force, Incursion, Asymmetric Warfare

### 6. Disponibilités Flexibles
- Date fixe unique
- Période (de/à)

---

## 📝 STATUTS RÉELS

### Tournois
- draft, registration_open, registration_closed, in_progress, completed

### Matchs de Tournoi
- scheduled, in_progress, completed

### Matchs Joueurs
- open, confirmed, completed, cancelled

### Demandes de Matchs
- pending, accepted, rejected, cancelled

### Traductions
- pending, auto, reviewed, approved

---

**Audit Complet Terminé**
**74 migrations**
**35 modèles**
**20 contrôleurs**
**45 tables**
**Toutes les données documentées**

