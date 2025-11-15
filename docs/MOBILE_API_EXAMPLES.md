# 📡 EXEMPLES API REST POUR MOBILE

**Date**: 7 novembre 2025

---

## 1. AUTHENTIFICATION

### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123",
  "device_name": "iPhone 15 Pro"
}
```

**Response 200:**
```json
{
  "token": "1|abc123def456...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "faction_id": 5,
    "detachment_id": 155,
    "role": "user"
  }
}
```

### Logout
```http
POST /api/auth/logout
Authorization: Bearer 1|abc123def456...
```

**Response 200:**
```json
{
  "message": "Logged out successfully"
}
```

---

## 2. TOURNOIS

### Lister les tournois
```http
GET /api/tournaments?page=1&per_page=15&sort=start_date
Authorization: Bearer 1|abc123def456...
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Warhammer 40K Championship 2025",
      "description": "Tournoi régional",
      "organizer": {
        "id": 2,
        "name": "Admin User"
      },
      "start_date": "2025-11-15",
      "end_date": "2025-11-17",
      "status": "registration_open",
      "max_players": 32,
      "current_round": 1,
      "registered_players": 24,
      "created_at": "2025-10-20T10:00:00Z"
    }
  ],
  "pagination": {
    "total": 5,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
  }
}
```

### Voir détails tournoi
```http
GET /api/tournaments/1
Authorization: Bearer 1|abc123def456...
```

**Response 200:**
```json
{
  "id": 1,
  "name": "Warhammer 40K Championship 2025",
  "description": "Tournoi régional",
  "organizer": {
    "id": 2,
    "name": "Admin User",
    "email": "admin@example.com"
  },
  "start_date": "2025-11-15",
  "end_date": "2025-11-17",
  "status": "registration_open",
  "max_players": 32,
  "current_round": 1,
  "registered_players": 24,
  "matches": [
    {
      "id": 1,
      "round": 1,
      "table_number": 1,
      "player1": {
        "id": 3,
        "name": "Player 1",
        "faction": "Necrons",
        "detachment": "Lords of Dread"
      },
      "player2": {
        "id": 4,
        "name": "Player 2",
        "faction": "Astra Militarum",
        "detachment": "Cadian Shock Troops"
      },
      "status": "scheduled",
      "player1_score": null,
      "player2_score": null
    }
  ],
  "created_at": "2025-10-20T10:00:00Z"
}
```

### S'inscrire à un tournoi
```http
POST /api/tournaments/1/register
Authorization: Bearer 1|abc123def456...
Content-Type: application/json

{
  "army_list_id": 1,
  "faction_id": 5,
  "detachment_id": 155
}
```

**Response 201:**
```json
{
  "message": "Successfully registered to tournament",
  "tournament_id": 1,
  "user_id": 1
}
```

---

## 3. MATCHS JOUEURS

### Lister les matchs
```http
GET /api/player-matches?status=open&sort=created_at
Authorization: Bearer 1|abc123def456...
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 5,
      "creator": {
        "id": 1,
        "name": "John Doe"
      },
      "opponent": null,
      "points": 2000,
      "faction": "Necrons",
      "detachment": "Lords of Dread",
      "status": "open",
      "available_from": "2025-11-10",
      "available_to": "2025-11-15",
      "location": "Paris",
      "created_at": "2025-11-07T14:30:00Z"
    }
  ],
  "pagination": {
    "total": 12,
    "per_page": 15,
    "current_page": 1
  }
}
```

### Créer un match
```http
POST /api/player-matches
Authorization: Bearer 1|abc123def456...
Content-Type: application/json

{
  "points": 2000,
  "faction_id": 5,
  "detachment_id": 155,
  "availability_type": "range",
  "available_from": "2025-11-10",
  "available_to": "2025-11-15",
  "location": "Paris",
  "type": "casual"
}
```

**Response 201:**
```json
{
  "id": 5,
  "creator_id": 1,
  "points": 2000,
  "faction_id": 5,
  "detachment_id": 155,
  "status": "open",
  "created_at": "2025-11-07T14:30:00Z"
}
```

### Rejoindre un match
```http
POST /api/player-matches/5/join
Authorization: Bearer 1|abc123def456...
Content-Type: application/json

{
  "faction_id": 8,
  "detachment_id": 208
}
```

**Response 200:**
```json
{
  "message": "Successfully joined match",
  "match_id": 5,
  "opponent_id": 1
}
```

### Enregistrer le score
```http
POST /api/player-matches/5/set-score
Authorization: Bearer 1|abc123def456...
Content-Type: application/json

{
  "player_vp": 15,
  "opponent_vp": 12,
  "result": "win"
}
```

**Response 200:**
```json
{
  "message": "Score recorded successfully",
  "match_id": 5,
  "status": "completed"
}
```

---

## 4. MISSIONS

### Lister les missions primaires
```http
GET /api/missions/primary?locale=fr
Authorization: Bearer 1|abc123def456...
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "LINCHPIN",
      "name_fr": "PIVOT STRATÉGIQUE",
      "description": "Several objective markers are key to victory...",
      "description_fr": "Plusieurs marqueurs d'objectif sont clés...",
      "max_vp": 20,
      "slug": "linchpin",
      "is_active": true
    }
  ]
}
```

### Voir détails mission
```http
GET /api/missions/primary/1?locale=fr
Authorization: Bearer 1|abc123def456...
```

**Response 200:**
```json
{
  "id": 1,
  "name": "LINCHPIN",
  "name_fr": "PIVOT STRATÉGIQUE",
  "description": "Several objective markers are key to victory...",
  "description_fr": "Plusieurs marqueurs d'objectif sont clés...",
  "full_text": "Primary Mission\nLINCHPIN\n...",
  "full_text_fr": "Mission Primaire\nPIVOT STRATÉGIQUE\n...",
  "max_vp": 20,
  "sections": [
    {
      "id": 1,
      "type": "scoring",
      "title": "WHEN",
      "title_fr": "QUAND",
      "content": "End of each Command phase",
      "content_fr": "À la fin de chaque phase de commandement",
      "vp": 5
    }
  ]
}
```

### Lister les missions secondaires
```http
GET /api/missions/secondary?locale=fr
Authorization: Bearer 1|abc123def456...
```

### Lister les péripéties
```http
GET /api/missions/twist?locale=fr
Authorization: Bearer 1|abc123def456...
```

---

## 5. TRADUCTIONS

### Récupérer traductions
```http
GET /api/translations?locale=fr&resource_type=PrimaryMission
Authorization: Bearer 1|abc123def456...
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "resource_type": "PrimaryMission",
      "resource_id": 1,
      "field": "name",
      "locale": "fr",
      "source_text": "LINCHPIN",
      "translated_text": "PIVOT STRATÉGIQUE",
      "status": "approved"
    }
  ]
}
```

### Récupérer glossaire
```http
GET /api/glossary?locale=fr
Authorization: Bearer 1|abc123def456...
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "english_term": "OBJECTIVE MARKER",
      "french_translation": "MARQUEUR D'OBJECTIF",
      "category": "keyword",
      "context": "primary_mission"
    }
  ]
}
```

---

## 6. UTILISATEURS

### Voir profil
```http
GET /api/users/me
Authorization: Bearer 1|abc123def456...
```

**Response 200:**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "faction": {
    "id": 5,
    "name": "Necrons"
  },
  "detachment": {
    "id": 155,
    "name": "Lords of Dread",
    "name_fr": "Seigneurs de l'effroi"
  },
  "created_at": "2025-10-01T10:00:00Z"
}
```

### Modifier profil
```http
PUT /api/users/me
Authorization: Bearer 1|abc123def456...
Content-Type: application/json

{
  "name": "John Doe Updated",
  "faction_id": 8,
  "detachment_id": 208
}
```

**Response 200:**
```json
{
  "id": 1,
  "name": "John Doe Updated",
  "faction_id": 8,
  "detachment_id": 208
}
```

---

## 7. DÉTACHEMENTS & FACTIONS

### Lister les factions
```http
GET /api/factions
Authorization: Bearer 1|abc123def456...
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Astra Militarum",
      "description": "The Imperial Guard",
      "color_code": "#FF0000",
      "is_active": true
    }
  ]
}
```

### Lister les détachements
```http
GET /api/detachments?faction_id=5&locale=fr
Authorization: Bearer 1|abc123def456...
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 155,
      "name": "Lords of Dread",
      "name_fr": "Seigneurs de l'effroi",
      "faction_id": 5,
      "description": "Ancient Necron overlords...",
      "description_fr": "Anciens seigneurs Necrons...",
      "is_active": true
    }
  ]
}
```

---

## 8. GESTION DES ERREURS

### Erreur 401 (Non authentifié)
```json
{
  "message": "Unauthenticated",
  "status": 401
}
```

### Erreur 403 (Non autorisé)
```json
{
  "message": "You are not authorized to perform this action",
  "status": 403
}
```

### Erreur 422 (Validation)
```json
{
  "message": "The given data was invalid",
  "errors": {
    "points": ["The points field is required"],
    "faction_id": ["The faction_id must be a valid faction"]
  },
  "status": 422
}
```

### Erreur 404 (Non trouvé)
```json
{
  "message": "Resource not found",
  "status": 404
}
```

### Erreur 429 (Rate limit)
```json
{
  "message": "Too many requests",
  "status": 429,
  "retry_after": 60
}
```

---

## 9. PAGINATION & FILTRAGE

### Pagination
```http
GET /api/tournaments?page=2&per_page=10
```

### Tri
```http
GET /api/player-matches?sort=created_at&order=desc
GET /api/tournaments?sort=start_date&order=asc
```

### Filtrage
```http
GET /api/player-matches?status=open&points=2000
GET /api/tournaments?status=registration_open
```

### Recherche
```http
GET /api/tournaments?search=championship
GET /api/player-matches?search=paris
```

---

## 10. STRUCTURE RÉPONSE STANDARD

### Succès (GET)
```json
{
  "data": { /* ... */ },
  "status": 200,
  "timestamp": "2025-11-07T14:30:00Z"
}
```

### Succès (POST/PUT)
```json
{
  "data": { /* ... */ },
  "message": "Resource created successfully",
  "status": 201,
  "timestamp": "2025-11-07T14:30:00Z"
}
```

### Erreur
```json
{
  "message": "Error message",
  "errors": { /* ... */ },
  "status": 400,
  "timestamp": "2025-11-07T14:30:00Z"
}
```

---

## 📝 NOTES

- Tous les endpoints requièrent le header `Authorization: Bearer {token}`
- Les réponses utilisent le format JSON
- Pagination par défaut: 15 items/page
- Rate limit: 60 requêtes/minute
- Tous les timestamps en ISO 8601 UTC

---

**Exemples générés**: 7 novembre 2025
