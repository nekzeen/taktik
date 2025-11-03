<?php

namespace App\Filament\Resources\SecondaryMissionResource\Pages;

use App\Filament\Resources\SecondaryMissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSecondaryMissions extends ListRecords
{
    protected static string $resource = SecondaryMissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('Importer depuis texte')
                ->icon('heroicon-o-arrow-up-tray')
                ->url(SecondaryMissionResource::getUrl('import'))
                ->color('info'),
            Actions\CreateAction::make(),
        ];
    }
}
