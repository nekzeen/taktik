# 🎮 SYSTÈME DE CONFIGURATION DES MATCHS - WARHAMMER 40K

## 📋 Vue d'ensemble

Système complet pour configurer les matchs de tournoi avec :
- ✅ **Tirage au sort aléatoire** : Mission primaire, disposition de terrain, péripétie
- ✅ **Mode manuel** : Sélection manuelle de tous les éléments
- ✅ **Support asymétrique** : Missions primaires asymétriques optionnelles
- ✅ **Interface intuitive** : Modal Livewire avec bouton sur la carte du match

---

## 🏗️ Architecture

### Base de Données

**Colonnes ajoutées à `tournament_matches` :**
```sql
- terrain_layout_id (FK) : Disposition de terrain sélectionnée
- twist_mission_id (FK) : Péripétie sélectionnée
- setup_mode (enum) : 'random' ou 'manual'
- asymmetric_primary_mission_id (FK) : Mission asymétrique (optionnel)
- is_setup_complete (boolean) : Tirage complété
```

### Modèles

**TournamentMatch.php**
- Relations : `terrainLayout()`, `twistMission()`, `asymmetricPrimaryMission()`
- Méthodes : `randomizeSetup()`, `isSetupValid()`

### Services

**MatchSetupService.php**
- `randomizeMatch()` : Tirage aléatoire complet
- `updateMatchSetup()` : Mise à jour manuelle
- `resetSetup()` : Réinitialisation
- `getAvailableOptions()` : Options disponibles

### Composants Livewire

**MatchSetupModal.php**
- Gestion de la modal
- Tirage aléatoire
- Mise à jour manuelle
- Réinitialisation

### Actions Filament

**SetupMatchAction.php**
- Action pour Filament (optionnel)
- Formulaire de configuration
- Notifications

---

## 🎯 Utilisation

### 1️⃣ Afficher le bouton sur la carte du match

Dans votre vue de carte du match, ajoutez :

```blade
<!-- Carte du match -->
<div class="match-card">
    <!-- ... contenu du match ... -->
    
    <!-- Bouton de configuration -->
    <livewire:match-setup-modal :match="$match" />
</div>
```

### 2️⃣ Tirage aléatoire

1. Cliquer sur le bouton "Configurer"
2. Activer "Tirage aléatoire"
3. Cliquer sur "🎲 Tirer au sort"
4. Les éléments sont générés aléatoirement :
   - Mission primaire (du pool du tournoi)
   - Disposition de terrain (parmi les 6 disponibles du pool)
   - Péripétie (aléatoire)

### 3️⃣ Mode manuel

1. Cliquer sur le bouton "Configurer"
2. Désactiver "Tirage aléatoire"
3. Sélectionner manuellement :
   - Mission primaire
   - Disposition de terrain
   - Péripétie
   - Mission asymétrique (optionnel)
4. Cliquer sur "✓ Enregistrer"

### 4️⃣ Réinitialiser

1. Cliquer sur le bouton "Configurer"
2. Cliquer sur "Réinitialiser"
3. Tous les éléments sont effacés

---

## 📊 Données

### Tirage aléatoire

**Mission primaire :**
- Provient du pool de missions du tournoi
- Défini lors de la création du tournoi

**Disposition de terrain :**
- Sélectionnée parmi les 6 terrains disponibles du pool
- Exemple : Pool 1 → Terrains [1, 2, 4, 6, 7, 8]

**Péripétie :**
- Sélectionnée aléatoirement parmi TOUTES les péripéties
- 10 péripéties disponibles

### Mode manuel

Tous les éléments peuvent être sélectionnés manuellement :
- Toutes les missions primaires
- Toutes les dispositions de terrain
- Toutes les péripéties
- Toutes les missions asymétriques (optionnel)

---

## 🔧 Intégration

### 1. Enregistrer le composant Livewire

Dans `config/livewire.php` ou automatiquement découvert :

```php
// Découverte automatique dans app/Livewire/
```

### 2. Publier les assets

```bash
php artisan livewire:publish
```

### 3. Inclure dans votre vue

```blade
<livewire:match-setup-modal :match="$match" />
```

### 4. Ajouter le script Livewire

```blade
@livewireScripts
```

---

## 📁 Fichiers créés

### Migrations
- `2025_10_31_180000_add_match_setup_to_tournament_matches.php`

### Modèles
- `app/Models/TournamentMatch.php` (modifié)

### Services
- `app/Services/MatchSetupService.php`

### Livewire
- `app/Livewire/MatchSetupModal.php`
- `resources/views/livewire/match-setup-modal.blade.php`

### Actions Filament
- `app/Filament/Actions/SetupMatchAction.php` (optionnel)

---

## 🎨 Interface

### Modal de configuration

```
┌─────────────────────────────────────────────┐
│ Configuration du match - Table 1            │ X
├─────────────────────────────────────────────┤
│                                             │
│ ☑ Tirage aléatoire                         │
│   Les éléments seront générés aléatoirement│
│                                             │
│ Éléments du match                           │
│ ┌─────────────────────────────────────────┐ │
│ │ Mission primaire                        │ │
│ │ [LINCHPIN                            ▼] │ │
│ │ Actuellement : LINCHPIN                 │ │
│ └─────────────────────────────────────────┘ │
│                                             │
│ ┌─────────────────────────────────────────┐ │
│ │ Disposition de terrain                  │ │
│ │ [Terrain Layout 1                    ▼] │ │
│ │ Actuellement : Terrain Layout 1         │ │
│ └─────────────────────────────────────────┘ │
│                                             │
│ ┌─────────────────────────────────────────┐ │
│ │ Péripétie                               │ │
│ │ [AMBUSH                              ▼] │ │
│ │ Actuellement : AMBUSH                   │ │
│ └─────────────────────────────────────────┘ │
│                                             │
│ ✅ Configuration complète - Mode : Aléatoire│
│                                             │
├─────────────────────────────────────────────┤
│ [Réinitialiser] [🎲 Tirer au sort] [Fermer]│
└─────────────────────────────────────────────┘
```

---

## 💾 Stockage des données

### Exemple : Match configuré

```php
$match = TournamentMatch::find(1);

// Tirage aléatoire
$match->primary_mission_id = 1;           // LINCHPIN
$match->terrain_layout_id = 2;            // Terrain Layout 2
$match->twist_mission_id = 3;             // AMBUSH
$match->setup_mode = 'random';
$match->is_setup_complete = true;

// Mode manuel
$match->primary_mission_id = 2;           // BURDEN OF TRUST
$match->terrain_layout_id = 4;            // Terrain Layout 4
$match->twist_mission_id = 5;             // BLOODLUST
$match->asymmetric_primary_mission_id = 1; // Mission asymétrique
$match->setup_mode = 'manual';
$match->is_setup_complete = true;
```

---

## 🔄 Workflow complet

### Avant le match

```
1. Créer le tournoi
   ↓
2. Ajouter le pool de missions
   ↓
3. Créer les matchs
   ↓
4. Configurer chaque match
   ├─ Tirage aléatoire (recommandé)
   └─ Mode manuel (si nécessaire)
   ↓
5. Afficher les détails du match
```

### Pendant le match

```
1. Afficher la mission primaire
2. Afficher la disposition de terrain
3. Afficher la péripétie
4. Jouer le match
5. Enregistrer les résultats
```

---

## 🧪 Tests

### Test du tirage aléatoire

```bash
# Créer un match
$match = TournamentMatch::create([
    'tournament_id' => 1,
    'round' => 1,
    'table_number' => 1,
    'player1_id' => 1,
    'player2_id' => 2,
]);

# Tirer au sort
$match->randomizeSetup();

# Vérifier
dd($match->primary_mission_id);      // ✅ Rempli
dd($match->terrain_layout_id);       // ✅ Rempli
dd($match->twist_mission_id);        // ✅ Rempli
dd($match->is_setup_complete);       // ✅ true
```

### Test du mode manuel

```bash
# Mise à jour manuelle
$service = new MatchSetupService();
$service->updateMatchSetup(
    $match,
    primaryMissionId: 2,
    terrainLayoutId: 4,
    twistMissionId: 5,
    asymmetricPrimaryMissionId: 1
);

# Vérifier
dd($match->setup_mode);              // ✅ 'manual'
dd($match->is_setup_complete);       // ✅ true
```

---

## 📝 Notes

- ✅ Tirage aléatoire respecte le pool de missions du tournoi
- ✅ Terrains disponibles limités aux 6 du pool
- ✅ Péripéties tirées aléatoirement parmi les 10 disponibles
- ✅ Mode manuel permet une sélection complète
- ✅ Missions asymétriques optionnelles
- ✅ Interface intuitive et responsive

---

## 🚀 Prochaines étapes

1. **Affichage des détails** : Afficher les détails du match configuré
2. **Feuille de score** : Intégrer le scoring en temps réel
3. **Historique** : Conserver l'historique des configurations
4. **Export** : Exporter les configurations en PDF/CSV
5. **Multi-langues** : Support des traductions

---

## 📞 Support

Pour toute question ou problème :
- Vérifier les logs Laravel : `storage/logs/laravel.log`
- Vérifier la console Livewire : Ouvrir les outils de développement
- Vérifier la base de données : Colonnes correctement migrées
