<?php

namespace App\Filament\Resources\ArmyListResource\Pages;

use App\Filament\Resources\ArmyListResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArmyList extends EditRecord
{
    protected static string $resource = ArmyListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
