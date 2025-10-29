<?php

namespace App\Filament\Resources\FactionResource\Pages;

use App\Filament\Resources\FactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFactions extends ListRecords
{
    protected static string $resource = FactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync-all')
                ->label('Synchroniser toutes les factions')
                ->icon('heroicon-m-arrow-path')
                ->color('success')
                ->action(function () {
                    \Illuminate\Support\Facades\Artisan::call('bsdata:sync');
                    \Filament\Notifications\Notification::make()
                        ->title('Synchronisation réussie')
                        ->body('Toutes les données BSData ont été mises à jour.')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Synchroniser toutes les factions')
                ->modalDescription('Êtes-vous sûr de vouloir synchroniser les données BSData pour toutes les factions ? Les modifications manuelles seront conservées.')
                ->modalSubmitActionLabel('Synchroniser'),
            Actions\CreateAction::make(),
        ];
    }
}
