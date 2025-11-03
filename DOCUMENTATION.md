# 📚 Documentation du Projet - Warhammer 40K Tournament Manager

## 🚀 Démarrage Rapide

**Toute la documentation est centralisée dans le répertoire `/docs`**

### ⭐ DOCUMENTS À CONSULTER EN PREMIER

1. **[docs/START_HERE.md](docs/START_HERE.md)** - Guide de démarrage
2. **[docs/CORE/APP_ARCHITECTURE.md](docs/CORE/APP_ARCHITECTURE.md)** - Architecture du projet
3. **[docs/CORE/windsurf.rules.json](docs/CORE/windsurf.rules.json)** - Procédures obligatoires

---

## 📂 Structure de la Documentation

```
docs/
├── START_HERE.md                    ⭐ LIRE EN PREMIER
├── CORE/                            # Fondamentaux du projet
│   ├── APP_ARCHITECTURE.md          ⭐ Architecture complète
│   ├── CODING_RULES.md
│   ├── windsurf.rules.json          ⭐ Procédures (10 phases)
│   └── README.md
├── PROCEDURES/                      # Procédures de vérification
│   ├── VERIFICATION_PROCEDURE.md
│   ├── VERIFICATION_CHECKLIST.md
│   ├── DATA_PROTECTION_PROCEDURE.md
│   └── MATCH_SETUP_SYSTEM.md
├── MISSIONS/                        # Système de missions et traductions
│   ├── TRANSLATION_SYSTEM_REFERENCE.md  ⭐ Traductions
│   ├── MISSIONS_AUTOMATION_SETUP.md
│   ├── MISSIONS_AUTO_UPDATE_GUIDE.md
│   ├── MISSIONS_UPDATE_GUIDE.md
│   └── MISSIONS_VALIDATION_GUIDE.md
├── WAHAPEDIA/                       # Intégration Wahapedia
│   ├── WAHAPEDIA_COMPLETE_SETUP.md
│   ├── WAHAPEDIA_IMPORT_GUIDE.md
│   ├── WAHAPEDIA_INSTALLATION.md
│   ├── WAHAPEDIA_README.md
│   └── WAHAPEDIA_SYNC_GUIDE.md
├── PLAYER_MATCHES/                  # Gestion des matchs joueurs
│   ├── PLAYER_MATCHES_BUSINESS_LOGIC.md
│   └── PLAYER_MATCHES_GUIDE.md
├── GUIDES/                          # Guides et checkpoints
│   ├── GUIDE_TESTEURS.md
│   └── WORKFLOW_CHECKPOINT.md
└── ARCHIVE/                         # Ancienne documentation
    └── (30+ fichiers archivés)
```

---

## 🎯 Par Domaine

### 🌍 Traductions
- **[docs/MISSIONS/TRANSLATION_SYSTEM_REFERENCE.md](docs/MISSIONS/TRANSLATION_SYSTEM_REFERENCE.md)** ⭐ PRIORITÉ

### 🎮 Missions
- [docs/MISSIONS/MISSIONS_AUTOMATION_SETUP.md](docs/MISSIONS/MISSIONS_AUTOMATION_SETUP.md)
- [docs/MISSIONS/MISSIONS_UPDATE_GUIDE.md](docs/MISSIONS/MISSIONS_UPDATE_GUIDE.md)
- [docs/MISSIONS/MISSIONS_VALIDATION_GUIDE.md](docs/MISSIONS/MISSIONS_VALIDATION_GUIDE.md)

### 🌐 Wahapedia
- [docs/WAHAPEDIA/WAHAPEDIA_COMPLETE_SETUP.md](docs/WAHAPEDIA/WAHAPEDIA_COMPLETE_SETUP.md)
- [docs/WAHAPEDIA/WAHAPEDIA_IMPORT_GUIDE.md](docs/WAHAPEDIA/WAHAPEDIA_IMPORT_GUIDE.md)

### 👥 Player Matches
- [docs/PLAYER_MATCHES/PLAYER_MATCHES_BUSINESS_LOGIC.md](docs/PLAYER_MATCHES/PLAYER_MATCHES_BUSINESS_LOGIC.md)
- [docs/PLAYER_MATCHES/PLAYER_MATCHES_GUIDE.md](docs/PLAYER_MATCHES/PLAYER_MATCHES_GUIDE.md)

### 🏗️ Architecture
- **[docs/CORE/APP_ARCHITECTURE.md](docs/CORE/APP_ARCHITECTURE.md)** ⭐ PRIORITÉ
- [docs/CORE/CODING_RULES.md](docs/CORE/CODING_RULES.md)

### ✅ Procédures
- [docs/PROCEDURES/VERIFICATION_PROCEDURE.md](docs/PROCEDURES/VERIFICATION_PROCEDURE.md)
- [docs/PROCEDURES/VERIFICATION_CHECKLIST.md](docs/PROCEDURES/VERIFICATION_CHECKLIST.md)

---

## 🔄 Workflow au Démarrage de Chaque Session

**OBLIGATOIRE :**

1. ✅ Lire la demande 3 fois
2. ✅ Consulter **[docs/CORE/APP_ARCHITECTURE.md](docs/CORE/APP_ARCHITECTURE.md)**
3. ✅ Consulter **[docs/CORE/windsurf.rules.json](docs/CORE/windsurf.rules.json)**
4. ✅ Consulter les docs spécifiques au domaine
5. ✅ Vérifier les données existantes en base
6. ✅ Implémenter les changements
7. ✅ Exécuter PHASE 9 (tests réels)
8. ✅ Documenter les changements

---

## 🚨 Erreurs à Éviter

❌ Ne pas consulter la documentation au démarrage
❌ Déboguer sans consulter APP_ARCHITECTURE.md
❌ Ignorer windsurf.rules.json
❌ Créer du nouveau au lieu d'utiliser les données existantes
❌ Oublier PHASE 9 (tests réels)
❌ Oublier `npm run build` + `php artisan cache:clear`

---

## 📞 Commandes Rapides

```bash
# Consulter le guide de démarrage
cat docs/START_HERE.md

# Lister la structure
find docs/ -type f -name "*.md" ! -path "*/ARCHIVE/*" | sort

# Chercher dans la documentation
grep -r "terme" docs/ --exclude-dir=ARCHIVE

# Consulter l'architecture
cat docs/CORE/APP_ARCHITECTURE.md | head -100

# Consulter les règles
cat docs/CORE/windsurf.rules.json | head -50
```

---

## 📝 Mise à Jour de la Documentation

La documentation est mise à jour régulièrement. Les fichiers archivés sont conservés dans `docs/ARCHIVE/` pour référence historique.

**Dernière mise à jour** : 3 novembre 2025
**Version** : 1.0

---

## 🔗 Liens Rapides

- [START_HERE.md](docs/START_HERE.md) - Guide de démarrage
- [APP_ARCHITECTURE.md](docs/CORE/APP_ARCHITECTURE.md) - Architecture
- [windsurf.rules.json](docs/CORE/windsurf.rules.json) - Procédures
- [TRANSLATION_SYSTEM_REFERENCE.md](docs/MISSIONS/TRANSLATION_SYSTEM_REFERENCE.md) - Traductions

