# 🚀 DÉMARRAGE - DOCUMENTATION CENTRALISÉE

## 📖 Structure de la Documentation

```
docs/
├── CORE/                          # Règles et architecture fondamentales
│   ├── APP_ARCHITECTURE.md        ⭐ À CONSULTER EN PREMIER
│   ├── CODING_RULES.md
│   ├── windsurf.rules.json        ⭐ PROCÉDURES OBLIGATOIRES
│   └── README.md
│
├── PROCEDURES/                    # Procédures de vérification
│   ├── VERIFICATION_PROCEDURE.md
│   ├── VERIFICATION_CHECKLIST.md
│   ├── DATA_PROTECTION_PROCEDURE.md
│   └── MATCH_SETUP_SYSTEM.md
│
├── WAHAPEDIA/                     # Intégration Wahapedia
│   ├── WAHAPEDIA_COMPLETE_SETUP.md
│   ├── WAHAPEDIA_IMPORT_GUIDE.md
│   ├── WAHAPEDIA_INSTALLATION.md
│   ├── WAHAPEDIA_README.md
│   └── WAHAPEDIA_SYNC_GUIDE.md
│
├── MISSIONS/                      # Système de missions et traductions
│   ├── MISSIONS_AUTOMATION_SETUP.md
│   ├── MISSIONS_AUTO_UPDATE_GUIDE.md
│   ├── MISSIONS_UPDATE_GUIDE.md
│   ├── MISSIONS_VALIDATION_GUIDE.md
│   └── TRANSLATION_SYSTEM_REFERENCE.md ⭐ TRADUCTIONS
│
├── PLAYER_MATCHES/                # Gestion des matchs joueurs
│   ├── PLAYER_MATCHES_BUSINESS_LOGIC.md
│   └── PLAYER_MATCHES_GUIDE.md
│
└── GUIDES/                        # Guides et checkpoints
    ├── GUIDE_TESTEURS.md
    └── WORKFLOW_CHECKPOINT.md
```

---

## ⭐ DOCUMENTS À CONSULTER EN PREMIER

### 1. **CORE/APP_ARCHITECTURE.md**
- Vue d'ensemble du projet
- Structure des bases de données
- Modèles et relations
- Pages et fonctionnalités
- Flux de données

### 2. **CORE/windsurf.rules.json**
- Procédure complète en 10 phases
- Règles critiques
- Checklist finale
- Commandes obligatoires

### 3. **MISSIONS/TRANSLATION_SYSTEM_REFERENCE.md**
- Architecture du système de traductions
- Tables de base de données
- Types de ressources
- Flux de traduction complet
- Dépannage courant

---

## 🔄 WORKFLOW AU DÉMARRAGE D'UNE SESSION

**À FAIRE SYSTÉMATIQUEMENT :**

1. ✅ Lire la demande 3 fois
2. ✅ Consulter **APP_ARCHITECTURE.md** pour le contexte
3. ✅ Consulter **windsurf.rules.json** pour les procédures
4. ✅ Consulter **TRANSLATION_SYSTEM_REFERENCE.md** si c'est lié aux traductions
5. ✅ Identifier les fichiers concernés
6. ✅ Implémenter les changements
7. ✅ Exécuter la PHASE 9 (tests réels)
8. ✅ Documenter les changements

---

## 📋 CHECKLIST AVANT CHAQUE DEMANDE

- [ ] Consulter APP_ARCHITECTURE.md
- [ ] Consulter windsurf.rules.json
- [ ] Consulter les docs spécifiques au domaine
- [ ] Identifier les fichiers concernés
- [ ] Vérifier les données existantes en base
- [ ] Implémenter les changements
- [ ] Exécuter PHASE 9 (tests réels)
- [ ] Documenter les changements

---

## 🎯 DOCUMENTS PAR DOMAINE

### Traductions
- `MISSIONS/TRANSLATION_SYSTEM_REFERENCE.md` ⭐ PRIORITÉ
- `MISSIONS/MISSIONS_AUTOMATION_SETUP.md`

### Missions
- `MISSIONS/MISSIONS_AUTOMATION_SETUP.md`
- `MISSIONS/MISSIONS_UPDATE_GUIDE.md`
- `MISSIONS/MISSIONS_VALIDATION_GUIDE.md`

### Wahapedia
- `WAHAPEDIA/WAHAPEDIA_COMPLETE_SETUP.md`
- `WAHAPEDIA/WAHAPEDIA_IMPORT_GUIDE.md`

### Player Matches
- `PLAYER_MATCHES/PLAYER_MATCHES_BUSINESS_LOGIC.md`
- `PLAYER_MATCHES/PLAYER_MATCHES_GUIDE.md`

### Architecture
- `CORE/APP_ARCHITECTURE.md` ⭐ PRIORITÉ
- `CORE/CODING_RULES.md`

### Procédures
- `PROCEDURES/VERIFICATION_PROCEDURE.md`
- `PROCEDURES/VERIFICATION_CHECKLIST.md`

---

## 🚨 ERREURS À ÉVITER

❌ Ne pas consulter la documentation au démarrage
❌ Déboguer sans consulter APP_ARCHITECTURE.md
❌ Ignorer windsurf.rules.json
❌ Créer du nouveau au lieu d'utiliser les données existantes
❌ Oublier la PHASE 9 (tests réels)
❌ Oublier de recompiler Tailwind CSS
❌ Oublier de vider le cache Laravel

---

## 📞 COMMANDES RAPIDES

```bash
# Vérifier la structure
ls -la docs/

# Consulter un document
cat docs/CORE/APP_ARCHITECTURE.md | head -50

# Chercher dans la documentation
grep -r "translation" docs/MISSIONS/

# Vider le cache
php artisan cache:clear
php artisan view:cache

# Recompiler Tailwind
npm run build
```

---

**Dernière mise à jour** : 3 novembre 2025
**Version** : 1.0
**Auteur** : Cascade

