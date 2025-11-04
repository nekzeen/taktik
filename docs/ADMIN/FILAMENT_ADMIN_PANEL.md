# ⚙️ FILAMENT ADMIN PANEL - DOCUMENTATION COMPLÈTE

**Date**: 3 novembre 2025
**URL**: `https://dev2.gaelmorvan.fr/admin`

---

## 🎯 OBJECTIF

Filament est le panel d'administration qui permet de gérer :
- Tournois et matchs
- Missions (primaires, secondaires, péripéties)
- Traductions et glossaire
- Utilisateurs et permissions
- Détachements et factions

---

## 📋 RESSOURCES FILAMENT DISPONIBLES

### 1. GESTION DES TOURNOIS

**Chemin**: `/admin/tournaments`

**Modèle**: `Tournament`

**Opérations**:
- ✅ Créer un tournoi
- ✅ Voir les tournois
- ✅ Modifier un tournoi
- ✅ Supprimer un tournoi

**Champs**:
- `name` - Nom du tournoi
- `description` - Description
- `organizer_id` - Organisateur (User)
- `start_date` - Date de début
- `end_date` - Date de fin
- `max_players` - Nombre max de joueurs
- `current_round` - Round actuel
- `status` - Statut (draft, registration_open, registration_closed, in_progress, completed)
- `army_size` - Taille de l'armée

**Relations**:
- `organizer` - Utilisateur organisateur
- `matches` - Matchs du tournoi
- `missionPools` - Pools de missions

**Permissions**:
- ✅ Admin : Accès complet
- ❌ Utilisateur : Pas d'accès

---

### 2. GESTION DES MATCHS DE TOURNOI

**Chemin**: `/admin/tournament-matches`

**Modèle**: `TournamentMatch`

**Opérations**:
- ✅ Voir les matchs
- ✅ Modifier un match
- ✅ Saisir les scores

**Champs**:
- `tournament_id` - Tournoi
- `player1_id` - Joueur 1 (User)
- `player2_id` - Joueur 2 (User)
- `round` - Round
- `table_number` - Numéro de table
- `primary_mission_id` - Mission primaire
- `secondary_mission_id` - Mission secondaire
- `twist_mission_id` - Péripétie
- `player1_score` - Score joueur 1
- `player2_score` - Score joueur 2
- `winner_id` - Gagnant (User)
- `status` - Statut (scheduled, in_progress, completed)
- `deployment_mode` - Mode de déploiement
- `setup_mode` - Mode de setup
- `army_points` - Points d'armée

**Relations**:
- `tournament` - Tournoi
- `player1` - Joueur 1
- `player2` - Joueur 2
- `primaryMission` - Mission primaire
- `secondaryMission` - Mission secondaire
- `twistMission` - Péripétie

---

### 3. GESTION DES MATCHS JOUEURS

**Chemin**: `/admin/player-matches`

**Modèle**: `PlayerMatch`

**Opérations**:
- ✅ Voir les matchs
- ✅ Modifier un match
- ✅ Supprimer un match

**Champs**:
- `creator_id` - Créateur (User)
- `opponent_id` - Adversaire (User)
- `type` - Type (competitive, narrative)
- `army_points` - Points d'armée
- `faction` - Faction
- `detachment` - Détachement
- `city` - Ville
- `department` - Département
- `availability_type` - Type de disponibilité (single, period)
- `available_at` - Date disponibilité (single)
- `available_from` - Date début (period)
- `available_to` - Date fin (period)
- `status` - Statut (open, confirmed, completed, cancelled)
- `creator_score` - Score créateur
- `opponent_score` - Score adversaire
- `primary_mission_id` - Mission primaire
- `secondary_mission_id` - Mission secondaire
- `terrain_layout_id` - Terrain
- `twist_mission_id` - Péripétie
- `deployment_mode` - Mode de déploiement
- `setup_mode` - Mode de setup (random, manual)
- `is_setup_complete` - Setup complet?
- `is_setup_validated` - Setup validé?
- `winner_id` - Gagnant
- `is_draw` - Match nul?

**Relations**:
- `creator` - Créateur
- `opponent` - Adversaire
- `requests` - Demandes de participation
- `availabilities` - Disponibilités

**Affichage du Statut**:
- `open` + `is_setup_validated=false` → "Configuration en cours"
- `open` + `is_setup_validated=true` → "Ouvert"
- `confirmed` → "Confirmé"
- `completed` → "Terminé"
- `cancelled` → "Annulé"

---

### 4. GESTION DES DEMANDES DE MATCHS

**Chemin**: `/admin/player-match-requests`

**Modèle**: `PlayerMatchRequest`

**Opérations**:
- ✅ Voir les demandes
- ✅ Modifier une demande
- ✅ Supprimer une demande

**Champs**:
- `player_match_id` - Match joueur
- `requester_id` - Demandeur (User)
- `faction_id` - Faction proposée
- `detachment_id` - Détachement proposé
- `status` - Statut (pending, accepted, rejected, cancelled)

**Relations**:
- `playerMatch` - Match joueur
- `requester` - Demandeur
- `faction` - Faction
- `detachment` - Détachement

---

### 5. GESTION DES MISSIONS PRIMAIRES

**Chemin**: `/admin/primary-missions`

**Modèle**: `PrimaryMission`

**Opérations**:
- ✅ Voir les missions
- ✅ Modifier une mission
- ✅ Supprimer une mission

**Onglets**:
- **Données de base** : Nom, description, slug
- **Texte complet** : Texte complet de la mission
- **Conditions** : When condition, timing
- **Métadonnées** : Edition, source, statut actif
- **Traductions** : Gestion des traductions

**Relations**:
- `sections` - Sections de la mission
- `translations` - Traductions

---

### 6. GESTION DES MISSIONS SECONDAIRES

**Chemin**: `/admin/secondary-missions`

**Modèle**: `SecondaryMission`

**Opérations**:
- ✅ Voir les missions
- ✅ Modifier une mission
- ✅ Supprimer une mission
- ✅ Importer des missions

**Onglets**:
- **Données de base** : Nom, description, slug
- **Texte complet** : Texte complet
- **Conditions** : When drawn, when condition, scoring conditions
- **Métadonnées** : Edition, source, statut actif
- **Traductions** : Gestion des traductions

**Import**:
- Page spéciale : `/admin/secondary-missions/import`
- Permet d'importer des missions via copier/coller

---

### 7. GESTION DES PÉRIPÉTIES

**Chemin**: `/admin/twist-missions`

**Modèle**: `TwistMission`

**Opérations**:
- ✅ Voir les péripéties
- ✅ Modifier une péripétie
- ✅ Supprimer une péripétie

**Onglets**:
- **Données de base** : Nom, description, slug
- **Texte complet** : Texte complet
- **Conditions et Effets** : When drawn, effet, timing
- **Métadonnées** : Edition, source, statut actif
- **Traductions** : Gestion des traductions

---

### 8. GESTION DES TRADUCTIONS

**Chemin**: `/admin/translations`

**Modèle**: `Translation`

**Opérations**:
- ✅ Voir les traductions
- ✅ Modifier une traduction
- ✅ Filtrer par statut

**Champs**:
- `source_text` - Texte source
- `translated_text` - Texte traduit
- `locale` - Langue (fr, de, es, it)
- `resource_type` - Type de ressource
- `resource_id` - ID de la ressource
- `field` - Champ traduit
- `status` - Statut (pending, auto, reviewed, approved)
- `reviewed_by` - Révisé par (User)
- `reviewed_at` - Date de révision

**Filtres**:
- Par statut
- Par locale
- Par resource type

**Workflow**:
1. Créer traduction (status: pending)
2. DeepL traduit (status: auto)
3. Admin modifie (status: reviewed)
4. Admin approuve (status: approved)

---

### 9. GESTION DU GLOSSAIRE WARHAMMER

**Chemin**: `/admin/warhammer-glossaries`

**Modèle**: `WarhammerGlossary`

**Opérations**:
- ✅ Voir les entrées
- ✅ Modifier une entrée
- ✅ Supprimer une entrée

**Champs**:
- `english_term` - Terme anglais
- `category` - Catégorie (ability, keyword, unit, condition, action, general)
- `context` - Contexte (primary_mission, secondary_mission, twist_mission, deployment_zone, general)
- `french_translation` - Traduction FR
- `german_translation` - Traduction DE
- `spanish_translation` - Traduction ES
- `italian_translation` - Traduction IT
- `description` - Description
- `example` - Exemple
- `status` - Statut (pending, approved)
- `usage_count` - Nombre d'utilisations

**Utilisation**:
- Créé automatiquement par l'Observer
- Mis à jour quand une traduction est modifiée
- Utilisé pour appliquer les traductions cohérentes

---

### 10. GESTION DES UTILISATEURS

**Chemin**: `/admin/users`

**Modèle**: `User`

**Opérations**:
- ✅ Voir les utilisateurs
- ✅ Modifier un utilisateur
- ✅ Supprimer un utilisateur

**Champs**:
- `name` - Nom
- `email` - Email
- `password` - Mot de passe
- `role` - Rôle (admin, user, tournament_organizer)
- `faction_id` - Faction préférée
- `detachment_id` - Détachement préféré

**Permissions**:
- ✅ Admin : Accès complet
- ❌ Utilisateur : Pas d'accès

---

### 11. GESTION DES DÉTACHEMENTS

**Chemin**: `/admin/detachments`

**Modèle**: `Detachment`

**Opérations**:
- ✅ Voir les détachements
- ✅ Modifier un détachement
- ✅ Supprimer un détachement

**Champs**:
- `name` - Nom
- `faction_id` - Faction
- `bsdata_id` - ID BsData
- `description` - Description
- `rules` - Règles
- `is_active` - Actif?

**Relations**:
- `faction` - Faction
- `abilities` - Capacités
- `translations` - Traductions

---

### 12. GESTION DES FACTIONS

**Chemin**: `/admin/factions`

**Modèle**: `Faction`

**Opérations**:
- ✅ Voir les factions
- ✅ Modifier une faction
- ✅ Supprimer une faction

**Champs**:
- `name` - Nom
- `description` - Description
- `color_code` - Code couleur
- `is_active` - Actif?

**Relations**:
- `detachments` - Détachements
- `users` - Utilisateurs

---

## 🔐 PERMISSIONS FILAMENT

| Resource | Admin | Organisateur | Utilisateur |
|----------|-------|--------------|-------------|
| Tournois | ✅ | ❌ | ❌ |
| Matchs Tournoi | ✅ | ❌ | ❌ |
| Matchs Joueurs | ✅ | ❌ | ❌ |
| Missions | ✅ | ❌ | ❌ |
| Traductions | ✅ | ❌ | ❌ |
| Glossaire | ✅ | ❌ | ❌ |
| Utilisateurs | ✅ | ❌ | ❌ |
| Détachements | ✅ | ❌ | ❌ |
| Factions | ✅ | ❌ | ❌ |

---

## 🔄 WORKFLOWS ADMIN COURANTS

### Workflow 1 : Créer un Tournoi

1. Aller à `/admin/tournaments`
2. Cliquer "Créer"
3. Remplir les champs
4. Sauvegarder
5. Ouvrir les inscriptions
6. Générer les appairements
7. Assigner les missions

### Workflow 2 : Gérer les Traductions

1. Aller à `/admin/translations`
2. Filtrer par statut "auto"
3. Modifier les traductions si nécessaire
4. Changer le statut à "reviewed"
5. Approuver les traductions

### Workflow 3 : Gérer le Glossaire

1. Aller à `/admin/warhammer-glossaries`
2. Voir les entrées créées automatiquement
3. Modifier les traductions si nécessaire
4. Approuver les entrées

---

## 📝 RÉSUMÉ

**12 Ressources Filament** :
- Tournois et matchs
- Missions (primaires, secondaires, péripéties)
- Traductions et glossaire
- Utilisateurs
- Détachements et factions

**Permissions** :
- ✅ Admin : Accès complet
- ❌ Autres : Pas d'accès

**Workflows** :
- Créer tournois
- Gérer traductions
- Gérer glossaire

