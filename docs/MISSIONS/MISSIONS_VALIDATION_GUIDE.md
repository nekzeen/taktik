# 🔍 Guide de Validation des Missions

## Objectif

Mettre en place un système de vérification automatique pour limiter les erreurs de données lors de l'import des missions depuis Wahapedia.

## Problème Résolu

Avant : Les données incorrectes du XML se propageaient directement en base de données sans vérification.
Après : Les données sont validées avant import et comparées avec Wahapedia.

## Commandes de Validation

### 1. Valider la Structure du Fichier XML

Vérifie que le fichier XML contient des données valides et complètes.

```bash
# Validation standard
php artisan missions:validate-xml

# Validation stricte (s'arrête à la première erreur)
php artisan missions:validate-xml --strict

# Validation d'un fichier spécifique
php artisan missions:validate-xml /chemin/vers/fichier.xml
```

**Vérifications effectuées :**
- ✅ Titre présent
- ✅ Description (flavour) présente
- ✅ Condition WHEN présente
- ✅ Items de scoring présents
- ✅ Chaque item contient des points VP
- ✅ Structure WHEN correcte (contient tiret ou "WHEN:")
- ✅ Timing reconnu (ANY BATTLE ROUND, SECOND BATTLE ROUND, etc.)
- ✅ Détection des doublons en base de données

### 2. Comparer avec Wahapedia

Compare les données en base de données avec les données de référence Wahapedia.

```bash
# Comparer toutes les missions primaires
php artisan missions:compare-wahapedia --type=primary

# Comparer une mission spécifique
php artisan missions:compare-wahapedia "TAKE AND HOLD" --type=primary

# Comparer les missions secondaires
php artisan missions:compare-wahapedia --type=secondary

# Comparer les péripéties
php artisan missions:compare-wahapedia --type=twist
```

**Vérifications effectuées :**
- ✅ Mission existe en base de données
- ✅ Nombre d'items de scoring correspond
- ✅ Description correspond
- ✅ Timing correspond

## Workflow de Validation Recommandé

### Avant d'importer des missions

```bash
# Étape 1 : Valider la structure du XML
php artisan missions:validate-xml --strict

# Étape 2 : Si validation OK, importer les missions
php artisan missions:import-xml

# Étape 3 : Comparer avec Wahapedia
php artisan missions:compare-wahapedia --type=primary

# Étape 4 : Corriger les discrepancies si nécessaire
# (Modifier le XML ou la base de données)

# Étape 5 : Réimporter si corrections
php artisan missions:import-xml

# Étape 6 : Vérifier à nouveau
php artisan missions:compare-wahapedia --type=primary
```

## Mise à Jour des Données de Référence Wahapedia

Les données de référence Wahapedia sont stockées dans la commande `CompareMissionsWithWahapedia.php`.

### Pour ajouter une nouvelle mission

1. Ouvrir `app/Console/Commands/CompareMissionsWithWahapedia.php`
2. Localiser la méthode `getWahapediaReferenceData()`
3. Ajouter la mission dans le tableau approprié :

```php
'TAKE AND HOLD' => [
    'scoring_count' => 1,
    'timing' => 'second_battle_round_onwards',
    'description' => 'Several strategic locations have been identified...'
],
```

**Champs disponibles :**
- `scoring_count` : Nombre d'items de scoring (OBLIGATOIRE)
- `timing` : Timing de la mission (OPTIONNEL)
- `description` : Description de la mission (OPTIONNEL)

## Exemple d'Utilisation

### Scénario 1 : Importer de nouvelles missions

```bash
# 1. Valider le XML
$ php artisan missions:validate-xml --strict
✅ Validation réussie - Aucun problème détecté

# 2. Importer
$ php artisan missions:import-xml
✅ Importation terminée !

# 3. Comparer avec Wahapedia
$ php artisan missions:compare-wahapedia --type=primary
✅ Toutes les missions correspondent à Wahapedia
```

### Scénario 2 : Détecter une erreur

```bash
# 1. Valider le XML
$ php artisan missions:validate-xml --strict
❌ Erreur: Aucun item de scoring trouvé

# 2. Corriger le XML
# (Ajouter les items de scoring manquants)

# 3. Réessayer
$ php artisan missions:validate-xml --strict
✅ Validation réussie - Aucun problème détecté
```

### Scénario 3 : Détecter une discrepancy

```bash
# 1. Comparer avec Wahapedia
$ php artisan missions:compare-wahapedia --type=primary
❌ Erreurs trouvées:
  - TAKE AND HOLD: Nombre d'items de scoring différent (DB: 2, Wahapedia: 1)

# 2. Corriger le XML ou la base de données
# (Supprimer l'item de scoring supplémentaire)

# 3. Réimporter
$ php artisan missions:import-xml

# 4. Vérifier à nouveau
$ php artisan missions:compare-wahapedia --type=primary
✅ Toutes les missions correspondent à Wahapedia
```

## Sorties des Commandes

### Validation XML - Succès

```
🔍 Validation des missions XML...
═══════════════════════════════════════════════════════════

📋 Mission 1: TAKE AND HOLD
  ✅ TAKE AND HOLD a 1 item(s) de scoring
  📊 Scoring items:
     1. The player whose turn it is scores 5VP for each objective marker...

═══════════════════════════════════════════════════════════
📊 Résultats de validation:
  Total missions: 10
  Erreurs: 0
  Avertissements: 0

✅ Validation réussie - Aucun problème détecté
```

### Validation XML - Erreur

```
❌ Erreurs trouvées:
  - Aucun item de scoring trouvé
  - Condition WHEN manquante

Validation échouée
```

### Comparaison Wahapedia - Succès

```
🔍 Comparaison des missions avec Wahapedia...
═══════════════════════════════════════════════════════════

📋 Vérification: TAKE AND HOLD
  ✅ Nombre d'items de scoring correct: 1
  ✅ Description correcte
  ✅ Timing correct: second_battle_round_onwards

═══════════════════════════════════════════════════════════
📊 Résultats de comparaison:
  Erreurs: 0
  Avertissements: 0

✅ Toutes les missions correspondent à Wahapedia
```

### Comparaison Wahapedia - Erreur

```
❌ Erreurs trouvées:
  - TAKE AND HOLD: Nombre d'items de scoring différent (DB: 2, Wahapedia: 1)

⚠️  Avertissements:
  - TERRAFORM: Timing différent (DB: second_battle_round_onwards, Wahapedia: any_battle_round)
```

## Fichiers Créés

1. **`app/Console/Commands/ValidateMissionsXml.php`**
   - Valide la structure et le contenu du fichier XML
   - Vérifie les données avant import

2. **`app/Console/Commands/CompareMissionsWithWahapedia.php`**
   - Compare les données en base de données avec Wahapedia
   - Détecte les discrepancies

3. **`MISSIONS_VALIDATION_GUIDE.md`** (ce fichier)
   - Documentation complète du système de validation

## État Actuel

✅ Commande de validation XML créée
✅ Commande de comparaison Wahapedia créée
✅ Documentation complète
✅ Prêt pour utilisation

## Prochaines Étapes

1. Exécuter les validations avant chaque import
2. Mettre à jour les données de référence Wahapedia régulièrement
3. Corriger les erreurs détectées avant import
4. Documenter les changements dans Wahapedia

## Notes Importantes

- Les données de référence Wahapedia doivent être mises à jour manuellement
- La validation XML ne remplace pas la vérification manuelle
- Toujours comparer avec Wahapedia après import
- En cas de doute, consulter la source officielle Wahapedia
