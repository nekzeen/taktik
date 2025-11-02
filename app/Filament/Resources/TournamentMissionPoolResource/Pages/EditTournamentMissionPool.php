<?php

namespace App\Filament\Resources\TournamentMissionPoolResource\Pages;

use App\Filament\Resources\TournamentMissionPoolResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTournamentMissionPool extends EditRecord
{
    protected static string $resource = TournamentMissionPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
