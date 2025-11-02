<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;

class TranslateAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'translate';
    }

    public static function make(?string $name = null): static
    {
        return parent::make($name ?? static::getDefaultName())
            ->label('🌍 Traduire')
            ->icon('heroicon-o-language')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Traduire les données')
            ->modalDescription('Cela va traduire automatiquement les données via DeepL.')
            ->modalSubmitActionLabel('Traduire')
            ->action(function ($livewire, $data) {
                try {
                    $type = $data['type'] ?? 'primary';
                    $locale = $data['locale'] ?? 'fr';

                    $commands = [
                        'primary' => 'missions:translate',
                        'secondary' => 'missions:translate-secondary',
                        'twist' => 'missions:translate-twist',
                        'asymmetric' => 'missions:translate-asymmetric',
                        'strike-force' => 'missions:translate-strike-force',
                        'incursion' => 'missions:translate-incursion',
                        'asymmetric-warfare' => 'missions:translate-asymmetric-warfare',
                    ];

                    if (!isset($commands[$type])) {
                        throw new \Exception("Type non supporté pour la traduction: {$type}");
                    }

                    try {
                        Artisan::call($commands[$type], ['--locale' => $locale]);
                    } catch (\Exception $e) {
                        throw new \Exception("Erreur lors de la traduction: " . $e->getMessage());
                    }

                    Notification::make()
                        ->title('✅ Traduction réussie')
                        ->body('Les données ont été traduites avec succès.')
                        ->success()
                        ->send();

                    $livewire->dispatch('refresh');
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('❌ Erreur lors de la traduction')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->form([
                \Filament\Forms\Components\Select::make('type')
                    ->label('Type de données')
                    ->options([
                        'primary' => 'Missions Primaires',
                        'secondary' => 'Missions Secondaires',
                        'twist' => 'Péripéties',
                        'asymmetric' => 'Missions Primaires Asymétriques',
                        'strike-force' => 'Cartes Strike Force',
                        'incursion' => 'Cartes Incursions',
                        'asymmetric-warfare' => 'Cartes Guerre Asymétrique',
                    ])
                    ->default('primary')
                    ->required(),
                \Filament\Forms\Components\Select::make('locale')
                    ->label('Langue')
                    ->options([
                        'fr' => 'Français',
                        'de' => 'Allemand',
                        'es' => 'Espagnol',
                        'it' => 'Italien',
                    ])
                    ->default('fr')
                    ->required(),
            ]);
    }
}
