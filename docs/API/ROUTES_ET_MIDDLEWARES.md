# 🛣️ ROUTES ET MIDDLEWARES - DOCUMENTATION COMPLÈTE

**Date**: 3 novembre 2025
**Fichier de configuration**: `routes/web.php`

---

## 📋 ROUTES PUBLIQUES (Sans Authentification)

### Authentification

| Méthode | Route | Contrôleur | Description |
|---------|-------|-----------|-------------|
| GET | `/register` | `RegisteredUserController@create` | Formulaire d'inscription |
| POST | `/register` | `RegisteredUserController@store` | Créer un utilisateur |
| GET | `/login` | `AuthenticatedSessionController@create` | Formulaire de connexion |
| POST | `/login` | `AuthenticatedSessionController@store` | Authentifier l'utilisateur |
| POST | `/logout` | `AuthenticatedSessionController@destroy` | Déconnecter l'utilisateur |

### Réinitialisation de Mot de Passe

| Méthode | Route | Contrôleur | Description |
|---------|-------|-----------|-------------|
| GET | `/forgot-password` | `PasswordResetLinkController@create` | Formulaire oubli mot de passe |
| POST | `/forgot-password` | `PasswordResetLinkController@store` | Envoyer lien réinitialisation |
| GET | `/reset-password/{token}` | `NewPasswordController@create` | Formulaire nouveau mot de passe |
| POST | `/reset-password` | `NewPasswordController@store` | Réinitialiser mot de passe |

### Vérification Email

| Méthode | Route | Contrôleur | Description |
|---------|-------|-----------|-------------|
| GET | `/email/verify` | `EmailVerificationPromptController@__invoke` | Formulaire vérification email |
| GET | `/email/verify/{id}/{hash}` | `VerifyEmailController@__invoke` | Vérifier email |
| POST | `/email/verification-notification` | `EmailVerificationNotificationController@store` | Renvoyer email vérification |

---

## 🔐 ROUTES AUTHENTIFIÉES

### Profil Utilisateur

| Méthode | Route | Contrôleur | Description | Middleware |
|---------|-------|-----------|-------------|-----------|
| GET | `/profile` | `ProfileController@edit` | Voir profil | auth |
| PATCH | `/profile` | `ProfileController@update` | Modifier profil | auth |
| DELETE | `/profile` | `ProfileController@destroy` | Supprimer profil | auth |

### Confirmation de Mot de Passe

| Méthode | Route | Contrôleur | Description | Middleware |
|---------|-------|-----------|-------------|-----------|
| GET | `/confirm-password` | `ConfirmablePasswordController@show` | Formulaire confirmation | auth |
| POST | `/confirm-password` | `ConfirmablePasswordController@store` | Confirmer mot de passe | auth |

### Modification de Mot de Passe

| Méthode | Route | Contrôleur | Description | Middleware |
|---------|-------|-----------|-------------|-----------|
| PUT | `/password` | `PasswordController@update` | Modifier mot de passe | auth |

---

## 🎮 ROUTES MATCHS JOUEURS

### Matchs Joueurs

| Méthode | Route | Contrôleur | Description | Middleware |
|---------|-------|-----------|-------------|-----------|
| GET | `/player-matches` | `PlayerMatchController@index` | Lister les matchs | auth |
| GET | `/player-matches/create` | `PlayerMatchController@create` | Formulaire création | auth |
| POST | `/player-matches` | `PlayerMatchController@store` | Créer un match | auth |
| GET | `/player-matches/{id}` | `PlayerMatchController@show` | Voir détails match | auth |
| GET | `/player-matches/{id}/edit` | `PlayerMatchController@edit` | Formulaire modification | auth |
| PATCH | `/player-matches/{id}` | `PlayerMatchController@update` | Modifier un match | auth |
| DELETE | `/player-matches/{id}` | `PlayerMatchController@destroy` | Supprimer un match | auth |

### Demandes de Matchs

| Méthode | Route | Contrôleur | Description | Middleware |
|---------|-------|-----------|-------------|-----------|
| GET | `/player-matches/{id}/request` | `PlayerMatchRequestController@create` | Formulaire demande | auth |
| POST | `/player-matches/{id}/request` | `PlayerMatchRequestController@store` | Créer demande | auth |
| GET | `/player-matches/{id}/requests` | `PlayerMatchRequestController@index` | Voir demandes | auth |
| POST | `/player-match-requests/{id}/accept` | `PlayerMatchRequestController@accept` | Accepter demande | auth |
| POST | `/player-match-requests/{id}/reject` | `PlayerMatchRequestController@reject` | Rejeter demande | auth |

### Disponibilités

| Méthode | Route | Contrôleur | Description | Middleware |
|---------|-------|-----------|-------------|-----------|
| POST | `/player-availabilities` | `PlayerAvailabilityController@store` | Ajouter disponibilité | auth |
| DELETE | `/player-availabilities/{id}` | `PlayerAvailabilityController@destroy` | Supprimer disponibilité | auth |

---

## 🏆 ROUTES TOURNOIS

### Tournois

| Méthode | Route | Contrôleur | Description | Middleware |
|---------|-------|-----------|-------------|-----------|
| GET | `/tournaments` | `TournamentController@index` | Lister les tournois | auth |
| GET | `/tournaments/{id}` | `TournamentController@show` | Voir détails tournoi | auth |
| GET | `/tournaments/{id}/ranking` | `TournamentController@ranking` | Voir classement | auth |

### Matchs de Tournoi

| Méthode | Route | Contrôleur | Description | Middleware |
|---------|-------|-----------|-------------|-----------|
| GET | `/tournaments/{id}/matches` | `TournamentMatchController@index` | Lister matchs | auth |
| GET | `/tournament-matches/{id}` | `TournamentMatchController@show` | Voir détails match | auth |
| PATCH | `/tournament-matches/{id}/score` | `TournamentMatchController@setScore` | Saisir scores | auth |

---

## 🎯 ROUTES AUTRES

### Accueil

| Méthode | Route | Contrôleur | Description | Middleware |
|---------|-------|-----------|-------------|-----------|
| GET | `/` | `HomeController@index` | Page d'accueil | - |
| GET | `/dashboard` | `HomeController@dashboard` | Tableau de bord | auth |

### Thème

| Méthode | Route | Contrôleur | Description | Middleware |
|---------|-------|-----------|-------------|-----------|
| POST | `/theme/{theme}` | `ThemeController@set` | Changer thème | auth |

---

## 🔐 MIDDLEWARES DISPONIBLES

### Middleware d'Authentification

```php
'auth' => \App\Http\Middleware\Authenticate::class
```

**Utilisation** : Protège les routes nécessitant une authentification

**Comportement** : Redirige vers `/login` si non authentifié

### Middleware de Vérification Email

```php
'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class
```

**Utilisation** : Vérifie que l'email est confirmé

**Comportement** : Redirige vers `/email/verify` si non vérifié

### Middleware de Throttle

```php
'throttle:60,1' => \Illuminate\Routing\Middleware\ThrottleRequests::class
```

**Utilisation** : Limite les requêtes (60 par minute)

**Comportement** : Retourne 429 si limite dépassée

---

## 📊 STRUCTURE DES ROUTES

### Routes Publiques
```
/register
/login
/forgot-password
/reset-password
/email/verify
```

### Routes Authentifiées
```
/profile
/password
/player-matches
/player-match-requests
/tournaments
/tournament-matches
/dashboard
```

### Routes Admin (Filament)
```
/admin/*
```

---

## 🔄 FLUX DE ROUTAGE

### Utilisateur Non Authentifié

```
GET / → HomeController@index
GET /login → AuthenticatedSessionController@create
POST /login → AuthenticatedSessionController@store
  ↓
Authentification réussie
  ↓
GET /dashboard → HomeController@dashboard
```

### Utilisateur Authentifié

```
GET /player-matches → PlayerMatchController@index
GET /player-matches/create → PlayerMatchController@create
POST /player-matches → PlayerMatchController@store
  ↓
Match créé
  ↓
GET /player-matches/{id} → PlayerMatchController@show
```

---

## 🛡️ PROTECTION DES ROUTES

### Routes Protégées par Authentification

```php
Route::middleware('auth')->group(function () {
    Route::get('/player-matches', ...);
    Route::post('/player-matches', ...);
    Route::get('/tournaments', ...);
    // etc.
});
```

### Routes Publiques

```php
Route::get('/', ...);
Route::get('/register', ...);
Route::post('/register', ...);
Route::get('/login', ...);
Route::post('/login', ...);
// etc.
```

---

## 📝 RÉSUMÉ

**Routes Publiques** : 8 routes
**Routes Authentifiées** : 20+ routes
**Routes Admin** : Gérées par Filament

**Middlewares** :
- `auth` - Authentification
- `verified` - Vérification email
- `throttle` - Limitation requêtes

**Flux** :
- Utilisateur non authentifié → `/login`
- Utilisateur authentifié → `/dashboard`
- Admin → `/admin`

