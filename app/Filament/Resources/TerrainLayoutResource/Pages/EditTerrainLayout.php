<?php

namespace App\Filament\Resources\TerrainLayoutResource\Pages;

use App\Filament\Resources\TerrainLayoutResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTerrainLayout extends EditRecord
{
    protected static string $resource = TerrainLayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
