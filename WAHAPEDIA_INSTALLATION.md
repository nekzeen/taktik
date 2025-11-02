# 📚 Installation - Système d'import Wahapedia

## 🚀 Démarrage rapide (5 minutes)

### Étape 1 : Installer la dépendance PHP

```bash
composer require league/csv
```

### Étape 2 : Obtenir une clé API

#### Option A : DeepL (Recommandé)

1. Aller sur https://www.deepl.com/pro
2. Créer un compte gratuit
3. Aller dans "Account" → "API"
4. Copier votre clé API
5. Garder la clé pour l'étape suivante

**Plan gratuit DeepL :**
- 500 000 caractères/mois
- Parfait pour l'import initial

#### Option B : Google Translate

1. Aller sur https://console.cloud.google.com
2. Créer un projet
3. Activer "Cloud Translation API"
4. Créer une clé API
5. Garder la clé pour l'étape suivante

### Étape 3 : Configurer les clés API

Éditer le fichier `.env` et ajouter :

```env
# Service de traduction par défaut
TRANSLATION_SERVICE=deepl

# DeepL API Key (si vous utilisez DeepL)
DEEPL_API_KEY=votre_clé_api_deepl

# Google Translate API Key (si vous utilisez Google)
GOOGLE_TRANSLATE_API_KEY=votre_clé_api_google
```

### Étape 4 : Exécuter les migrations

```bash
php artisan migrate
```

Cela créera la table `translations` pour stocker les traductions.

### Étape 5 : Enregistrer la ressource Filament

Éditer `app/Providers/Filament/AdminPanelProvider.php` :

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->login()
        ->colors([
            'primary' => Color::Red,
        ])
        ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
        // ... reste de la configuration
}
```

La ressource `TranslationResource` sera découverte automatiquement.

### Étape 6 : Importer les données

```bash
php artisan wahapedia:import --force
```

**Voilà ! C'est fait ! 🎉**

---

## 📋 Commandes disponibles

### Import

```bash
# Import simple (avec confirmation)
php artisan wahapedia:import

# Import forcé (sans confirmation)
php artisan wahapedia:import --force

# Utiliser Google Translate au lieu de DeepL
php artisan wahapedia:import --translator=google
```

---

## ✅ Vérifier l'installation

### Vérifier les clés API

```bash
php artisan tinker
>>> config('translation.services.deepl.key')
// Doit afficher votre clé API
```

### Vérifier les données importées

```bash
php artisan tinker
>>> DB::table('factions')->count()
>>> DB::table('datasheets')->count()
>>> DB::table('abilities')->count()
>>> DB::table('stratagems')->count()
```

### Accéder à l'interface d'administration

1. Aller à `/admin`
2. Cliquer sur "Traductions"
3. Voir la liste complète des traductions

---

## 🐛 Dépannage

### "Clé API manquante"

Vérifier que la clé est bien dans `.env` :

```bash
grep DEEPL_API_KEY /var/www/clients/client2/web12/web/.env
```

Si elle n'apparaît pas, l'ajouter manuellement.

### "Table doesn't exist"

Exécuter les migrations :

```bash
php artisan migrate
```

### "Impossible de télécharger"

Vérifier la connexion Internet et l'URL Wahapedia :

```bash
curl -I http://wahapedia.ru/wh40k10ed/Factions.csv
```

### "Traduction échouée"

Vérifier les logs :

```bash
tail -f storage/logs/laravel.log
```

---

## 📊 Exemple de rapport d'import

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

## 🎯 Prochaines étapes

1. ✅ Installer `league/csv`
2. ✅ Obtenir une clé API
3. ✅ Configurer `.env`
4. ✅ Exécuter les migrations
5. ✅ Importer les données
6. 📊 Vérifier les données en `/admin`
7. 🔄 Réexécuter l'import si nécessaire

---

**Besoin d'aide ?** Consultez `WAHAPEDIA_IMPORT_GUIDE.md`

