<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Mise à jour et validation automatique des missions TOUS LES JOURS à 2h du matin
        $schedule->command('missions:update-and-validate')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->onOneServer()
            ->appendOutputTo(storage_path('logs/missions-update.log'));

        // Archivage automatique des discussions des règles TOUS LES JOURS à 3h du matin
        $schedule->command('rule-discussions:archive')
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->onOneServer()
            ->appendOutputTo(storage_path('logs/rule-discussions-archive.log'));

        $schedule->command('security:weekly-report')
            ->weeklyOn(1, '08:00')
            ->withoutOverlapping()
            ->onOneServer()
            ->appendOutputTo(storage_path('logs/security-weekly-report.log'));

        // Alternative : Exécuter à chaque déploiement (webhook)
        // Voir la section "Webhook" ci-dessous pour plus de détails
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
