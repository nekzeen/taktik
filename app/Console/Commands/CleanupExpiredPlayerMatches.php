<?php

namespace App\Console\Commands;

use App\Models\PlayerMatch;
use Illuminate\Console\Command;

class CleanupExpiredPlayerMatches extends Command
{
    protected $signature = 'player-matches:cleanup';
    protected $description = 'Supprime les matchs proposés expirés sans adversaire';

    public function handle()
    {
        $deleted = PlayerMatch::where('status', 'open')
            ->where('opponent_id', null)
            ->where(function ($query) {
                $query->where('availability_type', 'single')
                    ->where('available_at', '<', now());
            })
            ->orWhere(function ($query) {
                $query->where('status', 'open')
                    ->where('opponent_id', null)
                    ->where('availability_type', 'period')
                    ->where('available_to', '<', now());
            })
            ->delete();

        $this->info("Supprimé {$deleted} match(s) expiré(s).");
    }
}
