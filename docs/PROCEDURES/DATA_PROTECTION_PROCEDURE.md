# 🛡️ PROCÉDURE DE PROTECTION DES DONNÉES

## ⚠️ RÈGLES ABSOLUES - JAMAIS D'EXCEPTIONS

### 1. AVANT TOUTE MIGRATION
- ✅ **TOUJOURS** faire une sauvegarde : `mysqldump dev40k > backup_$(date +%Y%m%d_%H%M%S).sql`
- ✅ **JAMAIS** utiliser `migrate:refresh` ou `migrate:reset`
- ✅ **TOUJOURS** utiliser `migrate` pour les migrations en attente uniquement

### 2. COMMANDES INTERDITES
- ❌ `php artisan migrate:refresh` - SUPPRIME TOUTES LES DONNÉES
- ❌ `php artisan migrate:reset` - SUPPRIME TOUTES LES DONNÉES
- ❌ `php artisan db:wipe` - SUPPRIME TOUTES LES DONNÉES

### 3. COMMANDES AUTORISÉES
- ✅ `php artisan migrate` - Exécute les migrations en attente UNIQUEMENT
- ✅ `php artisan migrate:rollback --step=1` - Rollback une migration à la fois
- ✅ `mysqldump` - Sauvegarde complète

### 4. PROCÉDURE POUR CORRIGER UN PROBLÈME DE MIGRATION

**Étape 1 : Sauvegarder**
```bash
mysqldump dev40k > backup_$(date +%Y%m%d_%H%M%S).sql
```

**Étape 2 : Identifier le problème**
```bash
php artisan migrate:status
```

**Étape 3 : Rollback seulement la migration problématique**
```bash
php artisan migrate:rollback --step=1
```

**Étape 4 : Corriger la migration**
- Éditer le fichier de migration
- Vérifier la syntaxe

**Étape 5 : Réexécuter**
```bash
php artisan migrate
```

### 5. DONNÉES CRITIQUES À PROTÉGER
- ✅ Factions (table: `factions`)
- ✅ Détachements (table: `bsdata_detachments`)
- ✅ Utilisateurs (table: `users`)
- ✅ Tournois (table: `tournaments`)
- ✅ Matchs (table: `tournament_matches`)

### 6. IMPORT DES DONNÉES BSDATA

**Source :** https://github.com/BSData/wh40k-10e

**Procédure d'import :**
```bash
php artisan bsdata:sync
```

**Résultat :** 
- Importe automatiquement les factions et détachements depuis GitHub
- Filtre UNIQUEMENT les factions avec au moins 1 détachement
- Importe 45 factions et 117+ détachements

**Commandes disponibles :**
- `php artisan bsdata:sync` - Synchroniser les données BSData
- `php artisan init:roles-permissions` - Initialiser les rôles et permissions
- `php artisan test:email-verification-flow` - Tester le flux de vérification d'email

## 🚨 EN CAS D'ERREUR

1. **ARRÊTER IMMÉDIATEMENT**
2. **NE PAS CONTINUER**
3. **RESTAURER LA SAUVEGARDE**
```bash
mysql dev40k < backup_YYYYMMDD_HHMMSS.sql
```
4. **ANALYSER LE PROBLÈME**
5. **APPLIQUER LA PROCÉDURE CORRECTE**

## ✅ CHECKLIST AVANT CHAQUE ACTION

- [ ] Sauvegarde faite
- [ ] Problème identifié
- [ ] Procédure correcte sélectionnée
- [ ] Commande vérifiée
- [ ] Données vérifiées après action
