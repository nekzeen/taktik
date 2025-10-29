<?php

namespace App\Filament\Resources\FactionResource\Pages;

use App\Filament\Resources\FactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFaction extends EditRecord
{
    protected static string $resource = FactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync')
                ->label('Synchroniser BSData')
                ->icon('heroicon-m-arrow-path')
                ->color('info')
                ->action(function () {
                    \Illuminate\Support\Facades\Artisan::call('bsdata:sync-faction', ['faction_id' => $this->record->id]);
                    \Filament\Notifications\Notification::make()
                        ->title('Synchronisation réussie')
                        ->body('Les données BSData pour ' . $this->record->name . ' ont été mises à jour.')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Synchroniser ' . $this->record->name)
                ->modalDescription('Êtes-vous sûr de vouloir synchroniser les données BSData pour cette faction ? Les modifications manuelles seront conservées.')
                ->modalSubmitActionLabel('Synchroniser'),
            Actions\DeleteAction::make(),
        ];
    }
}
