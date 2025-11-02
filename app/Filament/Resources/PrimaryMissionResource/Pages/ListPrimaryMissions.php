<?php

namespace App\Filament\Resources\PrimaryMissionResource\Pages;

use App\Filament\Resources\PrimaryMissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrimaryMissions extends ListRecords
{
    protected static string $resource = PrimaryMissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
