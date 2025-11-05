<?php

namespace App\Filament\Resources\TwistMissionResource\Pages;

use App\Filament\Resources\TwistMissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTwistMissions extends ListRecords
{
    protected static string $resource = TwistMissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('📝 Importer une Péripétie')
                ->url(fn () => TwistMissionResource::getUrl('import'))
                ->openUrlInNewTab(false)
                ->color('primary'),
            
            Actions\CreateAction::make(),
        ];
    }
}
