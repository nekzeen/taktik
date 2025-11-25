# 📋 Guide du Système de Discussion des Règles

## 📖 Vue d'ensemble

Le système de discussion des règles permet aux joueurs de poser des questions sur les règles Warhammer 40K et de recevoir des réponses de la communauté. Le système inclut des fonctionnalités avancées comme la modération, les votes, les tags, et l'archivage.

## 🎯 Fonctionnalités

### Pour les Joueurs
- ✅ Poser des questions sur les règles
- ✅ Répondre aux questions d'autres joueurs
- ✅ Voter (utile/pas utile) sur les discussions et réponses
- ✅ Archiver ses propres discussions
- ✅ Modifier/supprimer ses discussions et réponses
- ✅ Recevoir des notifications quand quelqu'un répond

### Pour les Modérateurs/Admins
- ✅ Approuver/rejeter les réponses (si modération activée)
- ✅ Épingler les discussions importantes
- ✅ Déplacer les discussions entre catégories
- ✅ Archiver/désarchiver les discussions
- ✅ Supprimer les discussions problématiques
- ✅ Gérer les catégories et tags

### Pour les Super-Admins
- ✅ Gérer tous les paramètres du système
- ✅ Configurer le nombre max d'images
- ✅ Activer/désactiver les notifications
- ✅ Activer/désactiver la modération
- ✅ Configurer l'archivage automatique

## 🌐 Accès aux Pages

### Pages Publiques
- **Index**: `/rule-discussions` - Liste de toutes les discussions
- **Détail**: `/rule-discussions/{id}` - Voir une discussion et ses réponses
- **Archivées**: `/rule-discussions/archived` - Discussions archivées

### Pages Authentifiées
- **Créer**: `/rule-discussions/create` - Poser une nouvelle question
- **Éditer**: `/rule-discussions/{id}/edit` - Modifier sa question
- **Voter**: POST `/rule-discussions/{id}/vote` - Voter sur une discussion
- **Répondre**: POST `/rule-discussions/{id}/replies` - Répondre à une question

### Pages Admin (Filament)
- **Discussions**: `/admin/rule-discussions` - Gérer les discussions
- **Catégories**: `/admin/rule-discussion-categories` - Gérer les catégories
- **Tags**: `/admin/rule-discussion-tags` - Gérer les tags
- **Paramètres**: `/admin/rule-discussion-settings` - Configurer le système

## 📊 Base de Données

### Tables Principales
- `rule_discussions` - Les discussions
- `rule_discussion_replies` - Les réponses
- `rule_discussion_categories` - Les catégories
- `rule_discussion_tags` - Les tags
- `rule_discussion_votes` - Les votes sur les discussions
- `rule_discussion_reply_votes` - Les votes sur les réponses
- `rule_discussion_settings` - Les paramètres du système

## 🔧 Configuration

### Paramètres Disponibles

Modifiez les paramètres via `/admin/rule-discussion-settings` :

| Paramètre | Défaut | Description |
|-----------|--------|-------------|
| `max_images_per_discussion` | 1 | Nombre max d'images par discussion |
| `enable_notifications` | true | Activer les notifications par email |
| `enable_moderation` | true | Activer la modération des réponses |
| `auto_archive_days` | 90 | Archiver automatiquement après X jours |

### Catégories Prédéfinies

1. Règles par faction
2. Règles générales
3. Règles liées aux missions primaires
4. Règles liées aux missions secondaires
5. Règles péripéties

### Tags Prédéfinis

- Déploiement
- Scoring
- Unités
- Terrain
- Stratégies
- Interactions
- Clarification
- Erreur de règles
- Cas limite
- Compétitif

## 🔐 Permissions

### Créer une Discussion
- ✅ Joueurs authentifiés

### Répondre à une Discussion
- ✅ Joueurs authentifiés

### Voter
- ✅ Joueurs authentifiés

### Modifier sa Discussion
- ✅ Auteur de la discussion

### Supprimer sa Discussion
- ✅ Auteur de la discussion

### Modérer (Approuver/Rejeter/Épingler)
- ✅ Modérateurs, Admins, Super-Admins

### Archiver sa Discussion
- ✅ Auteur de la discussion

### Désarchiver
- ✅ Modérateurs, Admins, Super-Admins

## 📧 Notifications

Quand un joueur répond à votre discussion, vous recevez une notification par email (si activé).

La notification contient:
- Le nom du répondeur
- Un extrait de la réponse
- Un lien direct vers la réponse

## 🤖 Archivage Automatique

Le système archive automatiquement les discussions selon le paramètre `auto_archive_days`.

**Command**: `php artisan rule-discussions:archive`

**Planification**: Tous les jours à 3h du matin (via le scheduler)

Les discussions archivées:
- Restent visibles dans la page `/rule-discussions/archived`
- Peuvent être désarchivées par les modérateurs
- Ne reçoivent plus de réponses automatiquement

## 🔍 Recherche

Utilisez la barre de recherche sur `/rule-discussions` pour:
- Rechercher par titre
- Rechercher par contenu
- Filtrer par catégorie
- Filtrer par tags
- Trier par: récent, utile, réponses

## 📱 Responsive Design

Le système est entièrement responsive et fonctionne sur:
- Desktop
- Tablette
- Mobile

## 🎨 Styling

Le système utilise Tailwind CSS et respecte le design global de l'application.

Couleurs principales:
- Rouge: `#b91c1c` (primary)
- Vert: Statut ouvert
- Rouge: Statut fermé
- Jaune: Statut résolu
- Gris: Statut archivé

## 🚀 Déploiement

1. Exécuter les migrations: `php artisan migrate`
2. Exécuter le seeder: `php artisan db:seed --class=RuleDiscussionSeeder`
3. Compiler les assets: `npm run build`
4. Vider le cache: `php artisan cache:clear`

## 📝 Modèles

### RuleDiscussion
```php
$discussion->user;           // Auteur
$discussion->category;       // Catégorie
$discussion->replies;        // Réponses
$discussion->approvedReplies; // Réponses approuvées
$discussion->tags;           // Tags
$discussion->votes;          // Votes
$discussion->usefulVotes();  // Votes utiles
$discussion->notUsefulVotes(); // Votes pas utiles
```

### RuleDiscussionReply
```php
$reply->discussion;          // Discussion
$reply->user;               // Auteur
$reply->votes;              // Votes
$reply->status;             // Statut (approved, pending, rejected)
$reply->isApproved();       // Est approuvée?
$reply->isPending();        // Est en attente?
$reply->isRejected();       // Est rejetée?
```

## 🐛 Dépannage

### Les discussions ne s'affichent pas
- Vérifier que les migrations ont été exécutées
- Vérifier que le seeder a été exécuté
- Vérifier les logs: `storage/logs/laravel.log`

### Les notifications ne sont pas envoyées
- Vérifier que `enable_notifications` est activé
- Vérifier la configuration du mail dans `.env`
- Vérifier les logs des jobs: `storage/logs/laravel.log`

### Les réponses ne s'affichent pas
- Si modération activée, vérifier que les réponses sont approuvées
- Vérifier que l'utilisateur a les permissions

### L'archivage automatique ne fonctionne pas
- Vérifier que le scheduler est en cours d'exécution
- Exécuter manuellement: `php artisan rule-discussions:archive`
- Vérifier les logs: `storage/logs/rule-discussions-archive.log`

## 📞 Support

Pour toute question ou problème, consultez:
- La documentation Laravel: https://laravel.com/docs
- La documentation Filament: https://filamentphp.com
- Les logs de l'application: `storage/logs/laravel.log`
