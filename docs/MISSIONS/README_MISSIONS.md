# 🎯 Bienvenue dans la Documentation Missions

## 📚 Où commencer ?

### 🚀 Je suis pressé (5 minutes)
→ Lire : **[MISSIONS_QUICK_REFERENCE.md](MISSIONS_QUICK_REFERENCE.md)**

### 📖 Je veux comprendre complètement (30 minutes)
→ Lire : **[MISSIONS_COMPLETE.md](MISSIONS_COMPLETE.md)**

### 🔧 Je veux modifier le code (1 heure)
→ Lire : **[MISSIONS_TECHNICAL_DETAILS.md](MISSIONS_TECHNICAL_DETAILS.md)**

### 🗺️ Je veux naviguer la documentation
→ Lire : **[MISSIONS_INDEX.md](MISSIONS_INDEX.md)**

---

## 📋 Fichiers disponibles

| Fichier | Taille | Contenu | Audience |
|---------|--------|---------|----------|
| **MISSIONS_INDEX.md** | 12 KB | Index et navigation | Tous |
| **MISSIONS_QUICK_REFERENCE.md** | 8 KB | Référence rapide | Utilisateurs |
| **MISSIONS_COMPLETE.md** | 24 KB | Documentation complète | Tous |
| **MISSIONS_TECHNICAL_DETAILS.md** | 20 KB | Détails techniques | Développeurs |

**Total** : 64 KB, 2093 lignes

---

## 🎯 Cas d'usage courants

### 📥 Importer des missions
1. Aller à : `/admin/secondary-missions/import`
2. Coller le texte avec séparateurs `---`
3. Cliquer "Importer"
4. Les traductions FR se créent automatiquement

**Détails** : [MISSIONS_QUICK_REFERENCE.md](MISSIONS_QUICK_REFERENCE.md#import-de-missions)

### ✏️ Ajouter une mission manuellement
1. Aller à : `/admin/secondary-missions/create`
2. Remplir le formulaire
3. Sauvegarder
4. Ajouter les traductions via le RelationManager

**Détails** : [MISSIONS_COMPLETE.md](MISSIONS_COMPLETE.md#gestion-des-missions)

### 🌐 Traduire une mission
1. Aller à : `/admin/secondary-missions/{id}/edit`
2. Cliquer sur "Traductions"
3. Ajouter ou éditer une traduction
4. Sauvegarder

**Détails** : [MISSIONS_COMPLETE.md](MISSIONS_COMPLETE.md#système-de-traductions)

### 🐛 Corriger une erreur
1. Consulter : [MISSIONS_QUICK_REFERENCE.md](MISSIONS_QUICK_REFERENCE.md#erreurs-courantes-et-solutions)
2. Ou : [MISSIONS_COMPLETE.md](MISSIONS_COMPLETE.md#troubleshooting)
3. Appliquer la solution

---

## 📊 État du système

### ✅ Missions importées
- **19 missions secondaires** + 38 traductions
- **5 missions asymétriques** + 10 traductions

### ⏳ À faire
- Missions primaires (à importer)
- Péripéties (à importer)
- Cartes de déploiement (à importer)

---

## 🔗 Accès rapide

### Admin Filament
```
/admin/secondary-missions
/admin/primary-missions
/admin/asymmetric-primary-missions
/admin/twist-missions
```

### Import
```
/admin/secondary-missions/import
/admin/primary-missions/import
/admin/asymmetric-primary-missions/import
/admin/twist-missions/import
```

---

## 💡 Points clés à retenir

### ✅ Traductions
- Automatiques lors de l'import
- Seulement `name` et `full_text`
- Via DeepL (gratuit)
- Statut `auto` par défaut

### ✅ Import
- Format : Texte avec séparateurs `---`
- Parser robuste (accepte tous les formats)
- Crée les missions ET les traductions
- Mise à jour automatique si doublon

### ✅ Unicité
- Index unique sur `(resource_type, resource_id, field, locale)`
- Évite les doublons
- Permet les mises à jour

### ❌ Glossaire
- **DÉSACTIVÉ** depuis la simplification
- Traductions uniquement via DeepL
- Observer ne fait rien

---

## 🚀 Commandes utiles

```bash
# Vider le cache
php artisan cache:clear

# Voir les logs
tail -f storage/logs/laravel.log

# Accéder à tinker
php artisan tinker

# Compter les missions
> App\Models\SecondaryMission::count()

# Compter les traductions
> App\Models\Translation::count()
```

---

## 📞 Besoin d'aide ?

### Problème d'import
→ Consulter : [MISSIONS_QUICK_REFERENCE.md#erreurs-courantes-et-solutions](MISSIONS_QUICK_REFERENCE.md#erreurs-courantes-et-solutions)

### Problème technique
→ Consulter : [MISSIONS_TECHNICAL_DETAILS.md#logs-et-debugging](MISSIONS_TECHNICAL_DETAILS.md#logs-et-debugging)

### Question générale
→ Consulter : [MISSIONS_COMPLETE.md#troubleshooting](MISSIONS_COMPLETE.md#troubleshooting)

---

## 📅 Historique

| Date | Version | Changements |
|------|---------|------------|
| 2025-11-05 | 1.0 | Documentation initiale |

---

## 🎓 Formation

### Débutant (20 min)
1. Lire cette page (5 min)
2. Lire MISSIONS_QUICK_REFERENCE.md (5 min)
3. Essayer d'importer une mission (10 min)

### Intermédiaire (1 h)
1. Lire MISSIONS_COMPLETE.md (30 min)
2. Essayer de modifier une mission (15 min)
3. Essayer de traduire une mission (15 min)

### Avancé (2 h)
1. Lire MISSIONS_TECHNICAL_DETAILS.md (30 min)
2. Modifier le code d'import (45 min)
3. Tester les modifications (45 min)

---

**Dernière mise à jour** : 2025-11-05  
**Version** : 1.0  
**Statut** : ✅ Complet et à jour

---

## 📖 Navigation

← [Retour aux docs](.)  
→ [Index complet](MISSIONS_INDEX.md)
