# 🚀 Guide de Mise à Jour Automatique des Missions

## Objectif

Automatiser complètement le processus de mise à jour, validation et correction des missions depuis Wahapedia.

## Commande Maître

### `php artisan missions:update-and-validate`

Exécute automatiquement le workflow complet pour toutes les missions.

**Workflow :**
1. ✅ Validation du XML
2. ✅ Import des missions
3. ✅ Comparaison avec Wahapedia
4. ✅ Correction automatique des discrepancies
5. ✅ Vérification finale

**Usage :**

```bash
# Mettre à jour TOUTES les missions (par défaut)
php artisan missions:update-and-validate

# Mettre à jour uniquement les missions primaires et secondaires
php artisan missions:update-and-validate --types=primary,secondary

# Mettre à jour en mode DRY-RUN (prévisualiser sans appliquer)
php artisan missions:update-and-validate --dry-run

# Mettre à jour uniquement les missions primaires
php artisan missions:update-and-validate --types=primary
```

**Types disponibles :**
- `primary` : Missions primaires
- `secondary` : Missions secondaires
- `twist` : Péripéties
- `asymmetric` : Missions asymétriques
- `strike-force` : Cartes Strike Force
- `incursion` : Cartes Incursion
- `asymmetric-warfare` : Cartes Asymmetric Warfare

## Commandes Individuelles

### Missions Primaires

```bash
# Valider
php artisan missions:validate-xml --strict

# Comparer
php artisan missions:compare-wahapedia --type=primary

# Corriger
php artisan missions:fix-discrepancies --type=primary --dry-run
php artisan missions:fix-discrepancies --type=primary
```

### Missions Secondaires

```bash
# Valider
php artisan missions:validate-secondary-xml --strict

# Comparer
php artisan missions:compare-secondary-wahapedia

# Corriger
php artisan missions:fix-secondary-discrepancies --dry-run
php artisan missions:fix-secondary-discrepancies
```

## Workflow Recommandé

### À chaque mise à jour de Wahapedia

```bash
# 1. Mettre à jour et valider TOUTES les missions
php artisan missions:update-and-validate

# 2. Si mode DRY-RUN, vérifier les changements
php artisan missions:update-and-validate --dry-run

# 3. Appliquer les changements
php artisan missions:update-and-validate

# 4. Vérifier que tout est correct
php artisan missions:compare-wahapedia --type=primary
php artisan missions:compare-secondary-wahapedia
```

### Mise à Jour Partielle

```bash
# Mettre à jour uniquement les missions primaires
php artisan missions:update-and-validate --types=primary

# Mettre à jour missions primaires et secondaires
php artisan missions:update-and-validate --types=primary,secondary

# Mettre à jour cartes de déploiement
php artisan missions:update-and-validate --types=strike-force,incursion,asymmetric-warfare
```

## Fichiers Créés

### Commandes Maître
1. `app/Console/Commands/UpdateAndValidateMissions.php`
   - Workflow complet automatisé
   - Traite tous les types de missions
   - Mode DRY-RUN disponible

### Missions Secondaires
2. `app/Console/Commands/ValidateSecondaryMissionsXml.php`
   - Valide le XML des missions secondaires
   
3. `app/Console/Commands/CompareSecondaryMissionsWithWahapedia.php`
   - Compare les missions secondaires avec Wahapedia
   
4. `app/Console/Commands/FixSecondaryMissionsDiscrepancies.php`
   - Corrige automatiquement les discrepancies

### Missions Primaires (Existantes)
5. `app/Console/Commands/ValidateMissionsXml.php`
6. `app/Console/Commands/CompareMissionsWithWahapedia.php`
7. `app/Console/Commands/FixMissionsDiscrepancies.php`

## Configuration des Données de Référence

### Missions Primaires

Fichier : `app/Console/Commands/CompareMissionsWithWahapedia.php`

```php
protected function getWahapediaReferenceData(string $type): array
{
    if ($type === 'primary') {
        return [
            'MISSION_NAME' => ['scoring_count' => N, 'timing' => 'timing_value'],
            // ...
        ];
    }
}
```

### Missions Secondaires

Fichier : `app/Console/Commands/CompareSecondaryMissionsWithWahapedia.php`

```php
protected function getWahapediaReferenceData(): array
{
    return [
        'MISSION_NAME' => ['scoring_count' => N],
        // À remplir avec les missions secondaires réelles
    ];
}
```

## Exemple d'Utilisation Complète

### Scénario : Mise à Jour Complète

```bash
# 1. Prévisualiser les changements
$ php artisan missions:update-and-validate --dry-run
🚀 Workflow Complet de Mise à Jour des Missions
═══════════════════════════════════════════════════════════
⚠️  Mode DRY-RUN : Les changements ne seront PAS appliqués

📊 Types de missions à traiter: primary, secondary, twist, asymmetric, strike-force, incursion, asymmetric-warfare

🔄 Traitement: primary
─────────────────────────────────────────────────────────────
Étape 1/4 : Validation du XML...
✅ Validation réussie - Aucun problème détecté

Étape 2/4 : Import des missions...
✅ Importation terminée !

Étape 3/4 : Comparaison avec Wahapedia...
✅ Toutes les missions correspondent à Wahapedia

Étape 4/4 : Correction des discrepancies...
✅ Aucune correction nécessaire

# 2. Appliquer les changements
$ php artisan missions:update-and-validate
🚀 Workflow Complet de Mise à Jour des Missions
═══════════════════════════════════════════════════════════

📊 Types de missions à traiter: primary, secondary, twist, asymmetric, strike-force, incursion, asymmetric-warfare

🔄 Traitement: primary
─────────────────────────────────────────────────────────────
[Étapes 1-5 exécutées...]
✅ primary : Workflow Terminé

[Autres types traités...]

═══════════════════════════════════════════════════════════
✅ Workflow Complet Terminé
```

## Bonnes Pratiques

### À FAIRE
- ✅ Exécuter `--dry-run` d'abord pour prévisualiser
- ✅ Mettre à jour les données de référence Wahapedia régulièrement
- ✅ Vérifier après chaque mise à jour
- ✅ Documenter les changements dans Wahapedia
- ✅ Utiliser la commande maître pour les mises à jour complètes

### À NE PAS FAIRE
- ❌ Modifier manuellement les données sans vérification
- ❌ Oublier de mettre à jour les données de référence
- ❌ Ignorer les erreurs de validation
- ❌ Appliquer les changements sans prévisualiser en DRY-RUN

## Maintenance

### Ajouter une Nouvelle Mission

1. Ajouter dans le XML source
2. Ajouter dans les données de référence Wahapedia
3. Exécuter `php artisan missions:update-and-validate`

### Corriger une Mission

1. Corriger dans le XML source
2. Exécuter `php artisan missions:update-and-validate --dry-run`
3. Vérifier les changements
4. Exécuter `php artisan missions:update-and-validate`

### Vérifier l'État

```bash
# Vérifier les missions primaires
php artisan missions:compare-wahapedia --type=primary

# Vérifier les missions secondaires
php artisan missions:compare-secondary-wahapedia

# Vérifier une mission spécifique
php artisan missions:compare-wahapedia "TAKE AND HOLD" --type=primary
```

## État Actuel

✅ Commande maître créée
✅ Missions secondaires supportées
✅ Mode DRY-RUN disponible
✅ Workflow complet automatisé
✅ Prêt pour utilisation

## Prochaines Étapes

1. Remplir les données de référence Wahapedia pour missions secondaires
2. Tester le workflow complet
3. Documenter les missions secondaires réelles
4. Mettre à jour régulièrement les données de référence
