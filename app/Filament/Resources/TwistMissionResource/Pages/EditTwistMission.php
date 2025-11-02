<?php

namespace App\Filament\Resources\TwistMissionResource\Pages;

use App\Filament\Resources\TwistMissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTwistMission extends EditRecord
{
    protected static string $resource = TwistMissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
