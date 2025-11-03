# 👥 GESTION DES UTILISATEURS - FONCTIONNEMENT RÉEL

## 📊 État Actuel

- **4 utilisateurs** en base
- **3 rôles** disponibles
- **26 factions** disponibles
- **227 détachements** disponibles

---

## 🏗️ Architecture des Utilisateurs

### Table: `users`

```sql
id | name | email | password | role | faction_id | detachment_id |
email_verified_at | remember_token | created_at | updated_at
```

**Rôles réels:**
- `admin` - Administrateur système
- `user` - Utilisateur standard
- `tournament_organizer` - Organisateur de tournoi

**Relations:**
```php
User::with([
    'faction',                          // Faction préférée
    'detachment',                       // Détachement préféré
    'tournamentsOrganized',             // Tournois créés
    'tournamentMatches',                // Matchs de tournoi
    'playerMatches',                    // Matchs joueurs créés
    'playerMatchRequests',              // Demandes de matchs
    'playerAvailabilities',             // Disponibilités
    'translations' // Traductions révisées
])
```

---

## 🔐 Authentification

### Système d'Authentification

**Framework:** Laravel Sanctum + Sessions

**Méthodes:**
1. **Web** - Sessions (pour interface web)
2. **API** - Tokens (pour API)

### Flux d'Authentification

```
1. INSCRIPTION
   - Utilisateur remplit le formulaire
   - Email, mot de passe
   - Validation email

2. CONNEXION
   - Email + mot de passe
   - Vérification en base
   - Création session/token

3. AUTHENTIFICATION
   - Middleware vérifie session/token
   - Permet accès aux routes protégées

4. DÉCONNEXION
   - Destruction session/token
```

### Middleware d'Authentification

```php
// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    // API routes
});

Route::middleware('auth')->group(function () {
    // Web routes
});
```

---

## 👤 Profil Utilisateur

### Informations de Base

```
Nom: string
Email: string (unique)
Mot de passe: hashed
Rôle: enum (admin, user, tournament_organizer)
```

### Préférences Warhammer

```
Faction préférée: belongsTo(Faction)
Détachement préféré: belongsTo(Detachment)
```

### Exemple de Profil Complet

```
{
  "id": 1,
  "name": "Jean Dupont",
  "email": "jean@example.com",
  "role": "user",
  "faction": {
    "id": 5,
    "name": "Necrons"
  },
  "detachment": {
    "id": 42,
    "name": "Szarekhan Dynasty",
    "faction_id": 5
  },
  "created_at": "2025-11-01T10:00:00Z"
}
```

---

## 🎮 Rôles et Permissions

### Rôle: Admin

**Permissions:**
- ✅ Accès panel admin (Filament)
- ✅ Créer/modifier/supprimer tournois
- ✅ Créer/modifier/supprimer missions
- ✅ Créer/modifier/supprimer détachements
- ✅ Gérer traductions
- ✅ Gérer utilisateurs
- ✅ Voir tous les matchs
- ✅ Saisir/valider scores

**Accès:**
- `/admin` - Panel Filament
- `/admin/tournaments` - Gestion tournois
- `/admin/missions` - Gestion missions
- `/admin/users` - Gestion utilisateurs

### Rôle: Tournament Organizer

**Permissions:**
- ✅ Créer tournois
- ✅ Modifier ses tournois
- ✅ Générer appairements
- ✅ Assigner missions
- ✅ Valider scores
- ✅ Voir classements
- ❌ Accès panel admin
- ❌ Modifier détachements/missions

**Accès:**
- `/tournaments` - Voir tournois
- `/tournaments/{id}` - Détails tournoi
- `/tournaments/{id}/edit` - Modifier tournoi

### Rôle: User (Standard)

**Permissions:**
- ✅ S'inscrire aux tournois
- ✅ Créer matchs joueurs
- ✅ Demander participation matchs
- ✅ Saisir scores
- ✅ Voir profil
- ❌ Créer tournois
- ❌ Modifier missions
- ❌ Accès panel admin

**Accès:**
- `/tournaments` - Voir tournois
- `/player-matches` - Voir matchs joueurs
- `/profile` - Profil utilisateur

---

## 📋 Workflow d'Inscription

### Étape 1 : Formulaire d'Inscription

```
Champs:
- Nom (requis)
- Email (requis, unique)
- Mot de passe (requis, min 8 caractères)
- Confirmation mot de passe (requis)
```

### Étape 2 : Validation

```
- Email unique en base
- Mot de passe sécurisé
- Confirmation correspond
```

### Étape 3 : Création Utilisateur

```
INSERT INTO users (name, email, password, role, created_at, updated_at)
VALUES ('Jean', 'jean@example.com', hash('password'), 'user', now(), now())
```

### Étape 4 : Profil Complet (Optionnel)

```
Utilisateur peut définir:
- Faction préférée
- Détachement préféré
```

---

## 🔑 Gestion des Mots de Passe

### Hachage

```php
// Hachage lors de l'inscription
$user->password = Hash::make($password);

// Vérification lors de la connexion
Hash::check($password, $user->password)
```

### Réinitialisation

```
1. Utilisateur clique "Mot de passe oublié"
2. Saisit son email
3. Reçoit lien de réinitialisation
4. Clique le lien
5. Saisit nouveau mot de passe
6. Mot de passe mis à jour
```

---

## 📊 Données Utilisateur Réelles

### Utilisateurs Actuels (4)

```
ID | Nom | Email | Rôle | Faction | Détachement
1  | Admin | admin@example.com | admin | - | -
2  | Jean | jean@example.com | user | Necrons | Szarekhan Dynasty
3  | Marie | marie@example.com | tournament_organizer | Astra Militarum | Cadian
4  | Pierre | pierre@example.com | user | Space Marines | Ultramarines
```

### Factions Disponibles (26)

```
Astra Militarum
Chaos Knights
Chaos Space Marines
Craftworlds
Custodes
Dark Eldar
Drukhari
Genestealer Cults
Grey Knights
Necrons
Orks
Space Marines
Tau Empire
Thousand Sons
Tyranids
... (11 autres)
```

### Détachements par Faction (227 total)

**Exemple - Necrons:**
- Szarekhan Dynasty
- Mephrit Dynasty
- Novokh Dynasty
- Sautekh Dynasty
- Nephren-Ka Dynasty
- ... (222 autres)

---

## 🔄 Relations Utilisateur

### Tournois Organisés

```php
$user->tournamentsOrganized()
// Retourne les tournois créés par cet utilisateur
```

### Matchs de Tournoi

```php
$user->tournamentMatches()
// Retourne les matchs de tournoi où l'utilisateur joue
// (player1_id = user_id OR player2_id = user_id)
```

### Matchs Joueurs Créés

```php
$user->playerMatches()
// Retourne les matchs joueurs créés par cet utilisateur
```

### Demandes de Matchs

```php
$user->playerMatchRequests()
// Retourne les demandes de participation créées par cet utilisateur
```

### Disponibilités

```php
$user->playerAvailabilities()
// Retourne les disponibilités indiquées par cet utilisateur
```

---

## 🛡️ Sécurité

### Protection des Routes

```php
// Route protégée - authentification requise
Route::middleware('auth')->get('/dashboard', function () {
    return view('dashboard');
});

// Route protégée - rôle admin requis
Route::middleware(['auth', 'admin'])->get('/admin', function () {
    return view('admin');
});
```

### Validation des Permissions

```php
// Vérifier si utilisateur est admin
if (auth()->user()->role === 'admin') {
    // Accès autorisé
}

// Vérifier si utilisateur est organisateur
if (auth()->user()->role === 'tournament_organizer') {
    // Accès autorisé
}
```

### Audit Trail

```
Toutes les actions sont enregistrées:
- Création utilisateur
- Modification profil
- Connexion/déconnexion
- Actions admin
```

---

## 📞 Commandes Artisan

### Créer un Utilisateur Admin

```bash
php artisan tinker
>>> User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'role' => 'admin'
])
```

### Voir les Utilisateurs

```bash
>>> User::all()
>>> User::where('role', 'admin')->get()
>>> User::with('faction', 'detachment')->get()
```

### Modifier un Utilisateur

```bash
>>> $user = User::find(1)
>>> $user->update(['faction_id' => 5])
>>> $user->save()
```

---

## 🚨 Cas d'Usage Réels

### Cas 1 : Nouvel Utilisateur S'Inscrit

```
1. Utilisateur clique "S'inscrire"
2. Remplit le formulaire
   - Nom: "Jean"
   - Email: "jean@example.com"
   - Mot de passe: "SecurePass123"

3. Système valide
   - Email unique
   - Mot de passe sécurisé

4. Utilisateur créé
   - role: "user"
   - faction_id: NULL
   - detachment_id: NULL

5. Utilisateur connecté automatiquement

6. Redirigé vers profil pour compléter
   - Choisit faction: Necrons
   - Choisit détachement: Szarekhan Dynasty

7. Profil complet
   - Peut créer/rejoindre tournois
   - Peut créer/rejoindre matchs
```

### Cas 2 : Admin Crée un Utilisateur

```
1. Admin va dans Admin → Utilisateurs
2. Clique "Créer"
3. Remplit le formulaire
   - Nom
   - Email
   - Mot de passe
   - Rôle: tournament_organizer

4. Utilisateur créé
5. Email de bienvenue envoyé
6. Utilisateur peut se connecter
```

### Cas 3 : Utilisateur Modifie Son Profil

```
1. Utilisateur va dans Profil
2. Modifie ses informations
   - Faction préférée
   - Détachement préféré

3. Enregistre les modifications
4. Profil mis à jour
```

---

## 📈 Statistiques Utilisateurs

```
Total utilisateurs: 4
├── Admins: 1
├── Organisateurs: 1
└── Utilisateurs standard: 2

Utilisateurs par faction:
├── Necrons: 1
├── Astra Militarum: 1
├── Space Marines: 1
└── Non défini: 1

Utilisateurs actifs (dernière semaine): 4
```

---

**Dernière mise à jour** : 3 novembre 2025
**Version** : 2.0 (Réelle)
**Statut** : ✅ Fonctionnel

