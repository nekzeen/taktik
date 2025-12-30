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

**Fichier modifié:** `[À REMPLIR]`
**Date:** `[À REMPLIR]`
**Statut:** `[À REMPLIR: ✅ VALIDE ou ❌ ERREURS]`

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
[À REMPLIR]
```

---

## Procédure d'utilisation

1. Effectuer la modification du fichier Blade
2. Remplir ce checklist point par point
3. Si une erreur est trouvée, la corriger immédiatement
4. Valider que la correction ne crée pas d'autres erreurs
5. Marquer le fichier comme ✅ VALIDE
6. Documenter les changements dans ce fichier
