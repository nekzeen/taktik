<?php

namespace App\Filament\Resources\TournamentMatchResource\Pages;

use App\Filament\Resources\TournamentMatchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTournamentMatch extends EditRecord
{
    protected static string $resource = TournamentMatchResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Si un gagnant est défini (winner_id), marquer le match comme terminé
        if ($data['winner_id'] !== null && $data['winner_id'] !== '') {
            $data['status'] = 'completed';
            if (!isset($data['completed_at']) || $data['completed_at'] === null) {
                $data['completed_at'] = now();
            }
        }
        
        // Si le match est marqué comme nul (is_draw), marquer comme terminé
        if ($data['is_draw'] === true) {
            $data['status'] = 'completed';
            if (!isset($data['completed_at']) || $data['completed_at'] === null) {
                $data['completed_at'] = now();
            }
        }
        
        return $data;
    }
}
