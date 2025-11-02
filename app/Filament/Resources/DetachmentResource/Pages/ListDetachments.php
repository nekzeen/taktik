<?php

namespace App\Filament\Resources\DetachmentResource\Pages;

use App\Filament\Resources\DetachmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDetachments extends ListRecords
{
    protected static string $resource = DetachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
