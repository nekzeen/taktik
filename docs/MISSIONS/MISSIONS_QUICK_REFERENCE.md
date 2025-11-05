# 🚀 Référence Rapide : Missions, Péripéties et Cartes

## Accès rapide

### URLs d'administration
```
Missions Secondaires    : /admin/secondary-missions
Missions Primaires      : /admin/primary-missions
Missions Asymétriques   : /admin/asymmetric-primary-missions
Péripéties              : /admin/twist-missions
Cartes Strike Force     : /admin/strike-force-deployment-cards
```

### URLs d'import
```
Importer Secondaires    : /admin/secondary-missions/import
Importer Primaires      : /admin/primary-missions/import
Importer Asymétriques   : /admin/asymmetric-primary-missions/import
Importer Péripéties     : /admin/twist-missions/import
```

---

## Modèles et tables

| Type | Modèle | Table | Colonnes clés |
|------|--------|-------|--------------|
| Secondaire | `SecondaryMission` | `secondary_missions` | name, slug, full_text, can_be_fixed, edition, source, is_active |
| Primaire | `PrimaryMission` | `primary_missions` | name, slug, full_text, edition, source, is_active |
| Asymétrique | `AsymmetricPrimaryMission` | `asymmetric_primary_missions` | name, slug, full_text, edition, source, is_active |
| Péripétie | `TwistMission` | `twist_missions` | name, slug, full_text, edition, source, is_active |

---

## Traductions

### Table de traductions
```
Table: translations
Colonnes: id, resource_type, resource_id, field, locale, 
          source_text, translated_text, status, created_at, updated_at
```

### Champs traduits
- `name` : Nom de la mission
- `full_text` : Texte complet

### Statuts
- `auto` : Traduction automatique (DeepL)
- `manual` : Traduction manuelle
- `reviewed` : Révisée
- `approved` : Approuvée
- `pending` : En attente

### Contrainte unique
```
(resource_type, resource_id, field, locale)
```

---

## Import de missions

### Format du texte
```
MISSION NAME
Description...

Details...

---

ANOTHER MISSION
More details...

---
```

### Séparateurs acceptés
- `---` (3 tirets)
- `----` (4+ tirets)
- ` --- ` (avec espaces)
- `\n---\n` (avec retours à la ligne)

### Processus
1. Coller le texte dans le formulaire
2. Cliquer "Importer les missions"
3. Les missions sont créées
4. Les traductions FR sont générées automatiquement via DeepL

---

## Statistiques actuelles

### Missions importées
- ✅ 19 missions secondaires
- ✅ 5 missions asymétriques
- ⏳ Missions primaires (à importer)
- ⏳ Péripéties (à importer)

### Traductions
- ✅ 38 traductions (secondaires)
- ✅ 10 traductions (asymétriques)
- ⏳ À créer (primaires)
- ⏳ À créer (péripéties)

---

## Commandes utiles

```bash
# Vider le cache
php artisan cache:clear

# Vérifier les migrations
php artisan migrate:status

# Voir les logs
tail -f storage/logs/laravel.log

# Compter les missions
php artisan tinker
> App\Models\SecondaryMission::count()
> App\Models\Translation::count()
```

---

## Erreurs courantes et solutions

| Erreur | Cause | Solution |
|--------|-------|----------|
| Unknown column 'slug' | Colonne manquante | Ajouter la colonne via migration |
| Duplicate entry for key 'unique_translation' | Traduction dupliquée | Utiliser `updateOrCreate` |
| Unknown column 'secondary_mission_sections.secondary_mission_id' | RelationManager défaillant | Supprimer le RelationManager |
| Les traductions ne se créent pas | `triggerAutoTranslations()` non appelée | Ajouter l'appel dans la boucle |
| Parser ne reconnaît pas les séparateurs | Regex trop restrictive | Utiliser `/\s*\n\s*-{3,}\s*\n\s*/` |

---

## Checklist d'import

### Avant
- [ ] Vérifier les colonnes existent
- [ ] Vérifier l'index unique
- [ ] Vider le cache
- [ ] Vérifier DeepL

### Pendant
- [ ] Coller le texte
- [ ] Cliquer "Importer"
- [ ] Attendre la fin

### Après
- [ ] Vérifier le nombre de missions
- [ ] Vérifier le nombre de traductions
- [ ] Tester l'édition
- [ ] Vérifier les logs

---

## Structure des missions

### Missions Secondaires (19)
```
BEHIND ENEMY LINES
STORM HOSTILE OBJECTIVE
ENGAGE ON ALL FRONTS
ESTABLISH LOCUS
CLEANSE
ASSASSINATION
NO PRISONERS
CULL THE HORDE
BRING IT DOWN
DEFEND STRONGHOLD
MARKED FOR DEATH
SECURE NO MAN'S LAND
SABOTAGE
AREA DENIAL
RECOVER ASSETS
A TEMPTING TARGET
EXTEND BATTLE LINES
OVERWHELMING FORCE
DISPLAY OF MIGHT
```

### Missions Asymétriques (5)
```
SYPHONED POWER
ESTABLISH CONTROL
UNEVEN GROUND
DENIED RESOURCES
HOLD OUT
```

---

## Fichiers importants

```
Modèles:
  app/Models/PrimaryMission.php
  app/Models/SecondaryMission.php
  app/Models/AsymmetricPrimaryMission.php
  app/Models/TwistMission.php
  app/Models/Translation.php

Ressources Filament:
  app/Filament/Resources/PrimaryMissionResource.php
  app/Filament/Resources/SecondaryMissionResource.php
  app/Filament/Resources/AsymmetricPrimaryMissionResource.php
  app/Filament/Resources/TwistMissionResource.php

Pages d'import:
  app/Filament/Resources/*/Pages/ImportMissions.php

RelationManagers:
  app/Filament/Resources/*/RelationManagers/TranslationsRelationManager.php

Services:
  app/Services/TranslationService.php
  app/Services/IntelligentTranslationService.php

Observers:
  app/Observers/TranslationObserver.php
```

---

## Notes importantes

### Glossaire
- ❌ **DÉSACTIVÉ** : Le glossaire Warhammer n'est plus utilisé
- Traductions uniquement via DeepL
- Observer ne fait rien (retour immédiat)

### Traductions
- ✅ Automatiques lors de l'import
- ✅ Seulement `name` et `full_text`
- ✅ Statut `auto` par défaut
- ✅ Peuvent être éditées manuellement

### Unicité
- ✅ Index unique sur `(resource_type, resource_id, field, locale)`
- ✅ Permet les mises à jour sans erreur
- ✅ Évite les doublons

---

**Dernière mise à jour** : 2025-11-05
**Version** : 1.0
