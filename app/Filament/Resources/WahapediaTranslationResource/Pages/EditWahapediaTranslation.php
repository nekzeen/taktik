<?php

namespace App\Filament\Resources\WahapediaTranslationResource\Pages;

use App\Filament\Resources\WahapediaTranslationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWahapediaTranslation extends EditRecord
{
    protected static string $resource = WahapediaTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
