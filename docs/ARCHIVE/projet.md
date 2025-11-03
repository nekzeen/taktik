# Contexte
Je développe une application Laravel 12 pour gérer des tournois/match Warhammer 40k avec les fonctionnalités suivantes :
- **Gestion des tournois** : Création, modification, fermeture.
- **Listes d’armées** : Chaque joueur soumet une liste unique par tournoi (PDF ou formulaire), modifiable uniquement par l’admin après validation.
- **Données BSData/wh40k-10e** : Import des unités, équipements, aptitudes depuis les fichiers `.cat` (XML) du dépôt GitHub [BSData/wh40k-10e](https://github.com/BSData/wh40k-10e).
- **Traductions** : Les descriptions/règles sont traduites automatiquement en français (sauf les noms d’unités, qui restent en anglais), avec possibilité de correction manuelle via une interface admin.
- **Sécurité/RGPD** : Chiffrement des données personnelles, gestion des rôles (admin, joueur), stockage sécurisé des PDF.
- **Interface moderne** : Backoffice avec interface intuitive et responsive.
- **Agenda partagé** : Mise en place d'un agenda partagé entre les utilisateurs afin de prévoir les possibilités de match.

#backoffice admin

- l'administrateur doit pouvoir gérer les utilisateurs
- l'administrateur doit pouvoir créer/éditer et supprimer des tournois/match
- l'administrateur doit pouvoir demander une mise à jour manuelle des données en provenance de https://github.com/BSData/wh40k-10e
- l'administeur doit pouvoir intervenir sur les traductions automatique des données en provenance de https://github.com/BSData/wh40k-10e
- l'administrateur doit pouvoir gérer l'apparence du frontoffice
- l'administrateur doit pouvoir ajouter des pages au frontoffice
- l'administrateur doit pouvoir gérer les menus du frontoffice (ajout de lien externe, pages du backoffice)
- l'administrateur doit pouvoir gérer l'agenda totalement.
- l'administrateur doit pouvoir gérer les PDF soumis par les utilisateurs.

#backoffice utilisateurs
- les utilisateurs doivent pouvoir créer un compte
- les utilisateurs doivent pouvoir modifier ses informations personnelles et son mot de passe
- les utilisateurs doivent pouvoir s'enregistrer pour les tournois en cours
- les utilisateurs doivent pouvoir créer des demandes de match qui seront ajoutés automatiquement à l'agenda, la demande de match sera en status ouverte.
- les utilisateurs doivent pouvoir s'inscrire pour des propositions de match en status ouvertes qui deviendront "fermées" automatiquement.
- les utilisateurs doivent pouvoir définir leurs disponibilité dans l'agenda pour les tournois en cours et match prévus ainsi que pouvoir modifier leurs entrées dans l'agenda
- les utilisateurs doivent pouvoir s'inscrire à un tournoi en cours
- les utilisateurs doivent pouvoir uploader leur liste d'armée en pdf

#frontoffice
- le front office doit présenter simplement et de manière visuellement simple et agréable les tournois en cours et match prévus sur la page d'accueil.
- Il faut une page présentant l'agenda avec les disponibilité de chacun soit pour les tournois en cours et match prévus.
- il faut une page tournoi présentant les matchs effectués lors du tournoi, les matchs restant à effectuer et le résultat en cours du tournois.

#gestion des tournois
- un tournoi peut être définit sur une période ou non
- un tournoi doit avoir une période d'enregistrement limite des joueurs ou non
- lors d'un tournoi ayant une période limite d'enregistrement, l'arboresence des match sera calculé automatiquement et deux pages seront créées. Une pour le backoffice pour l'administrateur pourra gérer manuellement le tournoi (ajout des scores,...). Une page frontoffice ou sera uniquement visible l'arborescence du tournoi avec les résultat de chaque match et résultat global.

#gestion des liste d'armée
- lors de la définition d'un tournoi et d'un match, sur la page front office correspondante, il faudra mettre à libre disposition le PDF téléchargé par les joueurs contenant leurs listes d'armée. Par exemple, le nom du joueur associé à sa faction (Orks, Empire T'au, Chevaliers du Chaos, ...) associé à un lien renvoyant vers le PDF associé.
- il faudra que le nom du détachement choisi par le joueur apparaissent.

#Mise en place
- il faut utiliser au maximum des packages officiels et maintenu.
- il faut faire le maximum de test de fonctionnement
- il faut prendre en compte le volet sécurité et protection des données
- On ne peux faire des upload que des fichiers dont le format est autorisé

#Détails
- ne demande pas pour lancer des commandes, fait le directement
- vide le cache si nécessaire

---

# Modèle de données

## Tables principales

### users
- `id` : identifiant unique
- `name` : nom complet
- `email` : email (unique)
- `password` : mot de passe hashé
- `role` : admin, moderator, player, visitor
- `phone` : téléphone (chiffré)
- `consent_at` : date consentement RGPD
- `last_activity_at` : dernière activité
- `deleted_at` : soft delete
- `created_at`, `updated_at`

### tournaments
- `id` : identifiant unique
- `name` : nom du tournoi
- `description` : description
- `format` : elimination, swiss, league (élimination directe, ronde suisse, ligue)
- `start_date` : date début
- `end_date` : date fin (nullable)
- `registration_deadline` : date limite inscription (nullable)
- `max_players` : nombre max joueurs (nullable)
- `status` : draft, open, registration_closed, in_progress, completed, cancelled
- `bracket_generated_at` : date génération arborescence
- `created_by` : user_id admin créateur
- `created_at`, `updated_at`

### matches
- `id` : identifiant unique
- `tournament_id` : lien tournoi (nullable pour matchs libres)
- `type` : tournament, friendly (match tournoi ou amical)
- `round` : numéro de round (pour tournois)
- `player1_id` : user_id joueur 1
- `player2_id` : user_id joueur 2 (nullable si pas encore assigné)
- `player1_score` : score joueur 1
- `player2_score` : score joueur 2
- `winner_id` : user_id gagnant
- `status` : pending, confirmed, in_progress, completed, cancelled
- `scheduled_at` : date/heure prévue
- `played_at` : date/heure réelle
- `location` : lieu (optionnel)
- `created_at`, `updated_at`

### army_lists
- `id` : identifiant unique
- `user_id` : propriétaire
- `tournament_id` : tournoi associé (nullable)
- `faction_id` : faction choisie
- `detachment` : nom du détachement
- `points` : total points
- `pdf_path` : chemin fichier PDF
- `pdf_size` : taille fichier (bytes)
- `pdf_hash` : hash SHA256 du fichier
- `status` : draft, pending, validated, rejected
- `validated_at` : date validation
- `validated_by` : user_id admin validateur
- `rejection_reason` : raison rejet (nullable)
- `created_at`, `updated_at`

### factions
- `id` : identifiant unique
- `bsdata_id` : ID depuis BSData
- `name` : nom faction (EN)
- `name_fr` : nom faction (FR, optionnel)
- `version` : version BSData
- `imported_at` : date import
- `created_at`, `updated_at`

### units
- `id` : identifiant unique
- `faction_id` : faction parente
- `bsdata_id` : ID depuis BSData
- `name` : nom unité (EN, non traduit)
- `description` : description (EN)
- `description_fr` : description traduite (FR)
- `points` : coût en points
- `translation_status` : pending, auto, reviewed, approved
- `created_at`, `updated_at`

### abilities
- `id` : identifiant unique
- `bsdata_id` : ID depuis BSData
- `name` : nom aptitude (EN, non traduit)
- `description` : description (EN)
- `description_fr` : description traduite (FR)
- `translation_status` : pending, auto, reviewed, approved
- `created_at`, `updated_at`

### wargear
- `id` : identifiant unique
- `bsdata_id` : ID depuis BSData
- `name` : nom équipement (EN, non traduit)
- `description` : description (EN)
- `description_fr` : description traduite (FR)
- `translation_status` : pending, auto, reviewed, approved
- `created_at`, `updated_at`

### calendar_slots
- `id` : identifiant unique
- `user_id` : utilisateur concerné
- `start_at` : date/heure début
- `end_at` : date/heure fin
- `type` : availability, match, tournament (disponibilité, match, tournoi)
- `status` : proposed, confirmed, cancelled
- `match_id` : lien match (nullable)
- `tournament_id` : lien tournoi (nullable)
- `timezone` : fuseau horaire (défaut: Europe/Paris)
- `created_at`, `updated_at`

### match_requests
- `id` : identifiant unique
- `creator_id` : user_id créateur demande
- `opponent_id` : user_id adversaire (nullable si ouvert)
- `proposed_at` : date proposition
- `status` : open, accepted, rejected, cancelled
- `match_id` : lien match créé (nullable)
- `message` : message optionnel
- `created_at`, `updated_at`

### pages
- `id` : identifiant unique
- `slug` : URL slug (unique)
- `title` : titre page
- `content` : contenu HTML
- `published` : booléen publication
- `created_by` : user_id créateur
- `created_at`, `updated_at`

### menus
- `id` : identifiant unique
- `label` : libellé menu
- `url` : URL (interne ou externe)
- `order` : ordre affichage
- `parent_id` : menu parent (nullable)
- `target` : _self, _blank
- `published` : booléen publication
- `created_at`, `updated_at`

### translations
- `id` : identifiant unique
- `resource_type` : type ressource (Unit, Ability, Wargear)
- `resource_id` : ID ressource
- `field` : champ traduit (description, rules, etc.)
- `locale` : langue cible (fr)
- `source_text` : texte source (EN)
- `translated_text` : texte traduit
- `status` : pending, auto, reviewed, approved
- `reviewed_by` : user_id admin réviseur (nullable)
- `reviewed_at` : date révision (nullable)
- `created_at`, `updated_at`

### audit_logs
- `id` : identifiant unique
- `user_id` : utilisateur ayant effectué l'action
- `action` : type action (create, update, delete, validate, etc.)
- `resource_type` : type ressource modifiée
- `resource_id` : ID ressource
- `changes` : JSON des modifications
- `ip_address` : adresse IP
- `user_agent` : navigateur
- `created_at`

### bsdata_imports
- `id` : identifiant unique
- `version` : version importée
- `status` : pending, in_progress, completed, failed
- `started_at` : date début import
- `completed_at` : date fin import
- `factions_count` : nombre factions importées
- `units_count` : nombre unités importées
- `errors` : JSON des erreurs rencontrées
- `triggered_by` : user_id (nullable si automatique)
- `created_at`, `updated_at`

---

# Règles métier

## Formats de tournoi

### Élimination directe (elimination)
- Bracket à élimination simple
- Nombre de joueurs : puissance de 2 (8, 16, 32, etc.)
- Si nombre impair : byes automatiques
- Génération automatique après clôture inscriptions

### Ronde suisse (swiss)
- Nombre de rounds défini (généralement log2(joueurs))
- Appariement par score similaire
- Pas d'élimination
- Classement final par points

### Ligue (league)
- Tous contre tous
- Nombre de matchs = n(n-1)/2
- Classement par points

## Scoring des matchs
- **Victoire** : 3 points
- **Égalité** : 1 point
- **Défaite** : 0 point
- **Forfait** : 0 point + pénalité possible

## Statuts des entités

### Tournoi
- `draft` : brouillon, non publié
- `open` : inscriptions ouvertes
- `registration_closed` : inscriptions fermées, en attente de début
- `in_progress` : tournoi en cours
- `completed` : terminé
- `cancelled` : annulé

### Match
- `pending` : en attente d'adversaire (matchs libres)
- `confirmed` : confirmé, adversaires assignés
- `in_progress` : en cours
- `completed` : terminé, scores enregistrés
- `cancelled` : annulé

### Liste d'armée
- `draft` : brouillon utilisateur
- `pending` : soumise, en attente validation
- `validated` : validée par admin
- `rejected` : rejetée par admin

### Demande de match
- `open` : ouverte à tous
- `accepted` : acceptée, match créé
- `rejected` : refusée
- `cancelled` : annulée par créateur

## Règles de validation

### Listes d'armées
- **Format** : PDF uniquement
- **Taille max** : 5 MB
- **Contenu requis** : faction, détachement, total points
- **Validation** : manuelle par admin avant publication
- **Visibilité** : publique après validation, privée avant

### Inscriptions tournoi
- Possible uniquement si statut `open`
- Avant `registration_deadline` si définie
- Maximum `max_players` si défini
- Une liste d'armée validée obligatoire

### Matchs libres
- Créateur propose créneau via `match_requests`
- Statut `open` : n'importe qui peut accepter
- Acceptation → création `match` + `calendar_slots` pour les 2 joueurs
- Annulation possible jusqu'à 24h avant `scheduled_at`

---

# Sécurité et RGPD

## Chiffrement
- **Méthode** : AES-256-CBC
- **Champs chiffrés** : `users.phone`, données sensibles
- **Clés** : stockées dans `.env`, rotation annuelle
- **Hashing mots de passe** : bcrypt (Laravel défaut)

## Rôles et permissions

### Super Admin
- Tous les droits
- Gestion des admins
- Accès logs système

### Admin
- Gestion tournois (CRUD)
- Gestion utilisateurs (lecture, modification, suspension)
- Validation listes d'armées
- Gestion traductions
- Gestion pages/menus
- Import BSData manuel

### Modérateur
- Validation listes d'armées
- Modération matchs
- Gestion agenda (lecture)

### Joueur
- Inscription tournois
- Upload listes d'armées
- Création/acceptation demandes match
- Gestion disponibilités agenda
- Modification profil

### Visiteur
- Lecture frontoffice uniquement
- Pas d'accès backoffice

## Politique RGPD

### Consentement
- Requis à l'inscription
- Checkbox explicite
- Révocable à tout moment

### Rétention des données
- **Comptes actifs** : conservation illimitée
- **Comptes inactifs** : suppression après 2 ans sans activité
- **Données tournois** : conservation 5 ans (archivage)
- **Logs audit** : conservation 1 an

### Droits utilisateurs
- **Accès** : export JSON de toutes données personnelles
- **Rectification** : modification profil à tout moment
- **Suppression** : anonymisation (conservation stats tournois)
- **Portabilité** : export format machine-readable
- **Opposition** : désinscription emails/notifications

### Sécurité fichiers
- **Validation MIME type** : PDF uniquement
- **Scan antivirus** : ClamAV (optionnel, recommandé)
- **Stockage** : `storage/app/private/army_lists/`
- **Accès** : via contrôleur avec `Policy`, liens signés temporaires
- **Nommage** : hash unique + extension
- **Backup** : quotidien, rétention 30 jours

### Audit trail
- Toutes actions sensibles loguées dans `audit_logs`
- IP + User-Agent enregistrés
- Conservation 1 an
- Consultation admin uniquement

---

# Choix techniques

## Stack
- **Framework** : Laravel 12
- **PHP** : 8.3+
- **Base de données** : MySQL 8.0+ ou PostgreSQL 15+
- **Cache** : Redis 7+
- **Queue** : Redis (driver Laravel)
- **Frontend** : Blade + Alpine.js + Tailwind CSS
- **Icons** : Heroicons ou Lucide
- **Admin UI** : Filament 3 (recommandé) ou custom

## Packages Laravel recommandés
- **Authentification** : Laravel Breeze ou Jetstream
- **Permissions** : spatie/laravel-permission
- **Audit** : owen-it/laravel-auditing
- **Media** : spatie/laravel-medialibrary (optionnel)
- **Backup** : spatie/laravel-backup
- **Activity Log** : spatie/laravel-activitylog
- **Translatable** : spatie/laravel-translatable (optionnel)

## Import BSData
- **Parsing XML** : SimpleXML (natif PHP) ou spatie/xml-to-array
- **Fréquence** : hebdomadaire (cron) + manuel admin
- **Stockage** : DB (tables normalisées)
- **Versioning** : table `bsdata_imports` avec historique
- **Fallback** : conserver dernière version valide en cas d'erreur

## Service de traduction
- **Option 1** : DeepL API (payant, ~20€/mois pour 500k caractères)
- **Option 2** : Google Cloud Translation (gratuit jusqu'à 500k caractères/mois)
- **Option 3** : LibreTranslate (self-hosted, gratuit, qualité moindre)
- **Workflow** : traduction auto → statut `auto` → revue admin → statut `approved`
- **Cache** : traductions approuvées mises en cache Redis

## Stockage fichiers
- **Local** : `storage/app/private/` (développement)
- **Production** : AWS S3 ou équivalent (DigitalOcean Spaces, Scaleway)
- **CDN** : CloudFlare (optionnel, pour assets statiques)

## Environnements
- **Local** : Laravel Sail (Docker) ou Valet
- **Staging** : serveur dédié ou VPS
- **Production** : VPS (OVH, Scaleway) ou cloud (AWS, DigitalOcean)

## CI/CD
- **Tests** : PHPUnit + Pest (optionnel)
- **Linting** : Laravel Pint ou PHP CS Fixer
- **Pipeline** : GitHub Actions ou GitLab CI
- **Déploiement** : Deployer ou Envoyer

---

# Plan d'implémentation

## Phase 1 : Fondations (2-3 semaines)
- [ ] Installation Laravel 12
- [ ] Configuration environnements (local, staging, prod)
- [ ] Authentification (Breeze/Jetstream)
- [ ] Système de rôles (Spatie Permission)
- [ ] Migrations tables principales
- [ ] Seeders de test
- [ ] CRUD utilisateurs (backoffice admin)
- [ ] Tests unitaires services de base

## Phase 2 : Gestion tournois (2-3 semaines)
- [ ] CRUD tournois (backoffice admin)
- [ ] Inscription tournois (backoffice utilisateurs)
- [ ] Upload listes d'armées (PDF)
- [ ] Validation listes (backoffice admin)
- [ ] Génération brackets (élimination directe)
- [ ] Page tournoi frontoffice (liste, détails)
- [ ] Tests feature tournois

## Phase 3 : Import BSData (2-3 semaines)
- [ ] Service parsing XML BSData
- [ ] Import factions/unités/aptitudes/équipements
- [ ] Commande artisan import manuelle
- [ ] Job planifié hebdomadaire
- [ ] Gestion versions et historique
- [ ] Interface admin visualisation données importées
- [ ] Tests import et parsing

## Phase 4 : Traductions (1-2 semaines)
- [ ] Intégration API traduction (DeepL ou Google)
- [ ] Job traduction asynchrone
- [ ] Interface admin révision traductions
- [ ] Workflow validation (auto → reviewed → approved)
- [ ] Cache traductions approuvées
- [ ] Tests traductions

## Phase 5 : Agenda & Matchs (2-3 semaines)
- [ ] Système calendar_slots
- [ ] Gestion disponibilités utilisateurs
- [ ] Création demandes match (match_requests)
- [ ] Acceptation/refus demandes
- [ ] Création matchs automatique
- [ ] Détection conflits horaires
- [ ] Notifications email (match confirmé, rappel)
- [ ] Page agenda frontoffice
- [ ] Tests agenda et matchs

## Phase 6 : CMS & Personnalisation (1-2 semaines)
- [ ] CRUD pages (backoffice admin)
- [ ] CRUD menus (backoffice admin)
- [ ] Éditeur WYSIWYG (TinyMCE ou Tiptap)
- [ ] Gestion apparence (couleurs, logo)
- [ ] Affichage pages/menus frontoffice
- [ ] Tests CMS

## Phase 7 : Sécurité & RGPD (1-2 semaines)
- [ ] Chiffrement données sensibles
- [ ] Policies Laravel (autorisation fine)
- [ ] Audit logs (spatie/laravel-activitylog)
- [ ] Page politique confidentialité
- [ ] Export données utilisateur (RGPD)
- [ ] Suppression/anonymisation compte
- [ ] Validation sécurité fichiers (MIME, taille, antivirus)
- [ ] Tests sécurité

## Phase 8 : Frontoffice & UX (1-2 semaines)
- [ ] Design responsive (Tailwind CSS)
- [ ] Page accueil (tournois en cours, prochains matchs)
- [ ] Page liste tournois
- [ ] Page détail tournoi (bracket, matchs, classement)
- [ ] Page agenda partagé
- [ ] Page profil utilisateur
- [ ] Notifications temps réel (optionnel, Laravel Echo)
- [ ] Tests E2E (Dusk ou Playwright)

## Phase 9 : Optimisation & Tests (1-2 semaines)
- [ ] Optimisation requêtes (eager loading, index DB)
- [ ] Cache pages frontoffice
- [ ] Tests de charge (Apache Bench ou k6)
- [ ] Monitoring (Laravel Telescope en dev)
- [ ] Logs structurés (Monolog)
- [ ] Documentation technique
- [ ] Documentation utilisateur

## Phase 10 : Déploiement (1 semaine)
- [ ] Configuration serveur production
- [ ] CI/CD (GitHub Actions)
- [ ] Migrations production
- [ ] Seeders données initiales
- [ ] Backup automatique (DB + fichiers)
- [ ] Monitoring production (Sentry, Laravel Forge)
- [ ] Tests post-déploiement
- [ ] Formation admin

---

# Estimation globale
**Durée totale** : 14-20 semaines (3,5 à 5 mois)
**Charge** : 1 développeur full-time ou 2 développeurs part-time

# Prochaines étapes immédiates
1. Valider ce document complété
2. Choisir service de traduction (DeepL recommandé)
3. Choisir format tournoi par défaut (élimination directe recommandé pour MVP)
4. Définir environnement de développement (Laravel Sail recommandé)
5. Initialiser projet Laravel 12
