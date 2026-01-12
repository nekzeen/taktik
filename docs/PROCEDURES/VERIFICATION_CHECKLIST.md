# Checklist de Vérification Systématique

## À effectuer OBLIGATOIREMENT après CHAQUE modification de fichier Blade

Voir aussi : `docs/PROCEDURES/SECURITY_CONFIGURATION.md` pour les paramètres de sécurité (rate limiting, headers, alertes, scheduler).

### 1. SYNTAXE BLADE ✓
- [ ] `@extends()` ou `<x-layout>` présent et correct
- [ ] `@section()` et `@endsection` correctement appairés
- [ ] Tous les `@if/@else/@elseif/@endif` fermés
- [ ] Tous les `@foreach/@endforeach` fermés
- [ ] Tous les `@auth/@endauth` fermés
- [ ] Tous les `@guest/@endguest` fermés
- [ ] Pas de `@` orphelins
- [ ] Pas de balises HTML non fermées

### 2. LAYOUTS ET COMPOSANTS ✓
- [ ] Layout utilisé existe (`layouts/public.blade.php`, `layouts/app.blade.php`, etc.)
- [ ] Composants utilisés existent (ex: `<x-dropdown>`, `<x-nav-link>`)
- [ ] `@yield('content')` ou équivalent présent dans la layout
- [ ] Pas d'erreur "Unable to locate a class or view for component"

### 3. VARIABLES ET DONNÉES ✓
- [ ] Toutes les variables utilisées sont passées par le contrôleur
- [ ] Pas d'accès direct à `Auth::user()` sans vérification `@auth`
- [ ] Pas d'accès à des propriétés null (ex: `$user->name` sans vérifier que `$user` existe)
- [ ] Méthodes PHP utilisées existent (`.count()`, `.isEmpty()`, `.format()`, etc.)
- [ ] Pas de variables non définies

### 4. DESIGN ET TAILWIND CSS ✓
- [ ] Pas de styles inline (sauf gradients Tailwind)
- [ ] Couleurs cohérentes:
  - Primaire rouge: `#b91c1c`, `#991b1b` (classes: `red-600`, `red-700`, `red-800`)
  - Secondaire vert: `#059669`, `#047857` (classes: `green-600`, `green-700`, `green-800`)
- [ ] Gradients corrects: `bg-gradient-to-r from-red-600 to-red-700`
- [ ] Cartes: `bg-white`, `border border-gray-200`, `shadow-sm`
- [ ] Effet hover: `hover:border-red-600 hover:shadow-md transition-all`
- [ ] Boutons primaires: `bg-red-600 text-white hover:bg-red-700`
- [ ] Boutons secondaires: `bg-white text-red-600 border border-red-200`
- [ ] Responsive: `md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4`

### 5. AUTHENTIFICATION ✓
- [ ] Sections utilisateur enveloppées avec `@auth/@endauth`
- [ ] Sections publiques enveloppées avec `@guest/@endguest` si nécessaire
- [ ] Pas d'accès direct à `Auth::user()` sans `@auth`
- [ ] Pas d'erreur "Attempt to read property on null"

### 6. ROUTES ET LIENS ✓
- [ ] Routes utilisées existent dans `routes/web.php`
- [ ] Paramètres de route corrects (ex: `route('tournaments.show', $tournament)`)
- [ ] Pas de routes cassées
- [ ] Liens de retour présents et fonctionnels

### 7. INDENTATION ET FORMATAGE ✓
- [ ] Indentation cohérente (4 espaces ou 1 tab)
- [ ] Pas de lignes vides excessives
- [ ] Pas de caractères spéciaux non échappés
- [ ] Pas de commentaires mal fermés

### 8. ERREURS COMMUNES À ÉVITER ✓
- [ ] Pas de `<x-app-layout>` pour les pages publiques (utiliser `@extends('layouts.public')`)
- [ ] Pas de `{{ Auth::user()->name }}` sans `@auth`
- [ ] Pas de styles inline (utiliser Tailwind)
- [ ] Pas de `onmouseover` inline (utiliser Tailwind `hover:`)
- [ ] Pas de `style="..."` (utiliser Tailwind)

---

## Résultat de la vérification

**Fichiers concernés:**
- `app/Http/Controllers/PlayerAvailabilityController.php`
- `app/Notifications/PlayerAvailabilityCreatedNotification.php`

**Date:** `2026-01-03`
**Statut:** `✅ VALIDE`

### Changements
```
- Envoi d'un email aux adversaires des matchs de tournoi déjà générés et non terminés lors de la première création d'une disponibilité globale (PlayerAvailability). Aucun email n'est envoyé lors des mises à jour ultérieures.
```

**Fichiers concernés:**
- `resources/views/tournaments/availability-calendar.blade.php`

**Date:** `2026-01-03`
**Statut:** `✅ VALIDE`

### Changements
```
- Ajout sur la page Agenda d'un bouton (auth) permettant de supprimer la disponibilité globale du joueur pour le tournoi via la route tournaments.player-availability.destroy.
```

**Fichiers concernés:**
- `app/Models/PlayerAvailability.php`

**Date:** `2026-01-03`
**Statut:** `✅ VALIDE`

### Changements
```
- Ajustement du scope PlayerAvailability::active() : une disponibilité de type "période" est désormais considérée active tant qu'elle n'est pas terminée (available_to >= now), afin qu'elle s'affiche sur les cartes et l'agenda même avant le début de la période.
```

**Fichiers concernés:**
- `resources/views/tournaments/index.blade.php`
- `resources/views/tournaments/show.blade.php`

**Date:** `2026-01-03`
**Statut:** `✅ VALIDE`

### Changements
```
- Affichage conditionnel de la date limite d'inscription sur les cartes de la page /tournaments.
- Affichage conditionnel de la date limite d'inscription dans le panneau "Informations" de la page détail du tournoi.
```

**Fichiers concernés:**
- `resources/views/tournaments/create.blade.php`
- `resources/views/tournaments/edit.blade.php`

---

## Résultat de la vérification

**Fichiers concernés:**
- `database/migrations/2026_01_10_213500_add_scheduled_at_to_tournament_matches_table.php`
- `app/Models/TournamentMatch.php`
- `app/Http/Controllers/TournamentMatchController.php`
- `routes/web.php`
- `app/Http/Controllers/PlayerAvailabilityController.php`
- `resources/views/tournaments/matches/_player_availability_modal.blade.php`
- `resources/views/tournaments/matches/index.blade.php`

**Date:** `2026-01-10`
**Statut:** `⏳ À TESTER`

### Changements
```
- Ajout du verrouillage de date sur un match de tournoi via un bouton "Accepter la date" sur la carte du match.
- Lors de l'acceptation, la date du match est enregistrée (scheduled_at) et la disponibilité globale (PlayerAvailability) du joueur ayant proposé est supprimée.
- Affichage "Match prévu" sur la carte quand le match est planifié.
- Désactivation de la disponibilité de type "période" (uniquement "ponctuelle").
```

---

## Résultat de la vérification

**Fichiers concernés:**
- `resources/views/matches/summary.blade.php`
- `resources/views/tournaments/matches/score-player2.blade.php`

**Date:** `2026-01-07`
**Statut:** `✅ VALIDE`

### Changements
```
- Masquage de l'affichage de la section "Péripétie" sur le résumé de configuration des matchs de tournoi (/tournaments/{tournament}/matches/{match}/summary).
- Suppression de l'affichage de la section "Péripétie" sur la page de scoring Player 2 (/tournaments/{tournament}/matches/{match}/score/player2).
```

---

## Résultat de la vérification

**Fichiers concernés:**
- `resources/views/matches/summary.blade.php`
- `resources/views/player-matches/score-creator.blade.php`
- `resources/views/player-matches/score-opponent.blade.php`
- `resources/views/player-matches/view-score.blade.php`
- `resources/views/player-matches/test-score.blade.php`

**Date:** `2026-01-07`
**Statut:** `✅ VALIDE`

### Changements
```
- Suppression de l'affichage de la section "Péripétie" sur le résumé de configuration des matchs simples (/player-matches/{playerMatch}/summary).
- Suppression de l'affichage de la section "Péripétie" sur les pages de scoring des matchs simples (/player-matches/{playerMatch}/score, /score/creator, /score/opponent, /view-score).
```
- `app/Http/Controllers/TournamentController.php`

**Date:** `2026-01-03`
**Statut:** `✅ VALIDE`

### Changements
```
- Ajout du champ "Date limite d'inscription" (datetime-local) dans le formulaire public de création de tournoi.
- Ajout du champ "Date limite d'inscription" (datetime-local) dans le formulaire public d'édition de tournoi (pré-rempli).
- Mise à jour de la validation/normalisation côté contrôleur pour accepter le format datetime-local et convertir en datetime (Carbon::parse).
```

**Fichier modifié:** `resources/views/player-matches/index.blade.php`
**Date:** `2025-12-30`
**Statut:** `✅ VALIDE`

**Fichier modifié:** `resources/views/tournaments/manage-registrations.blade.php`
**Date:** `2026-01-05`
**Statut:** `✅ VALIDE`

### Changements
```
- Suppression de l'affichage "Points" sur la page de gestion des inscriptions (cartes des demandes en attente + tableau des listes validées).
```

### Entrée (exemple) - Traductions Detachments Filament

**Fichiers concernés:**
- `app/Models/Detachment.php`
- `app/Filament/Resources/DetachmentResource.php`
- `app/Filament/Resources/DetachmentResource/RelationManagers/TranslationsRelationManager.php`

**But:**
- Alignement de l'interface de traduction des Détachements sur celle des Missions (onglet "Traductions" via RelationManager).

### Erreurs trouvées:
```
[À REMPLIR]
```

### Corrections apportées:
```
- Ajout d'une vue "cards" (mobile) pour la section "Historique des matchs" afin d'assurer la lisibilité sur smartphone.
- Encapsulation du tableau desktop dans un conteneur `overflow-x-auto` + `min-w-[900px]` pour éviter l'écrasement des colonnes sur écrans étroits.
```

---

## Procédure d'utilisation

1. Effectuer la modification du fichier Blade
2. Remplir ce checklist point par point
3. Si une erreur est trouvée, la corriger immédiatement
4. Valider que la correction ne crée pas d'autres erreurs
5. Marquer le fichier comme ✅ VALIDE
6. Documenter les changements dans ce fichier
