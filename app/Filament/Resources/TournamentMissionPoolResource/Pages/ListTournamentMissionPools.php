<?php

namespace App\Filament\Resources\TournamentMissionPoolResource\Pages;

use App\Filament\Resources\TournamentMissionPoolResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTournamentMissionPools extends ListRecords
{
    protected static string $resource = TournamentMissionPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
