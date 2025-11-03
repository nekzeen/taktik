<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Artisan;

class SyncWahapediaAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'sync_wahapedia';
    }

    public static function make(?string $name = null): static
    {
        return parent::make($name ?? static::getDefaultName())
            ->label('Synchroniser depuis Wahapedia')
            ->icon('heroicon-o-arrow-path')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Synchroniser depuis Wahapedia')
            ->modalDescription('Cela va synchroniser les données depuis Wahapedia. Les données existantes seront fusionnées.')
            ->modalSubmitActionLabel('Synchroniser')
            ->action(function ($livewire, $data) {
                try {
                    $type = $data['type'] ?? 'all';
                    $mode = $data['mode'] ?? 'merge';

                    // Mapper les types aux commandes d'import
                    $commands = [
                        'all' => [
                            'missions:import-xml',
                            'missions:import-secondary-xml',
                            'twist:scrape-wahapedia',
                            'missions:import-twist-xml',
                            'missions:translate-twist',
                            'missions:import-asymmetric-xml',
                            'missions:import-strike-force',
                            'missions:import-incursion',
                            'missions:import-asymmetric-warfare',
                        ],
                        'primary' => ['missions:import-xml'],
                        'secondary' => ['missions:import-secondary-xml'],
                        'twist' => ['twist:scrape-wahapedia', 'missions:import-twist-xml', 'missions:translate-twist'],
                        'asymmetric' => ['missions:import-asymmetric-xml'],
                        'strike-force' => ['missions:import-strike-force'],
                        'incursion' => ['missions:import-incursion'],
                        'asymmetric-warfare' => ['missions:import-asymmetric-warfare'],
                    ];

                    if (!isset($commands[$type])) {
                        throw new \Exception("Type non supporté: {$type}");
                    }

                    // Exécuter les commandes d'import
                    foreach ($commands[$type] as $command) {
                        try {
                            Artisan::call($command);
                        } catch (\Exception $e) {
                            throw new \Exception("Erreur lors de l'exécution de {$command}: " . $e->getMessage());
                        }
                    }

                    // AUTOMATIQUE : Exécuter le workflow complet de validation et correction
                    Notification::make()
                        ->title('Validation et correction en cours...')
                        ->body('Validation des données et correction automatique des discrepancies.')
                        ->info()
                        ->send();

                    // Mapper les types au format attendu par update-and-validate
                    $typesMap = [
                        'all' => 'primary,secondary,twist,asymmetric,strike-force,incursion,asymmetric-warfare',
                        'primary' => 'primary',
                        'secondary' => 'secondary',
                        'twist' => 'twist',
                        'asymmetric' => 'asymmetric',
                        'strike-force' => 'strike-force',
                        'incursion' => 'incursion',
                        'asymmetric-warfare' => 'asymmetric-warfare',
                    ];

                    $types = $typesMap[$type] ?? 'primary';

                    // Exécuter le workflow complet : validation + import + comparaison + correction
                    Artisan::call('missions:update-and-validate', [
                        '--types' => $types,
                    ]);

                    Notification::make()
                        ->title('Synchronisation et validation réussies')
                        ->body('Les données ont été importées, validées et corrigées automatiquement.')
                        ->success()
                        ->send();

                    $livewire->dispatch('refresh');
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Erreur lors de la synchronisation')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->form([
                \Filament\Forms\Components\Select::make('type')
                    ->label('Type de données')
                    ->options([
                        'all' => 'Toutes les données',
                        'primary' => 'Missions Primaires',
                        'secondary' => 'Missions Secondaires',
                        'twist' => 'Péripéties',
                        'asymmetric' => 'Missions Primaires Asymétriques',
                        'strike-force' => 'Cartes Strike Force',
                        'incursion' => 'Cartes Incursions',
                        'asymmetric-warfare' => 'Cartes Guerre Asymétrique',
                    ])
                    ->default('all')
                    ->required(),
                \Filament\Forms\Components\Select::make('mode')
                    ->label('Mode de synchronisation')
                    ->options([
                        'merge' => 'Fusionner (Recommandé)',
                        'add-only' => 'Ajouter seulement',
                        'replace' => 'Remplacer tout (Danger)',  
                    ])
                    ->default('merge')
                    ->required(),
            ]);
    }
}
