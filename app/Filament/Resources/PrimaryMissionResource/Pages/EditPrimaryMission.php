<?php

namespace App\Filament\Resources\PrimaryMissionResource\Pages;

use App\Filament\Resources\PrimaryMissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrimaryMission extends EditRecord
{
    protected static string $resource = PrimaryMissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
