# 🌐 Guide de Synchronisation depuis Wahapedia

## 🎯 Vue d'ensemble

Ce guide explique comment synchroniser automatiquement les données depuis Wahapedia pour mettre à jour facilement toutes les missions, cartes de déploiement et péripéties.

## 🔄 Workflow Recommandé

### Étape 1 : Synchroniser depuis Wahapedia

```bash
# Synchroniser TOUTES les données
php artisan missions:sync-wahapedia all

# Ou synchroniser un type spécifique
php artisan missions:sync-wahapedia primary
php artisan missions:sync-wahapedia secondary
php artisan missions:sync-wahapedia twist
php artisan missions:sync-wahapedia asymmetric
php artisan missions:sync-wahapedia strike-force
php artisan missions:sync-wahapedia incursion
php artisan missions:sync-wahapedia asymmetric-warfare
```

### Étape 2 : Télécharger les images (si applicable)

```bash
# Télécharger les images des cartes de déploiement
php artisan missions:download-strike-force-images --force
php artisan missions:download-incursion-images --force
php artisan missions:download-asymmetric-warfare-images --force
```

### Étape 3 : Traduire les données

```bash
# Traduire TOUTES les données en français
php artisan missions:translate --locale=fr
php artisan missions:translate-secondary --locale=fr
php artisan missions:translate-twist --locale=fr
php artisan missions:translate-asymmetric --locale=fr
php artisan missions:translate-strike-force --locale=fr
php artisan missions:translate-incursion --locale=fr
php artisan missions:translate-asymmetric-warfare --locale=fr
```

### Étape 4 : Vérifier dans l'admin

```
Admin → Warhammer 40k → [Type de données]
→ Vérifier que les données sont à jour
→ Réviser les traductions
```

## 📊 Types de Données Gérées

- ✅ **Missions Primaires** (`primary`)
- ✅ **Missions Secondaires** (`secondary`)
- ✅ **Péripéties** (`twist`)
- ✅ **Missions Primaires Asymétriques** (`asymmetric`)
- ✅ **Cartes Strike Force** (`strike-force`)
- ✅ **Cartes Incursions** (`incursion`)
- ✅ **Cartes Guerre Asymétrique** (`asymmetric-warfare`)
- ✅ **Toutes les données** (`all`)

## 🔄 Modes de Synchronisation

### Mode MERGE (Défaut - Recommandé)
Fusionne les données Wahapedia avec les existantes. Les données existantes ne sont pas supprimées.

```bash
php artisan missions:sync-wahapedia primary --mode=merge
```

### Mode REPLACE
Supprime TOUTES les données existantes et les remplace par celles de Wahapedia.

```bash
php artisan missions:sync-wahapedia primary --mode=replace
```

### Mode ADD-ONLY
Ajoute uniquement les nouvelles données de Wahapedia, ignore les doublons.

```bash
php artisan missions:sync-wahapedia primary --mode=add-only
```

## 📝 Cas d'Usage Courants

### Cas 1 : Mise à jour complète depuis Wahapedia

```bash
# 1. Synchroniser TOUTES les données
php artisan missions:sync-wahapedia all

# 2. Télécharger les images
php artisan missions:download-strike-force-images --force
php artisan missions:download-incursion-images --force
php artisan missions:download-asymmetric-warfare-images --force

# 3. Traduire
php artisan missions:translate --locale=fr
php artisan missions:translate-secondary --locale=fr
php artisan missions:translate-twist --locale=fr
php artisan missions:translate-asymmetric --locale=fr
php artisan missions:translate-strike-force --locale=fr
php artisan missions:translate-incursion --locale=fr
php artisan missions:translate-asymmetric-warfare --locale=fr

# 4. Vérifier dans l'admin
```

### Cas 2 : Mettre à jour UNE SEULE catégorie

```bash
# Synchroniser les missions primaires
php artisan missions:sync-wahapedia primary

# Traduire les missions primaires
php artisan missions:translate --locale=fr
```

### Cas 3 : Remplacer TOUTES les données (danger ⚠️)

```bash
# Cela supprimera TOUTES les données existantes
php artisan missions:sync-wahapedia all --mode=replace

# Puis télécharger et traduire
php artisan missions:download-strike-force-images --force
php artisan missions:download-incursion-images --force
php artisan missions:download-asymmetric-warfare-images --force
php artisan missions:translate --locale=fr
```

## 🔐 Sécurité des Données

✅ Les traductions manuelles ne sont JAMAIS supprimées
✅ Les modifications dans l'admin sont CONSERVÉES
✅ Les images téléchargées sont PRÉSERVÉES
⚠️ Mode `replace` supprime TOUTES les données

## 🚀 Commandes Complètes

### Synchronisation

```bash
# Synchroniser depuis Wahapedia
php artisan missions:sync-wahapedia {type} [--mode=merge|replace|add-only]
```

### Téléchargement d'images

```bash
php artisan missions:download-strike-force-images [--force]
php artisan missions:download-incursion-images [--force]
php artisan missions:download-asymmetric-warfare-images [--force]
```

### Traduction

```bash
php artisan missions:translate [--locale=fr]
php artisan missions:translate-secondary [--locale=fr]
php artisan missions:translate-twist [--locale=fr]
php artisan missions:translate-asymmetric [--locale=fr]
php artisan missions:translate-strike-force [--locale=fr]
php artisan missions:translate-incursion [--locale=fr]
php artisan missions:translate-asymmetric-warfare [--locale=fr]
```

### Mise à Jour Manuelle

```bash
# Ajouter une mission
php artisan missions:add-single {type} {name} {description}

# Mettre à jour une mission
php artisan missions:update-single {type} {name} [--description=...] [--full-text=...] [--active=0|1]
```

## 📋 Avantages de la Synchronisation Wahapedia

✅ **Données à jour** - Toujours synchronisé avec la source officielle
✅ **Automatisé** - Une seule commande pour tout mettre à jour
✅ **Sûr** - Les modifications existantes sont conservées
✅ **Flexible** - Modes de synchronisation adaptés à vos besoins
✅ **Traçable** - Historique des modifications conservé

## 🔍 Vérification

Pour vérifier les données synchronisées :

```bash
# Compter les missions
php artisan tinker
>>> App\Models\PrimaryMission::count()
>>> App\Models\PrimaryMission::where('is_active', true)->count()
>>> App\Models\PrimaryMission::where('source', 'chapter-approved-2025-26')->count()
```

## 💡 Conseils

1. **Utilisez `merge` par défaut** - C'est le plus sûr
2. **Synchronisez régulièrement** - Pour rester à jour avec Wahapedia
3. **Sauvegardez votre DB** - Avant une synchronisation importante
4. **Vérifiez dans l'admin** - Après chaque synchronisation
5. **Utilisez les traductions manuelles** - Pour les termes importants

## ❓ FAQ

**Q: Vais-je perdre mes modifications ?**
A: Non, avec le mode `merge` (défaut), vos modifications sont conservées.

**Q: Comment synchroniser uniquement les nouvelles données ?**
A: Utilisez `--mode=add-only`

**Q: Comment remplacer TOUTES les données ?**
A: Utilisez `--mode=replace`, mais attention !

**Q: Puis-je synchroniser une seule catégorie ?**
A: Oui, spécifiez le type : `php artisan missions:sync-wahapedia primary`

**Q: Comment vérifier que la synchronisation a fonctionné ?**
A: Allez dans l'admin et vérifiez les données, ou utilisez `php artisan tinker`

## 🔄 Mise à Jour Automatique

Pour mettre à jour automatiquement chaque jour :

```bash
# Ajouter au cron (crontab -e)
0 2 * * * cd /var/www/clients/client2/web12/web && php artisan missions:sync-wahapedia all >> /dev/null 2>&1
```

## 📞 Support

Pour toute question, consultez la documentation complète dans `/docs/missions/`
