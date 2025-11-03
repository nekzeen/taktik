# 🎮 Logique Métier des Matchs Simples (Player Matches)

## Vue d'Ensemble

Les **matchs simples** (Player Matches) permettent aux joueurs de créer et de rejoindre des matchs Warhammer 40k en dehors d'un tournoi. C'est un système de matching peer-to-peer avec demandes de participation.

---

## 1️⃣ Cycle de Vie d'un Match Simple

### État 1 : OPEN (Ouvert)
- **Créateur** : Crée un match avec ses paramètres (type, points, faction, détachement, localisation, disponibilité)
- **Autres joueurs** : Peuvent voir le match et soumettre une demande de participation
- **Durée** : Jusqu'à la date d'expiration ou acceptation d'une demande

### État 2 : CONFIRMED (Confirmé)
- **Créateur** : Accepte une demande de participation
- **Adversaire** : Devient le joueur accepté
- **Configuration** : Les deux joueurs peuvent configurer le match (missions, terrain, péripéties)
- **Validation** : Le créateur valide la configuration (irréversible)

### État 3 : COMPLETED (Terminé)
- **Créateur ou Adversaire** : Enregistre le score final
- **Gagnant** : Déterminé automatiquement selon les scores
- **Match nul** : Possible si les deux scores sont égaux

### État 4 : CANCELLED (Annulé)
- **Créateur** : Peut annuler un match ouvert
- **Raison** : Manque de participants ou changement de plans

---

## 2️⃣ Paramètres d'un Match Simple

### Paramètres Obligatoires

| Paramètre | Type | Description |
|-----------|------|-------------|
| **type** | enum | `competitive` (Compétitif) ou `narrative` (Narratif) |
| **army_points** | integer | Points d'armée : 1000, 1500, 2000, 3000, 3000+ |
| **faction** | string | Faction jouée (ex: "Necrons") |
| **detachment** | string | Détachement joué (ex: "Szarekhan Dynasty") |
| **city** | string | Ville du match |
| **department** | string | Département (ex: "75") |
| **availability_type** | enum | `single` (date unique) ou `range` (plage de dates) |

### Paramètres de Disponibilité

**Si `availability_type = 'single'` :**
- `available_at` : Date/heure unique du match

**Si `availability_type = 'range'` :**
- `available_from` : Date de début de la plage
- `available_to` : Date de fin de la plage

### Paramètres Optionnels

| Paramètre | Type | Description |
|-----------|------|-------------|
| **notes** | text | Notes/commentaires du créateur |
| **primary_mission_id** | FK | Mission primaire (après configuration) |
| **secondary_mission_id** | FK | Mission secondaire (après configuration) |
| **terrain_layout_id** | FK | Disposition de terrain (après configuration) |
| **twist_mission_id** | FK | Péripétie (après configuration) |
| **asymmetric_primary_mission_id** | FK | Mission asymétrique (si applicable) |

---

## 3️⃣ Flux des Demandes de Participation

### Étape 1 : Créateur Crée un Match
```
Créateur → Crée match (OPEN) → Attend des demandes
```

### Étape 2 : Joueur Soumet une Demande
```
Joueur → Clique "Répondre à ce match" → Soumet demande (PENDING)
```

### Étape 3 : Créateur Accepte/Refuse
```
Créateur → Voit demande en attente → Accepte ou Refuse

Si ACCEPTE :
  - Match passe à CONFIRMED
  - Joueur devient opponent_id
  - Les autres demandes sont automatiquement rejetées

Si REFUSE :
  - Demande passe à REJECTED
  - Match reste OPEN
  - Créateur peut envoyer un message de refus
```

### Étape 4 : Configuration du Match
```
CONFIRMED → Créateur configure le match
  - Sélectionne mission primaire
  - Sélectionne terrain
  - Sélectionne péripétie
  - Valide la configuration (irréversible)

Adversaire → Peut voir la configuration (lecture seule)
```

### Étape 5 : Enregistrement du Score
```
CONFIRMED → Créateur ou Adversaire enregistre le score
  - Saisit le score du créateur
  - Saisit le score de l'adversaire
  - Match passe à COMPLETED
  - Gagnant déterminé automatiquement
```

---

## 4️⃣ Permissions et Rôles

### Créateur du Match
- ✅ Crée le match
- ✅ Voit toutes les demandes de participation
- ✅ Accepte/Refuse les demandes
- ✅ Configure le match (missions, terrain, péripéties)
- ✅ Valide la configuration
- ✅ Enregistre le score
- ✅ Annule le match (si OPEN)
- ✅ Modifie le match (si OPEN)

### Adversaire (Joueur Accepté)
- ✅ Soumet une demande de participation
- ✅ Voit le match confirmé
- ✅ Consulte la configuration (lecture seule)
- ✅ Enregistre le score
- ❌ Ne peut pas configurer le match
- ❌ Ne peut pas valider la configuration

### Autres Joueurs
- ✅ Voient les matchs disponibles
- ✅ Soumettent une demande
- ❌ Ne voient pas les demandes des autres
- ❌ Ne peuvent pas configurer

---

## 5️⃣ Configuration du Match

### Avant Configuration
- Match créé avec paramètres de base
- `is_setup_complete = false`
- `is_setup_validated = false`

### Pendant Configuration
- Créateur sélectionne :
  - **Mission Primaire** : Normale ou Asymétrique
  - **Terrain** : Disposition de terrain
  - **Péripétie** : Twist mission
  - **Mode** : Aléatoire ou Manuel

### Tirage Aléatoire
```
Créateur clique "Tirer au sort"
  → Sélection aléatoire de :
    - Mission primaire
    - Terrain
    - Péripétie
  → is_setup_complete = true
  → setup_mode = 'random'
```

### Configuration Manuelle
```
Créateur sélectionne manuellement
  → Choisit mission primaire
  → Choisit terrain
  → Choisit péripétie
  → Clique "Enregistrer"
  → is_setup_complete = true
  → setup_mode = 'manual'
```

### Validation de Configuration
```
Créateur clique "Valider"
  → is_setup_validated = true
  → Configuration verrouillée (irréversible)
  → Adversaire voit "Configuration validée"
  → Bouton "Configurer" devient "Voir la configuration"
```

---

## 6️⃣ Disponibilité et Expiration

### Vérification de Disponibilité
```php
isAvailable(): bool
  - Si availability_type = 'single' :
    → available_at >= now()
  - Si availability_type = 'range' :
    → available_to >= now()
```

### Suppression Automatique
```
À chaque chargement de la page index :
  - Parcourt tous les matchs OPEN sans adversaire
  - Si !isAvailable() :
    → Supprime le match (forceDelete)
```

### Affichage de Disponibilité
```
- Single : "03/11/2025 14:30"
- Range : "03/11 - 10/11"
```

---

## 7️⃣ Calcul du Gagnant

### Déterminant Automatique
```php
determineWinner(): void
  - Si creator_score === opponent_score :
    → is_draw = true
    → winner_id = null
  - Si creator_score > opponent_score :
    → is_draw = false
    → winner_id = creator_id
  - Sinon :
    → is_draw = false
    → winner_id = opponent_id
```

### Affichage du Résultat
```
Match Nul : "Match nul"
Victoire : "[Nom du gagnant] a gagné"
Score : "Créateur [score] - [score] Adversaire"
```

---

## 8️⃣ Statistiques des Joueurs

### Ratio de Victoire
```
Calculé pour chaque joueur :
  - Total matchs complétés (créateur ou adversaire)
  - Nombre de victoires (score > adversaire)
  - Pourcentage : (victoires / total) * 100
```

### Affichage
```
"Ratio de victoire : 75% (3 victoires sur 4 matchs)"
```

---

## 9️⃣ Tri et Filtrage

### Matchs Disponibles
- **Filtre** : Status = OPEN, opponent_id = null, isAvailable() = true
- **Tri** : Par date de disponibilité (ascendant ou descendant)

### Mes Matchs Proposés
- **Filtre** : creator_id = user_id, status != CANCELLED
- **Tri** : Par date de disponibilité

### Mes Matchs Confirmés
- **Filtre** : (creator_id = user_id OU opponent_id = user_id) ET status = CONFIRMED
- **Tri** : Par date de disponibilité

---

## 🔟 Cas Limites

### Expiration d'un Match
```
Match OPEN sans adversaire + date expirée
→ Supprimé automatiquement à l'affichage
```

### Annulation d'un Match
```
Créateur peut annuler un match OPEN
→ Status = CANCELLED
→ N'apparaît plus dans les listes
```

### Modification d'un Match
```
Créateur peut modifier un match OPEN
→ Peut changer : type, points, faction, détachement, localisation, disponibilité
→ Ne peut pas modifier si CONFIRMED ou COMPLETED
```

### Rejet de Demande avec Message
```
Créateur refuse une demande
→ Peut envoyer un message de refus
→ Joueur voit le message sur la page du match
```

---

## 📊 Schéma de Flux Complet

```
┌─────────────────────────────────────────────────────────────────┐
│                    CRÉATEUR CRÉE UN MATCH                       │
│                      (Status: OPEN)                             │
└─────────────────────────────────────────────────────────────────┘
                              ↓
                    ┌─────────────────────┐
                    │ AUTRES JOUEURS VOIENT│
                    │ LE MATCH DISPONIBLE  │
                    └─────────────────────┘
                              ↓
                    ┌─────────────────────┐
                    │ JOUEUR SOUMET UNE   │
                    │ DEMANDE (PENDING)   │
                    └─────────────────────┘
                              ↓
                    ┌─────────────────────┐
                    │ CRÉATEUR ACCEPTE    │
                    │ (Status: CONFIRMED) │
                    └─────────────────────┘
                              ↓
                    ┌─────────────────────┐
                    │ CRÉATEUR CONFIGURE  │
                    │ LE MATCH            │
                    └─────────────────────┘
                              ↓
                    ┌─────────────────────┐
                    │ CRÉATEUR VALIDE     │
                    │ (Irréversible)      │
                    └─────────────────────┘
                              ↓
                    ┌─────────────────────┐
                    │ CRÉATEUR/ADVERSAIRE │
                    │ ENREGISTRE LE SCORE │
                    │ (Status: COMPLETED) │
                    └─────────────────────┘
                              ↓
                    ┌─────────────────────┐
                    │ GAGNANT DÉTERMINÉ   │
                    │ AUTOMATIQUEMENT     │
                    └─────────────────────┘
```

---

## 🎯 Résumé des Points Clés

1. **Création** : Créateur propose un match avec ses paramètres
2. **Demandes** : Autres joueurs demandent à participer
3. **Acceptation** : Créateur accepte une demande → Match confirmé
4. **Configuration** : Créateur configure missions, terrain, péripéties
5. **Validation** : Créateur valide (irréversible)
6. **Score** : Créateur ou Adversaire enregistre le score
7. **Résultat** : Gagnant déterminé automatiquement
8. **Expiration** : Matchs OPEN expirés sont supprimés automatiquement
9. **Statistiques** : Ratio de victoire calculé pour chaque joueur
10. **Permissions** : Créateur a tous les droits, Adversaire a accès limité

