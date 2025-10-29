<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanupSoftDeletedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:cleanup-soft-deleted {--days=30 : Nombre de jours avant suppression définitive}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Supprimer définitivement les utilisateurs soft-deleted après N jours';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $count = User::onlyTrashed()
            ->where('deleted_at', '<', $cutoffDate)
            ->forceDelete();

        $this->info("✅ {$count} utilisateur(s) soft-deleted supprimé(s) définitivement.");
        return 0;
    }
}
