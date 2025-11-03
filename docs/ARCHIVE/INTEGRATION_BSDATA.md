# 🔗 INTÉGRATION BSDATA

## ✅ SYSTÈME COMPLET IMPLÉMENTÉ

L'intégration avec BSData (BattleScribe Data) permet une validation précise des listes d'armées basée sur les données officielles de Warhammer 40k 10e édition.

---

## 🎯 FONCTIONNALITÉS

### **1. Synchronisation automatique** ✅
- Téléchargement depuis GitHub (BSData/wh40k-10e)
- Import des factions, unités et détachements
- Stockage en base de données
- Commande Artisan dédiée

### **2. Validation des listes** ✅
- Vérification des unités
- Validation des points
- Détection des erreurs
- Suggestions d'unités

### **3. Analyse améliorée** ✅
- Détection précise des factions
- Reconnaissance des détachements officiels
- Extraction des unités avec validation
- Calcul automatique des points

---

## 📊 BASE DE DONNÉES

### **Table: bsdata_units**
Stocke toutes les unités de Warhammer 40k 10e

**Colonnes** :
- `id` : ID interne
- `bsdata_id` : ID depuis BSData (unique)
- `faction_id` : Lien vers la faction
- `name` : Nom de l'unité
- `type` : HQ, Troops, Elites, etc.
- `points_min` : Points minimum
- `points_max` : Points maximum
- `keywords` : Mots-clés (JSON)
- `abilities` : Capacités (JSON)
- `wargear` : Équipement (JSON)
- `description` : Description
- `raw_data` : Données brutes XML (JSON)

### **Table: bsdata_detachments**
Stocke tous les détachements

**Colonnes** :
- `id` : ID interne
- `bsdata_id` : ID depuis BSData (unique)
- `faction_id` : Lien vers la faction
- `name` : Nom du détachement
- `description` : Description
- `rules` : Règles (JSON)
- `stratagems` : Stratagèmes (JSON)
- `enhancements` : Améliorations (JSON)
- `raw_data` : Données brutes XML (JSON)

---

## 🔧 SERVICES

### **BsdataImporter**
**Fichier** : `app/Services/BsdataImporter.php`

#### **Méthodes principales**

##### **importAll(): array**
Importe toutes les données depuis GitHub

**Retourne** :
```php
[
    'factions' => 10,
    'units' => 500,
    'detachments' => 50,
    'errors' => [],
]
```

##### **importCatalogFile(string $url): void**
Importe un fichier catalogue (.cat)

**Process** :
1. Télécharge le fichier XML
2. Parse le XML
3. Extrait faction, unités, détachements
4. Stocke en base de données

##### **importUnits(\SimpleXMLElement $xml, Faction $faction): void**
Importe les unités depuis le XML

**Extrait** :
- Nom de l'unité
- Type (HQ, Troops, etc.)
- Points (min/max)
- Mots-clés
- Capacités
- Équipement

##### **importDetachments(\SimpleXMLElement $xml, Faction $faction): void**
Importe les détachements

**Extrait** :
- Nom du détachement
- Description
- Règles
- Stratagèmes

---

### **ArmyListAnalyzer (amélioré)**
**Fichier** : `app/Services/ArmyListAnalyzer.php`

#### **Nouvelles méthodes**

##### **validateUnits(array $units, ?int $factionId): array**
Valide les unités avec BSData

**Paramètres** :
- `$units` : Liste des unités extraites du PDF
- `$factionId` : ID de la faction (optionnel)

**Retourne** :
```php
[
    'valid' => ['Intercessor Squad', 'Terminator Squad'],
    'invalid' => ['Unknown Unit'],
    'warnings' => ['Points incorrects pour Intercessor Squad'],
]
```

##### **getUnitSuggestions(?int $factionId, string $search): array**
Obtient des suggestions d'unités

**Paramètres** :
- `$factionId` : Filtrer par faction
- `$search` : Terme de recherche

**Retourne** :
```php
[
    ['id' => 1, 'name' => 'Intercessor Squad', 'type' => 'Troops', 'points' => '100-150 pts'],
    ...
]
```

---

## 🚀 COMMANDE ARTISAN

### **bsdata:sync**
Synchronise les données BSData depuis GitHub

**Usage** :
```bash
php artisan bsdata:sync
```

**Options** :
```bash
php artisan bsdata:sync --force
```

**Sortie** :
```
🚀 Début de la synchronisation BSData...

📥 Téléchargement des données depuis GitHub...
Repository: BSData/wh40k-10e

✅ Synchronisation terminée !

┌─────────────┬────────┐
│ Type        │ Nombre │
├─────────────┼────────┤
│ Factions    │ 10     │
│ Unités      │ 500    │
│ Détachements│ 50     │
└─────────────┴────────┘
```

---

## 📥 WORKFLOW D'IMPORT

### **Étape 1 : Téléchargement**
```
GitHub API
↓
Liste des fichiers .cat
↓
Téléchargement de chaque fichier
```

### **Étape 2 : Parsing**
```
Fichier XML
↓
SimpleXML Parser
↓
Extraction des données
```

### **Étape 3 : Stockage**
```
Données extraites
↓
Validation
↓
Insert/Update en base
```

---

## 🔍 ANALYSE AVEC BSDATA

### **Avant BSData**
```
PDF → Extraction texte → Patterns regex → Résultat approximatif
```

### **Après BSData**
```
PDF → Extraction texte → Patterns regex → Validation BSData → Résultat précis
```

### **Améliorations**
- ✅ Détection précise des factions
- ✅ Validation des détachements officiels
- ✅ Vérification des unités
- ✅ Validation des points
- ✅ Suggestions d'unités

---

## 📝 EXEMPLE D'UTILISATION

### **1. Synchroniser BSData**
```bash
php artisan bsdata:sync
```

### **2. Analyser une liste**
```php
$analyzer = new ArmyListAnalyzer();
$analysis = $analyzer->analyze('army-lists/my-list.pdf');

// Valider les unités
$validation = $analyzer->validateUnits(
    $analysis['units'],
    $analysis['faction_id']
);

// Afficher les résultats
echo "Unités valides: " . count($validation['valid']);
echo "Unités invalides: " . count($validation['invalid']);
echo "Avertissements: " . count($validation['warnings']);
```

### **3. Obtenir des suggestions**
```php
$suggestions = $analyzer->getUnitSuggestions(
    factionId: 1,
    search: 'Intercessor'
);

foreach ($suggestions as $unit) {
    echo "{$unit['name']} ({$unit['type']}) - {$unit['points']}";
}
```

---

## 🎯 DONNÉES DISPONIBLES

### **Factions**
- Space Marines
- Necrons
- Orks
- Tyranids
- Chaos Space Marines
- Astra Militarum
- T'au Empire
- Aeldari
- Drukhari
- Adeptus Mechanicus
- Et toutes les autres factions 10e édition

### **Unités**
- Nom complet
- Type (HQ, Troops, Elites, etc.)
- Points (min/max)
- Mots-clés (INFANTRY, VEHICLE, etc.)
- Capacités spéciales
- Équipement disponible

### **Détachements**
- Nom officiel
- Description
- Règles de détachement
- Stratagèmes
- Améliorations

---

## 🔄 MISE À JOUR

### **Automatique (recommandé)**
Créer un cron job :
```bash
# Tous les jours à 3h du matin
0 3 * * * cd /var/www/clients/client2/web12/web && php artisan bsdata:sync
```

### **Manuelle**
```bash
php artisan bsdata:sync --force
```

---

## 🐛 DÉPANNAGE

### **Erreur de téléchargement**
**Problème** : Impossible de télécharger depuis GitHub

**Solutions** :
1. Vérifier la connexion internet
2. Vérifier l'accès à api.github.com
3. Vérifier les limites de l'API GitHub

### **Erreur de parsing XML**
**Problème** : Impossible de parser le fichier XML

**Solutions** :
1. Vérifier que le fichier est bien du XML
2. Vérifier l'extension PHP XML
3. Consulter les logs : `storage/logs/laravel.log`

### **Données manquantes**
**Problème** : Certaines unités/détachements manquent

**Solutions** :
1. Re-synchroniser : `php artisan bsdata:sync --force`
2. Vérifier les logs d'erreur
3. Vérifier la structure du XML BSData

---

## 📊 STATISTIQUES

### **Données importées (estimation)**
- **Factions** : ~20 factions
- **Unités** : ~1000 unités
- **Détachements** : ~100 détachements

### **Taille en base de données**
- **bsdata_units** : ~50 MB
- **bsdata_detachments** : ~10 MB
- **Total** : ~60 MB

### **Temps de synchronisation**
- **Première fois** : 2-5 minutes
- **Mise à jour** : 1-2 minutes

---

## 🔮 AMÉLIORATIONS FUTURES

### **Phase 1 : Validation avancée** 🔄
- Vérifier la composition de l'armée
- Valider les restrictions de détachement
- Vérifier les limites d'unités

### **Phase 2 : Interface utilisateur** 🔄
- Afficher les unités BSData dans l'admin
- Autocomplete pour les unités
- Visualisation des détachements

### **Phase 3 : API** 🔄
- Endpoint pour rechercher des unités
- Endpoint pour valider une liste
- Documentation OpenAPI

---

## ✅ RÉSULTAT

**Intégration BSData complète et fonctionnelle !**

- ✅ Synchronisation depuis GitHub
- ✅ Import des factions, unités, détachements
- ✅ Stockage en base de données
- ✅ Validation des listes d'armées
- ✅ Suggestions d'unités
- ✅ Commande Artisan
- ✅ Analyse améliorée

**Les listes d'armées sont maintenant validées avec les données officielles ! 🔗**

---

**Date** : 21 octobre 2025, 01h19
**Source** : BSData/wh40k-10e (GitHub)
**Tables créées** : 2
**Services créés** : 1 (BsdataImporter)
**Commandes créées** : 1 (bsdata:sync)
**Status** : ✅ Opérationnel
