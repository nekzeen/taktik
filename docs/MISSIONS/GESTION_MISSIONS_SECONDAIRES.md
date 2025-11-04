# 🎯 GESTION DES MISSIONS SECONDAIRES - PAGE DE TEST

**Date**: 4 novembre 2025
**Page**: `/player-matches/{id}/test-score`
**Service**: `SecondaryMissionService`

---

## 📋 OBJECTIF

La page de test des scores permet maintenant de gérer les missions secondaires selon les règles Warhammer 40k :
- **Missions Fixes** : objectifs permanents
- **Missions Tactiques** : renouvelées chaque phase de commandement

---

## 🎮 INTERFACE UTILISATEUR

### Section Missions Secondaires

La page affiche maintenant :

1. **Mission Secondaire** (si définie)
   - Titre en anglais et français
   - Texte complet en anglais et français
   - Conditions et scoring

2. **Type de Missions Secondaires**
   - Radio buttons : Missions Fixes / Missions Tactiques
   - Info dynamique selon le type sélectionné

3. **Missions Fixes** (par défaut)
   - Liste de toutes les missions secondaires actives
   - Checkboxes pour sélectionner exactement 2 missions
   - Compteur : "Sélectionnées : X/2"
   - Impossible de sélectionner plus de 2

4. **Missions Tactiques** (caché par défaut)
   - Bouton "Piocher 2 missions"
   - Affiche 2 missions aléatoires
   - Possibilité de retirer une mission (✕)

---

## 🎯 RÈGLES MISSIONS FIXES

**Définition** : Objectifs permanents tout au long de la bataille

**Sélection** :
- ✅ Sélectionner exactement 2 missions
- ✅ Ces missions restent actives pendant toute la bataille
- ✅ Elles peuvent être accomplies plusieurs fois
- ✅ Elles ne peuvent pas être défaussées

**Utilisation** :
1. Sélectionner 2 missions dans la liste
2. Mettre de côté le reste du deck
3. Garder les 2 missions visibles pendant la bataille
4. Accomplir les objectifs pour marquer des points

---

## 🎲 RÈGLES MISSIONS TACTIQUES

**Définition** : Missions renouvelées au début de chaque phase de commandement

**Sélection Initiale** :
- ✅ Piocher 2 cartes au début de la première phase de commandement
- ✅ Ces 2 cartes deviennent vos missions actives
- ✅ Elles restent actives jusqu'à accomplissement

**Renouvellement** :
- Au début de chaque phase de commandement suivante
- Si vous avez moins de 2 missions actives
- Piochez jusqu'à avoir 2 cartes

**Accomplissement** :
- À la fin du tour de chaque joueur
- Si vous avez marqué ≥ 1 PV : défaussez la mission (accomplie)
- Vous pouvez défausser volontairement d'autres missions
- Si vous le faites pendant votre tour : gagnez 1 PC

**Stratégie "Ordres Nouveaux"** (1 PC) :
- À la fin de votre phase de commandement
- Défaussez l'une de vos missions actives
- Piochez une nouvelle carte de mission secondaire

---

## 🔧 IMPLÉMENTATION TECHNIQUE

### Service : SecondaryMissionService

**Fichier** : `app/Services/SecondaryMissionService.php`

**Méthodes principales** :

```php
// Obtenir toutes les missions secondaires actives
getActiveMissions(): Collection

// Initialiser les missions fixes (2 sélectionnées)
initializeFixedMissions(array $selectedMissionIds): array

// Initialiser les missions tactiques (2 aléatoires)
initializeTacticalMissions(int $count = 2): Collection

// Appliquer la stratégie "Ordres Nouveaux"
applyNewOrdersStratagem(array $activeMissionIds, int $missionToRemoveId): Collection

// Marquer une mission comme accomplie
accomplishMission(array $activeMissionIds, int $missionId): array

// Obtenir les points de victoire
getVictoryPoints(SecondaryMission $mission): int

// Obtenir les règles
getFixedMissionsRules(): string
getTacticalMissionsRules(): string
```

### Vue : test-score.blade.php

**Sections ajoutées** :

1. **Mission Secondaire** (affichage)
   - Titre et texte complet
   - Traductions FR/EN

2. **Type de Missions Secondaires** (sélection)
   - Radio buttons : Fixed / Tactical
   - Info dynamique

3. **Missions Fixes** (gestion)
   - Liste avec checkboxes
   - Validation : max 2
   - Compteur

4. **Missions Tactiques** (gestion)
   - Bouton "Piocher 2 missions"
   - Affichage aléatoire
   - Possibilité de retirer

### JavaScript

**Fonctions** :

```javascript
// Basculer entre Missions Fixes et Tactiques
toggleSecondaryType(type)

// Valider le nombre de missions fixes
validateFixedMissions()

// Piocher 2 missions tactiques aléatoires
drawTacticalMissions()

// Retirer une mission tactique
removeTacticalMission(index)
```

---

## 📊 FLUX UTILISATEUR

### Scénario 1 : Missions Fixes

```
1. Utilisateur arrive sur la page
2. Voit "Missions Fixes" sélectionné par défaut
3. Voit la liste de toutes les missions secondaires
4. Sélectionne 2 missions (checkboxes)
5. Compteur affiche "Sélectionnées : 2/2"
6. Peut voir les 2 missions sélectionnées
7. Remplit le reste du formulaire
8. Soumet le formulaire
```

### Scénario 2 : Missions Tactiques

```
1. Utilisateur arrive sur la page
2. Sélectionne "Missions Tactiques"
3. Section change : affiche "Piocher 2 missions"
4. Clique sur "Piocher 2 missions"
5. 2 missions aléatoires s'affichent
6. Peut retirer une mission (✕) et en piocher une autre
7. Remplit le reste du formulaire
8. Soumet le formulaire
```

---

## 🎨 INTERFACE VISUELLE

### Missions Fixes

```
Type de missions secondaires
○ Missions Fixes
○ Missions Tactiques

ℹ️ Les Missions Fixes sont des objectifs permanents...

Sélectionner 2 Missions Fixes
☐ Mission 1
☐ Mission 2
☐ Mission 3
...
Sélectionnées : 0/2
```

### Missions Tactiques

```
Type de missions secondaires
○ Missions Fixes
○ Missions Tactiques

ℹ️ Les Missions Tactiques se renouvellent...

Missions Tactiques Actives
Vous piochez 2 cartes au début...

[Piocher 2 missions]

┌─────────────────────────┐
│ Mission 1 (FR)          │ ✕
│ Mission 1 (EN)          │
└─────────────────────────┘
┌─────────────────────────┐
│ Mission 2 (FR)          │ ✕
│ Mission 2 (EN)          │
└─────────────────────────┘
```

---

## 🔄 INTÉGRATION AVEC LE SCORING

### Points Secondaires

Les missions secondaires contribuent aux points secondaires :
- Chaque mission accomplie : 3-4 points (selon la mission)
- Maximum : 40 points secondaires
- Affichage dans le formulaire : "Points secondaires (max 40)"

### Calcul Total

```
Total Points = Points Primaires + Points Secondaires + Points Peinture
```

---

## 📝 RÉSUMÉ

**Missions Fixes** :
- ✅ 2 missions permanentes
- ✅ Sélection au début
- ✅ Accomplissables plusieurs fois
- ✅ Ne peuvent pas être défaussées

**Missions Tactiques** :
- ✅ 2 missions renouvelées
- ✅ Piochage aléatoire
- ✅ Accomplissement défausse la mission
- ✅ Stratégie "Ordres Nouveaux" disponible

**Page de Test** :
- ✅ Affichage de la mission secondaire
- ✅ Sélection du type (Fixes/Tactiques)
- ✅ Gestion des missions
- ✅ Intégration au scoring

