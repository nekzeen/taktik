<?php

namespace App\Filament\Resources\AsymmetricPrimaryMissionResource\Pages;

use App\Filament\Resources\AsymmetricPrimaryMissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAsymmetricPrimaryMission extends EditRecord
{
    protected static string $resource = AsymmetricPrimaryMissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
