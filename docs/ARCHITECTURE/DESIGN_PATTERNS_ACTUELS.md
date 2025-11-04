# 🏗️ DESIGN PATTERNS ACTUELS DE L'APPLICATION

**Date**: 3 novembre 2025
**Objectif**: Documenter le design actuel pour faciliter la compréhension et les futures améliorations

---

## 📊 ARCHITECTURE ACTUELLE

### 1. MODÈLE - CONTRÔLEUR - VUE (MVC)

```
Utilisateur
    ↓
Routes (routes/web.php)
    ↓
Contrôleurs (app/Http/Controllers/)
    ↓
Modèles (app/Models/)
    ↓
Vues (resources/views/)
```

### 2. LOGIQUE MÉTIER DISTRIBUÉE

**Actuellement, la logique métier est dispersée :**

```
Modèles (PlayerMatch.php)
├─ getStatusLabel()          → Affichage du statut
├─ canJoin()                 → Permission de rejoindre
├─ canSetScore()             → Permission de saisir scores
├─ isAvailable()             → Vérification disponibilité
└─ determineWinner()         → Calcul du gagnant

Contrôleurs (PlayerMatchController.php)
├─ index()                   → Filtres et affichage
├─ create()                  → Création
├─ store()                   → Sauvegarde
└─ show()                    → Détails

Services (MatchSetupService.php)
└─ randomizeMatch()          → Configuration aléatoire
```

---

## 🔍 EXEMPLE : WORKFLOW DU MATCH JOUEUR

### Flux Actuel

```
1. CRÉATION
   PlayerMatchController::store()
   └─ PlayerMatch::create()
      └─ Modèle sauvegarde les données

2. AFFICHAGE LISTE
   PlayerMatchController::index()
   └─ Récupère les matchs
   └─ Filter avec canJoin()
   └─ Affiche avec getStatusLabel()

3. VÉRIFICATION PERMISSION
   PlayerMatchController::show()
   └─ Appelle $match->canJoin($user)
   └─ Affiche le bouton "Rejoindre" si true

4. ACCEPTATION DEMANDE
   PlayerMatchRequestController::accept()
   └─ Accepte la demande
   └─ Met à jour le match
   └─ Change status et opponent_id

5. SAISIE SCORES
   PlayerMatchController::setScore()
   └─ Vérifie canSetScore()
   └─ Sauvegarde les scores
   └─ Appelle determineWinner()
```

### Problèmes Identifiés

```
❌ Logique métier dans les modèles
   └─ Difficile à tester
   └─ Difficile à réutiliser

❌ Filtres dans les contrôleurs
   └─ Logique métier mélangée à la présentation
   └─ Difficile à maintenir

❌ Pas de constantes pour les statuts
   └─ "open", "confirmed", "completed" en dur
   └─ Risque d'erreurs

❌ Statuts implicites
   └─ "Configuration en cours" calculé dans getStatusLabel()
   └─ Pas évident pour un nouveau développeur

❌ Pas de Service Layer
   └─ Pas de réutilisabilité
   └─ Pas de centralisation
```

---

## 📋 STATUTS ACTUELS

### Valeurs en Base de Données

```php
enum('status', ['open', 'confirmed', 'completed', 'cancelled'])
boolean('is_setup_validated')
```

### Affichage à l'Utilisateur

```php
// PlayerMatch::getStatusLabel()
if ($this->status === 'open' && !$this->is_setup_validated) {
    return 'Configuration en cours';  // ⭐ Implicite
}

return match($this->status) {
    'open' => 'Ouvert',
    'confirmed' => 'Confirmé',
    'completed' => 'Terminé',
    'cancelled' => 'Annulé',
};
```

### Matrice de Statuts

| Statut BD | is_setup_validated | Affichage | Visible | Peut rejoindre |
|-----------|-------------------|-----------|---------|----------------|
| open | false | Configuration en cours | ❌ | ❌ |
| open | true | Ouvert | ✅ | ✅ |
| confirmed | true | Confirmé | ✅ | ❌ |
| completed | true | Terminé | ✅ | ❌ |
| cancelled | true | Annulé | ✅ | ❌ |

---

## 🔐 PERMISSIONS ACTUELLES

### Méthodes de Vérification

```php
// PlayerMatch::canJoin(User $user)
return $this->status === 'open'
    && $this->opponent_id === null
    && $this->creator_id !== $user->id
    && $this->isAvailable()
    && $this->is_setup_validated;  // ⭐ Clés

// PlayerMatch::canSetScore(User $user)
return $this->status === 'confirmed'
    && $this->creator_id === $user->id;

// PlayerMatch::isAvailable()
if ($this->availability_type === 'single') {
    return $this->available_at !== null && $this->available_at >= now();
}
return $this->available_from !== null && $this->available_to !== null && $this->available_to >= now();
```

### Matrice de Permissions

| Action | Condition | Où vérifiée |
|--------|-----------|-------------|
| Voir le match | status='open' + is_setup_validated=true | PlayerMatchController::index() |
| Rejoindre | canJoin() | PlayerMatchRequestController::create() |
| Accepter demande | creator_id = user_id | PlayerMatchRequestController::accept() |
| Saisir scores | canSetScore() | PlayerMatchController::setScore() |

---

## 🎯 FLUX COMPLET : MATCH JOUEUR

```
┌─────────────────────────────────────────────────────────────┐
│ 1. CRÉATION (status='open', is_setup_validated=false)       │
│    └─ Affichage: "Configuration en cours"                   │
│    └─ Visible: ❌ NON                                        │
└─────────────────────────────────────────────────────────────┘
                          ↓
                  [Créateur configure]
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. CONFIGURATION (status='open', is_setup_validated=true)   │
│    └─ Affichage: "Ouvert"                                   │
│    └─ Visible: ✅ OUI                                       │
└─────────────────────────────────────────────────────────────┘
                          ↓
              [Autres joueurs voient le match]
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. DEMANDES (status='open', is_setup_validated=true)        │
│    └─ Joueurs demandent à participer                        │
│    └─ Statut demande: pending                               │
└─────────────────────────────────────────────────────────────┘
                          ↓
              [Créateur accepte une demande]
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. ACCEPTATION (status='confirmed', opponent_id=défini)     │
│    └─ Affichage: "Confirmé"                                 │
│    └─ Autres demandes: rejetées                             │
└─────────────────────────────────────────────────────────────┘
                          ↓
                  [Joueurs jouent]
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 5. SAISIE SCORES (status='confirmed')                       │
│    └─ Créateur saisit les scores                            │
│    └─ determineWinner() calcule le gagnant                  │
└─────────────────────────────────────────────────────────────┘
                          ↓
              [Créateur valide les scores]
                          ↓
┌─────────────────────────────────────────────────────────────┐
│ 6. CLÔTURE (status='completed', winner_id=défini)           │
│    └─ Affichage: "Terminé"                                  │
│    └─ Match archivé                                         │
└─────────────────────────────────────────────────────────────┘
```

---

## 📁 STRUCTURE DES FICHIERS

### Modèles
```
app/Models/
├─ PlayerMatch.php              (Logique métier + données)
├─ PlayerMatchRequest.php       (Demandes)
├─ PlayerAvailability.php       (Disponibilités)
├─ Tournament.php               (Tournois)
├─ TournamentMatch.php          (Matchs tournoi)
└─ ...
```

### Contrôleurs
```
app/Http/Controllers/
├─ PlayerMatchController.php         (CRUD + filtres)
├─ PlayerMatchRequestController.php  (Demandes)
├─ TournamentController.php          (Tournois)
└─ ...
```

### Services
```
app/Services/
├─ MatchSetupService.php        (Configuration aléatoire)
└─ (Autres services à créer)
```

### Vues
```
resources/views/
├─ player-matches/
│  ├─ index.blade.php           (Liste des matchs)
│  ├─ show.blade.php            (Détails du match)
│  ├─ create.blade.php          (Création)
│  └─ edit.blade.php            (Édition)
├─ player-match-requests/
│  ├─ create.blade.php          (Demande de participation)
│  └─ index.blade.php           (Gestion des demandes)
└─ ...
```

---

## 🔄 INTERACTIONS ENTRE COMPOSANTS

### Modèles ↔ Contrôleurs

```
PlayerMatchController
    ↓
PlayerMatch::create()
PlayerMatch::find()
PlayerMatch::where()
    ↓
Modèle retourne les données
    ↓
Contrôleur appelle getStatusLabel()
Contrôleur appelle canJoin()
```

### Contrôleurs ↔ Vues

```
PlayerMatchController::index()
    ↓
Retourne $availableMatches
    ↓
Vue affiche avec {{ $match->getStatusLabel() }}
Vue affiche bouton "Rejoindre" si canJoin()
```

### Services ↔ Modèles

```
MatchSetupService::randomizeMatch()
    ↓
Récupère TournamentMissionPool
Récupère TerrainLayout
Récupère TwistMission
    ↓
Met à jour PlayerMatch
```

---

## 📝 RÉSUMÉ DU DESIGN ACTUEL

### Points Forts
- ✅ Simple et direct
- ✅ Fonctionne bien
- ✅ Facile à déboguer
- ✅ Peu de dépendances

### Points Faibles
- ❌ Logique métier dispersée
- ❌ Pas de réutilisabilité
- ❌ Statuts implicites
- ❌ Difficile à tester
- ❌ Pas de constantes

### Opportunités d'Amélioration
- 🔄 Créer une Service Layer
- 🔄 Créer des Enums pour les statuts
- 🔄 Centraliser la logique métier
- 🔄 Améliorer la testabilité
- 🔄 Documenter les patterns

---

**Prochaine étape**: Créer les Services et Enums sans modifier le code existant

