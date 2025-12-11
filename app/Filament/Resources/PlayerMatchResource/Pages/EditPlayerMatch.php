<?php

namespace App\Filament\Resources\PlayerMatchResource\Pages;

use App\Filament\Resources\PlayerMatchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlayerMatch extends EditRecord
{
    protected static string $resource = PlayerMatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Si le détachement n'est pas défini, charger celui du créateur
        if (empty($data['detachment']) && !empty($data['creator_id'])) {
            $creator = $this->record->creator;
            if ($creator && $creator->armyLists()->exists()) {
                // Prendre le détachement de la première liste d'armée du créateur
                $data['detachment'] = $creator->armyLists()->first()?->detachment;
            }
        }
        
        return $data;
    }
}
