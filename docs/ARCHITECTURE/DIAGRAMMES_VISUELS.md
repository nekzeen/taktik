# 📊 DIAGRAMMES VISUELS - ARCHITECTURE ET FLUX

**Date**: 3 novembre 2025
**Format**: Mermaid (compatible GitHub, GitLab, Confluence)

---

## 🏗️ ARCHITECTURE GÉNÉRALE

```mermaid
graph TB
    subgraph "Utilisateurs"
        Admin["👤 Admin"]
        User["👤 Utilisateur"]
        Organizer["👤 Organisateur"]
    end
    
    subgraph "Interface Web"
        Web["🌐 Web Public"]
        Admin_Panel["⚙️ Admin Panel Filament"]
    end
    
    subgraph "Logique Métier"
        Services["🏗️ Services"]
        Models["📦 Modèles"]
        Observers["👁️ Observers"]
    end
    
    subgraph "Données"
        DB["🗄️ Base de Données"]
        Cache["💾 Cache"]
    end
    
    subgraph "Intégrations Externes"
        DeepL["🌐 DeepL API"]
        Wahapedia["📖 Wahapedia"]
    end
    
    Admin --> Admin_Panel
    User --> Web
    Organizer --> Web
    
    Web --> Services
    Admin_Panel --> Services
    
    Services --> Models
    Models --> DB
    Models --> Observers
    Observers --> Services
    
    Services --> Cache
    Services --> DeepL
    Services --> Wahapedia
```

---

## 🎮 FLUX MATCH JOUEUR COMPLET

```mermaid
graph TD
    A["1️⃣ Créateur crée le match"] --> B["Status: open<br/>is_setup_validated: false<br/>Affichage: Configuration en cours"]
    B --> C["❌ Autres joueurs ne voient PAS"]
    
    B --> D["2️⃣ Créateur configure"]
    D --> E["Choisit missions<br/>Choisit terrain<br/>Choisit déploiement"]
    E --> F["Valide la configuration<br/>is_setup_validated: true"]
    
    F --> G["Status: open<br/>is_setup_validated: true<br/>Affichage: Ouvert"]
    G --> H["✅ Autres joueurs VOIENT"]
    
    H --> I["3️⃣ Autres joueurs demandent"]
    I --> J["PlayerMatchRequest<br/>Status: pending"]
    
    J --> K["4️⃣ Créateur accepte"]
    K --> L["opponent_id défini<br/>Status: confirmed<br/>Affichage: Confirmé"]
    
    L --> M["5️⃣ Joueurs jouent"]
    M --> N["Saisissent les scores"]
    
    N --> O["6️⃣ Créateur valide"]
    O --> P["Status: completed<br/>winner_id défini<br/>Affichage: Terminé"]
    
    style B fill:#fff3cd
    style G fill:#d1ecf1
    style L fill:#d4edda
    style P fill:#f8d7da
```

---

## 🏆 FLUX TOURNOI COMPLET

```mermaid
graph TD
    A["1️⃣ Organisateur crée"] --> B["Status: draft"]
    B --> C["2️⃣ Ouvre inscriptions"]
    C --> D["Status: registration_open"]
    
    D --> E["Joueurs s'inscrivent"]
    E --> F["3️⃣ Ferme inscriptions"]
    F --> G["Status: registration_closed"]
    
    G --> H["4️⃣ Génère appairements"]
    H --> I["Crée TournamentMatch<br/>Assigne missions"]
    I --> J["Status: in_progress<br/>Round: 1"]
    
    J --> K["5️⃣ Joueurs jouent"]
    K --> L["Saisissent scores"]
    
    L --> M["6️⃣ Valide résultats"]
    M --> N["Calcule classement"]
    
    N --> O["7️⃣ Génère appairements Round 2"]
    O --> P["Répète le processus"]
    
    P --> Q["8️⃣ Clôt tournoi"]
    Q --> R["Status: completed<br/>Affiche classement final"]
    
    style B fill:#fff3cd
    style D fill:#d1ecf1
    style J fill:#d4edda
    style R fill:#f8d7da
```

---

## 🌍 FLUX TRADUCTION COMPLET

```mermaid
graph TD
    A["1️⃣ Ressource créée"] --> B["Translation.create<br/>Status: pending"]
    
    B --> C["2️⃣ Observer détecte"]
    C --> D["Extrait terme anglais<br/>Cherche glossaire"]
    
    D --> E{"Terme existe?"}
    E -->|Non| F["Crée entrée glossaire"]
    E -->|Oui| G["Met à jour glossaire"]
    
    F --> H["3️⃣ DeepL traduit"]
    G --> H
    
    H --> I["Translation.update<br/>Status: auto"]
    
    I --> J["4️⃣ Admin modifie"]
    J --> K["Observer détecte changement"]
    
    K --> L["Crée/met à jour glossaire"]
    L --> M["Propage à TOUTES les traductions"]
    M --> N["Status: reviewed"]
    
    N --> O["5️⃣ Admin approuve"]
    O --> P["Status: approved"]
    
    style B fill:#fff3cd
    style I fill:#d1ecf1
    style N fill:#d4edda
    style P fill:#f8d7da
```

---

## 🔐 MATRICE DE PERMISSIONS

```mermaid
graph LR
    subgraph "Match Ouvert Non Validé"
        A["Status: open<br/>is_setup_validated: false"]
        A --> B["❌ Rejoindre"]
        A --> C["✅ Créateur: Modifier"]
        A --> D["❌ Autres: Voir"]
    end
    
    subgraph "Match Ouvert Validé"
        E["Status: open<br/>is_setup_validated: true"]
        E --> F["✅ Rejoindre"]
        E --> G["❌ Modifier"]
        E --> H["✅ Autres: Voir"]
    end
    
    subgraph "Match Confirmé"
        I["Status: confirmed"]
        I --> J["❌ Rejoindre"]
        I --> K["✅ Créateur: Saisir scores"]
        I --> L["✅ Voir détails"]
    end
    
    subgraph "Match Terminé"
        M["Status: completed"]
        M --> N["❌ Rejoindre"]
        M --> O["❌ Modifier"]
        M --> P["✅ Voir résultats"]
    end
    
    style A fill:#fff3cd
    style E fill:#d1ecf1
    style I fill:#d4edda
    style M fill:#f8d7da
```

---

## 📊 STRUCTURE DES DONNÉES - RELATIONS

```mermaid
erDiagram
    USERS ||--o{ PLAYER_MATCHES : creates
    USERS ||--o{ PLAYER_MATCH_REQUESTS : makes
    USERS ||--o{ TOURNAMENT_MATCHES : plays
    USERS ||--o{ TOURNAMENTS : organizes
    
    PLAYER_MATCHES ||--o{ PLAYER_MATCH_REQUESTS : receives
    PLAYER_MATCHES ||--o{ TRANSLATIONS : has
    PLAYER_MATCHES ||--o{ PRIMARY_MISSIONS : uses
    PLAYER_MATCHES ||--o{ SECONDARY_MISSIONS : uses
    PLAYER_MATCHES ||--o{ TWIST_MISSIONS : uses
    PLAYER_MATCHES ||--o{ TERRAIN_LAYOUTS : uses
    
    TOURNAMENTS ||--o{ TOURNAMENT_MATCHES : contains
    TOURNAMENTS ||--o{ TOURNAMENT_MISSION_POOLS : uses
    
    TOURNAMENT_MATCHES ||--o{ PRIMARY_MISSIONS : uses
    TOURNAMENT_MATCHES ||--o{ SECONDARY_MISSIONS : uses
    TOURNAMENT_MATCHES ||--o{ TWIST_MISSIONS : uses
    
    PRIMARY_MISSIONS ||--o{ TRANSLATIONS : has
    SECONDARY_MISSIONS ||--o{ TRANSLATIONS : has
    TWIST_MISSIONS ||--o{ TRANSLATIONS : has
    
    TRANSLATIONS ||--o{ WARHAMMER_GLOSSARY : references
    
    TOURNAMENT_MISSION_POOLS ||--o{ PRIMARY_MISSIONS : has
    TOURNAMENT_MISSION_POOLS ||--o{ SECONDARY_MISSIONS : has
    TOURNAMENT_MISSION_POOLS ||--o{ TERRAIN_LAYOUTS : has
```

---

## 🔄 CYCLE DE VIE D'UN MATCH JOUEUR

```mermaid
stateDiagram-v2
    [*] --> Open: Créateur crée
    
    Open --> Open: Configuration en cours
    note right of Open
        is_setup_validated = false
        Autres joueurs: ❌ Invisible
    end
    
    Open --> Confirmed: Créateur valide config
    note right of Confirmed
        is_setup_validated = true
        Autres joueurs: ✅ Visible
    end
    
    Confirmed --> Confirmed: Autres joueurs demandent
    
    Confirmed --> Confirmed: Créateur accepte demande
    note right of Confirmed
        opponent_id défini
        Status: confirmed
    end
    
    Confirmed --> Completed: Joueurs jouent et saisissent scores
    
    Completed --> [*]
    note right of Completed
        winner_id défini
        Match archivé
    end
    
    Open --> Cancelled: Créateur annule
    Confirmed --> Cancelled: Créateur annule
    Cancelled --> [*]
```

---

## 🎯 FLUX DE DÉCISION - PEUT REJOINDRE?

```mermaid
graph TD
    A["Utilisateur veut rejoindre"] --> B{"Status = open?"}
    B -->|Non| C["❌ Refusé"]
    B -->|Oui| D{"opponent_id = null?"}
    
    D -->|Non| C
    D -->|Oui| E{"is_setup_validated = true?"}
    
    E -->|Non| C
    E -->|Oui| F{"Match disponible?"}
    
    F -->|Non| C
    F -->|Oui| G{"Utilisateur ≠ créateur?"}
    
    G -->|Non| C
    G -->|Oui| H["✅ Autorisé"]
    
    style C fill:#f8d7da
    style H fill:#d4edda
```

---

## 📈 HIÉRARCHIE DES SERVICES

```mermaid
graph TB
    subgraph "Services Métier"
        PS["PlayerMatchService"]
        TS["TournamentService"]
        MS["MissionService"]
        TMS["TranslationManagementService"]
    end
    
    subgraph "Services Techniques"
        PMS["MatchPermissionService"]
        MSS["MatchSetupService"]
    end
    
    subgraph "Modèles"
        PM["PlayerMatch"]
        TM["TournamentMatch"]
        TR["Translation"]
    end
    
    subgraph "Observers"
        TO["TranslationObserver"]
    end
    
    PS --> PMS
    PS --> PM
    TS --> TM
    MS --> TR
    TMS --> TR
    
    PM --> TO
    TR --> TO
```

---

## 🔌 INTÉGRATIONS EXTERNES

```mermaid
graph LR
    App["Application"]
    
    App -->|Traduit| DeepL["🌐 DeepL API"]
    DeepL -->|Retourne traduction| App
    
    App -->|Importe missions| Wahapedia["📖 Wahapedia"]
    Wahapedia -->|Retourne données| App
    
    App -->|Stocke cache| Redis["💾 Redis Cache"]
    Redis -->|Retourne données| App
    
    App -->|Stocke données| DB["🗄️ PostgreSQL"]
    DB -->|Retourne données| App
```

---

## 📝 RÉSUMÉ DES DIAGRAMMES

- ✅ Architecture générale
- ✅ Flux match joueur (6 étapes)
- ✅ Flux tournoi (8 étapes)
- ✅ Flux traduction (5 étapes)
- ✅ Matrice de permissions
- ✅ Structure des données (ERD)
- ✅ Cycle de vie du match
- ✅ Flux de décision
- ✅ Hiérarchie des Services
- ✅ Intégrations externes

