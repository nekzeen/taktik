<?php

namespace App\Console\Commands;

use App\Mail\SecurityWeeklyReport as SecurityWeeklyReportMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SecurityWeeklyReport extends Command
{
    protected $signature = 'security:weekly-report';

    protected $description = 'Envoie un rapport sécurité hebdomadaire (checks de config + commandes de maintenance).';

    public function handle(): int
    {
        $enabled = (bool) config('security.weekly_report.enabled', true);
        if (!$enabled) {
            $this->info('Weekly report disabled.');
            return self::SUCCESS;
        }

        $to = config('security.weekly_report.email');
        if (!$to) {
            $this->warn('SECURITY_WEEKLY_REPORT_EMAIL / SECURITY_ALERT_EMAIL non configuré.');
            return self::SUCCESS;
        }

        $checks = $this->buildChecks();
        $commands = $this->buildCommands();

        Mail::to($to)->send(new SecurityWeeklyReportMail(
            checks: $checks,
            commands: $commands,
        ));

        $this->info('Security weekly report sent.');

        return self::SUCCESS;
    }

    private function buildChecks(): array
    {
        return [
            [
                'key' => 'APP_ENV',
                'expected' => 'production',
                'actual' => (string) config('app.env'),
            ],
            [
                'key' => 'APP_DEBUG',
                'expected' => false,
                'actual' => (bool) config('app.debug'),
            ],
            [
                'key' => 'SESSION_SECURE_COOKIE',
                'expected' => true,
                'actual' => (bool) config('session.secure'),
            ],
            [
                'key' => 'SESSION_SAME_SITE',
                'expected' => 'lax',
                'actual' => (string) config('session.same_site'),
            ],
            [
                'key' => 'MAIL_MAILER',
                'expected' => 'smtp|ses|postmark|resend',
                'actual' => (string) config('mail.default'),
            ],
        ];
    }

    private function buildCommands(): array
    {
        return [
            [
                'title' => 'Audit dépendances Composer',
                'command' => 'composer audit',
                'notes' => 'Lister les vulnérabilités connues.',
            ],
            [
                'title' => 'Mise à jour contrôlée',
                'command' => 'composer update --with-all-dependencies',
                'notes' => 'À faire sur une branche + tests.',
            ],
            [
                'title' => 'Optimisations caches',
                'command' => 'php artisan config:cache && php artisan route:cache && php artisan view:cache',
                'notes' => 'À exécuter après déploiement.',
            ],
            [
                'title' => 'Vérification migrations',
                'command' => 'php artisan migrate:status',
                'notes' => 'Vérifier que tout est appliqué.',
            ],
        ];
    }
}
