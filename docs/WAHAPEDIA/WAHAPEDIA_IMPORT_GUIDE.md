# 📖 Guide d'utilisation - Import Wahapedia

## 🎯 Vue d'ensemble

Ce système importe automatiquement les données Wahapedia (factions, fiches de données, capacités, stratagèmes) et les traduit en français via DeepL ou Google Translate.

---

## 🚀 Commandes principales

### Import complet

```bash
# Avec confirmation
php artisan wahapedia:import

# Sans confirmation (forcé)
php artisan wahapedia:import --force

# Avec Google Translate
php artisan wahapedia:import --translator=google --force
```

### Options disponibles

| Option | Description |
|--------|-------------|
| `--force` | Ignore la confirmation et lance l'import directement |
| `--translator=deepl` | Utilise DeepL (défaut) |
| `--translator=google` | Utilise Google Translate |

---

## 📊 Flux de travail complet

### 1. Téléchargement des fichiers CSV

L'import télécharge automatiquement les fichiers depuis Wahapedia :

```
http://wahapedia.ru/wh40k10ed/Factions.csv
http://wahapedia.ru/wh40k10ed/Datasheets.csv
http://wahapedia.ru/wh40k10ed/Datasheets_abilities.csv
http://wahapedia.ru/wh40k10ed/Stratagems.csv
```

### 2. Parsing des CSV

Les fichiers sont parsés avec le délimiteur `|` (pipe).

### 3. Traduction automatique

Chaque élément est traduit automatiquement :
- Nom anglais → Nom français
- Description anglaise → Description française

### 4. Vérification des doublons

Les doublons sont détectés et ignorés.

### 5. Insertion en base de données

Les données sont insérées dans les tables correspondantes.

### 6. Rapport détaillé

Un rapport affiche le nombre de créations, erreurs et ignorés.

---

## 📈 Exemple de rapport

```
═══════════════════════════════════════════════════════════
📊 RAPPORT D'IMPORT WAHAPEDIA
═══════════════════════════════════════════════════════════
🚩 Factions:
  ✅ Succès : 26
  ❌ Erreurs : 0
  ⏭️  Ignorés : 0

📋 Fiches de données:
  ✅ Succès : 1669
  ❌ Erreurs : 0
  ⏭️  Ignorés : 0

✨ Capacités:
  ✅ Succès : 6939
  ❌ Erreurs : 0
  ⏭️  Ignorés : 0

💡 Stratagèmes:
  ✅ Succès : 1284
  ❌ Erreurs : 0
  ⏭️  Ignorés : 0

───────────────────────────────────────────────────────────
📈 TOTAL : 9918 créés, 0 erreurs, 0 ignorés
═══════════════════════════════════════════════════════════

✅ Import terminé !
```

---

## 🔍 Vérifier les données

### Via Tinker

```bash
php artisan tinker

# Compter les factions
>>> DB::table('factions')->count()
26

# Compter les fiches
>>> DB::table('datasheets')->count()
1669

# Voir une faction
>>> DB::table('factions')->first()

# Voir une traduction
>>> DB::table('translations')->first()
```

### Via l'interface d'administration

1. Aller à `/admin`
2. Cliquer sur "Traductions"
3. Voir la liste complète

---

## 🔄 Réexécuter l'import

Si vous voulez réimporter les données :

### Option 1 : Vider et réimporter

```bash
# Vider les tables
php artisan tinker
>>> DB::table('stratagems')->delete();
>>> DB::table('abilities')->delete();
>>> DB::table('datasheets')->delete();
>>> DB::table('factions')->delete();
>>> exit();

# Réimporter
php artisan wahapedia:import --force
```

### Option 2 : Import incrémental

L'import détecte automatiquement les doublons et les ignore. Vous pouvez relancer l'import sans problème.

---

## 📝 Gestion des traductions

### Réviser les traductions

Via l'interface d'administration :

1. Aller à `/admin`
2. Cliquer sur "Traductions"
3. Cliquer sur une traduction
4. Modifier le texte traduit
5. Cocher "Révisée"
6. Sauvegarder

### Filtrer les traductions

- Par langue source
- Par langue cible
- Par traducteur (DeepL, Google, Manuel)
- Par statut (Révisée ou non)

---

## ⚙️ Configuration

### Fichier `config/translation.php`

```php
return [
    'default' => env('TRANSLATION_SERVICE', 'deepl'),
    
    'services' => [
        'deepl' => [
            'key' => env('DEEPL_API_KEY', ''),
            'base_url' => 'https://api-free.deepl.com/v1',
            'timeout' => 30,
        ],
        
        'google' => [
            'key' => env('GOOGLE_TRANSLATE_API_KEY', ''),
            'base_url' => 'https://translation.googleapis.com/language/translate/v2',
            'timeout' => 30,
        ],
    ],
    
    'cache_ttl' => 30 * 24 * 60 * 60, // 30 jours
];
```

### Fichier `.env`

```env
TRANSLATION_SERVICE=deepl
DEEPL_API_KEY=votre_clé_api_deepl
GOOGLE_TRANSLATE_API_KEY=votre_clé_api_google
```

---

## 🐛 Dépannage

### "Clé API manquante"

```bash
# Vérifier que la clé est dans .env
grep DEEPL_API_KEY .env

# Vider le cache de configuration
php artisan config:clear
php artisan config:cache
```

### "Impossible de télécharger"

```bash
# Vérifier la connexion
curl -I http://wahapedia.ru/wh40k10ed/Factions.csv

# Vérifier les logs
tail -f storage/logs/laravel.log
```

### "Erreur de traduction"

```bash
# Vérifier les logs
tail -f storage/logs/laravel.log

# Vérifier la clé API
php artisan tinker
>>> config('translation.services.deepl.key')
```

---

## 📊 Structure des données

### Table `factions`

| Colonne | Type | Description |
|---------|------|-------------|
| id | bigint | ID unique |
| name | varchar | Nom en anglais |
| name_fr | varchar | Nom en français |
| bsdata_id | varchar | ID Wahapedia |
| created_at | timestamp | Date de création |
| updated_at | timestamp | Date de modification |

### Table `datasheets`

| Colonne | Type | Description |
|---------|------|-------------|
| id | bigint | ID unique |
| name | varchar | Nom en anglais |
| name_fr | varchar | Nom en français |
| faction_id | bigint | ID de la faction |
| bsdata_id | varchar | ID Wahapedia |
| created_at | timestamp | Date de création |
| updated_at | timestamp | Date de modification |

### Table `abilities`

| Colonne | Type | Description |
|---------|------|-------------|
| id | bigint | ID unique |
| name | varchar | Nom en anglais |
| name_fr | varchar | Nom en français |
| bsdata_id | varchar | ID Wahapedia |
| created_at | timestamp | Date de création |
| updated_at | timestamp | Date de modification |

### Table `stratagems`

| Colonne | Type | Description |
|---------|------|-------------|
| id | bigint | ID unique |
| name | varchar | Nom en anglais |
| name_fr | varchar | Nom en français |
| bsdata_id | varchar | ID Wahapedia |
| created_at | timestamp | Date de création |
| updated_at | timestamp | Date de modification |

### Table `translations`

| Colonne | Type | Description |
|---------|------|-------------|
| id | bigint | ID unique |
| source_text | text | Texte source |
| translated_text | text | Texte traduit |
| source_language | varchar | Langue source |
| target_language | varchar | Langue cible |
| translator | varchar | Traducteur utilisé |
| reviewed | boolean | Révisée ? |
| reviewed_at | timestamp | Date de révision |
| created_at | timestamp | Date de création |
| updated_at | timestamp | Date de modification |

---

## 🎯 Cas d'usage

### Importer pour la première fois

```bash
php artisan wahapedia:import --force
```

### Réimporter après mise à jour Wahapedia

```bash
# Vider les tables
php artisan tinker
>>> DB::table('stratagems')->delete();
>>> DB::table('abilities')->delete();
>>> DB::table('datasheets')->delete();
>>> DB::table('factions')->delete();

# Réimporter
php artisan wahapedia:import --force
```

### Utiliser Google Translate

```bash
php artisan wahapedia:import --translator=google --force
```

---

**Besoin d'aide ?** Consultez `WAHAPEDIA_INSTALLATION.md`
