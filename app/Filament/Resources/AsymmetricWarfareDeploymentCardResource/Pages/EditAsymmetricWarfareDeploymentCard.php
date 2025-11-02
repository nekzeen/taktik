<?php

namespace App\Filament\Resources\AsymmetricWarfareDeploymentCardResource\Pages;

use App\Filament\Resources\AsymmetricWarfareDeploymentCardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAsymmetricWarfareDeploymentCard extends EditRecord
{
    protected static string $resource = AsymmetricWarfareDeploymentCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
