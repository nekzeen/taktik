# 🏆 GESTION DES TOURNOIS ET MATCHS - FONCTIONNEMENT RÉEL

## 📊 État Actuel

- **1 tournoi** actif
- **4 matchs de tournoi** programmés
- **4 matchs joueurs** (libres)
- **2 demandes de matchs** en attente

---

## 🎯 Deux Systèmes Parallèles

### 1. TOURNOIS (Matchs Appairés)

**Caractéristiques:**
- Organisateur crée le tournoi
- Joueurs s'inscrivent
- Système d'appairage automatique
- Missions assignées par l'organisateur
- Scores saisis et validés
- Classement final

**Modèles:**
- `Tournament` - Le tournoi
- `TournamentMatch` - Matchs du tournoi
- `TournamentMissionPool` - Pool de missions

### 2. MATCHS JOUEURS (Libres)

**Caractéristiques:**
- Joueur crée un match
- Autres joueurs demandent à participer
- Créateur accepte/rejette les demandes
- Matchs 1v1 uniquement
- Disponibilités gérées

**Modèles:**
- `PlayerMatch` - Le match
- `PlayerMatchRequest` - Demande de participation
- `PlayerAvailability` - Disponibilités

---

## 🏆 SYSTÈME DE TOURNOIS

### Cycle de Vie d'un Tournoi

```
1. CRÉATION
   └─ Organisateur crée le tournoi
      - Nom, description
      - Dates
      - Nombre max de joueurs
      - Statut: draft

2. INSCRIPTION
   └─ Statut: registration_open
      - Joueurs s'inscrivent
      - Vérification des détachements
      - Vérification des factions

3. FERMETURE INSCRIPTIONS
   └─ Statut: registration_closed
      - Plus d'inscriptions possibles
      - Appairage possible

4. APPAIRAGE ROUND 1
   └─ Statut: in_progress
      - Système d'appairage automatique
      - Création des TournamentMatch
      - Assignation des missions

5. JOUEURS JOUENT
   └─ Matchs en cours
      - Joueurs saisissent les scores
      - Validation des résultats

6. APPAIRAGE ROUND 2+
   └─ Répéter le processus
      - Appairage basé sur les scores
      - Nouvelles missions

7. CLÔTURE
   └─ Statut: completed
      - Classement final
      - Résultats archivés
```

### Table: `tournaments`

```sql
id | name | description | organizer_id | start_date | end_date |
max_players | current_round | status | created_at | updated_at
```

**Statuts réels:**
- `draft` - Brouillon
- `registration_open` - Inscriptions ouvertes
- `registration_closed` - Inscriptions fermées
- `in_progress` - En cours
- `completed` - Terminé

**Relations:**
```php
Tournament::with([
    'organizer',           // User
    'matches',             // TournamentMatch
    'missionPools',        // TournamentMissionPool
    'players'              // User (pivot)
])
```

### Table: `tournament_matches`

```sql
id | tournament_id | player1_id | player2_id | round | table_number |
primary_mission_id | secondary_mission_id | twist_mission_id |
player1_score | player2_score | winner_id | status | created_at | updated_at
```

**Statuts réels:**
- `scheduled` - Programmé
- `in_progress` - En cours
- `completed` - Terminé

**Scoring:**
- `player1_score` - Points du joueur 1 (0-120)
- `player2_score` - Points du joueur 2 (0-120)
- `winner_id` - ID du gagnant (ou NULL si égalité)

**Missions:**
- `primary_mission_id` - Mission primaire (obligatoire)
- `secondary_mission_id` - Mission secondaire (obligatoire)
- `twist_mission_id` - Péripétie (optionnel)

### Workflow Réel d'un Match de Tournoi

```
1. CRÉATION DU MATCH
   - Système crée TournamentMatch
   - Assigne joueurs (player1, player2)
   - Assigne missions
   - Statut: scheduled

2. AVANT LE MATCH
   - Joueurs consultent les missions
   - Joueurs se rencontrent
   - Joueurs jouent

3. SAISIE DES SCORES
   - Joueur 1 saisit: player1_score
   - Joueur 2 saisit: player2_score
   - Système calcule: winner_id

4. VALIDATION
   - Organisateur valide les résultats
   - Statut: completed

5. APPAIRAGE SUIVANT
   - Système calcule les nouveaux appairements
   - Basé sur les scores
   - Crée nouveaux TournamentMatch
```

---

## 👥 SYSTÈME DE MATCHS JOUEURS

### Cycle de Vie d'un Match Joueur

```
1. CRÉATION
   └─ Joueur crée le match
      - Type (competitive, narrative)
      - Points (1000, 1500, 2000, 3000, 3000+)
      - Localisation (ville, département)
      - Faction et détachement
      - Disponibilité (date fixe ou période)
      - Statut: open (ouvert)

2. CONFIGURATION (STATUT INTERMÉDIAIRE)
   └─ Créateur configure le match
      - Choisit les missions (primaire, secondaire, terrain, péripétie)
      - Choisit le mode de déploiement
      - Choisit le mode de setup
      - Marque setup comme complet (is_setup_complete)
      - Valide le setup (is_setup_validated)
      - Statut: confirmed (confirmé)

3. DEMANDES DE PARTICIPATION
   └─ Autres joueurs demandent à participer
      - Proposent leur faction/détachement
      - Statut demande: pending

4. ACCEPTATION/REJET
   └─ Créateur accepte une demande
      - Statut demande: accepted
      - opponent_id défini
      - Autres demandes: rejected

5. MATCH EN COURS
   └─ Les deux joueurs jouent
      - Saisissent les scores
      - creator_score et opponent_score

6. CLÔTURE
   └─ Statut: completed
      - Résultats archivés
      - Gagnant déterminé (winner_id)
```

### Table: `player_matches`

```sql
id | creator_id | opponent_id | type | army_points | faction | detachment_id |
city | department | availability_type | available_at | available_from | available_to |
status | creator_score | opponent_score | winner_id | is_draw | played_at |
primary_mission_id | secondary_mission_id | terrain_layout_id | twist_mission_id |
asymmetric_primary_mission_id | deployment_mode | setup_mode |
is_setup_complete | is_setup_validated | created_at | updated_at
```

**Types réels:**
- `competitive` - Compétitif
- `narrative` - Narratif

**Points réels:**
- `1000` - Incursion
- `1500` - Engagement à l'aube
- `2000` - Force de frappe
- `3000` - Offensive
- `3000+` - Plus de 3000 points

**Statuts réels (4 statuts):**
- `open` - Ouvert (en attente de configuration)
- `confirmed` - Confirmé (configuration complète, en attente de demandes)
- `completed` - Terminé (match joué)
- `cancelled` - Annulé

**Champs de Configuration:**
- `is_setup_complete` - Setup marqué comme complet
- `is_setup_validated` - Setup validé par le créateur
- `deployment_mode` - Mode de déploiement choisi
- `setup_mode` - Mode de setup choisi
- `primary_mission_id` - Mission primaire assignée
- `secondary_mission_id` - Mission secondaire assignée
- `terrain_layout_id` - Terrain choisi
- `twist_mission_id` - Péripétie optionnelle
- `asymmetric_primary_mission_id` - Mission asymétrique (si applicable)

**Relations:**
```php
PlayerMatch::with([
    'creator',              // User
    'faction',              // Faction
    'detachment',           // Detachment
    'requests',             // PlayerMatchRequest
    'availabilities'        // PlayerAvailability
])
```

### Table: `player_match_requests`

```sql
id | player_match_id | player_id | faction_id | detachment_id |
status | created_at | updated_at
```

**Statuts réels:**
- `pending` - En attente
- `accepted` - Acceptée
- `rejected` - Rejetée
- `cancelled` - Annulée

**Workflow:**
```
Joueur 2 crée demande
    ↓ (status: pending)
Créateur voit la demande
    ↓
Créateur accepte
    ↓ (status: accepted)
Match passe en in_progress
    ↓
Autres demandes deviennent rejected
```

### Workflow Réel d'un Match Joueur

```
1. CRÉATION (status: open)
   - Joueur 1 crée PlayerMatch
   - Définit: type, points, city, department, faction, détachement
   - Définit: disponibilité (date fixe ou période)
   - Statut: open
   - is_setup_complete: false
   - is_setup_validated: false

2. DEMANDES DE PARTICIPATION (PARALLÈLE À LA CONFIGURATION)
   ⚠️ LES DEMANDES PEUVENT ARRIVER IMMÉDIATEMENT APRÈS LA CRÉATION
   - Joueur 2 crée PlayerMatchRequest
   - Propose: faction, détachement
   - Statut: pending
   - ⚠️ AUCUNE VÉRIFICATION QUE LE MATCH SOIT CONFIGURÉ

   - Joueur 3 crée PlayerMatchRequest
   - Propose: faction, détachement
   - Statut: pending

3. CONFIGURATION (OPTIONNEL AVANT ACCEPTATION)
   - Joueur 1 configure le match (à tout moment)
   - Choisit missions: primaire, secondaire, terrain, péripétie
   - Choisit mode de déploiement
   - Choisit mode de setup
   - Marque is_setup_complete = true
   - Valide is_setup_validated = true
   - Statut: toujours open

4. ACCEPTATION D'UNE DEMANDE
   - Joueur 1 accepte demande de Joueur 2
   - PlayerMatchRequest.status = accepted
   - opponent_id = 2 (défini)
   - Statut match: open → confirmed
   - Demande de Joueur 3: rejected

5. MATCH EN COURS (status: confirmed)
   - Joueurs 1 et 2 jouent
   - Saisissent les scores
   - creator_score et opponent_score

6. CLÔTURE (status: completed)
   - winner_id déterminé
   - is_draw défini si égalité
   - played_at enregistré
```

---

## 📊 Comparaison Tournoi vs Match Joueur

| Aspect | Tournoi | Match Joueur |
|--------|---------|--------------|
| Créateur | Organisateur | Joueur |
| Nombre de joueurs | Plusieurs | 2 |
| Appairage | Automatique | Manuel |
| Missions | Assignées | Libres |
| Scoring | Centralisé | Décentralisé |
| Durée | Plusieurs rounds | 1 match |
| Classement | Oui | Non |

---

## 🎮 Gestion des Scores

### Scoring dans un Match de Tournoi

```
player1_score: 0-120 points
player2_score: 0-120 points

Calcul du gagnant:
- Si player1_score > player2_score → winner_id = player1_id
- Si player2_score > player1_score → winner_id = player2_id
- Si égalité → winner_id = NULL
```

### Composition des Points

**Points de Victoire (VP):**
- Mission Primaire: 0-15 VP
- Mission Secondaire: 0-15 VP
- Péripétie: 0-15 VP
- Bonus: 0-75 VP

**Total possible: 120 VP**

---

## 👤 Gestion des Utilisateurs

### Table: `users`

```sql
id | name | email | password | role | faction_id | detachment_id |
created_at | updated_at
```

**Rôles réels:**
- `admin` - Administrateur
- `user` - Utilisateur standard
- `tournament_organizer` - Organisateur

**Relations:**
```php
User::with([
    'faction',                    // Faction préférée
    'detachment',                 // Détachement préféré
    'tournamentsOrganized',       // Tournois créés
    'tournamentMatches',          // Matchs de tournoi
    'playerMatches',              // Matchs joueurs créés
    'playerMatchRequests',        // Demandes de matchs
    'availabilities'              // Disponibilités
])
```

### Workflow d'Inscription

```
1. Utilisateur s'inscrit
   - Email, mot de passe
   - Nom

2. Complète le profil
   - Faction préférée
   - Détachement préféré

3. Peut créer/rejoindre tournois
4. Peut créer/rejoindre matchs joueurs
5. Peut saisir les scores
```

---

## 📅 Gestion des Disponibilités

### Table: `player_availabilities`

```sql
id | player_id | player_match_id | day | time_slot | available |
created_at | updated_at
```

**Utilisation:**
- Joueurs indiquent leur disponibilité
- Créateur du match voit les disponibilités
- Aide à planifier les matchs

---

## 🔐 Permissions et Sécurité

### Permissions Tournoi

| Action | Organisateur | Joueur |
|--------|--------------|--------|
| Créer tournoi | ✅ | ❌ |
| Modifier tournoi | ✅ (propre) | ❌ |
| S'inscrire | ❌ | ✅ |
| Saisir scores | ✅ | ✅ |
| Valider scores | ✅ | ❌ |
| Voir classement | ✅ | ✅ |

### Permissions Match Joueur

| Action | Créateur | Demandeur | Autre |
|--------|----------|-----------|-------|
| Créer match | ✅ | ❌ | ❌ |
| Demander participation | ❌ | ✅ | ✅ |
| Accepter/Rejeter | ✅ | ❌ | ❌ |
| Modifier match | ✅ (avant) | ❌ | ❌ |
| Saisir scores | ✅ | ✅ | ❌ |

---

## 📞 Commandes Artisan

```bash
# Créer un tournoi (via Filament)
# Admin → Tournois → Créer

# Générer appairements
# (Automatique via Filament)

# Voir les matchs
php artisan tinker
>>> TournamentMatch::with('player1', 'player2', 'primaryMission')->get()

# Voir les demandes de matchs
>>> PlayerMatchRequest::with('playerMatch', 'player')->get()
```

---

## 🚨 Cas d'Usage Réels

### Cas 1 : Créer et Lancer un Tournoi

```
1. Admin crée tournoi
   - Nom: "Tournoi Régional 2025"
   - Max joueurs: 8
   - Statut: draft

2. Ouvre inscriptions
   - Statut: registration_open

3. Joueurs s'inscrivent
   - 8 joueurs inscrits

4. Ferme inscriptions
   - Statut: registration_closed

5. Génère appairements Round 1
   - Crée 4 TournamentMatch
   - Assigne missions
   - Statut: in_progress

6. Joueurs jouent et saisissent scores

7. Génère appairements Round 2
   - Basé sur les scores
   - Crée 4 nouveaux TournamentMatch

8. Répète jusqu'à fin

9. Clôt tournoi
   - Statut: completed
   - Affiche classement final
```

### Cas 2 : Créer un Match Joueur

```
1. Joueur 1 crée match
   - Type: competitive
   - Points: 2000
   - Ville: Paris
   - Département: 75
   - Faction: Necrons
   - Détachement: Szarekhan Dynasty
   - Disponibilité: Date fixe (15/11/2025 19:00)
   - Statut: open

2. Joueur 1 configure le match
   - Choisit mission primaire: LINCHPIN
   - Choisit mission secondaire: BREAK THROUGH
   - Choisit terrain: Urban
   - Choisit péripétie: AMBUSH
   - Choisit mode de déploiement: Strike Force
   - Choisit mode de setup: Standard
   - Marque setup complet et validé
   - Statut: confirmed

3. Joueur 2 demande participation
   - Propose: Astra Militarum, Cadian

4. Joueur 3 demande participation
   - Propose: Space Marines, Ultramarines

5. Joueur 1 accepte demande de Joueur 2
   - opponent_id = 2
   - Demande de Joueur 3: rejected

6. Joueurs 1 et 2 jouent le match

7. Saisissent les scores
   - creator_score: 85
   - opponent_score: 72

8. Match terminé
   - winner_id: 1 (Joueur 1)
   - is_draw: false
   - played_at: 15/11/2025 19:30
   - Statut: completed
```

---

**Dernière mise à jour** : 3 novembre 2025
**Version** : 2.0 (Réelle)
**Statut** : ✅ Fonctionnel

