<?php

namespace App\Observers;

use App\Models\TournamentMatch;

class TournamentMatchObserver
{
    /**
     * Handle the TournamentMatch "updating" event.
     */
    public function updating(TournamentMatch $match): void
    {
        // Si un gagnant est défini (winner_id) ET le statut n'est pas déjà "completed", marquer comme terminé
        if ($match->winner_id !== null && $match->status !== 'completed') {
            $match->status = 'completed';
            if (!$match->completed_at) {
                $match->completed_at = now();
            }
        }
        
        // Si le match est marqué comme nul (is_draw) ET le statut n'est pas déjà "completed", marquer comme terminé
        if ($match->is_draw === true && $match->status !== 'completed') {
            $match->status = 'completed';
            if (!$match->completed_at) {
                $match->completed_at = now();
            }
        }
    }

    /**
     * Handle the TournamentMatch "created" event.
     */
    public function created(TournamentMatch $match): void
    {
        // Si un gagnant est défini lors de la création, marquer comme terminé
        if ($match->winner_id !== null && $match->status !== 'completed') {
            $match->update([
                'status' => 'completed',
                'completed_at' => $match->completed_at ?? now(),
            ]);
        }
    }
}
