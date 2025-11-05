# 📚 Index Documentation Missions, Péripéties et Cartes

## 📖 Documentation disponible

### 1. **MISSIONS_COMPLETE.md** (21 KB)
Documentation complète et détaillée sur tous les aspects du système de missions.

**Contient** :
- ✅ Architecture générale du système
- ✅ Description détaillée de chaque type de mission
- ✅ Système de traductions complet
- ✅ Processus d'import détaillé
- ✅ Gestion des missions via Filament
- ✅ Cartes de déploiement
- ✅ Modèles de données (Eloquent)
- ✅ API et endpoints
- ✅ Troubleshooting complet

**À lire** : Pour une compréhension complète du système

---

### 2. **MISSIONS_QUICK_REFERENCE.md** (5.6 KB)
Référence rapide avec les informations essentielles.

**Contient** :
- ✅ URLs d'accès rapide
- ✅ Tableau des modèles et tables
- ✅ Résumé du système de traductions
- ✅ Format d'import
- ✅ Statistiques actuelles
- ✅ Commandes utiles
- ✅ Erreurs courantes et solutions
- ✅ Checklist d'import

**À consulter** : Pour les informations rapides et courantes

---

### 3. **MISSIONS_TECHNICAL_DETAILS.md** (20 KB)
Détails techniques approfondis pour les développeurs.

**Contient** :
- ✅ Architecture de la base de données (SQL)
- ✅ Flux de traitement des imports (code)
- ✅ Service de traduction (code)
- ✅ RelationManager pour traductions (code)
- ✅ Observer (code)
- ✅ Filament Resource (code)
- ✅ Migrations (code)
- ✅ Performance et optimisation
- ✅ Caching
- ✅ Logs et debugging

**À consulter** : Pour les modifications de code et optimisations

---

## 🎯 Guides par cas d'usage

### Je veux importer des missions
1. Lire : **MISSIONS_QUICK_REFERENCE.md** → Section "Import de missions"
2. Accéder : `/admin/secondary-missions/import`
3. Coller le texte avec séparateurs `---`
4. Cliquer "Importer"

### Je veux ajouter une nouvelle mission manuellement
1. Lire : **MISSIONS_COMPLETE.md** → Section "Gestion des missions"
2. Accéder : `/admin/secondary-missions/create`
3. Remplir le formulaire
4. Sauvegarder

### Je veux traduire une mission
1. Lire : **MISSIONS_COMPLETE.md** → Section "Système de traductions"
2. Aller à : `/admin/secondary-missions/{id}/edit`
3. Cliquer sur "Traductions"
4. Ajouter ou éditer une traduction

### Je veux corriger une erreur d'import
1. Lire : **MISSIONS_QUICK_REFERENCE.md** → Section "Erreurs courantes"
2. Ou : **MISSIONS_COMPLETE.md** → Section "Troubleshooting"
3. Appliquer la solution

### Je veux optimiser les performances
1. Lire : **MISSIONS_TECHNICAL_DETAILS.md** → Section "Performance et optimisation"
2. Ajouter les indexes recommandés
3. Implémenter le caching

### Je veux modifier le code d'import
1. Lire : **MISSIONS_TECHNICAL_DETAILS.md** → Section "Flux de traitement"
2. Modifier : `app/Filament/Resources/*/Pages/ImportMissions.php`
3. Tester l'import

---

## 📊 Statistiques actuelles

### Missions importées
```
Missions Secondaires    : 19 missions ✅
Missions Asymétriques   : 5 missions ✅
Missions Primaires      : À importer ⏳
Péripéties              : À importer ⏳
```

### Traductions créées
```
Missions Secondaires    : 38 traductions (name + full_text) ✅
Missions Asymétriques   : 10 traductions (name + full_text) ✅
Missions Primaires      : À créer ⏳
Péripéties              : À créer ⏳
```

---

## 🔗 URLs importantes

### Admin Filament
```
Missions Secondaires    : /admin/secondary-missions
Missions Primaires      : /admin/primary-missions
Missions Asymétriques   : /admin/asymmetric-primary-missions
Péripéties              : /admin/twist-missions
Cartes Strike Force     : /admin/strike-force-deployment-cards
```

### Import
```
Secondaires             : /admin/secondary-missions/import
Primaires               : /admin/primary-missions/import
Asymétriques            : /admin/asymmetric-primary-missions/import
Péripéties              : /admin/twist-missions/import
```

---

## 📁 Fichiers clés du projet

### Modèles
```
app/Models/PrimaryMission.php
app/Models/SecondaryMission.php
app/Models/AsymmetricPrimaryMission.php
app/Models/TwistMission.php
app/Models/Translation.php
app/Models/StrikeForceDeploymentCard.php
```

### Ressources Filament
```
app/Filament/Resources/PrimaryMissionResource.php
app/Filament/Resources/SecondaryMissionResource.php
app/Filament/Resources/AsymmetricPrimaryMissionResource.php
app/Filament/Resources/TwistMissionResource.php
```

### Pages d'import
```
app/Filament/Resources/PrimaryMissionResource/Pages/ImportMissions.php
app/Filament/Resources/SecondaryMissionResource/Pages/ImportMissions.php
app/Filament/Resources/AsymmetricPrimaryMissionResource/Pages/ImportMissions.php
app/Filament/Resources/TwistMissionResource/Pages/ImportMissions.php
```

### RelationManagers
```
app/Filament/Resources/*/RelationManagers/TranslationsRelationManager.php
```

### Services
```
app/Services/TranslationService.php
app/Services/IntelligentTranslationService.php
```

### Observers
```
app/Observers/TranslationObserver.php
```

---

## 🚀 Commandes utiles

```bash
# Vider le cache
php artisan cache:clear

# Vérifier les migrations
php artisan migrate:status

# Voir les logs
tail -f storage/logs/laravel.log

# Accéder à tinker
php artisan tinker

# Compter les missions
> App\Models\SecondaryMission::count()
> App\Models\Translation::count()

# Voir les traductions
> App\Models\Translation::where('resource_type', 'SecondaryMission')->get()

# Supprimer les traductions d'une mission
> App\Models\Translation::where('resource_id', 1)->delete()
```

---

## ✅ Checklist de maintenance

### Quotidienne
- [ ] Vérifier les logs : `tail -f storage/logs/laravel.log`
- [ ] Vérifier les missions importées

### Hebdomadaire
- [ ] Vérifier les traductions en attente
- [ ] Vérifier les erreurs d'import
- [ ] Nettoyer le cache si nécessaire

### Mensuelle
- [ ] Vérifier les performances
- [ ] Optimiser les indexes si nécessaire
- [ ] Mettre à jour la documentation

---

## 🔍 Recherche rapide

### Par type de mission
- **Missions Secondaires** : MISSIONS_COMPLETE.md → Section "Missions Secondaires"
- **Missions Primaires** : MISSIONS_COMPLETE.md → Section "Missions Primaires"
- **Missions Asymétriques** : MISSIONS_COMPLETE.md → Section "Missions Asymétriques"
- **Péripéties** : MISSIONS_COMPLETE.md → Section "Péripéties"

### Par sujet
- **Traductions** : MISSIONS_COMPLETE.md → Section "Système de traductions"
- **Import** : MISSIONS_COMPLETE.md → Section "Import de missions"
- **Gestion** : MISSIONS_COMPLETE.md → Section "Gestion des missions"
- **Cartes** : MISSIONS_COMPLETE.md → Section "Cartes de déploiement"
- **Modèles** : MISSIONS_COMPLETE.md → Section "Modèles de données"
- **Erreurs** : MISSIONS_COMPLETE.md → Section "Troubleshooting"

### Par niveau technique
- **Débutant** : MISSIONS_QUICK_REFERENCE.md
- **Intermédiaire** : MISSIONS_COMPLETE.md
- **Avancé** : MISSIONS_TECHNICAL_DETAILS.md

---

## 📝 Notes importantes

### Glossaire
- ❌ **DÉSACTIVÉ** : Le glossaire Warhammer n'est plus utilisé
- Traductions uniquement via DeepL
- Observer ne fait rien

### Traductions
- ✅ Automatiques lors de l'import
- ✅ Seulement `name` et `full_text`
- ✅ Statut `auto` par défaut
- ✅ Peuvent être éditées manuellement

### Unicité
- ✅ Index unique sur `(resource_type, resource_id, field, locale)`
- ✅ Permet les mises à jour sans erreur
- ✅ Évite les doublons

### Performance
- ✅ Cache les traductions (30 jours)
- ✅ Indexes sur les colonnes principales
- ✅ Eager loading des relations

---

## 📞 Support

### Problèmes courants
Voir : **MISSIONS_QUICK_REFERENCE.md** → Section "Erreurs courantes et solutions"

### Problèmes techniques
Voir : **MISSIONS_TECHNICAL_DETAILS.md** → Section "Logs et debugging"

### Questions générales
Voir : **MISSIONS_COMPLETE.md** → Section "Troubleshooting"

---

## 📅 Historique des mises à jour

| Date | Version | Changements |
|------|---------|------------|
| 2025-11-05 | 1.0 | Documentation initiale créée |

---

## 🎓 Formation

### Pour débuter
1. Lire : MISSIONS_QUICK_REFERENCE.md (5 min)
2. Lire : MISSIONS_COMPLETE.md - Architecture générale (10 min)
3. Essayer : Importer une mission via l'admin (5 min)

### Pour maîtriser
1. Lire : MISSIONS_COMPLETE.md (30 min)
2. Lire : MISSIONS_TECHNICAL_DETAILS.md (30 min)
3. Modifier : Le code d'import (30 min)
4. Tester : Les modifications (15 min)

### Temps total
- Débutant : ~20 minutes
- Intermédiaire : ~1 heure
- Avancé : ~2 heures

---

**Dernière mise à jour** : 2025-11-05
**Version** : 1.0
**Statut** : ✅ Complet et à jour
