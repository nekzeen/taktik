<?php

namespace App\Filament\Resources\FactionResource\Pages;

use App\Filament\Resources\FactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFaction extends EditRecord
{
    protected static string $resource = FactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
