# Implémentation de la Gestion des Matchs de Tournoi

## Vue d'ensemble

Cette documentation décrit l'implémentation complète du système de gestion des matchs de tournoi avec configuration automatique, sélection du joueur qui saisit le score, et pages de saisie/visualisation des scores.

## Objectifs réalisés

1. ✅ Configuration automatique des matchs via un pool de missions
2. ✅ Impossibilité de modifier un match une fois configuré
3. ✅ Sélection du joueur qui saisit le score (par les joueurs ou le créateur/admin)
4. ✅ Pages de saisie et visualisation des scores identiques aux matchs simples
5. ✅ Affichage de la configuration complète du match (non modifiable)

## Architecture

### Base de données

#### Table `tournament_matches`

Colonnes ajoutées :
- `score_recorder_id` (FK vers `users`, nullable) - Joueur qui saisit le score
- `score_recorder_selected_at` (timestamp, nullable) - Date de sélection du joueur
- `terrain_layout_id` (FK vers `terrain_layouts`, nullable) - Disposition du terrain
- `twist_mission_id` (FK vers `twist_missions`, nullable) - Péripétie
- `deployment_mode` (string, nullable) - Zone de déploiement
- `setup_mode` (string, nullable) - Mode de configuration (ex: 'random')
- `is_setup_complete` (boolean, default: false) - Indicateur de configuration complète

### Modèle `TournamentMatch`

#### Propriétés

```php
protected $fillable = [
    'tournament_id',
    'primary_mission_id',
    'terrain_layout_id',
    'twist_mission_id',
    'deployment_mode',
    'setup_mode',
    'score_recorder_id',
    'score_recorder_selected_at',
    // ... autres champs
];

protected $casts = [
    'is_setup_complete' => 'boolean',
    'score_recorder_selected_at' => 'datetime',
    // ... autres casts
];
```

#### Relations

```php
public function scoreRecorder(): BelongsTo
public function primaryMission(): BelongsTo
public function terrainLayout(): BelongsTo
public function twistMission(): BelongsTo
```

#### Méthodes principales

**`randomizeSetup(): void`**
- Sélectionne aléatoirement un pool de missions
- Configure automatiquement :
  - Mission primaire (du pool)
  - Terrain (aléatoire du pool)
  - Zone de déploiement (aléatoire des zones actives)
  - Péripétie (aléatoire des péripéties actives)
- Marque le match comme configuré

**`isSetupValid(): bool`**
- Vérifie que tous les champs obligatoires sont présents :
  - `primary_mission_id` ✓
  - `terrain_layout_id` ✓
  - `twist_mission_id` ✓

**`canSelectScoreRecorder(): bool`**
- Retourne `true` si :
  - Le match est configuré (`isSetupValid()`)
  - Aucun joueur n'a encore été sélectionné (`score_recorder_id` est null)

**`isScoreRecorderSelected(): bool`**
- Retourne `true` si `score_recorder_id` n'est pas null

**`isPlayer(User $user): bool`**
- Vérifie si l'utilisateur est l'un des deux joueurs du match

**`canEditResult(User $user): bool`**
- Vérifie si l'utilisateur peut saisir le score :
  - Doit être le joueur sélectionné OU
  - Doit être un joueur du match OU
  - Doit être le créateur du tournoi OU
  - Doit être super-admin

### Contrôleur `TournamentMatchController`

#### Méthodes

**`selectScoreRecorder(Tournament $tournament, TournamentMatch $match, Request $request)`**
- Permissions : Joueurs du match, créateur du tournoi, super-admin
- Validation : `score_recorder_id` doit être l'un des deux joueurs
- Action : Met à jour `score_recorder_id` et `score_recorder_selected_at`

**`scoreForm(Tournament $tournament, TournamentMatch $match)`**
- Affiche la page de saisie du score
- Permissions : Joueur sélectionné uniquement
- Charge les relations nécessaires

**`scoreView(Tournament $tournament, TournamentMatch $match)`**
- Affiche la page de visualisation du score
- Permissions : L'autre joueur uniquement
- Charge les relations nécessaires

**`storeScore(Tournament $tournament, TournamentMatch $match, Request $request)`**
- Enregistre le score du match
- Validation des points (0-50 primaires, 0-40 secondaires)
- Calcul automatique des totaux
- Détermination du gagnant
- Marque le match comme complété

**`determineWinnerFromSpecialResult(TournamentMatch $match, string $result): void`**
- Détermine le gagnant selon le résultat spécial (abandon, table rase, nul)

### Routes

```php
// Sélection du joueur qui saisit le score
POST /tournaments/{tournament}/matches/{match}/select-score-recorder
    -> TournamentMatchController@selectScoreRecorder
    -> Name: tournaments.matches.select-score-recorder

// Page de saisie du score
GET /tournaments/{tournament}/matches/{match}/score
    -> TournamentMatchController@scoreForm
    -> Name: tournaments.matches.score

// Page de visualisation du score
GET /tournaments/{tournament}/matches/{match}/view-score
    -> TournamentMatchController@scoreView
    -> Name: tournaments.matches.view-score

// Enregistrement du score
POST /tournaments/{tournament}/matches/{match}/store-score
    -> TournamentMatchController@storeScore
    -> Name: tournaments.matches.store-score

// Résumé de la configuration
GET /tournaments/{tournament}/matches/{match}/summary
    -> MatchSetupController@showTournamentSummary
    -> Name: tournaments.matches.summary
```

### Vues

#### `tournaments/matches/index.blade.php`

Affiche la liste des matchs du tournoi avec les boutons d'actions contextuels :

**Flux d'affichage des boutons :**

1. **Si match configuré et pas de joueur sélectionné** → Bouton "Voir config"
   - Affiche la page de résumé de la configuration
   - Accessible à tous les utilisateurs authentifiés

2. **Si match peut avoir un joueur sélectionné et utilisateur est joueur** → Bouton "Sélectionner joueur"
   - Ouvre une modale pour choisir qui saisit le score
   - Accessible aux deux joueurs du match

3. **Si joueur sélectionné et utilisateur est le joueur sélectionné** → Bouton "Saisir le score"
   - Affiche la page de saisie du score
   - Accessible uniquement au joueur sélectionné

4. **Si joueur sélectionné et utilisateur est l'autre joueur** → Bouton "Visualiser score"
   - Affiche la page de visualisation du score
   - Accessible uniquement à l'autre joueur

#### `tournaments/matches/score.blade.php`

Page de saisie du score (identique aux matchs simples) :
- Affichage des missions primaires et secondaires
- Saisie des points (primaires, secondaires, peinture)
- Calcul automatique des totaux
- Gestion des missions secondaires (fixes ou tactiques)
- Sauvegarde automatique en localStorage

#### `tournaments/matches/view-score.blade.php`

Page de visualisation du score (identique aux matchs simples) :
- Affichage en temps réel des scores
- Affichage des missions et péripéties
- Visualisation des points de chaque joueur

#### `matches/summary.blade.php`

Page de résumé de la configuration :
- Affichage de la zone de déploiement avec image
- Affichage de la disposition du terrain avec image
- Affichage de la mission primaire (EN/FR)
- Affichage de la péripétie (EN/FR)
- **Pour matchs de tournoi** : Pas de bouton "Modifier la configuration"
- **Pour matchs simples** : Bouton "Modifier la configuration" et "Valider"

## Flux utilisateur

### 1. Génération des matchs

```
Créateur/Admin génère les matchs
    ↓
Service TournamentMatchGenerator crée les matchs
    ↓
Chaque match appelle randomizeSetup()
    ↓
Configuration aléatoire appliquée :
    - Mission primaire (du pool)
    - Terrain (du pool)
    - Zone de déploiement (aléatoire)
    - Péripétie (aléatoire)
```

### 2. Sélection du joueur

```
Page des matchs du tournoi
    ↓
Joueur clique sur "Voir config" (optionnel)
    ↓
Joueur clique sur "Sélectionner joueur"
    ↓
Modale affiche les deux joueurs
    ↓
Joueur sélectionne qui saisit le score
    ↓
Confirmation et rechargement de la page
```

### 3. Saisie du score

```
Joueur sélectionné voit "Saisir le score"
    ↓
Clique sur le bouton
    ↓
Page de saisie du score (identique aux matchs simples)
    ↓
Saisie des points et missions secondaires
    ↓
Clique sur "Fin du match"
    ↓
Score enregistré et match marqué comme complété
```

### 4. Visualisation du score

```
L'autre joueur voit "Visualiser score"
    ↓
Clique sur le bouton
    ↓
Page de visualisation du score (identique aux matchs simples)
    ↓
Affichage en temps réel des scores
```

## Sécurité et Permissions

### Sélection du joueur
- ✅ Joueurs du match
- ✅ Créateur du tournoi
- ✅ Super-admin

### Saisie du score
- ✅ Uniquement le joueur sélectionné

### Visualisation du score
- ✅ L'autre joueur du match

### Modification de la configuration
- ❌ **Impossible** - Les matchs de tournoi ne sont pas modifiables après génération

## Modifications apportées au service

### `TournamentMatchGenerator`

La méthode `generate()` et `generateWithoutDeletingCompleted()` appellent maintenant `randomizeSetup()` sur chaque match généré pour configurer automatiquement les matchs.

```php
foreach ($matches as $match) {
    $match->randomizeSetup();
}
```

## Différences avec les matchs simples

| Aspect | Matchs simples | Matchs tournoi |
|--------|---|---|
| Configuration | Modifiable | Non modifiable |
| Zone de déploiement | Optionnelle | Obligatoire (aléatoire) |
| Sélection du joueur | N/A | Requise avant saisie |
| Pages de saisie/visualisation | Identiques | Identiques |
| Permissions | Créateur uniquement | Joueurs + créateur + admin |

## Fichiers modifiés

### Migrations
- `2025_11_05_213133_add_score_recorder_to_tournament_matches_table.php`
- `2025_11_05_215512_add_mission_configuration_to_tournament_matches_table.php`

### Modèles
- `app/Models/TournamentMatch.php`

### Contrôleurs
- `app/Http/Controllers/TournamentMatchController.php`
- `app/Http/Controllers/MatchSetupController.php`

### Services
- `app/Services/TournamentMatchGenerator.php`

### Routes
- `routes/web.php`

### Vues
- `resources/views/tournaments/matches/index.blade.php`
- `resources/views/tournaments/matches/score.blade.php`
- `resources/views/tournaments/matches/view-score.blade.php`
- `resources/views/matches/summary.blade.php`

## Notes importantes

1. **Configuration automatique** : Les matchs sont configurés automatiquement lors de la génération
2. **Non modifiable** : Les matchs de tournoi ne peuvent pas être modifiés après génération
3. **Sélection obligatoire** : Un joueur doit être sélectionné avant de saisir le score
4. **Permissions flexibles** : Les joueurs peuvent sélectionner qui saisit le score
5. **Pas d'emojis** : Tous les emojis ont été supprimés des boutons et textes

## Vérification

Pour vérifier le bon fonctionnement :

1. Générer des matchs de tournoi
2. Vérifier que chaque match a une configuration complète (mission, terrain, zone, péripétie)
3. Cliquer sur "Voir config" pour afficher la page de résumé
4. Sélectionner un joueur pour saisir le score
5. Saisir le score et vérifier l'enregistrement
6. Vérifier que l'autre joueur peut visualiser le score
