# 🔴 RÈGLES OBLIGATOIRES DE CODAGE

## ⚠️ CES RÈGLES DOIVENT ÊTRE RESPECTÉES SYSTÉMATIQUEMENT SANS EXCEPTION

---

## 1️⃣ TAILWIND CSS - OBLIGATOIRE

### ✅ À FAIRE
- **TOUJOURS** utiliser Tailwind CSS pour les styles
- Utiliser les classes Tailwind : `w-full`, `h-auto`, `bg-red-600`, `text-white`, etc.
- Utiliser les breakpoints : `md:`, `lg:`, `xl:`
- Utiliser les states : `hover:`, `active:`, `focus:`, etc.

### ❌ À NE PAS FAIRE
- **JAMAIS** de styles inline (`style="..."`)
- **JAMAIS** de balises `<style>` dans les fichiers Blade
- **JAMAIS** de CSS personnalisé sans justification

### 📝 Exemple CORRECT
```blade
<img src="{{ $image }}" alt="Description" class="w-full h-auto block rounded-lg">
```

### ❌ Exemple INCORRECT
```blade
<img src="{{ $image }}" alt="Description" style="max-width: 100%; height: auto; display: block;">
```

---

## 2️⃣ PROCÉDURE COMPLÈTE - 10 PHASES

### PHASE 1 : ANALYSE
- [ ] Lire 3 fois la demande
- [ ] Identifier EXACTEMENT ce qui est demandé
- [ ] Identifier les fichiers concernés

### PHASE 2 : VÉRIFICATIONS PRÉALABLES
- [ ] Vérifier les migrations en attente
- [ ] Vérifier les versions des packages
- [ ] Vérifier les méthodes existent
- [ ] Vérifier les imports
- [ ] **Vérifier les données/relations existantes en base**

### PHASE 3 : IMPLÉMENTATION
- [ ] Faire les changements
- [ ] Ajouter les imports
- [ ] Respecter le style existant
- [ ] **Utiliser les données existantes au lieu de créer du nouveau**

### PHASE 4 : VÉRIFICATIONS SYNTAXE
- [ ] Blade : @if/@endif, @foreach/@endforeach, @auth/@endauth
- [ ] HTML : balises fermées
- [ ] PHP : pas d'erreurs
- [ ] Routes : existent-elles ?
- [ ] Modèles : champs existent-ils ?

### PHASE 5 : VÉRIFICATIONS RÉFÉRENCES
- [ ] Routes utilisées existent ?
- [ ] Modèles utilisés existent ?
- [ ] Variables passées au contrôleur ?
- [ ] Relations correctes ?
- [ ] Champs fillable ?

### PHASE 6 : VÉRIFICATIONS DESIGN VISUEL
- [ ] Couleurs : rouge #b91c1c, vert #059669
- [ ] Styles : Tailwind uniquement
- [ ] Responsive : md:, lg:, xl: breakpoints
- [ ] Layout correct : @extends('layouts.public') ou <x-app-layout>
- [ ] Menu du haut présent

### PHASE 7 : VÉRIFICATIONS SÉCURITÉ
- [ ] Auth correcte (@auth/@endauth)
- [ ] Permissions vérifiées
- [ ] Validation des entrées
- [ ] Pas d'accès null

### PHASE 8 : VÉRIFICATIONS LOGIQUE
- [ ] Flux correct
- [ ] Cas limites gérés
- [ ] Pas de bugs
- [ ] Logique métier correcte
- [ ] Formulaires : tous les champs envoyés et stockés
- [ ] Contrôleurs : tous les champs traités
- [ ] **Données cohérentes** : Vérifier que les services retournent les mêmes données pour les deux types (tournoi ET matchs simples)
- [ ] **Conditions Blade** : Vérifier que les @if/@else correspondent aux données du contrôleur
- [ ] **Tests des deux cas** : Tester MANUELLEMENT tournoi ET matchs simples

### PHASE 9 : TESTS RÉELS - OBLIGATOIRE
- [ ] Vérification Blade : `php artisan view:cache`
- [ ] Recompiler : `npm run build`
- [ ] Vérifier erreurs de compilation
- [ ] Vider cache : `php artisan cache:clear`
- [ ] Vérifier les logs Laravel
- [ ] TESTER MANUELLEMENT : cliquer sur les boutons, soumettre les formulaires
- [ ] VÉRIFIER LES DONNÉES : vérifier que les données sont sauvegardées en DB
- [ ] VÉRIFIER LES REDIRECTIONS : vérifier que les redirections fonctionnent

### PHASE 10 : DOCUMENTATION
- [ ] Résumer les changements
- [ ] Confirmer que tout fonctionne

---

## 3️⃣ ERREURS CRITIQUES À ÉVITER ABSOLUMENT

```
❌ Pas de vérification Blade (php artisan view:cache)
❌ Pas de vérification réelle des pages
❌ Pas de test des formulaires
❌ Pas de vérification que les données sont sauvegardées
❌ Oublier de stocker les données dans le contrôleur
❌ Oublier d'ajouter les champs au fillable du modèle
❌ Oublier de créer les migrations pour les colonnes manquantes
❌ Mismatch @if/@endif, @foreach/@endforeach
❌ Balises HTML mal fermées
❌ Oublier recompilation Tailwind CSS
❌ Utiliser des styles inline au lieu de Tailwind
❌ Créer du nouveau au lieu d'utiliser les données existantes
```

---

## 4️⃣ COMMANDES OBLIGATOIRES APRÈS CHAQUE MODIFICATION VISUELLE

```bash
# 1. Recompiler Tailwind CSS
npm run build

# 2. Vider le cache Laravel
php artisan cache:clear

# 3. Compiler les vues
php artisan view:cache
```

---

## 5️⃣ VÉRIFICATIONS DONNÉES EXISTANTES

### AVANT de créer du nouveau :
1. **Chercher en base de données** - Vérifier si les données existent déjà
2. **Consulter l'admin** - Voir les données disponibles dans l'interface
3. **Vérifier les migrations** - Vérifier les colonnes existantes
4. **Utiliser les relations** - Utiliser les relations Eloquent existantes
5. **Réutiliser les modèles** - Ne pas créer de nouveaux modèles si possible

### Exemple : Chercher une image de déploiement
```bash
# Au lieu de créer une nouvelle table, chercher les données existantes
php artisan tinker
> \App\Models\StrikeForceDeploymentCard::where('name', 'LIKE', '%Dawn%')->first()
```

---

## 6️⃣ CHECKLIST FINALE AVANT "TERMINÉ"

```
✅ Phase 1 : Demande comprise
✅ Phase 2 : Vérifications préalables OK
✅ Phase 3 : Implémentation complète
✅ Phase 4 : Syntaxe vérifiée
✅ Phase 5 : Références vérifiées
✅ Phase 6 : Design vérifié VISUELLEMENT
✅ Phase 7 : Sécurité vérifiée
✅ Phase 8 : Logique vérifiée
✅ Phase 9 : VÉRIFICATION BLADE OBLIGATOIRE (php artisan view:cache)
✅ Phase 9 : Tests réels passés
✅ Phase 10 : Documentation complète
✅ Compilation réussie (npm run build)
✅ Pas d'erreurs dans les logs
✅ Résultat correspond à la demande
```

---

## 7️⃣ COULEURS STANDARD

- **Rouge primaire** : `#b91c1c` (Tailwind: `bg-red-700`)
- **Rouge clair** : `#dc2626` (Tailwind: `bg-red-600`)
- **Vert** : `#059669` (Tailwind: `bg-green-600`)
- **Gris** : `#6b7280` (Tailwind: `text-gray-500`)

---

## 8️⃣ STRUCTURE BLADE STANDARD

```blade
@extends('layouts.public')

@section('title', 'Titre de la page')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- En-tête gradient rouge -->
    <div class="bg-gradient-to-r from-red-600 to-red-700 border-b border-red-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <h1 class="text-3xl font-bold text-white">Titre</h1>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Contenu ici -->
    </div>
</div>
@endsection
```

---

## 9️⃣ RESPONSIVE BREAKPOINTS

```
- `sm:` - 640px
- `md:` - 768px (mobile à tablette)
- `lg:` - 1024px (tablette à desktop)
- `xl:` - 1280px (desktop large)
- `2xl:` - 1536px (très grand desktop)
```

---

## 🔟 VÉRIFICATIONS LOGIQUE MÉTIER - CRITIQUE

### Quand il y a DEUX types similaires (ex: TournamentMatch ET PlayerMatch)

**OBLIGATOIRE :**
- ✅ Vérifier que les services retournent les MÊMES données pour les deux types
- ✅ Vérifier que les contrôleurs passent les MÊMES variables aux vues
- ✅ Vérifier que les vues gèrent les DEUX cas (@if $matchType === 'tournament')
- ✅ Tester MANUELLEMENT les deux cas dans le navigateur
- ✅ Vérifier que les boutons/formulaires s'affichent pour les deux cas

**Exemple de bug détecté :**
```php
// ❌ MAUVAIS - Retourne vide pour PlayerMatch
'asymmetric_missions' => $isTournamentMatch ? AsymmetricMission::all() : collect()

// ✅ BON - Retourne les données pour les deux types
'asymmetric_missions' => AsymmetricMission::all()
```

---

## 1️⃣1️⃣ LIENS UTILES

- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Laravel Blade Documentation](https://laravel.com/docs/blade)
- [Eloquent ORM](https://laravel.com/docs/eloquent)

---

## 📌 RÉSUMÉ CRITIQUE

**Les 3 règles les plus importantes :**

1. **TAILWIND UNIQUEMENT** - Pas de styles inline
2. **PROCÉDURE COMPLÈTE** - Respecter les 10 phases
3. **VÉRIFICATIONS RÉELLES** - Tester manuellement avant de dire "terminé"

---

*Dernière mise à jour : 31 octobre 2025*
*À consulter systématiquement avant chaque modification*
