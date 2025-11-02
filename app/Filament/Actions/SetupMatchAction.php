<?php

namespace App\Filament\Actions;

use App\Models\TournamentMatch;
use App\Services\MatchSetupService;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;

class SetupMatchAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Configurer le match')
            ->icon('heroicon-o-cog-6-tooth')
            ->color('info')
            ->form(function (TournamentMatch $record) {
                $service = new MatchSetupService();
                $options = $service->getAvailableOptions($record);

                return [
                    Section::make('Mode de tirage')
                        ->schema([
                            Toggle::make('is_random')
                                ->label('Tirage aléatoire')
                                ->default($record->setup_mode === 'random')
                                ->live()
                                ->helperText('Activé : tirage aléatoire | Désactivé : sélection manuelle'),
                        ]),

                    Section::make('Éléments du match')
                        ->schema([
                            Select::make('primary_mission_id')
                                ->label('Mission primaire')
                                ->options($options['primary_missions']->pluck('name', 'id'))
                                ->default($record->primary_mission_id)
                                ->required(),

                            Select::make('terrain_layout_id')
                                ->label('Disposition de terrain')
                                ->options($options['terrain_layouts']->pluck('name', 'id'))
                                ->default($record->terrain_layout_id)
                                ->required(),

                            Select::make('twist_mission_id')
                                ->label('Péripétie')
                                ->options($options['twist_missions']->pluck('name', 'id'))
                                ->default($record->twist_mission_id)
                                ->required(),

                            Select::make('asymmetric_primary_mission_id')
                                ->label('Mission primaire asymétrique (optionnel)')
                                ->options($options['asymmetric_primary_missions']->pluck('name', 'id'))
                                ->default($record->asymmetric_primary_mission_id)
                                ->nullable(),
                        ]),
                ];
            })
            ->action(function (TournamentMatch $record, array $data) {
                $service = new MatchSetupService();

                if ($data['is_random']) {
                    // Tirage aléatoire
                    $service->randomizeMatch($record);
                    Notification::make()
                        ->success()
                        ->title('Match configuré')
                        ->body('Les éléments du match ont été tirés au sort.')
                        ->send();
                } else {
                    // Mise à jour manuelle
                    $service->updateMatchSetup(
                        $record,
                        $data['primary_mission_id'] ?? null,
                        $data['terrain_layout_id'] ?? null,
                        $data['twist_mission_id'] ?? null,
                        $data['asymmetric_primary_mission_id'] ?? null
                    );
                    Notification::make()
                        ->success()
                        ->title('Match configuré')
                        ->body('Les éléments du match ont été mis à jour.')
                        ->send();
                }
            });
    }
}
