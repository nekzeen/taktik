<?php

namespace App\Filament\Resources\WarhammerGlossaryResource\Pages;

use App\Filament\Resources\WarhammerGlossaryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWarhammerGlossary extends EditRecord
{
    protected static string $resource = WarhammerGlossaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
