# 📋 Guide de Mise à Jour des Missions Warhammer 40k

## 🎯 Vue d'ensemble

Ce guide explique comment mettre à jour facilement les missions, cartes de déploiement et péripéties de deux façons :
- **Via Wahapedia** : Synchronisation automatique depuis la source officielle
- **Manuellement** : Ajout/modification directe de missions individuelles

Les deux méthodes coexistent et se complètent.

## 📊 Types de Données Gérées

- ✅ **Missions Primaires** (`primary`)
- ✅ **Missions Secondaires** (`secondary`)
- ✅ **Péripéties** (`twist`)
- ✅ **Missions Primaires Asymétriques** (`asymmetric`)
- ✅ **Cartes Strike Force** (`strike-force`)
- ✅ **Cartes Incursions** (`incursion`)
- ✅ **Cartes Guerre Asymétrique** (`asymmetric-warfare`)

## 🔄 Deux Approches Complémentaires

### Approche 1 : Synchronisation depuis Wahapedia (Recommandée)

Récupère les données directement depuis Wahapedia (source officielle).

```bash
# Synchroniser depuis Wahapedia
php artisan missions:sync-wahapedia primary

# Puis traduire
php artisan missions:translate --locale=fr
```

**Avantages :**
- ✅ Données toujours à jour
- ✅ Source officielle
- ✅ Automatisé
- ✅ Conserve les modifications existantes

### Approche 2 : Mise à Jour Manuelle

Ajouter ou modifier des missions manuellement dans le système.

```bash
# Ajouter une mission
php artisan missions:add-single primary "NOUVELLE MISSION" "Description"

# Modifier une mission existante
php artisan missions:update-single primary "LINCHPIN" --description="Nouvelle description"
```

**Avantages :**
- ✅ Contrôle total
- ✅ Pas de dépendance à Wahapedia
- ✅ Idéal pour les données personnalisées
- ✅ Modifications rapides

### Approche 3 : Mise à Jour dans l'Admin

Modifier directement dans l'interface admin.

```
Admin → Warhammer 40k → [Type de données]
→ Éditer une mission
→ Modifier les champs
→ Enregistrer
```

**Avantages :**
- ✅ Interface visuelle
- ✅ Pas de ligne de commande
- ✅ Traductions intégrées
- ✅ Glossaire automatique

## 🔄 Modes de Synchronisation (Wahapedia)

### 1. **Mode MERGE** (Par défaut - Recommandé)
Fusionne les données Wahapedia avec les existantes. Les données existantes ne sont pas supprimées.

```bash
php artisan missions:sync-wahapedia primary --mode=merge
```

### 2. **Mode REPLACE** (Remplace tout)
Supprime toutes les données existantes et les remplace par celles de Wahapedia.

```bash
php artisan missions:sync-wahapedia primary --mode=replace
```

### 3. **Mode ADD-ONLY** (Ajoute seulement)
Ajoute uniquement les nouvelles données de Wahapedia, ignore les doublons.

```bash
php artisan missions:sync-wahapedia primary --mode=add-only
```

## 📝 Cas d'Usage Courants

### Cas 1 : Mettre à jour depuis Wahapedia (Recommandé)

```bash
# Synchroniser depuis Wahapedia
php artisan missions:sync-wahapedia primary

# Télécharger les images (si applicable)
php artisan missions:download-strike-force-images --force

# Traduire
php artisan missions:translate --locale=fr
```

### Cas 2 : Ajouter UNE SEULE nouvelle mission MANUELLEMENT

```bash
php artisan missions:add-single primary "NOUVELLE MISSION" "Description de la nouvelle mission"
```

### Cas 3 : Modifier UNE SEULE mission existante MANUELLEMENT

```bash
# Mettre à jour la description
php artisan missions:update-single primary "LINCHPIN" --description="Nouvelle description"

# Mettre à jour le texte complet
php artisan missions:update-single primary "LINCHPIN" --full-text="Nouveau texte complet"

# Désactiver une mission
php artisan missions:update-single primary "LINCHPIN" --active=0

# Réactiver une mission
php artisan missions:update-single primary "LINCHPIN" --active=1
```

### Cas 4 : Modifier dans l'interface Admin

```
Admin → Warhammer 40k → Missions Primaires
→ Éditer une mission
→ Modifier les champs
→ Enregistrer
```

### Cas 5 : Remplacer TOUTES les missions depuis Wahapedia (danger ⚠️)

```bash
# Cela supprimera TOUTES les missions existantes
php artisan missions:sync-wahapedia primary --mode=replace

# Télécharger les images
php artisan missions:download-strike-force-images --force

# Traduire
php artisan missions:translate --locale=fr
```

### Cas 6 : Combiner Wahapedia + Modifications Manuelles

```bash
# 1. Synchroniser depuis Wahapedia (mode merge par défaut)
php artisan missions:sync-wahapedia primary

# 2. Ajouter une mission personnalisée
php artisan missions:add-single primary "MA MISSION CUSTOM" "Description personnalisée"

# 3. Modifier une mission existante
php artisan missions:update-single primary "LINCHPIN" --description="Modification personnalisée"

# 4. Traduire
php artisan missions:translate --locale=fr

# 5. Vérifier dans l'admin
```

## 🎯 Commandes Disponibles

### 1. Synchronisation depuis Wahapedia

```bash
php artisan missions:sync-wahapedia {type} [--mode=merge|replace|add-only] [--source=chapter-approved-2025-26]
```

**Types disponibles :**
- `primary` - Missions primaires
- `secondary` - Missions secondaires
- `twist` - Péripéties
- `asymmetric` - Missions primaires asymétriques
- `strike-force` - Cartes Strike Force
- `incursion` - Cartes Incursions
- `asymmetric-warfare` - Cartes Guerre Asymétrique
- `all` - Toutes les données

**Modes :**
- `merge` (défaut) - Fusionne avec les existantes
- `replace` - Remplace tout
- `add-only` - Ajoute seulement les nouvelles

### 2. Ajouter une Mission MANUELLEMENT

```bash
php artisan missions:add-single {type} {name} {description} [--source=chapter-approved-2025-26]
```

**Exemple :**
```bash
php artisan missions:add-single primary "NOUVELLE MISSION" "Description courte"
```

### 3. Mettre à Jour une Mission MANUELLEMENT

```bash
php artisan missions:update-single {type} {name} [--description=...] [--full-text=...] [--active=0|1] [--source=chapter-approved-2025-26]
```

**Exemples :**
```bash
# Mettre à jour la description
php artisan missions:update-single primary "LINCHPIN" --description="Nouvelle description"

# Mettre à jour le texte complet
php artisan missions:update-single primary "LINCHPIN" --full-text="Nouveau texte complet"

# Désactiver
php artisan missions:update-single primary "LINCHPIN" --active=0

# Réactiver
php artisan missions:update-single primary "LINCHPIN" --active=1
```

### 4. Importer les Données

```bash
# Missions primaires
php artisan missions:import-xml

# Missions secondaires
php artisan missions:import-secondary-xml

# Péripéties
php artisan missions:import-twist-xml

# Missions primaires asymétriques
php artisan missions:import-asymmetric-xml

# Cartes Strike Force
php artisan missions:import-strike-force

# Cartes Incursions
php artisan missions:import-incursion

# Cartes Guerre Asymétrique
php artisan missions:import-asymmetric-warfare
```

### 5. Télécharger les Images

```bash
# Cartes Strike Force
php artisan missions:download-strike-force-images --force

# Cartes Incursions
php artisan missions:download-incursion-images --force

# Cartes Guerre Asymétrique
php artisan missions:download-asymmetric-warfare-images --force
```

### 6. Traduire les Données

```bash
# Missions primaires
php artisan missions:translate --locale=fr

# Missions secondaires
php artisan missions:translate-secondary --locale=fr

# Péripéties
php artisan missions:translate-twist --locale=fr

# Missions primaires asymétriques
php artisan missions:translate-asymmetric --locale=fr

# Cartes Strike Force
php artisan missions:translate-strike-force --locale=fr

# Cartes Incursions
php artisan missions:translate-incursion --locale=fr

# Cartes Guerre Asymétrique
php artisan missions:translate-asymmetric-warfare --locale=fr
```

## 🔐 Sécurité des Données

### ✅ Vos modifications sont protégées

- Les traductions manuelles ne sont **jamais** supprimées
- Les modifications dans l'admin sont **conservées**
- Les images téléchargées sont **préservées**

### ⚠️ Attention

- Mode `replace` supprime TOUTES les données
- Utilisez `merge` ou `add-only` pour être sûr

## 📋 Workflow Recommandé

### Pour une mise à jour complète (sûre) :

```bash
# 1. Mettre à jour les données (mode merge)
php artisan missions:update primary

# 2. Importer les nouvelles missions
php artisan missions:import-xml

# 3. Télécharger les images (si applicable)
php artisan missions:download-strike-force-images --force

# 4. Traduire les nouvelles missions
php artisan missions:translate --locale=fr

# 5. Vérifier dans l'admin
# Admin → Warhammer 40k → Missions Primaires
```

### Pour ajouter une seule mission :

```bash
# 1. Ajouter la mission
php artisan missions:add-single primary "NOUVELLE MISSION" "Description"

# 2. Traduire (optionnel)
php artisan missions:translate --locale=fr

# 3. Vérifier dans l'admin
```

### Pour modifier une mission existante :

```bash
# 1. Mettre à jour
php artisan missions:update-single primary "LINCHPIN" --description="Nouvelle description"

# 2. Vérifier dans l'admin
```

## 🎨 Interface Admin

Vous pouvez aussi mettre à jour directement dans l'interface admin :

```
Admin → Warhammer 40k → [Type de données]
→ Éditer une mission
→ Modifier les champs
→ Enregistrer
```

Les modifications seront automatiquement propagées au glossaire intelligent.

## 📊 Statut des Données

Chaque mission a un statut :

- ✅ **Actif** - Visible et utilisable
- ❌ **Inactif** - Caché mais conservé

Vous pouvez désactiver une mission sans la supprimer :

```bash
php artisan missions:update-single primary "LINCHPIN" --active=0
```

## 🔍 Vérification

Pour vérifier les données importées :

```bash
# Compter les missions
php artisan tinker
>>> App\Models\PrimaryMission::count()
>>> App\Models\PrimaryMission::where('is_active', true)->count()
```

## 💡 Conseils

1. **Utilisez `merge` par défaut** - C'est le plus sûr
2. **Testez en développement** - Avant de faire en production
3. **Sauvegardez votre DB** - Avant une mise à jour importante
4. **Vérifiez dans l'admin** - Après chaque mise à jour
5. **Utilisez les traductions manuelles** - Pour les termes importants

## ❓ FAQ

**Q: Vais-je perdre mes modifications ?**
A: Non, avec le mode `merge` (défaut), vos modifications sont conservées.

**Q: Comment ajouter une mission sans toucher aux autres ?**
A: Utilisez `php artisan missions:add-single`

**Q: Comment modifier une mission existante ?**
A: Utilisez `php artisan missions:update-single` ou l'interface admin

**Q: Puis-je remplacer TOUTES les missions ?**
A: Oui, avec `--mode=replace`, mais attention !

**Q: Comment désactiver une mission ?**
A: Utilisez `php artisan missions:update-single {type} {name} --active=0`

## 📞 Support

Pour toute question, consultez la documentation complète dans `/docs/missions/`
