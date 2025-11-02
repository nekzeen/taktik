<?php

namespace App\Filament\Resources\TerrainLayoutResource\Pages;

use App\Filament\Resources\TerrainLayoutResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTerrainLayouts extends ListRecords
{
    protected static string $resource = TerrainLayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
