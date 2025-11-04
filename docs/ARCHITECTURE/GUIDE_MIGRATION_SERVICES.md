# 🔄 GUIDE DE MIGRATION VERS LES SERVICES

**Date**: 3 novembre 2025
**Statut**: Guide progressif et optionnel
**Risque**: Zéro - chaque étape peut être annulée

---

## 📋 OBJECTIF

Migrer progressivement le code existant vers les Services, une étape à la fois, sans risque de dysfonctionnement.

---

## 🛡️ PRINCIPES DE MIGRATION

1. ✅ **Une étape à la fois** - Migrer un contrôleur à la fois
2. ✅ **Tester après chaque étape** - Vérifier que tout fonctionne
3. ✅ **Rollback facile** - Chaque étape peut être annulée
4. ✅ **Zéro modification du code existant** - Ajouter, ne pas modifier
5. ✅ **Garder la compatibilité** - L'application continue à fonctionner

---

## 📊 SERVICES DISPONIBLES

### 1. PlayerMatchService
- `getAvailableMatches(User $user)` - Matchs disponibles
- `getConfirmedMatches(User $user)` - Matchs confirmés
- `getCompletedMatches(User $user)` - Matchs terminés
- `getProposedMatches(User $user)` - Matchs proposés
- `getUserStats(User $user)` - Statistiques utilisateur
- `canJoin(PlayerMatch $match, User $user)` - Permission de rejoindre
- `canSetScore(PlayerMatch $match, User $user)` - Permission de saisir scores
- `getStatusLabel(PlayerMatch $match)` - Label du statut
- `getAvailabilityDisplay(PlayerMatch $match)` - Affichage disponibilité
- `getLocationDisplay(PlayerMatch $match)` - Affichage localisation

### 2. MatchPermissionService
- `canJoin(PlayerMatch $match, User $user)` - Permission de rejoindre
- `canSetScore(PlayerMatch $match, User $user)` - Permission de saisir scores
- `canAcceptRequest(PlayerMatch $match, User $user)` - Permission d'accepter demande
- `canRejectRequest(PlayerMatch $match, User $user)` - Permission de rejeter demande
- `canEdit(PlayerMatch $match, User $user)` - Permission de modifier
- `canCancel(PlayerMatch $match, User $user)` - Permission d'annuler
- `canView(PlayerMatch $match, User $user)` - Permission de voir
- `getErrorMessage(PlayerMatch $match, User $user, string $action)` - Message d'erreur

---

## 🔄 ÉTAPES DE MIGRATION

### ÉTAPE 1 : Migrer PlayerMatchController::index()

**Avant** (code actuel) :
```php
public function index(Request $request)
{
    $user = Auth::user();
    
    // Matchs disponibles
    $availableMatches = PlayerMatch::where('status', 'open')
        ->where('opponent_id', null)
        ->with(['creator'])
        ->get()
        ->filter(fn($match) => $match->isAvailable())
        ->filter(fn($match) => !$user || $match->creator_id !== $user->id)
        ->sort(...)
        ->values();
    
    // Mes matchs confirmés
    $myConfirmedMatches = $user ? PlayerMatch::where(function ($q) use ($user) {
        $q->where('creator_id', $user->id)
            ->orWhere('opponent_id', $user->id);
    })
        ->where('status', 'confirmed')
        ->get() : collect();
    
    return view('player-matches.index', compact(...));
}
```

**Après** (avec Service) :
```php
protected $matchService;

public function __construct(PlayerMatchService $matchService)
{
    $this->matchService = $matchService;
}

public function index(Request $request)
{
    $user = Auth::user();
    
    // Matchs disponibles
    $availableMatches = $this->matchService->getAvailableMatches($user);
    
    // Mes matchs confirmés
    $myConfirmedMatches = $user ? $this->matchService->getConfirmedMatches($user) : collect();
    
    return view('player-matches.index', compact(...));
}
```

**Avantages** :
- ✅ Code plus lisible
- ✅ Logique métier centralisée
- ✅ Facile à tester

**Rollback** :
```bash
git checkout app/Http/Controllers/PlayerMatchController.php
```

---

### ÉTAPE 2 : Migrer PlayerMatchRequestController::create()

**Avant** :
```php
public function create(PlayerMatch $playerMatch)
{
    if (Auth::id() === $playerMatch->creator_id) {
        return redirect()->back()->with('error', 'Vous ne pouvez pas répondre à votre propre match');
    }
    
    if (!$playerMatch->canJoin(Auth::user())) {
        return redirect()->back()->with('error', 'Vous ne pouvez pas rejoindre ce match');
    }
    
    // ...
}
```

**Après** :
```php
protected $permissionService;

public function __construct(MatchPermissionService $permissionService)
{
    $this->permissionService = $permissionService;
}

public function create(PlayerMatch $playerMatch)
{
    if (!$this->permissionService->canJoin($playerMatch, Auth::user())) {
        $message = $this->permissionService->getErrorMessage($playerMatch, Auth::user(), 'join');
        return redirect()->back()->with('error', $message);
    }
    
    // ...
}
```

**Avantages** :
- ✅ Messages d'erreur centralisés
- ✅ Logique de permission claire
- ✅ Facile à maintenir

---

### ÉTAPE 3 : Migrer PlayerMatchRequestController::accept()

**Avant** :
```php
public function accept(PlayerMatchRequest $request)
{
    $match = $request->playerMatch;
    
    if (Auth::id() !== $match->creator_id) {
        return redirect()->back()->with('error', 'Non autorisé');
    }
    
    $request->update(['status' => 'accepted']);
    $match->update([
        'opponent_id' => $request->requester_id,
        'status' => 'confirmed',
    ]);
    
    return redirect()->route('player-matches.show', $match)
        ->with('success', 'Demande acceptée !');
}
```

**Après** :
```php
protected $permissionService;

public function __construct(MatchPermissionService $permissionService)
{
    $this->permissionService = $permissionService;
}

public function accept(PlayerMatchRequest $request)
{
    $match = $request->playerMatch;
    
    if (!$this->permissionService->canAcceptRequest($match, Auth::user())) {
        $message = $this->permissionService->getErrorMessage($match, Auth::user(), 'accept_request');
        return redirect()->back()->with('error', $message);
    }
    
    $request->update(['status' => 'accepted']);
    $match->update([
        'opponent_id' => $request->requester_id,
        'status' => 'confirmed',
    ]);
    
    return redirect()->route('player-matches.show', $match)
        ->with('success', 'Demande acceptée !');
}
```

---

### ÉTAPE 4 : Utiliser les Enums (Optionnel)

**Avant** :
```php
if ($match->status === 'open') {
    // ...
}
```

**Après** :
```php
use App\Enums\MatchStatus;

if ($match->status === MatchStatus::OPEN->value) {
    // ...
}

// Ou mieux encore:
if (MatchStatus::from($match->status)->isActive()) {
    // ...
}
```

---

## 📝 CHECKLIST DE MIGRATION

Pour chaque étape :

- [ ] Lire le code actuel
- [ ] Identifier la logique métier
- [ ] Créer la méthode dans le Service
- [ ] Modifier le contrôleur pour utiliser le Service
- [ ] Tester manuellement
- [ ] Vérifier les logs
- [ ] Committer les changements
- [ ] Documenter les changements

---

## 🧪 TESTS UNITAIRES

Avant de migrer, créer des tests :

```bash
php artisan test tests/Unit/Services/PlayerMatchServiceTest.php
```

Les tests valident que les Services fonctionnent correctement.

---

## 🔄 ROLLBACK RAPIDE

Si problème lors d'une étape :

```bash
# Voir les changements
git diff app/Http/Controllers/PlayerMatchController.php

# Annuler les changements
git checkout app/Http/Controllers/PlayerMatchController.php

# Ou annuler le dernier commit
git revert HEAD
```

---

## 📊 PROGRESSION

```
ÉTAPE 1: PlayerMatchController::index()
├─ Status: À faire
├─ Risque: Zéro
└─ Rollback: 1 commande

ÉTAPE 2: PlayerMatchRequestController::create()
├─ Status: À faire
├─ Risque: Zéro
└─ Rollback: 1 commande

ÉTAPE 3: PlayerMatchRequestController::accept()
├─ Status: À faire
├─ Risque: Zéro
└─ Rollback: 1 commande

ÉTAPE 4: Utiliser les Enums
├─ Status: À faire
├─ Risque: Zéro
└─ Rollback: 1 commande
```

---

## 🎯 RÉSUMÉ

**Migration Progressive** :
- ✅ Une étape à la fois
- ✅ Tester après chaque étape
- ✅ Rollback facile
- ✅ Zéro risque

**Bénéfices** :
- ✅ Code plus clair
- ✅ Logique métier centralisée
- ✅ Facile à tester
- ✅ Facile à maintenir

**Prochaines Étapes** :
1. Migrer PlayerMatchController::index()
2. Migrer PlayerMatchRequestController
3. Utiliser les Enums
4. Créer d'autres Services

