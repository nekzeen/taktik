<?php

namespace App\Filament\Resources\TournamentResource\Pages;

use App\Filament\Resources\TournamentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class EditTournament extends EditRecord
{
    protected static string $resource = TournamentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\TournamentResource\Widgets\TournamentStatsWidget::class,
        ];
    }
    
    protected function getHeaderWidgetsData(): array
    {
        return [
            'record' => $this->record,
        ];
    }
}
