# 📚 SYSTÈME DE TRADUCTIONS WARHAMMER 40K - DOCUMENT DE RÉFÉRENCE

## 🎯 Vue d'ensemble

Le système de traductions utilise une architecture complète avec :
- **DeepL** pour les traductions automatiques
- **Glossaire Warhammer** pour la cohérence terminologique
- **Observer Pattern** pour l'automatisation
- **Table `translations`** pour le stockage centralisé

---

## 📊 Architecture du Système

### 1. Tables de Base de Données

#### Table `translations`
```sql
CREATE TABLE translations (
    id BIGINT PRIMARY KEY,
    source_text TEXT,                    -- Texte anglais original
    translated_text TEXT,                -- Texte traduit
    locale VARCHAR(5),                   -- Code langue (fr, de, es, it)
    resource_type VARCHAR(255),          -- Type de ressource (voir liste ci-dessous)
    resource_id BIGINT,                  -- ID de la ressource
    field VARCHAR(255),                  -- Champ traduit (name, description, etc.)
    status VARCHAR(50),                  -- auto, manual, reviewed, approved
    translator VARCHAR(20),              -- deepl, google, manual
    reviewed BOOLEAN,
    reviewed_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### Table `warhammer_glossary`
```sql
CREATE TABLE warhammer_glossary (
    id BIGINT PRIMARY KEY,
    english_term VARCHAR(255) UNIQUE,    -- Terme anglais
    category VARCHAR(50),                -- ability, keyword, unit, condition, action, general
    context VARCHAR(50),                 -- primary_mission, secondary_mission, twist_mission, deployment_zone, etc.
    french_translation VARCHAR(255),     -- Traduction française
    german_translation VARCHAR(255),
    spanish_translation VARCHAR(255),
    italian_translation VARCHAR(255),
    description TEXT,
    example TEXT,
    status VARCHAR(50),                  -- pending, approved, rejected
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 2. Types de Ressources Gérées

| Resource Type | Table | Commande Traduction | Observer Context |
|---|---|---|---|
| `PrimaryMission` | primary_missions | `missions:translate` | primary_mission |
| `SecondaryMission` | secondary_missions | `missions:translate-secondary` | secondary_mission |
| `TwistMission` | twist_missions | `missions:translate-twist` | twist_mission |
| `AsymmetricPrimaryMission` | asymmetric_primary_missions | `missions:translate-asymmetric` | asymmetric_primary_mission |
| `StrikeForceDeploymentCard` | strike_force_deployment_cards | `deployment-cards:translate` | strike_force_deployment |
| `IncursionDeploymentCard` | incursion_deployment_cards | `deployment-cards:translate` | incursion_deployment |
| `AsymmetricWarfareDeploymentCard` | asymmetric_warfare_deployment_cards | `deployment-cards:translate` | asymmetric_warfare_deployment |
| `TerrainLayout` | terrain_layouts | (via Filament) | general |
| `PrimaryMissionSection` | primary_mission_sections | (via Filament) | primary_mission |
| `SecondaryMissionSection` | secondary_mission_sections | (via Filament) | secondary_mission |

---

## 🔄 Flux de Traduction Complet

### Étape 1 : Créer les Traductions

#### Option A : Via Commande Artisan (Recommandé)
```bash
# Traductions des missions primaires
php artisan missions:translate --locale=fr

# Traductions des missions secondaires
php artisan missions:translate-secondary --locale=fr

# Traductions des péripéties
php artisan missions:translate-twist --locale=fr

# Traductions des cartes de déploiement
php artisan deployment-cards:translate --locale=fr
```

#### Option B : Via Interface Admin Filament
```
Admin → Warhammer 40k → [Type de ressource]
→ Éditer une ressource
→ Onglet "Traductions"
→ Ajouter une traduction
→ Enregistrer
```

### Étape 2 : Observer se Déclenche Automatiquement

Quand une traduction est créée via `Translation::create()` :

```
1. Event `created()` se déclenche
   ↓
2. TranslationObserver::created() s'exécute
   ↓
3. Extrait le terme anglais du source_text
   ↓
4. Crée/met à jour l'entrée dans warhammer_glossary
   ↓
5. Ajoute la traduction au glossaire
   ↓
6. Marque le glossaire comme "approved"
   ↓
7. Incrémente le compteur d'utilisation
```

### Étape 3 : Utiliser les Traductions dans les Vues

#### Pattern Standard pour Récupérer une Traduction

```blade
@php
    $translation = DB::table('translations')
        ->where('resource_type', 'PrimaryMission')
        ->where('resource_id', $mission->id)
        ->where('field', 'name')
        ->where('locale', 'fr')
        ->value('translated_text');
@endphp

@if($translation)
    {{ $translation }} ({{ $mission->name }})
@else
    {{ $mission->name }}
@endif
```

#### Pattern pour Plusieurs Tables (Cartes de Déploiement)

```blade
@php
    $deploymentFr = null;
    
    // Chercher dans les trois tables de cartes de déploiement
    $tables = [
        'asymmetric_warfare_deployment_cards' => 'AsymmetricWarfareDeploymentCard',
        'strike_force_deployment_cards' => 'StrikeForceDeploymentCard',
        'incursion_deployment_cards' => 'IncursionDeploymentCard',
    ];
    
    foreach ($tables as $tableName => $resourceType) {
        $card = DB::table($tableName)
            ->where('name', $deploymentMode)
            ->first();
        
        if ($card) {
            $deploymentFr = DB::table('translations')
                ->where('resource_type', $resourceType)
                ->where('resource_id', $card->id)
                ->where('field', 'name')
                ->where('locale', 'fr')
                ->value('translated_text');
            
            if ($deploymentFr) {
                break; // Sortir si traduction trouvée
            }
        }
    }
@endphp

@if($deploymentFr)
    {{ $deploymentFr }} ({{ $deploymentMode }})
@else
    {{ $deploymentMode }}
@endif
```

---

## 🔧 Fichiers Clés du Système

### Observers
- **`app/Observers/TranslationObserver.php`**
  - Méthode `created()` : Gère les traductions nouvellement créées
  - Méthode `updated()` : Gère les modifications de traductions
  - Crée/met à jour le glossaire automatiquement
  - Propage les modifications à toutes les traductions du système

### Services
- **`app/Services/TranslationService.php`**
  - Traduction via DeepL ou Google Translate
  - Gère les appels API
  - Gère les erreurs et timeouts

- **`app/Services/IntelligentTranslationService.php`**
  - Applique le glossaire Warhammer aux traductions
  - Remplace les termes avec les traductions du glossaire
  - Respecte la casse

### Commandes Artisan
- **`app/Console/Commands/TranslatePrimaryMissions.php`**
- **`app/Console/Commands/TranslateSecondaryMissions.php`**
- **`app/Console/Commands/TranslateTwistMissions.php`**
- **`app/Console/Commands/TranslateDeploymentCards.php`**

### Modèles
- **`app/Models/Translation.php`**
  - Observe `TranslationObserver`
  - Fillable : source_text, translated_text, locale, resource_type, resource_id, field, status

- **`app/Models/WarhammerGlossary.php`**
  - Méthode `findByEnglishTerm()`
  - Méthode `setTranslation()`
  - Méthode `incrementUsage()`

---

## 📋 Checklist pour Ajouter une Nouvelle Traduction

### 1. Vérifier le Resource Type
- [ ] Le type de ressource existe-t-il dans la liste ci-dessus ?
- [ ] Si non, l'ajouter à la table `translations`
- [ ] Ajouter le context correspondant dans `TranslationObserver::getContextFromResourceType()`

### 2. Créer la Commande Artisan (si nécessaire)
```php
// app/Console/Commands/TranslateNewResource.php
class TranslateNewResource extends Command
{
    protected $signature = 'new-resource:translate {--locale=fr}';
    
    public function handle()
    {
        $locale = $this->option('locale');
        $translationService = new TranslationService();
        $intelligentService = new IntelligentTranslationService();
        
        // Récupérer les ressources
        $resources = NewResource::active()->get();
        
        foreach ($resources as $resource) {
            // Vérifier si traduction existe
            $existing = Translation::where('resource_type', 'NewResource')
                ->where('resource_id', $resource->id)
                ->where('field', 'name')
                ->where('locale', $locale)
                ->first();
            
            if ($existing) continue;
            
            // Traduire
            $translatedText = $translationService->translate(
                $resource->name,
                'en',
                $locale
            );
            
            // Appliquer glossaire
            $translatedText = $intelligentService->translateWithGlossary(
                $translatedText,
                $locale,
                'context_name',
                $resource->name
            );
            
            // Créer la traduction (Observer se déclenche automatiquement)
            Translation::create([
                'source_text' => $resource->name,
                'translated_text' => $translatedText,
                'locale' => $locale,
                'resource_type' => 'NewResource',
                'resource_id' => $resource->id,
                'field' => 'name',
                'status' => 'auto',
            ]);
        }
    }
}
```

### 3. Exécuter la Commande
```bash
php artisan new-resource:translate --locale=fr
```

### 4. Utiliser dans la Vue
```blade
@php
    $translation = DB::table('translations')
        ->where('resource_type', 'NewResource')
        ->where('resource_id', $resource->id)
        ->where('field', 'name')
        ->where('locale', 'fr')
        ->value('translated_text');
@endphp

@if($translation)
    {{ $translation }} ({{ $resource->name }})
@else
    {{ $resource->name }}
@endif
```

### 5. Vérifier le Glossaire
```
Admin → Warhammer 40k → Glossaire
→ Chercher le terme anglais
→ Vérifier la traduction française
→ Vérifier le statut "approved"
```

---

## 🐛 Dépannage Courant

### Problème : Traduction n'apparaît pas
**Solutions :**
1. Vérifier que la traduction existe dans `translations`
   ```sql
   SELECT * FROM translations WHERE resource_type = 'XYZ' AND locale = 'fr';
   ```
2. Vérifier que le `resource_id` est correct
3. Vérifier que le `field` est 'name'
4. Vérifier que la vue utilise le bon `resource_type`
5. Vider le cache : `php artisan cache:clear`
6. Recompiler les vues : `php artisan view:cache`

### Problème : Traduction ne s'ajoute pas au glossaire
**Solutions :**
1. Vérifier que l'Observer est enregistré dans `AppServiceProvider`
2. Vérifier que le `source_text` contient un terme en majuscules
3. Vérifier que le contexte est correct dans `getContextFromResourceType()`
4. Vérifier les logs : `tail -f storage/logs/laravel.log`

### Problème : Traduction incorrecte
**Solutions :**
1. Modifier manuellement dans l'admin
2. L'Observer se déclenche automatiquement
3. Le glossaire est mis à jour
4. Les autres traductions contenant le même terme sont propagées

---

## 📈 Statistiques du Système

### Traductions Actuelles
- **Missions Primaires** : ~10 traductions
- **Missions Secondaires** : ~10 traductions
- **Péripéties** : ~41 traductions
- **Cartes Strike Force** : 6 traductions
- **Cartes Incursion** : 6 traductions
- **Cartes Asymmetric Warfare** : 5 traductions
- **Total** : ~78 traductions

### Entrées Glossaire
- **Total** : ~78 entrées
- **Statut Approved** : ~78
- **Contextes** : 10+

---

## 🚀 Bonnes Pratiques

### ✅ À FAIRE
- ✅ Toujours utiliser `Translation::create()` pour créer les traductions
- ✅ Toujours passer par les commandes Artisan pour les traductions en masse
- ✅ Vérifier le glossaire après chaque traduction
- ✅ Utiliser le pattern standard pour récupérer les traductions
- ✅ Vider le cache après chaque modification
- ✅ Documenter les nouveaux resource types

### ❌ À NE PAS FAIRE
- ❌ Insérer directement dans `translations` sans passer par le modèle
- ❌ Oublier de vérifier le resource_type correct
- ❌ Oublier de vider le cache
- ❌ Utiliser des patterns différents pour récupérer les traductions
- ❌ Modifier le glossaire directement sans passer par l'Observer

---

## 📞 Commandes Rapides

```bash
# Traduire tous les types
php artisan missions:translate --locale=fr
php artisan missions:translate-secondary --locale=fr
php artisan missions:translate-twist --locale=fr
php artisan missions:translate-asymmetric --locale=fr
php artisan deployment-cards:translate --locale=fr

# Vider le cache
php artisan cache:clear
php artisan view:cache

# Vérifier les traductions
php artisan tinker
>>> DB::table('translations')->where('locale', 'fr')->count()
>>> DB::table('warhammer_glossary')->count()

# Vérifier les logs
tail -f storage/logs/laravel.log
```

---

## 📝 Notes Importantes

1. **DeepL API Key** : Doit être configurée dans `.env` (`DEEPL_API_KEY`)
2. **Observer Pattern** : Automatise complètement l'intégration au glossaire
3. **Propagation** : Les modifications se propagent à toutes les traductions du système
4. **Statut Auto** : Les traductions créées via commande sont marquées "auto"
5. **Glossaire Approved** : Les entrées glossaire sont approuvées automatiquement
6. **Cache** : Toujours vider le cache après modifications
7. **Blade Compilation** : Toujours recompiler les vues après modifications

---

**Dernière mise à jour** : 3 novembre 2025
**Version** : 1.0
**Auteur** : Cascade
