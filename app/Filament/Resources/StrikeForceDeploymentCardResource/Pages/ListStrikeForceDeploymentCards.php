<?php

namespace App\Filament\Resources\StrikeForceDeploymentCardResource\Pages;

use App\Filament\Resources\StrikeForceDeploymentCardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStrikeForceDeploymentCards extends ListRecords
{
    protected static string $resource = StrikeForceDeploymentCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
