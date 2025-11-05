<?php

namespace App\Filament\Resources\AsymmetricPrimaryMissionResource\Pages;

use App\Filament\Resources\AsymmetricPrimaryMissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAsymmetricPrimaryMissions extends ListRecords
{
    protected static string $resource = AsymmetricPrimaryMissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('Importer les missions')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn () => AsymmetricPrimaryMissionResource::getUrl('import'))
                ->openUrlInNewTab(false),
            Actions\CreateAction::make(),
        ];
    }
}
