# Sécurité – configuration et paramètres

Ce document décrit les mécanismes de sécurité mis en place et les paramètres configurables (principalement via `.env`) pour ajuster le comportement sans modifier le code.

## 1) Prérequis / principes

- **Les commandes Artisan doivent être exécutées avec PHP CLI** (ex: `/usr/bin/php8.3`), pas avec `php-fpm`.
- **Les tâches planifiées** (scheduler) sont déclarées dans `routes/console.php` et sont déclenchées via un cron `schedule:run`.
- **Les emails** ne sont envoyés que si le mailer est configuré (par défaut `MAIL_MAILER=log` => aucun email réel).

## 2) Rate limiting (anti-abus)

### 2.1 Paramètres `.env`

- `SECURITY_LOGIN_PER_MINUTE` (défaut: `10`)
  - **Effet**: limite le nombre de tentatives de connexion (`POST /login`).
  - **Clé**: par `IP + email` (réduit l’impact d’un attaquant sur un email ciblé).

- `SECURITY_AUTH_PER_MINUTE` (défaut: `30`)
  - **Effet**: limite les actions d’auth sensibles (inscription, mot de passe oublié, reset password).

- `SECURITY_MATCH_API_PER_MINUTE` (défaut: `120`)
  - **Effet**: throttle sur le groupe d’API match authentifié (ex: draft scores, tactical state, validation-status).
  - **But**: éviter le spam sur endpoints d’écriture/lecture sensibles.

- `SECURITY_SPECTATOR_POLL_PER_MINUTE` (défaut: `60`)
  - **Effet**: throttle sur les endpoints publics de polling spectateur.
  - **But**: limiter le spam sans casser le mode spectateur.

### 2.2 Où c’est appliqué

- Définition des limiters nommés : `app/Providers/AppServiceProvider.php`
  - `security-login`
  - `security-auth`
  - `security-match-api`
  - `security-spectator`

- Application aux routes :
  - `routes/auth.php` : `login`, `register`, `forgot-password`, `reset-password`
  - `routes/web.php` : endpoints match API (auth) + polling spectateur (public)

## 3) Security Headers (durcissement HTTP)

Les headers HTTP sont ajoutés globalement via un middleware.

### 3.1 Paramètres `.env`

- `SECURITY_CSP`
  - **Défaut**:
    - `default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'; img-src 'self' data: https:; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https:; connect-src 'self' https:; font-src 'self' data: https:; object-src 'none';`
  - **Effet**: définit une Content Security Policy (CSP).
  - **Note**: la valeur par défaut vise la compatibilité (inclut `'unsafe-inline'`).

- `SECURITY_HSTS_ENABLED` (défaut: `true`)
  - **Effet**: active HSTS uniquement si :
    - `APP_ENV=production`
    - et la requête est en HTTPS.

- `SECURITY_HSTS_MAX_AGE` (défaut: `31536000`)
  - **Effet**: durée HSTS en secondes.

- `SECURITY_HSTS_INCLUDE_SUBDOMAINS` (défaut: `true`)
  - **Effet**: ajoute `includeSubDomains`.

- `SECURITY_HSTS_PRELOAD` (défaut: `false`)
  - **Effet**: ajoute `preload`.

### 3.2 Où c’est appliqué

- Middleware : `app/Http/Middleware/SecurityHeaders.php`
- Enregistrement global : `bootstrap/app.php`

### 3.3 Headers ajoutés

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: geolocation=(), microphone=(), camera=()`
- `Cross-Origin-Opener-Policy: same-origin`
- `Content-Security-Policy: ...` (si `SECURITY_CSP` non vide)
- `Strict-Transport-Security: ...` (si activé, HTTPS, prod)

## 4) Logs sécurité + alertes email

### 4.1 Paramètres `.env`

- `SECURITY_ALERT_EMAIL`
  - **Effet**: destinataire des alertes sécurité.
  - Si vide : aucune alerte email n’est envoyée (les logs restent actifs).

- `SECURITY_LOG_LEVEL` (défaut: `info`)
  - **Effet**: niveau du canal de logs `security`.

- `SECURITY_LOG_DAILY_DAYS` (défaut: `30`)
  - **Effet**: rétention des logs `storage/logs/security.log`.

### 4.2 Limitation des emails (anti-spam)

- `SECURITY_ALERT_THROTTLE_ENABLED` (défaut: `true`)
  - Active/désactive le système de limitation.

- `SECURITY_FAILED_LOGIN_THRESHOLD` (défaut: `5`)
  - **Effet**: nombre d’échecs login requis avant envoi d’une alerte.

- `SECURITY_FAILED_LOGIN_WINDOW_SECONDS` (défaut: `900`)
  - **Effet**: fenêtre de comptage des échecs.

- `SECURITY_FAILED_LOGIN_COOLDOWN_SECONDS` (défaut: `900`)
  - **Effet**: période de cooldown après un envoi (pas de nouveaux emails).

### 4.3 Où c’est appliqué

- Canal de logs : `config/logging.php` (`security` => `storage/logs/security.log`)
- Listener : `app/Listeners/SecurityAuthEventListener.php`
- Events branchés : `app/Providers/EventServiceProvider.php`

## 5) Rapport sécurité hebdomadaire (maintenance)

### 5.1 Paramètres `.env`

- `SECURITY_WEEKLY_REPORT_ENABLED` (défaut: `true`)
  - Active/désactive l’envoi.

- `SECURITY_WEEKLY_REPORT_EMAIL`
  - Destinataire du rapport.
  - Si vide : fallback sur `SECURITY_ALERT_EMAIL`.

### 5.2 Exécution

- Commande : `php artisan security:weekly-report`
- Code : `app/Console/Commands/SecurityWeeklyReport.php`
- Schedule : `routes/console.php` (lundi 08:00)
- Log de sortie : `storage/logs/security-weekly-report.log`

## 6) CRON à installer (scheduler Laravel)

### 6.1 Ligne CRON

Le scheduler Laravel doit être lancé **toutes les minutes** via PHP CLI.

Exemple (PHP 8.3 CLI) :

```cron
* * * * * /usr/bin/php8.3 /var/www/clients/client2/web12/web/artisan schedule:run >> /dev/null 2>&1
```

### 6.2 Vérification

- Lister les tâches :

```bash
/usr/bin/php8.3 /var/www/clients/client2/web12/web/artisan schedule:list
```

## 7) Mailer (indispensable pour recevoir les alertes)

Par défaut :
- `MAIL_MAILER=log` => les emails sont écrits dans les logs, pas envoyés.

Pour activer l’envoi réel :
- définir `MAIL_MAILER=smtp` et configurer `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`.

## 8) Recommandations prod (minimum)

- `APP_ENV=production`
- `APP_DEBUG=false`
- `SESSION_SECURE_COOKIE=true` (si HTTPS)
- Activer le cron `schedule:run`
- Configurer un mailer réel si vous souhaitez recevoir les alertes et rapports
