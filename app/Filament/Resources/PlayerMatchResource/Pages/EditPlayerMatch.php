<?php

namespace App\Filament\Resources\PlayerMatchResource\Pages;

use App\Filament\Resources\PlayerMatchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlayerMatch extends EditRecord
{
    protected static string $resource = PlayerMatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
