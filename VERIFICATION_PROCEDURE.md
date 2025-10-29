# Procédure de Vérification Systématique

## 🎯 Objectif
Garantir la qualité et la cohérence de chaque modification avant déploiement.

## ✅ Checklist Avant Chaque Modification

### 1. **Comprendre la Demande**
- [ ] Lire attentivement la demande
- [ ] Identifier les fichiers concernés
- [ ] Vérifier les dépendances (routes, modèles, relations)
- [ ] Clarifier les ambiguïtés

### 2. **Vérifier les Ressources Existantes**
- [ ] Routes : Vérifier dans `routes/web.php`
- [ ] Modèles : Vérifier les champs et relations
- [ ] Vues : Vérifier la structure et les variables
- [ ] Contrôleurs : Vérifier les méthodes existantes
- [ ] Migrations : Vérifier les colonnes de base de données

### 3. **Implémenter la Modification**
- [ ] Faire les changements de code
- [ ] Ajouter les imports nécessaires
- [ ] Respecter les conventions de style
- [ ] Ajouter les validations

### 4. **Vérifier la Syntaxe**
- [ ] Blade : `@if/@endif`, `@foreach/@endforeach`, `@section/@endsection`
- [ ] HTML : Balises fermées correctement
- [ ] PHP : Pas d'erreurs de syntaxe
- [ ] Routes : Existent et sont correctes

### 5. **Vérifier les Références**
- [ ] Routes utilisées existent
- [ ] Modèles et champs existent
- [ ] Variables passées par le contrôleur
- [ ] Relations de modèles chargées
- [ ] Méthodes appelées existent

### 6. **Vérifier le Design**
- [ ] Cohérence Tailwind CSS
- [ ] Pas de styles inline (sauf gradients)
- [ ] Cohérence des couleurs (rouge/vert)
- [ ] Responsive design
- [ ] Alignement avec les autres pages

### 7. **Vérifier la Sécurité**
- [ ] Authentification correcte (`@auth`)
- [ ] Autorisation correcte (`Gate::authorize`)
- [ ] Pas d'accès null à `Auth::user()`
- [ ] Validation des entrées utilisateur
- [ ] Protection CSRF

### 8. **Vérifier la Logique Métier**
- [ ] Flux utilisateur cohérent
- [ ] Pas de bugs logiques
- [ ] Gestion des cas limites
- [ ] Messages d'erreur clairs
- [ ] Feedback utilisateur approprié

### 9. **Tester Avant Déploiement**
- [ ] Recompiler Tailwind CSS : `npm run build`
- [ ] Vérifier les erreurs de compilation
- [ ] Tester manuellement si possible
- [ ] Vérifier les logs d'erreur

### 10. **Documenter**
- [ ] Mettre à jour VERIFICATION_CHECKLIST.md
- [ ] Noter les changements clés
- [ ] Documenter les limitations
- [ ] Signaler les problèmes trouvés

## 🔍 Vérifications Spécifiques par Type

### Modification de Vue Blade
1. Vérifier les routes utilisées
2. Vérifier les variables passées
3. Vérifier les relations de modèles
4. Vérifier la syntaxe Blade
5. Vérifier le design cohérent

### Modification de Contrôleur
1. Vérifier les imports
2. Vérifier les modèles utilisés
3. Vérifier les routes nommées
4. Vérifier les validations
5. Vérifier les relations chargées

### Ajout de Fonctionnalité
1. Vérifier les dépendances
2. Vérifier les migrations (si nécessaire)
3. Vérifier les modèles
4. Vérifier les routes
5. Vérifier les vues
6. Vérifier la logique métier

### Email/Notification
1. Vérifier les routes utilisées
2. Vérifier les champs du modèle
3. Vérifier les relations
4. Vérifier les variables dans la vue
5. Vérifier les destinataires

## 📋 Exemple de Vérification

### Demande : "Ajouter une notification email"

**Avant d'implémenter :**
```
✓ Routes existantes : tournaments.registrations.manage
✓ Modèles : ArmyList, Tournament, User
✓ Champs : points (pas army_points)
✓ Relations : armyList->tournament, tournament->creator
✓ Destinataire : tournament->creator->email
```

**Après implémentation :**
```
✓ Classe Mail créée
✓ Vue email créée
✓ Contrôleur modifié
✓ Imports ajoutés
✓ Routes vérifiées
✓ Champs vérifiés
✓ Recompilation effectuée
```

## ⚠️ Erreurs Critiques à Éviter

- ❌ Utiliser une route qui n'existe pas
- ❌ Accéder à un champ qui n'existe pas
- ❌ Oublier les imports nécessaires
- ❌ Mismatch @if/@endif, @foreach/@endforeach
- ❌ Auth::user() en dehors de @auth
- ❌ Styles inline au lieu de Tailwind
- ❌ Oublier de recompiler Tailwind CSS
- ❌ Pas de validation des entrées
- ❌ Pas de gestion des erreurs

## 🚀 Procédure Avant Chaque Demande

1. **Lire la demande** et identifier les fichiers
2. **Vérifier les ressources** (routes, modèles, champs)
3. **Implémenter** la modification
4. **Vérifier** la syntaxe et les références
5. **Tester** la recompilation
6. **Documenter** les changements

---

**Cette procédure doit être suivie pour CHAQUE demande.**
