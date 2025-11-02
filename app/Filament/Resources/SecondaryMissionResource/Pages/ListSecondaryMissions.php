<?php

namespace App\Filament\Resources\SecondaryMissionResource\Pages;

use App\Filament\Resources\SecondaryMissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSecondaryMissions extends ListRecords
{
    protected static string $resource = SecondaryMissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
