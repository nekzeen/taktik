<?php

namespace App\Filament\Resources\IncursionDeploymentCardResource\Pages;

use App\Filament\Resources\IncursionDeploymentCardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIncursionDeploymentCard extends EditRecord
{
    protected static string $resource = IncursionDeploymentCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
