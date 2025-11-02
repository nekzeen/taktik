<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;

class DownloadImagesAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'download_images';
    }

    public static function make(?string $name = null): static
    {
        return parent::make($name ?? static::getDefaultName())
            ->label('📥 Télécharger les images')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Télécharger les images')
            ->modalDescription('Cela va télécharger toutes les images depuis Wahapedia.')
            ->modalSubmitActionLabel('Télécharger')
            ->action(function ($livewire, $data) {
                try {
                    $type = $data['type'] ?? 'strike-force';

                    $commands = [
                        'strike-force' => 'missions:download-strike-force-images',
                        'incursion' => 'missions:download-incursion-images',
                        'asymmetric-warfare' => 'missions:download-asymmetric-warfare-images',
                    ];

                    if (!isset($commands[$type])) {
                        throw new \Exception("Type non supporté pour le téléchargement d'images: {$type}");
                    }

                    try {
                        Artisan::call($commands[$type], ['--force' => true]);
                    } catch (\Exception $e) {
                        throw new \Exception("Erreur lors du téléchargement: " . $e->getMessage());
                    }

                    Notification::make()
                        ->title('✅ Téléchargement réussi')
                        ->body('Les images ont été téléchargées avec succès.')
                        ->success()
                        ->send();

                    $livewire->dispatch('refresh');
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('❌ Erreur lors du téléchargement')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->form([
                \Filament\Forms\Components\Select::make('type')
                    ->label('Type de cartes')
                    ->options([
                        'strike-force' => 'Cartes Strike Force',
                        'incursion' => 'Cartes Incursions',
                        'asymmetric-warfare' => 'Cartes Guerre Asymétrique',
                    ])
                    ->default('strike-force')
                    ->required(),
            ]);
    }
}
