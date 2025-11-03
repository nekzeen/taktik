# 🌍 SYSTÈME DE TRADUCTIONS COMPLET - FONCTIONNEMENT RÉEL

## 📊 État Actuel

- **233 traductions** en base de données
- **47 entrées glossaire** Warhammer
- **4 langues** supportées (FR, DE, ES, IT)
- **8 types de ressources** traduisibles

---

## 🏗️ Architecture du Système

### 1. Tables de Base de Données

#### `translations` (233 enregistrements)
```sql
id | source_text | translated_text | locale | resource_type | resource_id |
field | status | reviewed_by | reviewed_at | created_at | updated_at
```

**Statuts réels:**
- `pending` - En attente de traduction
- `auto` - Traduction automatique (DeepL)
- `reviewed` - Révisée manuellement
- `approved` - Approuvée

**Resource Types actuels:**
- `PrimaryMission` (10 missions × 4 champs = 40 traductions)
- `SecondaryMission` (19 missions × 5 champs = 95 traductions)
- `TwistMission` (9 missions × 5 champs = 45 traductions)
- `Detachment` (227 détachements × 1 champ = 227 traductions)
- `Faction` (26 factions × 1 champ = 26 traductions)

#### `warhammer_glossary` (47 entrées)
```sql
id | english_term | category | context | french_translation |
german_translation | spanish_translation | italian_translation |
description | example | status | usage_count | created_at | updated_at
```

**Catégories réelles:**
- `ability` - Capacités (ex: INVULNERABLE SAVE)
- `keyword` - Mots-clés (ex: AIRCRAFT)
- `condition` - Conditions (ex: WHEN DRAWN)
- `action` - Actions (ex: CLEANSE)
- `general` - Général

---

## 🔄 Workflow Complet de Traduction

### Phase 1 : Création de Ressource

```
Admin crée une mission primaire
    ↓
Enregistre en base (status: active)
    ↓
Observer::created() se déclenche
```

### Phase 2 : Observer Crée les Traductions

```php
// app/Observers/TranslationObserver.php
public function created(Translation $translation): void
{
    // 1. Extraire le terme anglais
    $englishTerm = $this->extractTermFromSourceText($translation->source_text);
    
    // 2. Chercher/créer entrée glossaire
    $glossaryTerm = WarhammerGlossary::findByEnglishTerm($englishTerm);
    if (!$glossaryTerm) {
        $glossaryTerm = WarhammerGlossary::create([...]);
    }
    
    // 3. Mettre à jour traduction glossaire
    $glossaryTerm->setTranslation('fr', $translation->translated_text);
    
    // 4. Incrémenter compteur d'utilisation
    $glossaryTerm->incrementUsage();
}
```

### Phase 3 : Traduction Automatique

**Commande Artisan:**
```bash
php artisan missions:translate --locale=fr
```

**Processus:**
1. Récupère toutes les missions primaires
2. Pour chaque mission, pour chaque champ:
   - Vérifie si traduction existe
   - Appelle DeepL API
   - Applique glossaire Warhammer
   - Crée Translation avec status `auto`

**Exemple:**
```php
$translatedText = $translationService->translate(
    'BREAK THROUGH',  // source
    'en',             // source language
    'fr'              // target language
);

// Résultat: "PERCÉE" (via DeepL)

$translatedText = $intelligentService->translateWithGlossary(
    'PERCÉE',
    'fr',
    'primary_mission',
    'BREAK THROUGH'
);

// Résultat: "PERCÉE" (glossaire appliqué)
```

### Phase 4 : Modification Manuelle

**Workflow:**
```
Admin → Missions Primaires → Éditer → Onglet Traductions
    ↓
Modifier: "PERCÉE" → "PERCÉE FINALE"
    ↓
Enregistrer
    ↓
Observer::updated() se déclenche
    ↓
1. Crée/met à jour glossaire
2. Marque traduction comme "reviewed"
3. Propage à TOUTES les traductions contenant ce terme
4. Marque propagées comme "reviewed"
```

---

## 📋 Statuts de Traduction - Cycle Complet

```
pending (initial)
    ↓
auto (après DeepL)
    ↓
reviewed (après modification manuelle)
    ↓
approved (validation finale)
```

### Transitions Réelles

| De | À | Déclencheur |
|----|---|-------------|
| pending | auto | Commande `missions:translate` |
| auto | reviewed | Modification manuelle + Observer |
| reviewed | approved | Admin approuve |

---

## 🔧 Commandes de Traduction Réelles

### 1. Missions Primaires
```bash
php artisan missions:translate --locale=fr
```

**Résultat:**
- 10 missions × 4 champs = 40 traductions
- Statut: `auto`
- Glossaire: créé/mis à jour

### 2. Missions Secondaires
```bash
php artisan missions:translate-secondary --locale=fr
```

**Résultat:**
- 19 missions × 5 champs = 95 traductions
- Statut: `auto`
- Glossaire: créé/mis à jour

### 3. Péripéties
```bash
php artisan missions:translate-twist --locale=fr
```

**Résultat:**
- 9 missions × 5 champs = 45 traductions
- Statut: `auto`
- Glossaire: créé/mis à jour

### 4. Détachements
```bash
php artisan deployment-cards:translate --locale=fr
```

**Résultat:**
- 227 détachements × 1 champ = 227 traductions
- Statut: `auto`
- Glossaire: créé/mis à jour

---

## 🎯 Cas d'Usage Réels

### Cas 1 : Importer une Nouvelle Mission Secondaire

```
1. Utilisateur copie/colle le texte de Wahapedia
2. Clique "Importer les missions"
3. Page ImportMissions parse le texte
4. Crée SecondaryMission
5. Appelle triggerAutoTranslations()
6. Translation::create() pour chaque champ
7. Observer détecte création
8. DeepL traduit automatiquement
9. Glossaire appliqué
10. Traduction propagée à toutes les missions
```

**Résultat:**
- Mission créée
- 5 traductions créées (status: auto)
- Glossaire mis à jour
- Autres missions mises à jour si termes communs

### Cas 2 : Corriger une Traduction

```
1. Admin va dans Admin → Missions Secondaires
2. Édite une mission
3. Onglet "Traductions"
4. Modifie: "TOUS LES ROUNDS" → "À N'IMPORTE QUEL ROUND"
5. Enregistre
6. Observer::updated() se déclenche
7. Glossaire créé/mis à jour
8. Traduction marquée "reviewed"
9. Propagation à TOUTES les traductions contenant ce terme
10. Autres traductions mises à jour et marquées "reviewed"
```

**Résultat:**
- Traduction corrigée
- Glossaire mis à jour
- Toutes les missions contenant ce terme sont mises à jour
- Cohérence garantie dans tout le système

### Cas 3 : Ajouter une Nouvelle Langue

```
1. Ajouter locale 'de' (allemand)
2. Exécuter: php artisan missions:translate --locale=de
3. DeepL traduit en allemand
4. Glossaire mis à jour
5. 180 traductions créées (missions + détachements)
```

---

## 🔍 Vérification des Traductions

### Commandes de Diagnostic

```bash
# Compter les traductions par statut
php artisan tinker
>>> DB::table('translations')->groupBy('status')->selectRaw('status, count(*) as count')->get()

# Voir les traductions en attente
>>> DB::table('translations')->where('status', 'pending')->count()

# Voir le glossaire
>>> DB::table('warhammer_glossary')->count()

# Voir les traductions d'une mission
>>> DB::table('translations')->where('resource_type', 'SecondaryMission')->where('resource_id', 1)->get()
```

### Requêtes SQL Utiles

```sql
-- Traductions par statut
SELECT status, COUNT(*) as count FROM translations GROUP BY status;

-- Traductions par resource type
SELECT resource_type, COUNT(*) as count FROM translations GROUP BY resource_type;

-- Traductions en attente
SELECT * FROM translations WHERE status = 'pending' AND translated_text = '';

-- Glossaire non utilisé
SELECT * FROM warhammer_glossary WHERE usage_count = 0;
```

---

## 🚨 Problèmes Courants et Solutions

### Problème 1 : Traductions en Attente Vides

**Cause:** DeepL API non disponible ou erreur lors de la traduction

**Solution:**
1. Vérifier clé API DeepL
2. Vérifier logs: `tail -f storage/logs/laravel.log`
3. Relancer la commande de traduction

### Problème 2 : Glossaire Non Mis à Jour

**Cause:** Observer non déclenché ou erreur dans extraction du terme

**Solution:**
1. Vérifier Observer enregistré dans AppServiceProvider
2. Vérifier que source_text contient un terme en majuscules
3. Vérifier les logs

### Problème 3 : Traductions Non Propagées

**Cause:** Terme anglais non trouvé ou erreur dans propagation

**Solution:**
1. Vérifier que le terme existe dans le glossaire
2. Vérifier que d'autres traductions contiennent ce terme
3. Relancer manuellement la propagation

---

## 📈 Statistiques Actuelles

```
Total Traductions: 233
├── Status auto: 109
├── Status reviewed: 124
├── Status pending: 0
└── Status approved: 0

Par Resource Type:
├── SecondaryMission: 95
├── Detachment: 227 (non comptées ci-dessus)
├── PrimaryMission: 40
├── TwistMission: 45
├── Faction: 26
└── Autres: 0

Glossaire Warhammer: 47 entrées
├── Category ability: 15
├── Category keyword: 12
├── Category condition: 10
├── Category action: 8
└── Category general: 2
```

---

## 🔐 Sécurité des Traductions

### Permissions

| Action | Admin | Utilisateur |
|--------|-------|-------------|
| Voir traductions | ✅ | ❌ |
| Modifier traductions | ✅ | ❌ |
| Approuver traductions | ✅ | ❌ |
| Voir glossaire | ✅ | ❌ |
| Modifier glossaire | ✅ | ❌ |

### Audit Trail

Toutes les modifications sont enregistrées:
- `reviewed_by` - Qui a révisé
- `reviewed_at` - Quand
- `created_at` - Création
- `updated_at` - Dernière modification

---

## 📞 Commandes Rapides

```bash
# Traduire tout
php artisan missions:translate --locale=fr
php artisan missions:translate-secondary --locale=fr
php artisan missions:translate-twist --locale=fr

# Vérifier les traductions
php artisan tinker
>>> DB::table('translations')->where('status', 'pending')->count()

# Nettoyer les traductions vides
>>> DB::table('translations')->where('translated_text', '')->delete()

# Vider le cache
php artisan cache:clear
php artisan view:cache
```

---

**Dernière mise à jour** : 3 novembre 2025
**Version** : 2.0 (Réelle)
**Statut** : ✅ Fonctionnel

