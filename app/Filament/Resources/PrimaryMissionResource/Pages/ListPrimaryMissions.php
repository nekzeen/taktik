<?php

namespace App\Filament\Resources\PrimaryMissionResource\Pages;

use App\Filament\Resources\PrimaryMissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrimaryMissions extends ListRecords
{
    protected static string $resource = PrimaryMissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('Importer les missions')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn () => PrimaryMissionResource::getUrl('import'))
                ->openUrlInNewTab(false),
            Actions\CreateAction::make(),
        ];
    }
}
