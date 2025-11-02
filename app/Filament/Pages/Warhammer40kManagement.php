<?php

namespace App\Filament\Pages;

use App\Filament\Actions\SyncWahapediaAction;
use App\Filament\Actions\DownloadImagesAction;
use App\Filament\Actions\TranslateAction;
use Filament\Pages\Page;
use Filament\Actions;

class Warhammer40kManagement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Warhammer 40k';
    protected static ?string $navigationLabel = 'Gestion des données';
    protected static ?int $navigationSort = 100;
    protected static string $view = 'filament.pages.warhammer-40k-management';

    public function getTitle(): string
    {
        return 'Gestion des données Warhammer 40k';
    }

    protected function getHeaderActions(): array
    {
        return [
            SyncWahapediaAction::make(),
            DownloadImagesAction::make(),
            TranslateAction::make(),
        ];
    }
}
