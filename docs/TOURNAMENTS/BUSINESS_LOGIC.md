# 🎮 Logique Métier des Tournois W40K

**Date**: 5 novembre 2025  
**Version**: 1.0  
**Statut**: ✅ Documentation complète

---

## 📋 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Cycle de vie d'un tournoi](#cycle-de-vie-dun-tournoi)
3. [Formats de tournoi](#formats-de-tournoi)
4. [Système de points](#système-de-points)
5. [Génération des matchs](#génération-des-matchs)
6. [Classement et ranking](#classement-et-ranking)
7. [Inscriptions et listes d'armée](#inscriptions-et-listes-darmée)
8. [Statuts et transitions](#statuts-et-transitions)

---

## Vue d'ensemble

### 🎯 Objectif principal
Gérer des tournois de Warhammer 40K avec différents formats, générer des matchs, et calculer les classements.

### 🔑 Entités principales
- **Tournament**: Le tournoi lui-même
- **ArmyList**: Listes d'armée des participants
- **TournamentMatch**: Les matchs du tournoi
- **User**: Les joueurs

### 📊 Données clés
- **Format**: elimination, swiss, league
- **Taille d'armée**: incursion (1000 pts), strike_force (2000 pts), onslaught (3000 pts)
- **Statuts**: draft, registration_open, registration_closed, in_progress, completed

---

## Cycle de vie d'un tournoi

### Phase 1: Création (Draft)
```
Status: draft
- Organisateur crée le tournoi
- Définit les paramètres (nom, format, taille d'armée)
- Définit les dates (début, fin, deadline d'inscription)
- Définit le nombre maximum de joueurs
```

### Phase 2: Inscriptions ouvertes (Registration Open)
```
Status: registration_open
- Les joueurs peuvent s'inscrire
- Les joueurs soumettent leurs listes d'armée
- Les listes sont validées par l'organisateur
- Vérification: Au minimum 2 participants validés
```

### Phase 3: Inscriptions fermées (Registration Closed)
```
Status: registration_closed
- Les inscriptions sont fermées
- Les matchs peuvent être générés
- Vérification: Minimum 2 participants validés
```

### Phase 4: En cours (In Progress)
```
Status: in_progress
- Les matchs sont générés
- Les joueurs jouent leurs matchs
- Les résultats sont enregistrés
- Le classement se met à jour en temps réel
```

### Phase 5: Terminé (Completed)
```
Status: completed
- Tous les matchs sont joués
- Le classement final est établi
- Les résultats sont archivés
```

---

## Formats de tournoi

### 1. 🏆 Élimination directe (Elimination)

**Fonctionnement**:
- Premier round: appairage simple des joueurs
- Les perdants sont éliminés
- Les gagnants avancent au round suivant
- Jusqu'à un seul gagnant

**Génération**:
```php
// Shuffle des joueurs
// Appairage par paires (joueur 1 vs joueur 2, 3 vs 4, etc.)
// Création des matchs pour le round 1
```

**Avantages**:
- Format rapide
- Peu de matchs
- Gagnant clair

**Inconvénients**:
- Élimine les joueurs après une défaite
- Peu de matchs pour chaque joueur

---

### 2. 🇨🇭 Suisse (Swiss)

**Fonctionnement**:
- 3 rounds de matchs
- Appairage aléatoire à chaque round
- Les joueurs ne sont pas éliminés
- Classement basé sur les points

**Génération**:
```php
// Round 1: Shuffle et appairage simple
// Round 2: Shuffle et appairage simple
// Round 3: Shuffle et appairage simple
// Chaque joueur joue 3 matchs
```

**Avantages**:
- Tous les joueurs jouent plusieurs matchs
- Format équitable
- Classement basé sur la performance

**Inconvénients**:
- Plus de matchs que l'élimination
- Appairage aléatoire (peut créer des matchs déséquilibrés)

---

### 3. 🏅 Ligue (League)

**Fonctionnement**:
- Round-robin complet
- Chaque joueur joue contre chaque autre joueur une fois
- Classement basé sur les points
- Format le plus équitable

**Génération**:
```php
// Pour chaque paire de joueurs (i, j) où i < j
// Créer un match entre joueur i et joueur j
// Nombre total de matchs = n * (n-1) / 2
// Où n = nombre de joueurs
```

**Exemple avec 4 joueurs**:
- Joueur 1 vs 2, 3, 4 (3 matchs)
- Joueur 2 vs 1, 3, 4 (3 matchs, mais 1 vs 2 déjà créé)
- Total: 6 matchs

**Avantages**:
- Format le plus équitable
- Chaque joueur joue contre tous les autres
- Classement très précis

**Inconvénients**:
- Beaucoup de matchs (n * (n-1) / 2)
- Peut être long pour beaucoup de joueurs

---

## Système de points

### 🎯 Attribution des points

| Résultat | Points |
|----------|--------|
| Victoire | 3 points |
| Nul | 1 point |
| Défaite | 0 point |

### 📊 Calcul du classement

```php
// Pour chaque joueur:
$points = (wins * 3) + draws;

// Tri par points décroissants
// En cas d'égalité: ordre d'apparition
```

### 📈 Exemple

**Joueur A**: 2 victoires, 1 nul, 0 défaite
- Points = (2 * 3) + 1 = 7 points

**Joueur B**: 2 victoires, 0 nul, 1 défaite
- Points = (2 * 3) + 0 = 6 points

**Classement**:
1. Joueur A (7 points)
2. Joueur B (6 points)

---

## Génération des matchs

### 🔄 Processus de génération

#### Étape 1: Vérification des prérequis
```php
// Récupérer les listes d'armée validées
$validatedArmyLists = $tournament->armyLists()
    ->where('status', 'validated')
    ->get();

// Vérifier minimum 2 participants
if ($validatedArmyLists->count() < 2) {
    return error('Au minimum 2 participants validés');
}
```

#### Étape 2: Suppression des anciens matchs
```php
// Supprimer les matchs existants (sauf complétés en ligue)
TournamentMatch::where('tournament_id', $tournament->id)
    ->where('status', '!=', 'completed')
    ->delete();
```

#### Étape 3: Génération selon le format
```php
$matches = match ($tournament->format) {
    'elimination' => $this->generateEliminationMatches(...),
    'swiss' => $this->generateSwissMatches(...),
    'league' => $this->generateLeagueMatches(...),
};
```

### 📋 Structure d'un match

```php
TournamentMatch::create([
    'tournament_id' => $tournament->id,
    'round' => $round,
    'table_number' => $tableNumber,
    'player1_id' => $player1->user_id,
    'player1_army_list_id' => $player1->id,
    'player2_id' => $player2->user_id,
    'player2_army_list_id' => $player2->id,
    'status' => 'pending',
]);
```

### 🔐 Cas spécial: Ligue sans suppression des matchs complétés

```php
// Récupérer les matchs complétés
$completedMatches = TournamentMatch::where('tournament_id', $tournament->id)
    ->where('status', 'completed')
    ->get();

// Supprimer uniquement les matchs non complétés
TournamentMatch::where('tournament_id', $tournament->id)
    ->where('status', '!=', 'completed')
    ->delete();

// Générer les nouveaux matchs en évitant les doublons
$matches = $this->generateLeagueMatches($tournament, $armyLists, $completedMatches);
```

---

## Classement et ranking

### 🏆 Calcul du classement

#### Service: `TournamentService::getTournamentRanking()`

```php
public function getTournamentRanking(Tournament $tournament): array
{
    $players = $tournament->players;
    $ranking = [];

    foreach ($players as $player) {
        // Récupérer tous les matchs complétés du joueur
        $matches = TournamentMatch::where('tournament_id', $tournament->id)
            ->where(function ($q) use ($player) {
                $q->where('player1_id', $player->id)
                    ->orWhere('player2_id', $player->id);
            })
            ->where('status', 'completed')
            ->get();

        // Compter les victoires, défaites, nuls
        $wins = $matches->filter(fn($m) => $m->winner_id === $player->id)->count();
        $losses = $matches->filter(fn($m) => $m->winner_id !== $player->id && $m->winner_id !== null)->count();
        $draws = $matches->filter(fn($m) => $m->is_draw)->count();

        // Calculer les points
        $totalPoints = ($wins * 3) + $draws;

        $ranking[] = [
            'player' => $player,
            'matches' => $matches->count(),
            'wins' => $wins,
            'losses' => $losses,
            'draws' => $draws,
            'points' => $totalPoints,
        ];
    }

    // Trier par points (décroissant)
    usort($ranking, fn($a, $b) => $b['points'] <=> $a['points']);

    return $ranking;
}
```

#### Contrôleur: `TournamentController::calculateRankings()`

```php
private function calculateRankings(Tournament $tournament)
{
    $players = [];
    $armyLists = $tournament->armyLists()
        ->where('status', 'validated')
        ->with(['user', 'faction'])
        ->get();

    // Initialiser les statistiques pour chaque joueur
    foreach ($armyLists as $armyList) {
        $userId = $armyList->user_id;
        $players[$userId] = [
            'user' => $armyList->user,
            'faction' => $armyList->faction->name ?? 'Unknown',
            'points' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
            'matches_played' => 0,
        ];
    }

    // Traiter chaque match complété
    $matches = $tournament->tournamentMatches()
        ->where('status', '!=', 'pending')
        ->get();

    foreach ($matches as $match) {
        // Mettre à jour les stats du joueur 1
        if (isset($players[$match->player1_id])) {
            $players[$match->player1_id]['matches_played']++;
            if ($match->winner_id === $match->player1_id) {
                $players[$match->player1_id]['wins']++;
                $players[$match->player1_id]['points'] += 3;
            } elseif ($match->is_draw) {
                $players[$match->player1_id]['draws']++;
                $players[$match->player1_id]['points'] += 1;
            } else {
                $players[$match->player1_id]['losses']++;
            }
        }

        // Mettre à jour les stats du joueur 2
        if (isset($players[$match->player2_id])) {
            $players[$match->player2_id]['matches_played']++;
            if ($match->winner_id === $match->player2_id) {
                $players[$match->player2_id]['wins']++;
                $players[$match->player2_id]['points'] += 3;
            } elseif ($match->is_draw) {
                $players[$match->player2_id]['draws']++;
                $players[$match->player2_id]['points'] += 1;
            } else {
                $players[$match->player2_id]['losses']++;
            }
        }
    }

    // Trier par points
    usort($players, function($a, $b) {
        return $b['points'] - $a['points'];
    });

    return $players;
}
```

---

## Inscriptions et listes d'armée

### 📝 Processus d'inscription

#### Étape 1: Vérification des conditions
```php
// Le tournoi doit être en statut "registration_open"
if ($tournament->status !== 'open') {
    return error('Les inscriptions sont fermées');
}

// La deadline d'inscription ne doit pas être dépassée
if ($tournament->registration_deadline && $tournament->registration_deadline < now()) {
    return error('La date limite d\'inscription est dépassée');
}

// L'utilisateur ne doit pas être déjà inscrit
$existingArmyList = $tournament->armyLists()
    ->where('user_id', auth()->id())
    ->first();

if ($existingArmyList) {
    return error('Vous êtes déjà inscrit à ce tournoi');
}
```

#### Étape 2: Création de la liste d'armée
```php
$armyList = $tournament->armyLists()->create([
    'user_id' => auth()->id(),
    'faction_id' => $validated['faction_id'],
    'detachment' => $validated['detachment'],
    'pdf_path' => $pdfPath,
    'pdf_size' => $request->file('pdf')->getSize(),
    'pdf_hash' => hash_file('sha256', $pdfPath),
    'status' => 'pending',  // En attente de validation
    'points' => $request->input('points'),
]);
```

#### Étape 3: Notification
```php
// Envoyer une notification à l'organisateur
Mail::to($tournament->creator->email)
    ->send(new NewArmyListRegistration($armyList));
```

### ✅ Validation des listes d'armée

**Par l'organisateur**:
- Vérifier la conformité de la liste avec les règles W40K
- Vérifier le nombre de points
- Vérifier les détachements
- Approuver ou rejeter la liste

**Statuts possibles**:
- `pending`: En attente de validation
- `validated`: Approuvée
- `rejected`: Rejetée

### 🚫 Désinscription

**Conditions**:
- L'utilisateur ne doit pas avoir joué de matchs
- Si matchs joués: impossible de se désinscrire

```php
$hasPlayedMatches = TournamentMatch::where('tournament_id', $tournament->id)
    ->where(function ($query) {
        $query->where('player1_id', auth()->id())
            ->orWhere('player2_id', auth()->id());
    })
    ->where('status', '!=', 'pending')
    ->exists();

if ($hasPlayedMatches) {
    return error('Vous ne pouvez pas vous désinscrire car vous avez déjà joué des matchs');
}
```

---

## Statuts et transitions

### 📊 Diagramme des statuts

```
draft
  ↓
registration_open
  ↓
registration_closed
  ↓
in_progress
  ↓
completed
```

### 🔄 Transitions possibles

| De | Vers | Condition |
|---|---|---|
| draft | registration_open | Organisateur ouvre les inscriptions |
| registration_open | registration_closed | Deadline atteinte ou organisateur ferme |
| registration_closed | in_progress | Matchs générés |
| in_progress | completed | Tous les matchs joués ou organisateur ferme |

### 🔐 Restrictions

**Modification du tournoi**:
- Possible uniquement en statut `draft`
- Impossible après `registration_open`

**Inscription**:
- Possible uniquement en statut `registration_open`
- Avant la deadline d'inscription

**Génération des matchs**:
- Possible en statut `registration_closed` ou `in_progress`
- Minimum 2 participants validés requis

**Désinscription**:
- Possible tant qu'aucun match n'a été joué
- Impossible après le début des matchs

---

## 🎮 Cas d'usage complets

### Cas 1: Tournoi en élimination directe

```
1. Créer le tournoi (format: elimination)
2. Ouvrir les inscriptions
3. Les joueurs s'inscrivent et soumettent leurs listes
4. Organisateur valide les listes
5. Fermer les inscriptions
6. Générer les matchs (round 1)
7. Les joueurs jouent leurs matchs
8. Générer les matchs (round 2) avec les gagnants
9. Continuer jusqu'à un seul gagnant
10. Fermer le tournoi
```

### Cas 2: Tournoi en ligue

```
1. Créer le tournoi (format: league)
2. Ouvrir les inscriptions
3. Les joueurs s'inscrivent
4. Organisateur valide les listes
5. Fermer les inscriptions
6. Générer tous les matchs (round-robin)
7. Les joueurs jouent leurs matchs
8. Le classement se met à jour en temps réel
9. Fermer le tournoi
```

---

## 📚 Documentation connexe

- [Tournament Model](../MODELS/TOURNAMENT.md)
- [Tournament Match Model](../MODELS/TOURNAMENT_MATCH.md)
- [Army List Model](../MODELS/ARMY_LIST.md)
- [Tournament Service](../SERVICES/TOURNAMENT_SERVICE.md)

---

**Dernière mise à jour**: 5 novembre 2025  
**Auteur**: Cascade (AI Coding Assistant)  
**Statut**: ✅ Production Ready
