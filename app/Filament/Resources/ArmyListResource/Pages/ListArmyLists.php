<?php

namespace App\Filament\Resources\ArmyListResource\Pages;

use App\Filament\Resources\ArmyListResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListArmyLists extends ListRecords
{
    protected static string $resource = ArmyListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
