# Guide Utilisateur - Mise à Jour Automatique des Scores

**Date** : 21 novembre 2025  
**Version** : 1.0

---

## 📋 Résumé des Améliorations

La mise à jour automatique des scores a été corrigée et améliorée sur toutes les pages :

✅ **Page du créateur** (`/player-matches/{id}/score`)
- Les scores de l'adversaire se mettent à jour automatiquement toutes les 2 secondes
- Vous pouvez modifier vos scores
- Les scores de l'adversaire sont en lecture seule

✅ **Page de l'adversaire** (`/player-matches/{id}/score/opponent`)
- Les scores du créateur se mettent à jour automatiquement toutes les 2 secondes
- Vous pouvez modifier vos scores
- Les scores du créateur sont en lecture seule

✅ **Mode spectateur** (`/player-matches/{id}/spectate`)
- Les scores des deux joueurs se mettent à jour automatiquement toutes les 2 secondes
- Vous pouvez regarder le match en temps réel
- Aucune modification possible

---

## 🎮 Comment Ça Fonctionne

### Flux de Mise à Jour

```
Vous modifiez votre score
        ↓
Sauvegarde automatique en base de données
        ↓
Polling toutes les 2 secondes
        ↓
Affichage des scores mis à jour
```

### Exemple

**Créateur (Nekzeen)** :
1. Modifie son score primaire : 10 points
2. Sauvegarde automatique
3. Adversaire (Nek) voit le score passer de 0 à 10 après 2 secondes
4. Spectateurs voient aussi la mise à jour

---

## 🔒 Sécurité

### Scores en Lecture Seule
- Les scores de l'adversaire sont **en lecture seule**
- Vous ne pouvez pas modifier les scores de l'adversaire
- Les boutons +/- sont masqués pour les scores de l'adversaire

### Exemple
```
Page du créateur :
- Vos scores : Modifiables ✏️
- Scores adversaire : Lecture seule 🔒

Page de l'adversaire :
- Vos scores : Modifiables ✏️
- Scores créateur : Lecture seule 🔒

Mode spectateur :
- Tous les scores : Lecture seule 🔒
```

---

## 📊 Affichage des Scores

### Total des Scores
Le total est calculé automatiquement :
```
Total = Points Primaires + Points Secondaires + Points Peinture
Exemple : 10 + 5 + 10 = 25 points
```

### Scores Affichés
- **Points Primaires** : 0-50 points
- **Points Secondaires** : 0-40 points
- **Points Peinture** : +10 points (oui/non)
- **Total** : Somme de tous les points

---

## 👁️ Mode Spectateur

### Contenu Affiché
- ✅ Scores en temps réel (créateur et adversaire)
- ✅ Mission Primaire
- ✅ Péripétie
- ✅ Déploiement avec image
- ✅ Disposition Terrain avec image

### Contenu Non Affiché
- ❌ Missions Secondaires
- ❌ Missions Tactiques

### Accès au Mode Spectateur
1. Allez sur la page du match
2. Cliquez sur le bouton "👁️ Spectateur"
3. Regardez le match en temps réel

---

## ⏱️ Timing de Mise à Jour

### Délai de Mise à Jour
- **Polling** : Toutes les 2 secondes
- **Décalage** : 2 secondes (pour éviter les affichages prématurés)
- **Total** : ~2-4 secondes avant de voir la mise à jour

### Exemple
```
14:00:00 - Créateur modifie son score
14:00:00 - Sauvegarde en base de données
14:00:02 - Adversaire voit la mise à jour (après 2 secondes)
14:00:04 - Spectateurs voient la mise à jour (après 4 secondes)
```

---

## 🖼️ Images

### Déploiement
- L'image du déploiement s'affiche sans déformation
- Affichage optimal de l'image

### Terrain
- L'image du terrain s'affiche correctement
- Affichage optimal de l'image

---

## ❓ Questions Fréquentes

### Q : Pourquoi les scores de l'adversaire ne se mettent-ils pas à jour immédiatement ?
**R** : Il y a un délai de 2 secondes pour le polling. C'est normal.

### Q : Puis-je modifier les scores de l'adversaire ?
**R** : Non, les scores de l'adversaire sont en lecture seule. Vous ne pouvez modifier que vos propres scores.

### Q : Où puis-je voir les scores en temps réel ?
**R** : 
- Sur votre page de scoring (`/player-matches/{id}/score`)
- Sur la page de l'adversaire (`/player-matches/{id}/score/opponent`)
- En mode spectateur (`/player-matches/{id}/spectate`)

### Q : Comment accéder au mode spectateur ?
**R** : Cliquez sur le bouton "👁️ Spectateur" sur la page du match.

### Q : Les spectateurs peuvent-ils modifier les scores ?
**R** : Non, les spectateurs ne peuvent que regarder les scores. Aucune modification n'est possible.

### Q : Pourquoi le total des scores est-il incorrect ?
**R** : Vérifiez que vous avez saisi tous les scores (primaire, secondaire, peinture).

---

## 🆘 Dépannage

### Problème : Les scores ne se mettent pas à jour
**Solution** :
1. Rafraîchissez la page (F5)
2. Vérifiez votre connexion Internet
3. Vérifiez que le match est confirmé (pas en attente)

### Problème : Je ne vois pas le bouton "Spectateur"
**Solution** :
1. Vérifiez que le match est confirmé ou complété
2. Vérifiez que vous n'êtes pas le créateur ou l'adversaire du match
3. Rafraîchissez la page

### Problème : Les images ne s'affichent pas
**Solution** :
1. Vérifiez votre connexion Internet
2. Attendez quelques secondes que les images se chargent
3. Rafraîchissez la page

### Problème : Le total des scores est incorrect
**Solution** :
1. Vérifiez que tous les scores sont saisis
2. Vérifiez que les points peinture sont correctement sélectionnés
3. Rafraîchissez la page

---

## 📞 Support

Pour toute question ou problème :
1. Consultez ce guide
2. Contactez l'administrateur du site
3. Vérifiez votre connexion Internet

---

**Version** : 1.0  
**Date** : 21 novembre 2025  
**Dernière mise à jour** : 21 novembre 2025
