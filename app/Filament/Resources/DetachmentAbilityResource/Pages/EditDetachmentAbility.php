<?php

namespace App\Filament\Resources\DetachmentAbilityResource\Pages;

use App\Filament\Resources\DetachmentAbilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDetachmentAbility extends EditRecord
{
    protected static string $resource = DetachmentAbilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
