<?php

namespace App\Filament\Resources\SecondaryMissionResource\Pages;

use App\Filament\Resources\SecondaryMissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSecondaryMission extends EditRecord
{
    protected static string $resource = SecondaryMissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
