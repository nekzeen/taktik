# 🤖 Configuration de l'Automatisation des Missions

## Objectif

Automatiser complètement la mise à jour et la validation des missions depuis Wahapedia.

## Deux Méthodes d'Automatisation

### 1️⃣ Scheduler (Exécution Quotidienne)

Exécute automatiquement la mise à jour **tous les jours à 2h du matin**.

**Fichier :** `app/Console/Kernel.php`

**Configuration :**
```php
$schedule->command('missions:update-and-validate')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/missions-update.log'));
```

**Pour que cela fonctionne :**
1. Configurer le cron job du serveur :
```bash
* * * * * cd /var/www/clients/client2/web12/web && php artisan schedule:run >> /dev/null 2>&1
```

2. Vérifier que le scheduler est actif :
```bash
php artisan schedule:list
```

3. Vérifier les logs :
```bash
tail -f storage/logs/missions-update.log
```

### 2️⃣ Webhooks (Exécution à la Demande)

Déclenche la mise à jour via une requête HTTP POST.

**Fichier :** `app/Http/Controllers/WebhookController.php`

**Endpoints disponibles :**

#### Mise à Jour Complète
```bash
POST /webhooks/missions-update
Headers: X-Webhook-Token: your-secret-token
Body: {
  "types": "primary,secondary,twist,asymmetric,strike-force,incursion,asymmetric-warfare"
}
```

#### Validation Uniquement
```bash
POST /webhooks/missions-validate
Headers: X-Webhook-Token: your-secret-token
Body: {
  "type": "primary"
}
```

## Configuration du Token Webhook

### 1. Ajouter le token au fichier `.env`

```bash
WEBHOOK_TOKEN=your-super-secret-token-here
```

### 2. Utiliser le token dans le code

Le token est récupéré depuis la configuration :
```php
$token = config('app.webhook_token');
```

### 3. Générer un token sécurisé

```bash
php artisan tinker
>>> bin2hex(random_bytes(32))
# Copier le résultat et l'ajouter à .env
```

## Utilisation des Webhooks

### Depuis une Pipeline CI/CD (GitHub Actions, GitLab CI, etc.)

**Exemple GitHub Actions :**
```yaml
- name: Déclencher mise à jour des missions
  run: |
    curl -X POST https://dev2.gaelmorvan.fr/webhooks/missions-update \
      -H "X-Webhook-Token: ${{ secrets.WEBHOOK_TOKEN }}" \
      -H "Content-Type: application/json" \
      -d '{"types": "primary,secondary,twist"}'
```

**Exemple GitLab CI :**
```yaml
update_missions:
  stage: deploy
  script:
    - curl -X POST https://dev2.gaelmorvan.fr/webhooks/missions-update \
        -H "X-Webhook-Token: $WEBHOOK_TOKEN" \
        -H "Content-Type: application/json" \
        -d '{"types": "primary,secondary,twist"}'
```

### Depuis un Script Bash

```bash
#!/bin/bash

WEBHOOK_URL="https://dev2.gaelmorvan.fr/webhooks/missions-update"
WEBHOOK_TOKEN="your-secret-token"

curl -X POST "$WEBHOOK_URL" \
  -H "X-Webhook-Token: $WEBHOOK_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "types": "primary,secondary,twist,asymmetric,strike-force,incursion,asymmetric-warfare"
  }'
```

### Depuis un Cron Job

```bash
# Ajouter à crontab
0 2 * * * curl -X POST https://dev2.gaelmorvan.fr/webhooks/missions-update \
  -H "X-Webhook-Token: your-secret-token" \
  -H "Content-Type: application/json" \
  -d '{"types": "primary,secondary,twist"}'
```

## Logs et Monitoring

### Logs du Scheduler
```bash
tail -f storage/logs/missions-update.log
```

### Logs des Webhooks
```bash
tail -f storage/logs/laravel.log | grep "Webhook missions"
```

### Vérifier les exécutions récentes
```bash
php artisan tinker
>>> \Illuminate\Support\Facades\Log::channel('single')->read()
```

## Workflow Complet Automatisé

### Avant (Manuel)
1. Modifier le XML
2. Exécuter `php artisan missions:update-and-validate`
3. Vérifier les logs
4. Corriger les erreurs si nécessaire

### Après (Automatisé)
1. Modifier le XML
2. Déclencher le webhook (ou attendre 2h du matin)
3. Les logs sont enregistrés automatiquement
4. Les erreurs sont loggées et peuvent être alertées

## Alertes et Notifications

### Ajouter une alerte en cas d'erreur

**Modifier `WebhookController.php` :**
```php
catch (\Exception $e) {
    Log::error('Webhook missions-update: Erreur', ['error' => $e->getMessage()]);
    
    // Envoyer une notification
    \Notification::route('mail', 'admin@example.com')
        ->notify(new \App\Notifications\MissionsUpdateFailed($e));
    
    return response()->json([...], 500);
}
```

### Créer une notification

```bash
php artisan make:notification MissionsUpdateFailed
```

## Vérification de la Configuration

### 1. Vérifier que le scheduler est configuré
```bash
php artisan schedule:list
```

### 2. Vérifier que les webhooks sont accessibles
```bash
curl -X POST http://localhost/webhooks/missions-update \
  -H "X-Webhook-Token: test-token" \
  -H "Content-Type: application/json"
```

### 3. Vérifier les logs
```bash
tail -f storage/logs/laravel.log
tail -f storage/logs/missions-update.log
```

## État Actuel

✅ Scheduler configuré (exécution quotidienne à 2h)
✅ Webhooks créés (exécution à la demande)
✅ Routes ajoutées
✅ Contrôleur implémenté
✅ Logs configurés
✅ Prêt pour automatisation

## Prochaines Étapes

1. **Configurer le token webhook** dans `.env`
2. **Configurer le cron job** du serveur
3. **Tester le scheduler** : `php artisan schedule:work`
4. **Tester les webhooks** : `curl -X POST ...`
5. **Configurer les alertes** si nécessaire
6. **Vérifier les logs** régulièrement

## Commandes Utiles

```bash
# Tester le scheduler en temps réel
php artisan schedule:work

# Exécuter manuellement la mise à jour
php artisan missions:update-and-validate

# Vérifier les logs du scheduler
tail -f storage/logs/missions-update.log

# Vérifier les logs des webhooks
tail -f storage/logs/laravel.log | grep "Webhook"

# Tester un webhook
curl -X POST http://localhost/webhooks/missions-update \
  -H "X-Webhook-Token: your-token" \
  -H "Content-Type: application/json" \
  -d '{"types": "primary"}'
```

## Sécurité

⚠️ **Important :**
- Le token webhook doit être **secret** et **unique**
- Ne jamais le commiter dans Git
- Le stocker dans `.env` (qui est dans `.gitignore`)
- Utiliser un token fort (32+ caractères)
- Changer régulièrement le token

## Support

Pour plus de détails sur les commandes :
- `php artisan missions:update-and-validate --help`
- `php artisan missions:validate-xml --help`
- `php artisan missions:compare-wahapedia --help`
- `php artisan missions:fix-discrepancies --help`
