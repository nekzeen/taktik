# Guide - Système de Matchs entre Joueurs

## Vue d'ensemble

Le système de matchs entre joueurs permet aux utilisateurs de proposer des matchs amicaux ou compétitifs et à d'autres joueurs de s'y inscrire.

## Fonctionnalités

### Pour le créateur du match

1. **Proposer un match** (`/player-matches/create`)
   - Type : Compétitif ou Narratif
   - Points d'armée : 500 à 5000 points
   - Faction et détachement (optionnel)
   - Localisation : Ville et département
   - Disponibilité : Date fixe ou période
   - Commentaire optionnel

2. **Gérer ses propositions**
   - Voir tous ses matchs proposés
   - Modifier un match (avant qu'un adversaire ne s'inscrive)
   - Annuler un match
   - Supprimer un match

3. **Enregistrer le score**
   - Une fois un adversaire trouvé et le match confirmé
   - Enregistrer le score des deux joueurs
   - Le système détermine automatiquement le gagnant

### Pour les autres joueurs

1. **Voir les matchs disponibles** (`/player-matches`)
   - Liste des matchs ouverts
   - Filtrage par localisation, type, points
   - Affichage de la disponibilité

2. **Rejoindre un match**
   - Cliquer sur "Rejoindre ce match"
   - Le match passe en "Confirmé"
   - Les deux joueurs peuvent maintenant enregistrer le score

## États du match

- **Ouvert** : En attente d'un adversaire
- **Confirmé** : Deux joueurs inscrits, en attente du score
- **Terminé** : Score enregistré
- **Annulé** : Annulé par le créateur

## Disponibilité

### Date fixe
- Spécifier une date et heure précise
- Le match expire après cette date/heure

### Période
- Spécifier une plage horaire (du/au)
- Le match expire après la date de fin

## Nettoyage automatique

Les matchs expirés sans adversaire sont supprimés automatiquement via la commande :

```bash
php artisan player-matches:cleanup
```

À planifier dans le cron :
```
0 * * * * cd /path/to/app && php artisan player-matches:cleanup >> /dev/null 2>&1
```

## Routes

### Public (authentifiés)
- `GET /player-matches` - Liste des matchs
- `GET /player-matches/create` - Formulaire de création
- `POST /player-matches` - Créer un match
- `GET /player-matches/{playerMatch}` - Détail du match
- `GET /player-matches/{playerMatch}/edit` - Modifier un match
- `PUT /player-matches/{playerMatch}` - Enregistrer les modifications
- `POST /player-matches/{playerMatch}/join` - Rejoindre un match
- `POST /player-matches/{playerMatch}/set-score` - Enregistrer le score
- `POST /player-matches/{playerMatch}/cancel` - Annuler un match
- `DELETE /player-matches/{playerMatch}` - Supprimer un match

## Modèle de données

### Table `player_matches`

| Colonne | Type | Description |
|---------|------|-------------|
| id | ID | Identifiant unique |
| creator_id | FK | Créateur du match |
| opponent_id | FK | Adversaire (null si ouvert) |
| type | enum | 'competitive' ou 'narrative' |
| army_points | int | Points d'armée |
| faction | string | Faction jouée |
| detachment | string | Détachement joué |
| notes | text | Commentaire |
| city | string | Ville |
| department | string | Département |
| availability_type | enum | 'single' ou 'period' |
| available_at | datetime | Pour date fixe |
| available_from | datetime | Début de période |
| available_to | datetime | Fin de période |
| status | enum | 'open', 'confirmed', 'completed', 'cancelled' |
| creator_score | int | Score du créateur |
| opponent_score | int | Score de l'adversaire |
| winner_id | FK | Gagnant (null si nul) |
| is_draw | boolean | Match nul |
| played_at | datetime | Date du match |
| created_at | timestamp | Création |
| updated_at | timestamp | Modification |
| deleted_at | timestamp | Suppression (soft delete) |

## Scopes disponibles

```php
// Matchs ouverts
PlayerMatch::open()->get();

// Matchs confirmés
PlayerMatch::confirmed()->get();

// Matchs terminés
PlayerMatch::completed()->get();

// Matchs actifs (non expirés)
PlayerMatch::active()->get();
```

## Méthodes utiles

```php
$match = PlayerMatch::find(1);

// Vérifier si le match est toujours disponible
$match->isAvailable();

// Affichage formaté de la disponibilité
$match->getAvailabilityDisplay(); // "22/10/2025 19:00" ou "22/10-23/10"

// Affichage de la localisation
$match->getLocationDisplay(); // "Paris (75)"

// Vérifier si un joueur peut rejoindre
$match->canJoin($user);

// Vérifier si un joueur peut enregistrer le score
$match->canSetScore($user);

// Déterminer le gagnant
$match->determineWinner();

// Labels localisés
$match->getTypeLabel(); // "Compétitif" ou "Narratif"
$match->getStatusLabel(); // "Ouvert", "Confirmé", etc.
```

## Sécurité

- Seul le créateur peut modifier un match ouvert
- Seul le créateur peut supprimer un match
- Seul le créateur ou l'adversaire peut enregistrer le score
- Un joueur ne peut pas rejoindre son propre match
- Un joueur ne peut pas rejoindre un match expiré

## Intégration avec les classements

Les matchs terminés sont inclus dans le calcul des classements globaux :
- Victoire = 3 points
- Nul = 1 point
- Défaite = 0 point
