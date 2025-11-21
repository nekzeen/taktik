# 👁️ MODE SPECTATEUR - QUESTIONS À CLARIFIER

**Date** : 21 novembre 2025  
**Statut** : Analyse complète - En attente de validation  

---

## ❓ QUESTIONS IMPORTANTES

### 1. DÉCALAGE 2 SECONDES

**Question** : Où implémenter le décalage 2 secondes ?

**Options** :
- **A) Côté serveur** (recommandé)
  - Endpoint retourne `delay_until = now + 2s`
  - Frontend attend avant d'afficher
  - Plus fiable, pas dépendant de la connexion client
  
- **B) Côté client**
  - Frontend attend 2 secondes avant d'afficher
  - Plus simple à implémenter
  - Moins fiable (dépend de la connexion client)

**Recommandation** : **Option A (Côté serveur)**

**Justification** :
- Plus fiable
- Pas dépendant de la connexion client
- Synchronisé avec le serveur
- Meilleure UX

---

### 2. MISSIONS TACTIQUES

**Question** : Comment afficher les missions tactiques ?

**Options** :
- **A) Afficher le nombre uniquement** (recommandé)
  - "🎴 Missions Tactiques: 3 en main, 2 défaussées, 1 ok"
  - Simple, lisible
  - Pas de détails
  
- **B) Afficher les noms des missions**
  - "🎴 Missions Tactiques:"
  - "  En main: BREAK THROUGH, ASSASSINATE, SECURE OBJECTIVE"
  - "  Défaussées: BRING IT DOWN"
  - "  Complétées: LINEBREAKER"
  - Plus détaillé
  - Plus long à afficher

**Recommandation** : **Option A (Afficher le nombre uniquement)**

**Justification** :
- Plus lisible
- Plus rapide à comprendre
- Moins de clutter visuel
- Suffisant pour un spectateur

---

### 3. AUTHENTIFICATION

**Question** : Qui peut accéder au mode spectateur ?

**Options** :
- **A) Complètement public** (recommandé)
  - Visiteurs non-inscrits peuvent accéder
  - Pas d'authentification requise
  - Lien public partageable
  
- **B) Réservé aux inscrits**
  - Authentification requise
  - Seuls les utilisateurs inscrits peuvent accéder
  - Plus restrictif

**Recommandation** : **Option A (Complètement public)**

**Justification** :
- Permet aux visiteurs de suivre les matchs
- Crée une audience pour les tournois
- Lien public partageable
- Pas de données sensibles exposées

---

### 4. HISTORIQUE

**Question** : Afficher les matchs terminés en spectateur ?

**Options** :
- **A) Oui** (recommandé)
  - Bouton "👁️ Spectate" visible sur les matchs terminés
  - Permet de revoir les matchs
  - Même logique que les joueurs
  
- **B) Non**
  - Bouton "👁️ Spectate" visible uniquement sur les matchs en cours
  - Pas d'accès à l'historique

**Recommandation** : **Option A (Oui)**

**Justification** :
- Permet de revoir les matchs
- Même logique que les joueurs
- Utile pour l'analyse
- Pas de surcharge serveur

---

### 5. IMAGES DÉPLOIEMENT

**Question** : Afficher une image pour le déploiement ?

**Options** :
- **A) Oui, si disponible** (recommandé)
  - Afficher l'image si elle existe
  - Afficher un placeholder sinon
  - Améliore l'UX
  
- **B) Non, juste le texte**
  - Afficher uniquement le nom du déploiement
  - Plus simple
  - Moins visuel

**Recommandation** : **Option A (Oui, si disponible)**

**Justification** :
- Améliore l'UX
- Images déploiement existent déjà
- Facile à implémenter
- Plus visuel

---

### 6. TRADUCTIONS

**Question** : Afficher les données en français ou en anglais ?

**Options** :
- **A) Français** (recommandé)
  - Utiliser les champs `*_fr` des missions
  - Cohérent avec le site
  - Meilleure UX pour les utilisateurs français
  
- **B) Anglais**
  - Utiliser les champs `name`, `description`
  - Plus simple
  - Moins accessible

**Recommandation** : **Option A (Français)**

**Justification** :
- Cohérent avec le site
- Meilleure UX
- Traductions déjà disponibles
- Utilisateurs français

---

### 7. RESPONSIVE DESIGN

**Question** : Adapter le design pour mobile ?

**Options** :
- **A) Oui** (recommandé)
  - Utiliser Tailwind CSS responsive
  - Adapter le layout pour mobile
  - Meilleure UX
  
- **B) Non**
  - Desktop uniquement
  - Plus simple
  - Moins accessible

**Recommandation** : **Option A (Oui)**

**Justification** :
- Meilleure UX
- Utilisateurs mobiles
- Facile avec Tailwind CSS
- Cohérent avec le site

---

### 8. RAFRAÎCHISSEMENT AUTOMATIQUE

**Question** : Rafraîchir automatiquement les scores ?

**Options** :
- **A) Oui, toutes les 2 secondes** (recommandé)
  - Polling automatique
  - Scores à jour en temps réel
  - Même logique que les joueurs
  
- **B) Non, bouton manuel**
  - Utilisateur clique pour rafraîchir
  - Plus simple
  - Moins de requêtes serveur

**Recommandation** : **Option A (Oui, toutes les 2 secondes)**

**Justification** :
- Temps réel
- Même logique que les joueurs
- Meilleure UX
- Pas de surcharge serveur

---

### 9. AFFICHAGE DÉTAILS SCORES

**Question** : Afficher les détails des scores (primaire, secondaire, peinture) ?

**Options** :
- **A) Oui** (recommandé)
  - Afficher primaire, secondaire, peinture séparément
  - Plus détaillé
  - Meilleure compréhension
  
- **B) Non, juste le total**
  - Afficher uniquement le score total
  - Plus simple
  - Moins d'informations

**Recommandation** : **Option A (Oui)**

**Justification** :
- Meilleure compréhension
- Détails importants
- Facile à afficher
- Cohérent avec les joueurs

---

### 10. NOTIFICATIONS

**Question** : Afficher des notifications quand les scores changent ?

**Options** :
- **A) Non** (recommandé)
  - Pas de notifications
  - Plus simple
  - Moins de clutter
  
- **B) Oui**
  - Afficher une notification quand les scores changent
  - Plus interactif
  - Peut être intrusif

**Recommandation** : **Option A (Non)**

**Justification** :
- Plus simple
- Moins de clutter
- Polling suffit
- Pas nécessaire

---

## 📋 RÉSUMÉ DES RECOMMANDATIONS

| Question | Recommandation | Justification |
|----------|---|---|
| Décalage 2s | Côté serveur | Plus fiable |
| Missions tactiques | Nombre uniquement | Plus lisible |
| Authentification | Public | Audience plus large |
| Historique | Oui | Permet de revoir |
| Images déploiement | Oui, si disponible | Meilleure UX |
| Traductions | Français | Cohérent avec le site |
| Responsive | Oui | Meilleure UX |
| Rafraîchissement | Toutes les 2s | Temps réel |
| Détails scores | Oui | Meilleure compréhension |
| Notifications | Non | Plus simple |

---

## ✅ VALIDATION

**Avant implémentation, confirmer** :

- [ ] Décalage 2 secondes côté serveur ?
- [ ] Missions tactiques : nombre uniquement ?
- [ ] Accès complètement public ?
- [ ] Afficher les matchs terminés ?
- [ ] Afficher images déploiement ?
- [ ] Afficher en français ?
- [ ] Adapter pour mobile ?
- [ ] Rafraîchissement toutes les 2s ?
- [ ] Afficher détails scores ?
- [ ] Pas de notifications ?

---

## 🚀 PROCHAINES ÉTAPES

1. **Clarifier les questions** : Confirmer les réponses
2. **Valider les recommandations** : Accepter ou modifier
3. **Implémenter** : Créer contrôleur, routes, vues
4. **Tester** : Polling, accès public, données
5. **Déployer** : Compiler, déployer, tester

---

**Statut** : En attente de validation  
**Complexité** : Faible  
**Temps estimé** : 1-2 jours  
