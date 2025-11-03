# 🎮 WORKFLOW RÉEL COMPLET DES MATCHS JOUEURS

**Date**: 3 novembre 2025
**Basé sur**: Code réel de PlayerMatch.php et PlayerMatchController.php

---

## 📊 STATUTS RÉELS VS AFFICHAGE

### Valeurs en Base de Données
```
status = 'open'      → Statut réel en base
status = 'confirmed' → Statut réel en base
status = 'completed' → Statut réel en base
status = 'cancelled' → Statut réel en base
```

### Affichage à l'Utilisateur (getStatusLabel())
```
status = 'open' + is_setup_validated = false  → "Configuration en cours" ⭐
status = 'open' + is_setup_validated = true   → "Ouvert"
status = 'confirmed'                          → "Confirmé"
status = 'completed'                          → "Terminé"
status = 'cancelled'                          → "Annulé"
```

---

## 🔄 WORKFLOW RÉEL COMPLET

### ÉTAPE 1 : CRÉATION DU MATCH

**Créateur (Gaël Morvan) crée le match :**

```
PlayerMatch::create([
    'creator_id' => 1,
    'type' => 'competitive',
    'army_points' => 2000,
    'faction' => 'Necrons',
    'detachment' => 'Szarekhan Dynasty',
    'city' => 'Paris',
    'department' => '75',
    'availability_type' => 'single',
    'available_at' => '2025-11-15 19:00',
    'status' => 'open',
    'is_setup_complete' => false,
    'is_setup_validated' => false,
])
```

**État en base :**
- `status` = `open`
- `is_setup_validated` = `false`
- `opponent_id` = `NULL`

**Affichage à l'utilisateur :**
- Statut : **"Configuration en cours"** ⭐

**Autres joueurs voient :**
- ❌ Le match N'APPARAÎT PAS dans la liste des matchs disponibles
- ❌ Raison : `canJoin()` retourne `false` (ligne 161 : `!$this->is_setup_validated`)

---

### ÉTAPE 2 : CONFIGURATION DU MATCH

**Créateur configure le match :**

```
$match->update([
    'primary_mission_id' => 1,
    'secondary_mission_id' => 5,
    'terrain_layout_id' => 2,
    'twist_mission_id' => 3,
    'deployment_mode' => 'Strike Force',
    'setup_mode' => 'random',
    'is_setup_complete' => true,
    'is_setup_validated' => true,  // ⭐ CLÉS !
])
```

**État en base :**
- `status` = `open`
- `is_setup_validated` = `true` ⭐
- `opponent_id` = `NULL`

**Affichage à l'utilisateur :**
- Statut : **"Ouvert"** ⭐

**Autres joueurs voient :**
- ✅ Le match APPARAÎT dans la liste des matchs disponibles
- ✅ Raison : `canJoin()` retourne `true` (toutes les conditions satisfaites)

---

### ÉTAPE 3 : DEMANDES DE PARTICIPATION

**Autres joueurs peuvent maintenant demander à participer :**

```
PlayerMatchRequest::create([
    'player_match_id' => 10,
    'requester_id' => 2,
    'faction' => 'Astra Militarum',
    'detachment' => 'Cadian',
    'status' => 'pending',
])
```

**Conditions pour que la demande soit possible :**
- ✅ `status = 'open'`
- ✅ `opponent_id = NULL`
- ✅ `is_setup_validated = true` ⭐
- ✅ Match disponible (date pas expirée)
- ✅ Demandeur ≠ créateur

---

### ÉTAPE 4 : ACCEPTATION D'UNE DEMANDE

**Créateur accepte une demande :**

```
// Accepter la demande
$request->update(['status' => 'accepted'])

// Mettre à jour le match
$match->update([
    'opponent_id' => 2,
    'status' => 'confirmed',  // ⭐ CHANGEMENT !
])
```

**État en base :**
- `status` = `confirmed` ⭐
- `opponent_id` = `2`
- `is_setup_validated` = `true`

**Affichage à l'utilisateur :**
- Statut : **"Confirmé"**

**Autres demandes :**
- Automatiquement rejetées (pas de code, juste pas acceptées)

---

### ÉTAPE 5 : MATCH EN COURS

**Les deux joueurs jouent et saisissent les scores :**

```
$match->update([
    'creator_score' => 85,
    'opponent_score' => 72,
    'played_at' => now(),
])

// Déterminer le gagnant
$match->determineWinner()  // Calcule winner_id

$match->update([
    'status' => 'completed',
    'winner_id' => 1,
    'is_draw' => false,
])
```

**État en base :**
- `status` = `completed`
- `creator_score` = `85`
- `opponent_score` = `72`
- `winner_id` = `1`
- `is_draw` = `false`

**Affichage à l'utilisateur :**
- Statut : **"Terminé"**

---

## 🔐 PERMISSIONS ET VÉRIFICATIONS

### Méthode `canJoin(User $user)` (ligne 155-162)

```php
public function canJoin(User $user): bool
{
    return $this->status === 'open'           // ✅ Match ouvert
        && $this->opponent_id === null        // ✅ Pas d'adversaire
        && $this->creator_id !== $user->id    // ✅ Pas le créateur
        && $this->isAvailable()                // ✅ Date pas expirée
        && $this->is_setup_validated;         // ✅ CONFIGURATION VALIDÉE ⭐
}
```

**Un joueur ne peut rejoindre QUE SI `is_setup_validated = true`**

### Méthode `canSetScore(User $user)` (ligne 164-168)

```php
public function canSetScore(User $user): bool
{
    return $this->status === 'confirmed'      // ✅ Match confirmé
        && $this->creator_id === $user->id;   // ✅ Créateur du match
}
```

**Seul le créateur peut saisir les scores quand le match est confirmé**

---

## 📋 RÉSUMÉ DU WORKFLOW RÉEL

| Étape | Statut BD | is_setup_validated | Affichage | Autres joueurs |
|-------|-----------|-------------------|-----------|----------------|
| 1. Création | `open` | `false` | Configuration en cours | ❌ Invisible |
| 2. Configuration | `open` | `true` | Ouvert | ✅ Visible |
| 3. Demandes | `open` | `true` | Ouvert | ✅ Peuvent demander |
| 4. Acceptation | `confirmed` | `true` | Confirmé | ❌ Pas d'autres demandes |
| 5. Clôture | `completed` | `true` | Terminé | - |

---

## 🎯 POINTS CLÉS DÉCOUVERTS

### 1. Deux Niveaux de Statut
- **Statut en base** : `open`, `confirmed`, `completed`, `cancelled`
- **Affichage** : "Configuration en cours", "Ouvert", "Confirmé", "Terminé", "Annulé"

### 2. Configuration Obligatoire
- `is_setup_validated = true` est **OBLIGATOIRE** pour que les autres joueurs voient le match
- Sans cette validation, le match reste invisible

### 3. Logique Métier Complexe
- La validation de configuration est la **clés** pour passer de "Configuration en cours" à "Ouvert"
- C'est une **logique métier**, pas juste un changement de statut

### 4. Filtre dans l'Index
```php
$availableMatches = PlayerMatch::where('status', 'open')
    ->where('opponent_id', null)
    ->get()
    ->filter(fn($match) => $match->isAvailable())  // Date pas expirée
    ->filter(fn($match) => !$user || $match->creator_id !== $user->id)
```

Les matchs avec `is_setup_validated = false` sont **FILTRÉS** par `canJoin()` dans le contrôleur

---

## 🔍 EXEMPLE RÉEL : MATCH 10 (GAËL MORVAN)

```
Match 10 - Créé par Gaël Morvan
├─ status: 'open'
├─ is_setup_validated: false
├─ Affichage: "Configuration en cours"
└─ Autres joueurs: ❌ Invisible

[Gaël configure le match]

Match 10 - Après configuration
├─ status: 'open'
├─ is_setup_validated: true
├─ Affichage: "Ouvert"
└─ Autres joueurs: ✅ Visible et peuvent demander
```

---

## 📝 LOGIQUE MÉTIER RÉSUMÉE

```
CRÉATION
    ↓
    status = 'open'
    is_setup_validated = false
    Affichage: "Configuration en cours"
    Autres joueurs: ❌ Invisible
    
    ↓ [Créateur configure]
    
CONFIGURATION VALIDÉE
    ↓
    status = 'open'
    is_setup_validated = true
    Affichage: "Ouvert"
    Autres joueurs: ✅ Visible
    
    ↓ [Autres joueurs demandent]
    
DEMANDES REÇUES
    ↓
    [Créateur accepte une demande]
    
    ↓
    
ACCEPTATION
    ↓
    status = 'confirmed'
    opponent_id = défini
    Affichage: "Confirmé"
    
    ↓ [Joueurs jouent]
    
CLÔTURE
    ↓
    status = 'completed'
    winner_id = défini
    Affichage: "Terminé"
```

---

**Audit Réel Complété**
**Workflow Documenté**
**Logique Métier Expliquée**

