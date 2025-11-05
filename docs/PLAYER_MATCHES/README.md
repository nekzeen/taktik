# 🎮 DOCUMENTATION - MATCHS SIMPLES (PLAYER MATCHES)

**Version**: 1.0 | **Date**: 2025-11-05 | **Statut**: ✅ Complète

---

## 📖 FICHIERS

### 1. [PLAYER_MATCHES_INDEX.md](./PLAYER_MATCHES_INDEX.md)
**Point d'entrée principal**
- Index et accès rapide
- Recherche par sujet
- Démarrage rapide
- Fichiers clés
- Checklist avant déploiement

### 2. [PLAYER_MATCHES_DOCUMENTATION_PART1.md](./PLAYER_MATCHES_DOCUMENTATION_PART1.md)
**Fondamentaux et structure**
- Vue d'ensemble
- Structure de base (table, colonnes)
- Flux de vie d'un match (4 états)
- Logique métier (6 processus)
- Modèles et relations
- Routes principales
- Permissions par action

### 3. [PLAYER_MATCHES_DOCUMENTATION_PART2.md](./PLAYER_MATCHES_DOCUMENTATION_PART2.md)
**Implémentation et dépannage**
- Services
- Vues et interfaces
- Configuration du match
- Scoring et résultats
- Checklist de correction (8 étapes)
- Erreurs courantes et solutions
- Fichiers importants
- Commandes utiles
- Support rapide

---

## 🚀 DÉMARRAGE RAPIDE

### Je dois corriger un problème
1. Ouvrir [PLAYER_MATCHES_INDEX.md](./PLAYER_MATCHES_INDEX.md)
2. Chercher dans "Recherche par sujet"
3. Aller à la section correspondante
4. Appliquer la solution

### Je dois comprendre le flux
1. Lire [PLAYER_MATCHES_DOCUMENTATION_PART1.md](./PLAYER_MATCHES_DOCUMENTATION_PART1.md)
2. Comprendre les 4 états
3. Comprendre les 6 processus

### Je dois implémenter
1. Vérifier la structure (PART 1)
2. Vérifier les modèles (PART 1)
3. Vérifier les routes (PART 1)
4. Vérifier les services (PART 2)
5. Vérifier les vues (PART 2)

---

## 📊 RÉSUMÉ

### États d'un match
- `open` + `is_setup_validated=false` → Configuration en cours
- `open` + `is_setup_validated=true` → Ouvert
- `confirmed` → Confirmé
- `completed` → Terminé

### Permissions
| Action | Créateur | Adversaire | Autre |
|--------|----------|-----------|-------|
| Voir | ✅ | ✅ | ❌ |
| Configurer | ✅ | ❌ | ❌ |
| Valider | ✅ | ❌ | ❌ |
| Rejoindre | ❌ | ✅ | ✅ |
| Scores | ✅ | ❌ | ❌ |

### Configuration
- Mode aléatoire: Utilise pool de missions
- Mode manuel: Sélection manuelle
- 8 modes de déploiement
- 20 pools de missions (A-T)

---

## 📁 FICHIERS CLÉS

### Modèles
- `app/Models/PlayerMatch.php`
- `app/Models/PlayerMatchRequest.php`

### Contrôleurs
- `app/Http/Controllers/PlayerMatchController.php`
- `app/Http/Controllers/MatchSetupController.php`
- `app/Http/Controllers/PlayerMatchRequestController.php`

### Services
- `app/Services/MatchSetupService.php`
- `app/Services/PlayerMatchService.php`
- `app/Services/MatchPermissionService.php`

### Vues
- `resources/views/player-matches/index.blade.php`
- `resources/views/player-matches/show.blade.php`
- `resources/views/matches/setup.blade.php`
- `resources/views/matches/summary.blade.php`
- `resources/views/matches/score.blade.php`

---

**← [Retour à la documentation principale](../README.md)**
