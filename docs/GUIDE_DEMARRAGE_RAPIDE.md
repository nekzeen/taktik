# 🚀 GUIDE DE DÉMARRAGE RAPIDE - POUR CHAQUE DEMANDE

**Date**: 3 novembre 2025
**Objectif**: Vous aider à comprendre rapidement comment je vais traiter votre demande

---

## 📋 AVANT CHAQUE DEMANDE

### ÉTAPE 1 : Consulter la Mémoire Système

Je consulterai automatiquement :
- ✅ Procédure d'audit systématique
- ✅ Documentation centralisée
- ✅ Architecture réelle
- ✅ Services disponibles

### ÉTAPE 2 : Lire la Documentation

Je lirai :
1. **docs/CORE/ARCHITECTURE_REELLE.md** - Comprendre la structure
2. **docs/ARCHITECTURE/DESIGN_PATTERNS_ACTUELS.md** - Comprendre le design
3. **docs/ARCHITECTURE/DIAGRAMMES_VISUELS.md** - Visualiser les flux
4. **docs/ARCHITECTURE/SERVICES_DISPONIBLES.md** - Voir les Services disponibles
5. **Docs spécifiques au domaine** - Selon votre demande

### ÉTAPE 3 : Identifier le Type de Demande

Votre demande concerne :
- [ ] Matchs joueurs (PlayerMatch)
- [ ] Tournois (Tournament)
- [ ] Missions (Primaires, Secondaires, Péripéties)
- [ ] Traductions (Translation, WarhammerGlossary)
- [ ] Utilisateurs (User)
- [ ] Admin Filament
- [ ] Routes/API
- [ ] Autre

---

## 🎯 TYPES DE DEMANDES COURANTS

### TYPE 1 : Ajouter une Fonctionnalité aux Matchs Joueurs

**Fichiers à consulter** :
- docs/TOURNAMENTS/GESTION_TOURNOIS_MATCHS.md
- docs/ARCHITECTURE/SERVICES_DISPONIBLES.md (PlayerMatchService)
- docs/ADMIN/FILAMENT_ADMIN_PANEL.md (Matchs Joueurs)
- docs/API/ROUTES_ET_MIDDLEWARES.md (Routes)

**Services à utiliser** :
- `PlayerMatchService` - Logique métier
- `MatchPermissionService` - Permissions
- `MatchSetupService` - Configuration

**Modèles concernés** :
- `PlayerMatch`
- `PlayerMatchRequest`
- `PlayerAvailability`

**Étapes** :
1. Lire le workflow actuel
2. Identifier où ajouter la fonctionnalité
3. Utiliser les Services existants
4. Ajouter des tests
5. Tester manuellement

---

### TYPE 2 : Modifier une Traduction ou le Glossaire

**Fichiers à consulter** :
- docs/MISSIONS/TRADUCTIONS_COMPLETE.md
- docs/ARCHITECTURE/SERVICES_DISPONIBLES.md (TranslationManagementService)
- docs/ADMIN/FILAMENT_ADMIN_PANEL.md (Traductions)

**Services à utiliser** :
- `TranslationManagementService` - Gestion des traductions
- `MissionService` - Missions et traductions

**Modèles concernés** :
- `Translation`
- `WarhammerGlossary`
- `PrimaryMission`, `SecondaryMission`, `TwistMission`

**Étapes** :
1. Comprendre le flux de traduction
2. Identifier le terme à modifier
3. Utiliser l'Observer pour la propagation
4. Vérifier le glossaire
5. Tester la propagation

---

### TYPE 3 : Créer un Tournoi ou Gérer les Matchs

**Fichiers à consulter** :
- docs/TOURNAMENTS/GESTION_TOURNOIS_MATCHS.md
- docs/ARCHITECTURE/SERVICES_DISPONIBLES.md (TournamentService)
- docs/ADMIN/FILAMENT_ADMIN_PANEL.md (Tournois)

**Services à utiliser** :
- `TournamentService` - Logique métier
- `MatchSetupService` - Configuration des matchs

**Modèles concernés** :
- `Tournament`
- `TournamentMatch`
- `TournamentMissionPool`

**Étapes** :
1. Lire le workflow du tournoi
2. Identifier l'étape concernée
3. Utiliser les Services
4. Vérifier les permissions
5. Tester le workflow

---

### TYPE 4 : Ajouter une Route ou Modifier l'API

**Fichiers à consulter** :
- docs/API/ROUTES_ET_MIDDLEWARES.md
- docs/ARCHITECTURE/SERVICES_DISPONIBLES.md
- docs/ADMIN/FILAMENT_ADMIN_PANEL.md

**Étapes** :
1. Consulter les routes existantes
2. Identifier le pattern à suivre
3. Ajouter la route
4. Créer le contrôleur
5. Utiliser les Services
6. Ajouter les tests
7. Tester manuellement

---

### TYPE 5 : Modifier l'Admin Filament

**Fichiers à consulter** :
- docs/ADMIN/FILAMENT_ADMIN_PANEL.md
- docs/ARCHITECTURE/SERVICES_DISPONIBLES.md

**Étapes** :
1. Consulter la Resource Filament
2. Identifier le champ/relation à modifier
3. Modifier la Resource
4. Tester dans l'admin
5. Vérifier les permissions

---

## 🔍 PROCÉDURE DE DIAGNOSTIC

Si vous décrivez un problème, je vais :

1. **Consulter la documentation** pour comprendre le fonctionnement attendu
2. **Lire le code réel** pour voir l'implémentation
3. **Vérifier les données** en base de données
4. **Tracer le flux** pour identifier où ça casse
5. **Proposer une solution** basée sur l'architecture

**Exemple** :
```
Vous: "Les traductions ne se propagent pas"
Moi:
1. Lire TRADUCTIONS_COMPLETE.md
2. Vérifier TranslationObserver.php
3. Vérifier les logs
4. Tracer le flux de propagation
5. Identifier le problème
6. Proposer une solution
```

---

## 📊 CHECKLIST POUR CHAQUE DEMANDE

- [ ] Lire la mémoire système
- [ ] Consulter la documentation pertinente
- [ ] Identifier le type de demande
- [ ] Lire le code réel (modèles, contrôleurs, services)
- [ ] Vérifier les données en base
- [ ] Proposer une solution
- [ ] Implémenter la solution
- [ ] Ajouter des tests
- [ ] Tester manuellement
- [ ] Documenter les changements

---

## 🎯 RÉSUMÉ

**Pour chaque demande** :
1. ✅ Consulter la mémoire système
2. ✅ Lire la documentation pertinente
3. ✅ Identifier le type de demande
4. ✅ Lire le code réel
5. ✅ Proposer une solution
6. ✅ Implémenter
7. ✅ Tester
8. ✅ Documenter

**Cela rend notre collaboration** :
- ✅ Plus rapide
- ✅ Plus efficace
- ✅ Plus fiable
- ✅ Moins d'allers-retours

