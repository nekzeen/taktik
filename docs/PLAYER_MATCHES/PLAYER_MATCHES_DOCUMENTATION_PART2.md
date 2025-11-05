# 📖 DOCUMENTATION COMPLÈTE - MATCHS SIMPLES (PART 2/2)

**Version**: 1.0 | **Date**: 2025-11-05 | **Statut**: ✅ Documentation de référence

---

## 📋 TABLE DES MATIÈRES

1. Services
2. Vues et interfaces
3. Configuration du match
4. Scoring et résultats
5. Checklist de correction
6. Erreurs courantes

---

## 🔧 SERVICES

### MatchSetupService

**Fichier**: `app/Services/MatchSetupService.php`

**Méthode: getAvailableOptions()**
```php
public function getAvailableOptions($match): array
{
    return [
        'primary_missions' => PrimaryMission::all(),
        'secondary_missions' => SecondaryMission::all(),
        'terrain_layouts' => TerrainLayout::all(),
        'twist_missions' => TwistMission::all(),
        'asymmetric_primary_missions' => AsymmetricPrimaryMission::all(),
    ];
}
```

**Méthode: randomizeMatch()**
```php
public function randomizeMatch($match, string $mode = 'normal'): void
{
    if ($mode === 'asymmetric') {
        // Mode asymétrique
        $match->asymmetric_primary_mission_id = AsymmetricPrimaryMission::inRandomOrder()->first()->id;
        $match->deployment_mode = ['Hammer and Anvil', 'Dawn of War', 'Incursion'][array_rand([...])];
    } else {
        // Mode normal - utilise le pool de missions
        $randomPool = TournamentMissionPool::where('is_active', true)->inRandomOrder()->first();
        if ($randomPool) {
            $match->primary_mission_id = $randomPool->primary_mission_id;
            $match->deployment_mode = $randomPool->deployment_mode;
            $match->terrain_layout_id = $randomPool->availableTerrainLayouts()->inRandomOrder()->first()->id;
        }
    }
    
    $match->twist_mission_id = TwistMission::inRandomOrder()->first()->id;
    $match->setup_mode = 'random';
    $match->is_setup_complete = true;
    $match->save();
}
```

### PlayerMatchService

**Fichier**: `app/Services/PlayerMatchService.php`

**Méthode: canJoin()**
```php
public function canJoin(PlayerMatch $match, User $user): bool
{
    return $match->status === 'open'
        && $match->opponent_id === null
        && $match->creator_id !== $user->id
        && $match->isAvailable()
        && $match->is_setup_validated;
}
```

**Méthode: canSetScore()**
```php
public function canSetScore(PlayerMatch $match, User $user): bool
{
    return $match->status === 'confirmed'
        && $match->creator_id === $user->id;
}
```

**Méthode: getStatusLabel()**
```php
public function getStatusLabel(PlayerMatch $match): string
{
    if ($match->status === 'open' && !$match->is_setup_validated) {
        return 'Configuration en cours';
    }
    
    return match($match->status) {
        'open' => 'Ouvert',
        'confirmed' => 'Confirmé',
        'completed' => 'Terminé',
        'cancelled' => 'Annulé',
        default => 'Inconnu',
    };
}
```

### MatchPermissionService

**Fichier**: `app/Services/MatchPermissionService.php`

Gère les permissions pour les actions sur les matchs.

---

## 🎨 VUES ET INTERFACES

### Vues principales

| Vue | Fichier | Description |
|-----|---------|-------------|
| Index | `resources/views/player-matches/index.blade.php` | Liste des matchs |
| Show | `resources/views/player-matches/show.blade.php` | Détails d'un match |
| Setup | `resources/views/matches/setup.blade.php` | Configuration |
| Summary | `resources/views/matches/summary.blade.php` | Résumé |
| Score | `resources/views/matches/score.blade.php` | Saisie des scores |

### setup.blade.php - Points clés

**Tirage aléatoire**:
- Bouton "Mode Normal" (utilise pool)
- Bouton "Asymétrique" (missions asymétriques)

**Configuration manuelle**:
- Sélection mission primaire
- Sélection terrain
- Sélection mode de déploiement (8 modes)
- Sélection péripétie

**Modes de déploiement disponibles**:
1. Hammer and Anvil
2. Dawn of War
3. Incursion
4. Pitched Battle
5. Tipping Point
6. Search and Destroy
7. Crucible of Battle
8. Sweeping Engagement

### summary.blade.php - Points clés

**Images**:
- Utiliser `asset('storage/' . $image_path)` (pas `$image_url`)
- Ajouter `rounded-lg` pour arrondir

**Onglets langue**:
- Anglais: `bg-gray-50` + `border-gray-200`
- Français: `bg-blue-50` + `border-blue-200`

**Pas de défilement**:
- Supprimer `max-h-96 overflow-y-auto`
- Laisser le texte entièrement visible

### score.blade.php - Points clés

**Champs de saisie**:
- Score créateur
- Score adversaire
- VP créateur
- VP adversaire
- État tactique (JSON)

**Sauvegarde progressive**:
- Scores sauvegardés dans `draft_scores`
- Adversaire voit les scores en temps réel

---

## ⚙️ CONFIGURATION DU MATCH

### Mode aléatoire (random)

**Processus**:
1. Sélectionner un pool de missions aléatoire actif
2. Récupérer la mission primaire du pool
3. Sélectionner un terrain aléatoire du pool
4. Utiliser le mode de déploiement du pool
5. Sélectionner une péripétie aléatoire

**Résultat**:
```
setup_mode: 'random'
is_setup_complete: true
primary_mission_id: <id>
terrain_layout_id: <id>
deployment_mode: 'Hammer and Anvil'
twist_mission_id: <id>
```

### Mode manuel (manual)

**Processus**:
1. Sélectionner manuellement une mission primaire
2. Sélectionner manuellement un terrain
3. Sélectionner manuellement un mode de déploiement
4. Sélectionner manuellement une péripétie

**Résultat**:
```
setup_mode: 'manual'
is_setup_complete: true
primary_mission_id: <id>
terrain_layout_id: <id>
deployment_mode: 'Tipping Point'
twist_mission_id: <id>
```

### Pools de missions

**20 pools disponibles** (A-T):
- **Pool A-D**: Tipping Point (6 terrains: 1,2,4,6,7,8)
- **Pool E-H**: Hammer and Anvil (3 terrains: 1,7,8)
- **Pool I-L**: Search and Destroy (5 terrains: 1,2,3,4,6)
- **Pool M-P**: Crucible of Battle (5 terrains: 1,2,4,6,8)
- **Pool Q-R**: Sweeping Engagement (2 terrains: 3,5)
- **Pool S-T**: Dawn of War (1 terrain: 5)

---

## 📊 SCORING ET RÉSULTATS

### Saisie des scores

**Champs**:
- `creator_score` - Score du créateur (0-100)
- `opponent_score` - Score de l'adversaire (0-100)
- `creator_victory_points` - VP du créateur
- `opponent_victory_points` - VP de l'adversaire

**Brouillons**:
- `draft_scores` - JSON avec scores en cours
- `draft_tactical_state_creator` - État tactique créateur
- `draft_tactical_state_opponent` - État tactique adversaire

### Finalisation

**Conditions**:
- Status = 'confirmed'
- Scores saisis

**Résultat**:
```
Status: 'completed'
winner_id: <id du gagnant ou NULL>
is_draw: true/false
played_at: <timestamp actuel>
```

**Détermination du gagnant**:
- Si `creator_score > opponent_score`: créateur gagne
- Si `opponent_score > creator_score`: adversaire gagne
- Si `creator_score == opponent_score`: match nul

---

## ✅ CHECKLIST DE CORRECTION

### Si tout est cassé

**Étape 1: Vérifier la base de données**
- [ ] Table `player_matches` existe
- [ ] Colonnes `creator_id`, `opponent_id` existent
- [ ] Colonne `status` existe
- [ ] Colonne `is_setup_validated` existe
- [ ] Colonne `deployment_mode` existe
- [ ] Colonnes `draft_scores`, `draft_tactical_state_*` existent
- [ ] Colonnes `primary_mission_id`, `terrain_layout_id` existent
- [ ] Colonnes `twist_mission_id`, `asymmetric_primary_mission_id` existent

**Étape 2: Vérifier les modèles**
- [ ] `app/Models/PlayerMatch.php` existe
- [ ] Relations définies (creator, opponent, missions, etc.)
- [ ] Attributs fillable corrects
- [ ] Casts corrects

**Étape 3: Vérifier les routes**
- [ ] Routes player-matches définies
- [ ] Routes setup définies
- [ ] Routes score définies
- [ ] Routes requests définies

**Étape 4: Vérifier les contrôleurs**
- [ ] `app/Http/Controllers/PlayerMatchController.php` existe
- [ ] `app/Http/Controllers/MatchSetupController.php` existe
- [ ] `app/Http/Controllers/PlayerMatchRequestController.php` existe
- [ ] Méthodes principales existent

**Étape 5: Vérifier les vues**
- [ ] `resources/views/player-matches/index.blade.php` existe
- [ ] `resources/views/player-matches/show.blade.php` existe
- [ ] `resources/views/matches/setup.blade.php` existe
- [ ] `resources/views/matches/summary.blade.php` existe
- [ ] `resources/views/matches/score.blade.php` existe

**Étape 6: Vérifier les services**
- [ ] `app/Services/MatchSetupService.php` existe
- [ ] `app/Services/PlayerMatchService.php` existe
- [ ] `app/Services/MatchPermissionService.php` existe
- [ ] Méthodes principales existent

**Étape 7: Vérifier la logique métier**
- [ ] Statuts corrects (open, confirmed, completed)
- [ ] Permissions correctes
- [ ] Configuration validée avant participation
- [ ] Scores sauvegardés progressivement

**Étape 8: Tester les flux**
- [ ] Créer un match
- [ ] Configurer le match (manuel)
- [ ] Valider la configuration
- [ ] Demander à rejoindre
- [ ] Accepter la demande
- [ ] Saisir les scores
- [ ] Finaliser le match

---

## 🐛 ERREURS COURANTES ET SOLUTIONS

| Erreur | Cause | Solution |
|--------|-------|----------|
| "Column not found: deployment_mode" | Colonne manquante | Ajouter colonne `deployment_mode` |
| "Undefined variable: deploymentModes" | Variable non passée au contrôleur | Ajouter `$deploymentModes` au contrôleur |
| Images n'apparaissent pas | URLs incorrectes | Utiliser `asset('storage/' . $image_path)` |
| Défilement dans les cadres texte | Classes `overflow-y-auto` | Supprimer `max-h-96 overflow-y-auto` |
| Texte français même couleur | Pas de distinction | Utiliser `bg-blue-50` pour français |
| Match ne peut pas être rejoint | Configuration non validée | Vérifier `is_setup_validated` |
| Scores ne s'affichent pas | Permissions incorrectes | Vérifier `creator_id === auth()->id()` |
| Pool de missions vide | Pools non importés | Exécuter `php artisan pools:import` |
| Mode de déploiement vide | Variable non passée | Ajouter `$deploymentModes` au contrôleur |

---

## 📁 FICHIERS IMPORTANTS

### Modèles
- `app/Models/PlayerMatch.php`
- `app/Models/PlayerMatchRequest.php`
- `app/Models/TournamentMissionPool.php`
- `app/Models/PrimaryMission.php`
- `app/Models/TerrainLayout.php`
- `app/Models/TwistMission.php`

### Contrôleurs
- `app/Http/Controllers/PlayerMatchController.php`
- `app/Http/Controllers/MatchSetupController.php`
- `app/Http/Controllers/PlayerMatchRequestController.php`

### Services
- `app/Services/MatchSetupService.php`
- `app/Services/PlayerMatchService.php`
- `app/Services/MatchPermissionService.php`

### Vues
- `resources/views/player-matches/index.blade.php`
- `resources/views/player-matches/show.blade.php`
- `resources/views/matches/setup.blade.php`
- `resources/views/matches/summary.blade.php`
- `resources/views/matches/score.blade.php`

### Routes
- `routes/web.php` (section player-matches)

### Migrations
- `database/migrations/*_create_player_matches_table.php`
- `database/migrations/*_create_player_match_requests_table.php`

---

## 🔍 COMMANDES UTILES

```bash
# Importer les pools de missions
php artisan pools:import

# Tester le système complet
php artisan test:complete-system

# Tester les flux de match
php artisan test:complete-match-flow

# Tester les interfaces
php artisan test:match-interfaces

# Tester les routes
php artisan test:match-routes

# Vider le cache
php artisan cache:clear
```

---

## 📞 SUPPORT RAPIDE

### Problème: Match ne s'affiche pas
1. Vérifier que l'utilisateur est créateur ou adversaire
2. Vérifier que le match existe en base de données
3. Vérifier les permissions dans `MatchPermissionService`

### Problème: Configuration ne peut pas être validée
1. Vérifier que `is_setup_complete` = true
2. Vérifier que `primary_mission_id` est défini
3. Vérifier que `terrain_layout_id` est défini
4. Vérifier que `twist_mission_id` est défini
5. Vérifier que `deployment_mode` est défini

### Problème: Scores ne s'affichent pas
1. Vérifier que status = 'confirmed'
2. Vérifier que `opponent_id` ≠ NULL
3. Vérifier que `creator_score` et `opponent_score` sont définis
4. Vérifier les permissions (créateur peut saisir, adversaire peut voir)

### Problème: Match ne peut pas être rejoint
1. Vérifier que status = 'open'
2. Vérifier que `is_setup_validated` = true
3. Vérifier que `opponent_id` = NULL
4. Vérifier que le match est disponible (date pas expirée)
5. Vérifier que l'utilisateur n'est pas le créateur

---

**← Voir PART 1 pour: Vue d'ensemble, Structure, Flux, Logique métier, Modèles**
