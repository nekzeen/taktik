<?php

namespace App\Filament\Resources\AsymmetricWarfareDeploymentCardResource\Pages;

use App\Filament\Resources\AsymmetricWarfareDeploymentCardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAsymmetricWarfareDeploymentCards extends ListRecords
{
    protected static string $resource = AsymmetricWarfareDeploymentCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
