# 📋 Documentation Complète : Missions, Péripéties et Cartes de Déploiement

## Table des matières
1. [Architecture générale](#architecture-générale)
2. [Types de missions](#types-de-missions)
3. [Système de traductions](#système-de-traductions)
4. [Import de missions](#import-de-missions)
5. [Gestion des missions](#gestion-des-missions)
6. [Cartes de déploiement](#cartes-de-déploiement)
7. [Modèles de données](#modèles-de-données)
8. [API et endpoints](#api-et-endpoints)
9. [Troubleshooting](#troubleshooting)

---

## Architecture générale

### Vue d'ensemble du système

Le système de gestion des missions est organisé en 4 types principaux :

```
┌─────────────────────────────────────────────────────────────┐
│                    SYSTÈME DE MISSIONS                       │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌──────────────────┐  ┌──────────────────┐                 │
│  │ MISSIONS         │  │ MISSIONS         │                 │
│  │ PRIMAIRES        │  │ SECONDAIRES      │                 │
│  │ (Primary)        │  │ (Secondary)      │                 │
│  └──────────────────┘  └──────────────────┘                 │
│                                                               │
│  ┌──────────────────┐  ┌──────────────────┐                 │
│  │ MISSIONS         │  │ PÉRIPÉTIES       │                 │
│  │ ASYMÉTRIQUES     │  │ (Twist)          │                 │
│  │ (Asymmetric)     │  │                  │                 │
│  └──────────────────┘  └──────────────────┘                 │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

### Flux de données

```
IMPORT (Filament Admin)
    ↓
PARSING (Regex - Séparateur ---)
    ↓
CRÉATION/MISE À JOUR (Eloquent)
    ↓
TRADUCTIONS AUTOMATIQUES (DeepL)
    ↓
BASE DE DONNÉES
    ↓
AFFICHAGE (Frontend)
```

---

## Types de missions

### 1. Missions Primaires (Primary Missions)

**Modèle** : `App\Models\PrimaryMission`

**Colonnes principales** :
- `id` : Identifiant unique
- `name` : Nom de la mission (EN)
- `slug` : URL-friendly identifier
- `full_text` : Texte complet de la mission (EN)
- `edition` : Édition (ex: "10ed")
- `source` : Source (ex: "chapter-approved-2025-26")
- `is_active` : Statut actif/inactif
- `created_at`, `updated_at` : Timestamps

**Caractéristiques** :
- Missions de base du jeu
- Peuvent être jouées par les deux joueurs
- Contiennent les objectifs principaux
- Traductions disponibles en FR

**Exemple** :
```
Nom: SECURE OBJECTIVES
Description: Capture and hold key positions on the battlefield
Full Text: [Texte complet avec conditions de victoire]
```

---

### 2. Missions Secondaires (Secondary Missions)

**Modèle** : `App\Models\SecondaryMission`

**Colonnes principales** :
- `id` : Identifiant unique
- `name` : Nom de la mission (EN)
- `slug` : URL-friendly identifier
- `full_text` : Texte complet (EN)
- `can_be_fixed` : Peut être utilisée comme mission fixe (booléen)
- `edition` : Édition
- `source` : Source
- `is_active` : Statut actif/inactif
- `created_at`, `updated_at` : Timestamps

**Caractéristiques** :
- Missions optionnelles
- Peuvent être fixes ou tactiques
- Apportent des points de victoire supplémentaires
- **19 missions disponibles** (importées)

**Missions disponibles** :
1. BEHIND ENEMY LINES
2. STORM HOSTILE OBJECTIVE
3. ENGAGE ON ALL FRONTS
4. ESTABLISH LOCUS
5. CLEANSE
6. ASSASSINATION
7. NO PRISONERS
8. CULL THE HORDE
9. BRING IT DOWN
10. DEFEND STRONGHOLD
11. MARKED FOR DEATH
12. SECURE NO MAN'S LAND
13. SABOTAGE
14. AREA DENIAL
15. RECOVER ASSETS
16. A TEMPTING TARGET
17. EXTEND BATTLE LINES
18. OVERWHELMING FORCE
19. DISPLAY OF MIGHT

---

### 3. Missions Primaires Asymétriques (Asymmetric Primary Missions)

**Modèle** : `App\Models\AsymmetricPrimaryMission`

**Colonnes principales** :
- `id` : Identifiant unique
- `name` : Nom de la mission (EN)
- `slug` : URL-friendly identifier
- `full_text` : Texte complet (EN)
- `edition` : Édition
- `source` : Source
- `is_active` : Statut actif/inactif
- `created_at`, `updated_at` : Timestamps

**Caractéristiques** :
- Missions avec règles différentes pour Attaquant/Défenseur
- Conditions de victoire asymétriques
- Points de victoire différents selon le rôle
- **5 missions disponibles** (importées)

**Missions disponibles** :
1. SYPHONED POWER
2. ESTABLISH CONTROL
3. UNEVEN GROUND
4. DENIED RESOURCES
5. HOLD OUT

**Exemple de structure asymétrique** :
```
ATTACKER:
- Objectif: Contrôler les marqueurs
- VP: 5VP par marqueur contrôlé

DEFENDER:
- Objectif: Nier les marqueurs
- VP: 7VP par marqueur nié
```

---

### 4. Péripéties (Twist Missions)

**Modèle** : `App\Models\TwistMission`

**Colonnes principales** :
- `id` : Identifiant unique
- `name` : Nom de la péripétie (EN)
- `slug` : URL-friendly identifier
- `full_text` : Texte complet (EN)
- `edition` : Édition
- `source` : Source
- `is_active` : Statut actif/inactif
- `created_at`, `updated_at` : Timestamps

**Caractéristiques** :
- Événements aléatoires pendant la bataille
- Modifient les conditions de jeu
- Peuvent affecter les deux joueurs
- Ajoutent de la variabilité

---

## Système de traductions

### Architecture

```
┌─────────────────────────────────────────────────────┐
│            TABLE: translations                      │
├─────────────────────────────────────────────────────┤
│ id                                                  │
│ resource_type (PrimaryMission, SecondaryMission...) │
│ resource_id (ID de la mission)                      │
│ field (name, full_text, description...)             │
│ locale (fr, en, de...)                              │
│ source_text (Texte original EN)                     │
│ translated_text (Texte traduit FR)                  │
│ status (auto, manual, reviewed, approved)           │
│ created_at, updated_at                              │
└─────────────────────────────────────────────────────┘
```

### Champs traduits

**Par mission** :
- `name` : Nom de la mission
- `full_text` : Texte complet

**Statuts de traduction** :
- `auto` : Traduction automatique via DeepL
- `manual` : Traduction manuelle
- `reviewed` : Traduction révisée
- `approved` : Traduction approuvée
- `pending` : En attente

### Processus de traduction

```
1. CRÉATION DE LA MISSION
   ↓
2. EXTRACTION DES CHAMPS
   - name
   - full_text
   ↓
3. APPEL À DEEPL
   - Langue source: EN
   - Langue cible: FR
   ↓
4. CRÉATION DES ENREGISTREMENTS
   - Translation::create([...])
   ↓
5. STOCKAGE EN BASE
   - Statut: auto
   - Source text: Texte original
   - Translated text: Traduction FR
```

### Contraintes uniques

**Index unique** : `(resource_type, resource_id, field, locale)`

Cela signifie :
- ✅ Une seule traduction FR pour "name" de chaque mission
- ✅ Une seule traduction FR pour "full_text" de chaque mission
- ✅ Mise à jour automatique si doublon (via `updateOrCreate`)

---

## Import de missions

### Processus d'import

#### 1. Accès à l'interface d'import

**URL** : `/admin/[type]-missions/import`

Exemples :
- `/admin/secondary-missions/import`
- `/admin/primary-missions/import`
- `/admin/asymmetric-primary-missions/import`
- `/admin/twist-missions/import`

#### 2. Format du texte

**Séparateur** : `---` (3 tirets ou plus)

**Format accepté** :
```
MISSION NAME
Description or introduction text.

Details about the mission...

---

ANOTHER MISSION NAME
More details...

---
```

**Formats de séparateur acceptés** :
- `---`
- `----` (4 tirets ou plus)
- ` --- ` (avec espaces)
- `\n---\n` (avec retours à la ligne)

#### 3. Extraction du nom

Le parser utilise deux méthodes pour extraire le nom :

**Méthode 1** : Format `**Nom**`
```
**MISSION NAME**
Description...
```

**Méthode 2** : Première ligne non-vide
```
MISSION NAME
Description...
```

#### 4. Parsing du texte

**Regex utilisé** : `/\s*\n\s*-{3,}\s*\n\s*/`

**Étapes** :
1. Divise le texte par les séparateurs `---`
2. Extrait le nom de chaque bloc
3. Utilise le bloc entier comme `full_text`
4. Génère le slug automatiquement

#### 5. Création en base de données

```php
$mission = SecondaryMission::updateOrCreate(
    ['name' => $name],  // Clé unique
    [
        'full_text' => $block,
        'slug' => Str::slug($name),
        'edition' => '10ed',
        'source' => 'chapter-approved-2025-26',
        'is_active' => true,
    ]
);
```

#### 6. Traductions automatiques

```php
// Pour chaque champ (name, full_text)
$translatedText = $translationService->translate(
    $sourceText,
    'en',  // Source language
    'fr'   // Target language
);

// Créer l'enregistrement de traduction
Translation::create([
    'source_text' => $sourceText,
    'translated_text' => $translatedText,
    'locale' => 'fr',
    'resource_type' => 'SecondaryMission',
    'resource_id' => $mission->id,
    'field' => $field,
    'status' => 'auto',
]);
```

### Statistiques d'import

**Missions importées** :
- ✅ 19 missions secondaires + 38 traductions
- ✅ 5 missions asymétriques + 10 traductions
- ✅ Missions primaires (à importer)
- ✅ Péripéties (à importer)

---

## Gestion des missions

### Interface Filament

#### 1. Listing des missions

**Colonnes affichées** :
- Nom (EN) - Avec badge couleur selon `can_be_fixed`
- Nom (FR) - Traduction automatique
- Slug
- Edition
- Source
- Statut (Actif/Inactif)

**Filtres disponibles** :
- Recherche par nom
- Filtre par statut
- Filtre par édition

#### 2. Édition d'une mission

**Sections du formulaire** :

**Données de base**
- Nom (EN) - Génère automatiquement le slug
- Slug (URL-friendly)

**Texte complet**
- Textarea avec le texte complet de la mission

**Options** (SecondaryMissions uniquement)
- Toggle "Peut être utilisée comme Mission Fixe"

#### 3. Traductions

**RelationManager** : `TranslationsRelationManager`

**Champs traductibles** :
- `name` : Nom de la mission
- `full_text` : Texte complet

**Actions disponibles** :
- Ajouter une traduction
- Éditer une traduction existante
- Supprimer une traduction

**Comportement** :
- `updateOrCreate` : Met à jour si existe, crée sinon
- Évite les doublons grâce à l'index unique
- Champs cachés : `resource_type`, `source_text`

---

## Cartes de déploiement

### Modèles disponibles

**Modèles** :
- `App\Models\StrikeForceDeploymentCard`
- `App\Models\IncursionDeploymentCard`
- `App\Models\AsymmetricWarfareDeploymentCard`

### Structure générale

```
┌─────────────────────────────────────┐
│   DEPLOYMENT CARD                   │
├─────────────────────────────────────┤
│ id                                  │
│ name (EN)                           │
│ slug                                │
│ full_text (EN)                      │
│ image_url                           │
│ image_filename                      │
│ image_path                          │
│ edition                             │
│ source                              │
│ is_active                           │
│ created_at, updated_at              │
└─────────────────────────────────────┘
```

### Colonnes principales

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint | Identifiant unique |
| `name` | varchar(255) | Nom de la carte (EN) |
| `slug` | varchar(255) | URL-friendly identifier |
| `full_text` | longtext | Texte complet (EN) |
| `image_url` | varchar(255) | URL de l'image |
| `image_filename` | varchar(255) | Nom du fichier image |
| `image_path` | varchar(255) | Chemin du fichier |
| `edition` | varchar(255) | Édition (ex: "10ed") |
| `source` | varchar(255) | Source (ex: "chapter-approved") |
| `is_active` | tinyint(1) | Statut actif/inactif |
| `created_at` | timestamp | Date de création |
| `updated_at` | timestamp | Date de modification |

---

## Modèles de données

### Modèle PrimaryMission

```php
namespace App\Models;

class PrimaryMission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'full_text',
        'edition',
        'source',
        'is_active',
    ];

    // Relations
    public function translations()
    {
        return $this->morphMany(Translation::class, 'resource');
    }
}
```

### Modèle SecondaryMission

```php
namespace App\Models;

class SecondaryMission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'full_text',
        'can_be_fixed',
        'edition',
        'source',
        'is_active',
    ];

    protected $casts = [
        'can_be_fixed' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relations
    public function translations()
    {
        return $this->morphMany(Translation::class, 'resource');
    }
}
```

### Modèle AsymmetricPrimaryMission

```php
namespace App\Models;

class AsymmetricPrimaryMission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'full_text',
        'edition',
        'source',
        'is_active',
    ];

    // Relations
    public function translations()
    {
        return $this->morphMany(Translation::class, 'resource');
    }
}
```

### Modèle TwistMission

```php
namespace App\Models;

class TwistMission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'full_text',
        'edition',
        'source',
        'is_active',
    ];

    // Relations
    public function translations()
    {
        return $this->morphMany(Translation::class, 'resource');
    }
}
```

### Modèle Translation

```php
namespace App\Models;

class Translation extends Model
{
    protected $fillable = [
        'source_text',
        'translated_text',
        'locale',
        'resource_type',
        'resource_id',
        'field',
        'status',
    ];

    // Relations
    public function resource()
    {
        return $this->morphTo();
    }
}
```

---

## API et endpoints

### Filament Admin Endpoints

#### Missions Secondaires

| Action | URL | Méthode |
|--------|-----|---------|
| Lister | `/admin/secondary-missions` | GET |
| Créer | `/admin/secondary-missions/create` | GET/POST |
| Éditer | `/admin/secondary-missions/{id}/edit` | GET/POST |
| Importer | `/admin/secondary-missions/import` | GET/POST |
| Supprimer | `/admin/secondary-missions/{id}` | DELETE |

#### Missions Primaires

| Action | URL | Méthode |
|--------|-----|---------|
| Lister | `/admin/primary-missions` | GET |
| Créer | `/admin/primary-missions/create` | GET/POST |
| Éditer | `/admin/primary-missions/{id}/edit` | GET/POST |
| Importer | `/admin/primary-missions/import` | GET/POST |
| Supprimer | `/admin/primary-missions/{id}` | DELETE |

#### Missions Asymétriques

| Action | URL | Méthode |
|--------|-----|---------|
| Lister | `/admin/asymmetric-primary-missions` | GET |
| Créer | `/admin/asymmetric-primary-missions/create` | GET/POST |
| Éditer | `/admin/asymmetric-primary-missions/{id}/edit` | GET/POST |
| Importer | `/admin/asymmetric-primary-missions/import` | GET/POST |
| Supprimer | `/admin/asymmetric-primary-missions/{id}` | DELETE |

#### Péripéties

| Action | URL | Méthode |
|--------|-----|---------|
| Lister | `/admin/twist-missions` | GET |
| Créer | `/admin/twist-missions/create` | GET/POST |
| Éditer | `/admin/twist-missions/{id}/edit` | GET/POST |
| Importer | `/admin/twist-missions/import` | GET/POST |
| Supprimer | `/admin/twist-missions/{id}` | DELETE |

---

## Troubleshooting

### Problème : "Unknown column 'slug' in 'INSERT INTO'"

**Cause** : Colonne manquante dans la table

**Solution** :
```sql
ALTER TABLE secondary_missions ADD COLUMN IF NOT EXISTS slug VARCHAR(255) NULL;
ALTER TABLE primary_missions ADD COLUMN IF NOT EXISTS slug VARCHAR(255) NULL;
ALTER TABLE asymmetric_primary_missions ADD COLUMN IF NOT EXISTS slug VARCHAR(255) NULL;
```

### Problème : "Duplicate entry for key 'unique_translation'"

**Cause** : Tentative de créer deux traductions identiques

**Solution** : Utiliser `updateOrCreate` au lieu de `create`
```php
Translation::updateOrCreate(
    [
        'resource_type' => $type,
        'resource_id' => $id,
        'field' => $field,
        'locale' => 'fr',
    ],
    [
        'translated_text' => $text,
        'status' => 'auto',
    ]
);
```

### Problème : "Unknown column 'secondary_mission_sections.secondary_mission_id'"

**Cause** : RelationManager référence une table inexistante

**Solution** : Supprimer le RelationManager défaillant
```php
public static function getRelations(): array
{
    return [
        // Supprimer: RelationManagers\SectionsRelationManager::class,
        RelationManagers\TranslationsRelationManager::class,
    ];
}
```

### Problème : Les traductions ne se créent pas

**Cause** : `triggerAutoTranslations()` n'est pas appelée

**Solution** : Ajouter l'appel dans la boucle d'import
```php
foreach ($missions as $mission) {
    $model = Mission::updateOrCreate([...]);
    $this->triggerAutoTranslations($model);  // ✅ AJOUTER
}
```

### Problème : Le parser ne reconnaît pas les séparateurs

**Cause** : Regex trop restrictive

**Solution** : Utiliser `/\s*\n\s*-{3,}\s*\n\s*/`
```php
$blocks = preg_split('/\s*\n\s*-{3,}\s*\n\s*/', $text);
```

---

## Checklist de maintenance

### Avant chaque import

- [ ] Vérifier que toutes les colonnes existent
- [ ] Vérifier que l'index unique est en place
- [ ] Vider le cache : `php artisan cache:clear`
- [ ] Vérifier la connexion DeepL

### Après chaque import

- [ ] Vérifier le nombre de missions importées
- [ ] Vérifier le nombre de traductions créées
- [ ] Tester l'édition d'une mission
- [ ] Tester l'affichage des traductions
- [ ] Vérifier les logs d'erreur

### Commandes utiles

```bash
# Vider le cache
php artisan cache:clear

# Vérifier les migrations
php artisan migrate:status

# Voir les logs
tail -f storage/logs/laravel.log

# Tester les traductions
php artisan tinker
> App\Models\Translation::count()
> App\Models\SecondaryMission::count()
```

---

## Résumé des statuts

### État actuel du système

**✅ Missions Secondaires**
- 19 missions importées
- 38 traductions créées (name + full_text)
- Interface Filament fonctionnelle
- Import automatique avec traductions

**✅ Missions Asymétriques**
- 5 missions importées
- 10 traductions créées (name + full_text)
- Interface Filament fonctionnelle
- Import automatique avec traductions

**⏳ Missions Primaires**
- À importer
- Interface Filament prête
- Import automatique avec traductions

**⏳ Péripéties (Twist)**
- À importer
- Interface Filament prête
- Import automatique avec traductions

**⏳ Cartes de déploiement**
- Modèles créés
- Interface Filament à configurer
- Import à implémenter

---

## Contact et support

Pour toute question ou problème, consultez :
- Les logs : `storage/logs/laravel.log`
- La base de données : `dev40k`
- L'interface admin : `/admin`

**Dernière mise à jour** : 2025-11-05
**Version** : 1.0
