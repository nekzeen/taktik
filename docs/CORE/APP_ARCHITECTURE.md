# 🏗️ ARCHITECTURE COMPLÈTE - WARHAMMER 40K TOURNAMENT MANAGER

**Dernière mise à jour:** 31 octobre 2025  
**Version:** 1.0  
**Status:** Production  
**À CONSULTER SYSTÉMATIQUEMENT avant chaque demande**

---

## 📋 TABLE DES MATIÈRES

1. [Vue d'ensemble](#vue-densemble)
2. [Structure des bases de données](#structure-des-bases-de-données)
3. [Modèles et relations](#modèles-et-relations)
4. [Pages et fonctionnalités](#pages-et-fonctionnalités)
5. [Flux de données](#flux-de-données)
6. [Mise à jour des données](#mise-à-jour-des-données)
7. [Checklist d'utilisation](#checklist-dutilisation)

---

## 🎯 VUE D'ENSEMBLE

### Objectif de l'application
Gérer les tournois et matchs Warhammer 40k avec configuration automatique des missions, terrains et péripéties.

### Technologies principales
- **Framework:** Laravel 11
- **Frontend:** Blade + Tailwind CSS + Livewire
- **Admin:** Filament
- **Base de données:** MySQL
- **Traductions:** DeepL API
- **Scraping:** Wahapedia

### Entités principales
- **Tournaments** - Tournois
- **TournamentMatches** - Matchs de tournoi
- **PlayerMatches** - Matchs simples entre joueurs
- **TournamentMissionPools** - Pools de missions pour les tournois
- **Missions** - Missions primaires, secondaires, asymétriques, péripéties
- **TerrainLayouts** - Dispositions de terrain
- **Factions** - Factions Warhammer 40k
- **Datasheets** - Fiches de détachement
- **Detachments** - Détachements

---

## 🗄️ STRUCTURE DES BASES DE DONNÉES

### 1. TOURNAMENTS & MATCHES

#### Table: `tournaments`
**Contenu:**
- `id` - Identifiant unique
- `name` - Nom du tournoi
- `description` - Description
- `created_by` - Créateur (User ID)
- `status` - État (draft, active, completed)
- `start_date`, `end_date` - Dates
- `location` - Lieu
- `max_players` - Nombre max de joueurs
- `timestamps` - Créé/Modifié

**Mise à jour:** Manuelle via interface admin  
**Utilisation:** Page d'accueil, détails tournoi, gestion des matchs

---

#### Table: `tournament_matches`
**Contenu:**
- `id` - Identifiant unique
- `tournament_id` - FK vers tournaments
- `player1_id`, `player2_id` - FK vers users
- `table_number` - Numéro de table
- `round` - Numéro du round
- `status` - État (scheduled, in_progress, completed)
- `player1_score`, `player2_score` - Scores
- `winner_id` - Gagnant (User ID)
- **SETUP FIELDS:**
  - `primary_mission_id` - FK vers primary_missions
  - `terrain_layout_id` - FK vers terrain_layouts
  - `twist_mission_id` - FK vers twist_missions
  - `asymmetric_primary_mission_id` - FK vers asymmetric_primary_missions
  - `deployment_mode` - Zone de déploiement (du pool)
  - `setup_mode` - Mode (random, manual)
  - `is_setup_complete` - Configuration complète?

**Mise à jour:**
- Manuelle via page `/tournaments/{id}/matches/{match}/setup`
- Tirage au sort via `MatchSetupService`
- Scores via page de résumé

**Utilisation:**
- Page de tournoi (liste des matchs)
- Page de configuration du match
- Page de résumé du match
- Affichage des résultats

---

#### Table: `player_matches`
**Contenu:**
- `id` - Identifiant unique
- `creator_id` - Créateur (User ID)
- `opponent_id` - Adversaire (User ID)
- `type` - Type (competitive, narrative)
- `army_points` - Points d'armée
- `faction` - Faction
- `detachment` - Détachement
- `status` - État (open, confirmed, completed, cancelled)
- `city`, `department` - Localisation
- `availability_type` - Type de disponibilité (single, range)
- `available_at`, `available_from`, `available_to` - Dates
- `creator_score`, `opponent_score` - Scores
- `winner_id` - Gagnant
- `is_draw` - Match nul?
- `played_at` - Date du match
- **SETUP FIELDS:**
  - `primary_mission_id` - FK vers primary_missions
  - `terrain_layout_id` - FK vers terrain_layouts
  - `twist_mission_id` - FK vers twist_missions
  - `asymmetric_primary_mission_id` - FK vers asymmetric_primary_missions
  - `deployment_mode` - Zone de déploiement
  - `setup_mode` - Mode (random, manual)
  - `is_setup_complete` - Configuration complète?

**Mise à jour:**
- Manuelle via page `/player-matches/{id}/setup`
- Tirage au sort via `MatchSetupService`
- Scores via page de résumé

**Utilisation:**
- Page de matchs simples
- Page de configuration du match simple
- Page de résumé du match simple
- Affichage des résultats

---

### 2. MISSION POOLS

#### Table: `tournament_mission_pools`
**Contenu:**
- `id` - Identifiant unique
- `pool_number` - Numéro (1-20)
- `pool_letter` - Lettre (A-T)
- `name` - Nom du pool
- `slug` - Slug unique
- `description` - Description
- `primary_mission_id` - FK vers primary_missions
- `deployment_mode` - Zone de déploiement (Hammer and Anvil, Dawn of War, etc.)
- `terrain_layout_id` - Terrain principal (pour compatibilité)
- `use_twist_deck` - Utiliser les péripéties?
- `source` - Source (Chapter Approved, etc.)
- `is_active` - Actif?
- `timestamps`

**Relations:**
- `availableTerrainLayouts()` - Many-to-many vers terrain_layouts
- `secondaryMissions()` - Many-to-many vers secondary_missions
- `primaryMission()` - BelongsTo vers primary_missions

**Mise à jour:**
- Manuelle via admin Filament
- Synchronisation depuis Wahapedia (futur)

**Utilisation:**
- Tirage au sort des matchs de tournoi
- Tirage au sort des matchs simples
- Affichage des zones de déploiement disponibles

**IMPORTANT:** Chaque pool définit:
- ✅ La mission primaire
- ✅ La zone de déploiement (fixe)
- ✅ Les terrains disponibles (6 options)
- ✅ Les missions secondaires

---

### 3. MISSIONS

#### Table: `primary_missions`
**Contenu:**
- `id` - Identifiant unique
- `name` - Nom (ex: "LINCHPIN")
- `description` - Description courte
- `full_text` - Texte complet
- `timing` - Timing
- `scoring_conditions` - Conditions de scoring
- `max_vp` - VP maximum
- `edition` - Édition (Chapter Approved 2025-26)
- `source` - Source
- `slug` - Slug unique
- `is_active` - Actif?
- `timestamps`

**Relations:**
- `sections()` - HasMany vers primary_mission_sections
- `translations()` - HasMany vers translations

**Mise à jour:**
- Importation via `php artisan missions:import-xml`
- Traduction via `php artisan missions:translate --locale=fr`
- Manuelle via admin Filament

**Utilisation:**
- Affichage dans les pools de missions
- Tirage au sort des matchs
- Page de résumé du match

---

#### Table: `primary_mission_sections`
**Contenu:**
- `id` - Identifiant unique
- `primary_mission_id` - FK vers primary_missions
- `type` - Type (action, scoring, objective)
- `order` - Ordre d'affichage
- `title` - Titre
- `title_fr` - Titre FR
- `timing` - Timing
- `timing_fr` - Timing FR
- `content` - Contenu
- `content_fr` - Contenu FR
- `vp` - Points de victoire
- `timestamps`

**Mise à jour:**
- Importation via XML
- Manuelle via admin Filament

**Utilisation:**
- Affichage détaillé des missions

---

#### Table: `secondary_missions`
**Contenu:**
- Similaire à primary_missions
- Utilisées dans les pools

**Mise à jour:**
- Importation via XML
- Traduction via DeepL

---

#### Table: `twist_missions`
**Contenu:**
- `id` - Identifiant unique
- `name` - Nom (ex: "AMBUSH")
- `description` - Description
- `full_text` - Texte complet
- `when_drawn` - Quand tiré
- `effect` - Effet
- `timing` - Timing
- `edition` - Édition
- `source` - Source
- `slug` - Slug unique
- `is_active` - Actif?
- `timestamps`

**Mise à jour:**
- Importation via `php artisan missions:import-twist-xml`
- Traduction via `php artisan missions:translate-twist --locale=fr`

**Utilisation:**
- Tirage au sort des matchs
- Affichage dans le résumé du match

---

#### Table: `asymmetric_primary_missions`
**Contenu:**
- Similaire à primary_missions
- Missions spécifiques au mode asymétrique

**Mise à jour:**
- Importation via XML
- Traduction via DeepL

**Utilisation:**
- Tirage au sort en mode asymétrique
- Affichage dans le résumé du match asymétrique

---

### 4. TERRAIN & DÉPLOIEMENT

#### Table: `terrain_layouts`
**Contenu:**
- `id` - Identifiant unique
- `name` - Nom (ex: "Ruins")
- `description` - Description
- `image_path` - Chemin de l'image
- `image_url` - URL de l'image
- `source` - Source
- `slug` - Slug unique
- `is_active` - Actif?
- `timestamps`

**Mise à jour:**
- Manuelle via admin Filament
- Importation depuis Wahapedia (futur)

**Utilisation:**
- Tirage au sort des matchs
- Affichage dans le résumé du match
- Affichage dans les pools

---

#### Table: `strike_force_deployment_cards`
**Contenu:**
- `id` - Identifiant unique
- `name` - Nom (ex: "Hammer and Anvil")
- `description` - Description
- `full_text` - Texte complet
- `image_path` - Chemin local de l'image
- `image_url` - URL originale
- `image_filename` - Nom du fichier
- `edition` - Édition
- `source` - Source
- `slug` - Slug unique
- `is_active` - Actif?
- `timestamps`

**Mise à jour:**
- Importation via `php artisan missions:import-strike-force`
- Téléchargement d'images via `php artisan missions:download-strike-force-images`
- Traduction via `php artisan missions:translate-strike-force --locale=fr`

**Utilisation:**
- Référence pour les zones de déploiement
- Affichage dans les détails du match

---

### 5. WARHAMMER 40K DATA

#### Table: `factions`
**Contenu:**
- `id` - Identifiant unique
- `wahapedia_id` - ID Wahapedia
- `name` - Nom (ex: "Necrons")
- `slug` - Slug unique
- `description` - Description
- `image_url` - URL de l'image
- `is_active` - Actif?
- `timestamps`

**Mise à jour:**
- Importation via Wahapedia
- Manuelle via admin Filament

**Utilisation:**
- Sélection de faction dans les matchs simples
- Affichage dans les listes d'armées

---

#### Table: `datasheets`
**Contenu:**
- `id` - Identifiant unique
- `wahapedia_id` - ID Wahapedia
- `faction_id` - FK vers factions
- `name` - Nom (ex: "Immortals")
- `slug` - Slug unique
- `description` - Description
- `points` - Coût en points
- `is_active` - Actif?
- `timestamps`

**Mise à jour:**
- Importation via Wahapedia
- Manuelle via admin Filament

**Utilisation:**
- Affichage des unités disponibles
- Sélection dans les listes d'armées

---

#### Table: `detachments`
**Contenu:**
- `id` - Identifiant unique
- `wahapedia_id` - ID Wahapedia
- `faction_id` - FK vers factions
- `name` - Nom (ex: "Immortal Legions")
- `name_fr` - Nom FR
- `slug` - Slug unique
- `description` - Description
- `is_active` - Actif?
- `timestamps`

**Mise à jour:**
- Importation via Wahapedia
- Traduction via DeepL
- Manuelle via admin Filament

**Utilisation:**
- Sélection de détachement dans les matchs simples
- Affichage formaté : "Faction - Nom Anglais (Nom Français)"

---

### 6. TRADUCTIONS

#### Table: `translations`
**Contenu:**
- `id` - Identifiant unique
- `resource_type` - Type (PrimaryMission, TwistMission, Detachment, etc.)
- `resource_id` - ID de la ressource
- `field` - Champ traduit (name, description, etc.)
- `locale` - Langue (fr, de, es, it)
- `source_text` - Texte source (anglais)
- `translated_text` - Texte traduit
- `status` - Statut (pending, auto, manual, reviewed, approved)
- `reviewed_by` - User ID de celui qui a révisé
- `reviewed_at` - Date de révision
- `timestamps`

**Mise à jour:**
- Automatique via DeepL lors de l'importation
- Manuelle via admin Filament
- Propagation automatique via Observer

**Utilisation:**
- Affichage multilingue dans l'application
- Glossaire Warhammer

---

### 7. GLOSSAIRE

#### Table: `glossaries`
**Contenu:**
- `id` - Identifiant unique
- `english_term` - Terme anglais (ex: "AMBUSH")
- `french_translation` - Traduction FR (ex: "Embuscade")
- `category` - Catégorie (action, mission, etc.)
- `context` - Contexte (twist_mission, primary_mission, etc.)
- `status` - Statut (pending, approved)
- `usage_count` - Nombre d'utilisations
- `timestamps`

**Mise à jour:**
- Automatique via Observer quand une traduction est modifiée
- Propagation automatique à TOUTES les traductions

**Utilisation:**
- Référence pour les traductions cohérentes
- Propagation des modifications

---

## 🔗 MODÈLES ET RELATIONS

### Hiérarchie des relations

```
Tournament (1)
  ├─ TournamentMatch (Many)
  │   ├─ PrimaryMission (1)
  │   ├─ TerrainLayout (1)
  │   ├─ TwistMission (1)
  │   ├─ AsymmetricPrimaryMission (1)
  │   └─ User (2) - player1, player2
  │
  └─ TournamentMissionPool (Many)
      ├─ PrimaryMission (1)
      ├─ TerrainLayout (Many) - availableTerrainLayouts
      └─ SecondaryMission (Many)

PlayerMatch (1)
  ├─ PrimaryMission (1)
  ├─ TerrainLayout (1)
  ├─ TwistMission (1)
  ├─ AsymmetricPrimaryMission (1)
  ├─ User (2) - creator, opponent
  └─ PlayerMatchRequest (Many)

Faction (1)
  ├─ Datasheet (Many)
  │   └─ Weapon (Many)
  ├─ Detachment (Many)
  │   └─ DetachmentAbility (Many)
  └─ Stratagem (Many)

PrimaryMission (1)
  ├─ PrimaryMissionSection (Many)
  └─ Translation (Many)

TwistMission (1)
  └─ Translation (Many)

Detachment (1)
  └─ Translation (Many)
```

---

## 📄 PAGES ET FONCTIONNALITÉS

### TOURNOIS

#### Page: `/tournaments`
**Affiche:** Liste des tournois  
**Données utilisées:**
- `tournaments` table
- Compte des matchs par tournoi

**Actions possibles:**
- Créer un tournoi
- Voir les détails
- Modifier
- Supprimer

---

#### Page: `/tournaments/{id}`
**Affiche:** Détails du tournoi + liste des matchs  
**Données utilisées:**
- `tournaments`
- `tournament_matches` (avec relations)
- `primary_missions`
- `terrain_layouts`
- `twist_missions`
- `users`

**Actions possibles:**
- Voir les matchs
- Configurer un match
- Voir le résumé d'un match

---

#### Page: `/tournaments/{id}/matches/{match}/setup`
**Affiche:** Configuration du match de tournoi  
**Données utilisées:**
- `tournament_matches`
- `tournament_mission_pools` (pour zones de déploiement)
- `primary_missions`
- `terrain_layouts`
- `twist_missions`
- `asymmetric_primary_missions`

**Actions possibles:**
- Tirage au sort (normal ou asymétrique)
- Configuration manuelle
- Réinitialisation

**Service utilisé:** `MatchSetupService::randomizeMatch()`

---

#### Page: `/tournaments/{id}/matches/{match}/summary`
**Affiche:** Résumé de la configuration + scoring  
**Données utilisées:**
- `tournament_matches`
- `primary_missions` + sections
- `terrain_layouts`
- `twist_missions`
- `asymmetric_primary_missions`
- `strike_force_deployment_cards`

**Actions possibles:**
- Entrer les scores
- Valider le résultat

---

### MATCHS SIMPLES

#### Page: `/player-matches`
**Affiche:** Liste des matchs simples  
**Données utilisées:**
- `player_matches`
- `users`
- `factions`
- `detachments`

**Actions possibles:**
- Créer un match
- Voir les détails
- Rejoindre un match
- Configurer un match

---

#### Page: `/player-matches/{id}`
**Affiche:** Détails du match simple  
**Données utilisées:**
- `player_matches`
- `users`
- `factions`
- `detachments`
- `primary_missions`
- `terrain_layouts`
- `twist_missions`

**Actions possibles:**
- Configurer le match (bouton "⚙️ Configurer")
- Voir le résumé
- Entrer les scores

---

#### Page: `/player-matches/{id}/setup`
**Affiche:** Configuration du match simple  
**Données utilisées:**
- `player_matches`
- `tournament_mission_pools` (pour zones de déploiement)
- `primary_missions`
- `terrain_layouts`
- `twist_missions`
- `asymmetric_primary_missions`

**Actions possibles:**
- Tirage au sort (normal ou asymétrique)
- Configuration manuelle
- Réinitialisation

**Service utilisé:** `MatchSetupService::randomizeMatch()`

**IMPORTANT:** Utilise le MÊME système que les tournois (pools de missions)

---

#### Page: `/player-matches/{id}/summary`
**Affiche:** Résumé de la configuration + scoring  
**Données utilisées:**
- `player_matches`
- `primary_missions` + sections
- `terrain_layouts`
- `twist_missions`
- `asymmetric_primary_missions`
- `strike_force_deployment_cards`

**Actions possibles:**
- Entrer les scores
- Valider le résultat

---

## 🔄 FLUX DE DONNÉES

### Flux 1: Tirage au sort d'un match

```
Utilisateur clique "🎲 Normal"
    ↓
POST /tournaments/{id}/matches/{match}/randomize
    ↓
MatchSetupController::randomizeTournamentMatch()
    ↓
MatchSetupService::randomizeMatch($match, 'normal')
    ↓
Récupère un pool aléatoire actif
    ↓
Tire au sort:
  - primary_mission_id (du pool)
  - terrain_layout_id (parmi les terrains du pool)
  - twist_mission_id (aléatoire)
  - deployment_mode (du pool)
    ↓
Sauvegarde en DB
    ↓
Redirection vers la page setup
    ↓
Affichage de la configuration
```

---

### Flux 2: Configuration manuelle d'un match

```
Utilisateur sélectionne les options
    ↓
POST /tournaments/{id}/matches/{match}/setup
    ↓
MatchSetupController::updateTournamentMatch()
    ↓
Validation des données
    ↓
MatchSetupService::updateMatchSetup(
  $match,
  $primaryMissionId,
  $terrainLayoutId,
  $twistMissionId,
  $asymmetricMissionId
)
    ↓
Mise à jour en DB
    ↓
Sauvegarde deployment_mode si fourni
    ↓
Redirection vers la page setup
```

---

### Flux 3: Traduction automatique

```
Admin lance: php artisan missions:translate --locale=fr
    ↓
TranslatePrimaryMissions command
    ↓
Pour chaque mission primaire:
  - Récupère le texte anglais
  - Appelle DeepL API
  - Crée une entrée dans translations
  - Statut: 'auto'
    ↓
Traductions disponibles dans l'interface
```

---

### Flux 4: Propagation de traductions

```
Admin modifie une traduction dans Filament
    ↓
TranslationObserver::updated()
    ↓
Détecte le changement
    ↓
Extrait le terme anglais
    ↓
Crée/met à jour le glossaire
    ↓
Cherche TOUTES les traductions contenant ce terme
    ↓
Remplace le terme dans TOUTES les traductions
    ↓
Marque les traductions comme 'reviewed'
    ↓
Traductions propagées partout
```

---

## 🔄 MISE À JOUR DES DONNÉES

### Missions primaires

**Commande:**
```bash
php artisan missions:import-xml
php artisan missions:translate --locale=fr
```

**Processus:**
1. Importe depuis XML ou Wahapedia
2. Crée les entrées dans `primary_missions`
3. Crée les sections dans `primary_mission_sections`
4. Traduit via DeepL
5. Crée les entrées dans `translations`

**Résultat:**
- ✅ 10 missions disponibles
- ✅ Traductions FR
- ✅ Sections détaillées

---

### Péripéties

**Commande:**
```bash
php artisan missions:import-twist-xml
php artisan missions:translate-twist --locale=fr
```

**Processus:**
1. Importe depuis XML
2. Crée les entrées dans `twist_missions`
3. Traduit via DeepL
4. Crée les entrées dans `translations`

**Résultat:**
- ✅ 10 péripéties disponibles
- ✅ Traductions FR

---

### Détachements

**Commande:**
```bash
php artisan bsdata:sync
php artisan missions:translate-detachments --locale=fr
```

**Processus:**
1. Importe depuis Wahapedia
2. Crée les entrées dans `detachments`
3. Traduit via DeepL
4. Crée les entrées dans `translations`

**Résultat:**
- ✅ Tous les détachements disponibles
- ✅ Traductions FR
- ✅ Format: "Faction - Nom Anglais (Nom Français)"

---

### Pools de missions

**Mise à jour:** Manuelle via admin Filament

**Processus:**
1. Créer un pool
2. Sélectionner la mission primaire
3. Sélectionner la zone de déploiement
4. Sélectionner les terrains disponibles
5. Sélectionner les missions secondaires
6. Activer le pool

**Résultat:**
- ✅ Pool disponible pour les tirages au sort

---

## 📊 CHECKLIST D'UTILISATION

### AVANT CHAQUE MODIFICATION

**PHASE 1 - ANALYSE:**
- [ ] Lire la demande 3 fois
- [ ] Identifier EXACTEMENT ce qui est demandé
- [ ] Identifier les tables concernées
- [ ] Identifier les pages concernées

**PHASE 2 - VÉRIFICATIONS:**
- [ ] Vérifier les tables existent
- [ ] Vérifier les colonnes existent
- [ ] Vérifier les relations existent
- [ ] Vérifier les données existent en base

**PHASE 3-7 - IMPLÉMENTATION:**
- [ ] Faire les changements
- [ ] Respecter le style existant
- [ ] Utiliser les données existantes

**PHASE 8 - VÉRIFICATIONS LOGIQUE:**
- [ ] Vérifier les deux types (tournoi ET matchs simples)
- [ ] Vérifier les données cohérentes
- [ ] Vérifier les conditions Blade
- [ ] Tester MANUELLEMENT les deux cas

**PHASE 9 - TESTS RÉELS:**
- [ ] Compiler: `npm run build`
- [ ] Vider cache: `php artisan cache:clear`
- [ ] Compiler vues: `php artisan view:cache`
- [ ] Tester dans le navigateur
- [ ] Vérifier en base de données
- [ ] Vérifier les redirections

---

### COMMANDES IMPORTANTES

**Vérifier les données:**
```bash
php artisan tinker
> \App\Models\TournamentMissionPool::where('is_active', true)->get()
> \App\Models\PlayerMatch::find(4)
> DB::table('translations')->where('resource_type', 'Detachment')->get()
```

**Compiler et nettoyer:**
```bash
npm run build
php artisan cache:clear
php artisan view:cache
```

**Importer les données:**
```bash
php artisan missions:import-xml
php artisan missions:translate --locale=fr
php artisan missions:import-twist-xml
php artisan missions:translate-twist --locale=fr
```

---

### POINTS CRITIQUES À RETENIR

**1. DEUX TYPES DE MATCHS - MÊME COMPORTEMENT**
- ✅ TournamentMatch et PlayerMatch doivent avoir le MÊME fonctionnement
- ✅ Les deux utilisent les pools de missions
- ✅ Les deux tirent au sort la zone de déploiement depuis le pool
- ✅ Les deux supportent le mode asymétrique

**2. ZONES DE DÉPLOIEMENT**
- ✅ Viennent du pool de missions (pas de liste statique)
- ✅ Définies dans `tournament_mission_pools.deployment_mode`
- ✅ Sauvegardées dans `tournament_matches.deployment_mode` et `player_matches.deployment_mode`
- ✅ Affichées dans le résumé du match

**3. MISSIONS ASYMÉTRIQUES**
- ✅ Disponibles pour les deux types de matchs
- ✅ Utilisent `asymmetric_primary_mission_id`
- ✅ Zones de déploiement spécifiques: Hammer and Anvil, Dawn of War, Incursion
- ✅ Tirage au sort automatique de la zone

**4. TRADUCTIONS**
- ✅ Automatiques via DeepL lors de l'importation
- ✅ Propagation automatique via Observer
- ✅ Statut: pending → auto → manual → reviewed → approved
- ✅ Glossaire automatique

**5. DONNÉES EXISTANTES**
- ✅ TOUJOURS chercher en base avant de créer du nouveau
- ✅ Utiliser les relations Eloquent
- ✅ Réutiliser les modèles existants
- ✅ Ne pas dupliquer les données

---

## 🎯 RÉSUMÉ POUR LES DEMANDES FUTURES

**Quand vous me faites une demande, je vais:**

1. **Consulter ce document** pour comprendre l'architecture
2. **Identifier les tables** concernées
3. **Vérifier les relations** entre les tables
4. **Vérifier les deux types** (tournoi ET matchs simples)
5. **Appliquer les CODING_RULES** (windsurf.rules.json)
6. **Faire les tests réels** (PHASE 9 obligatoire)
7. **Vérifier en base de données** que les données sont correctes

**Ce document est la SOURCE DE VÉRITÉ pour toute modification.**

---

*Document créé le 31 octobre 2025*  
*À mettre à jour à chaque changement majeur d'architecture*
