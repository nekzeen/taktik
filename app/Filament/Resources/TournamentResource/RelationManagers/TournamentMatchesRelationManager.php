<?php

namespace App\Filament\Resources\TournamentResource\RelationManagers;

use App\Models\TournamentMatch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TournamentMatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'tournamentMatches';
    
    protected static ?string $title = 'Matchs';
    
    protected static ?string $modelLabel = 'match';
    
    protected static ?string $pluralModelLabel = 'matchs';

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Si un gagnant est défini (winner_id), marquer le match comme terminé
        if (isset($data['winner_id']) && $data['winner_id'] !== null && $data['winner_id'] !== '') {
            $data['status'] = 'completed';
            if (!isset($data['completed_at']) || $data['completed_at'] === null) {
                $data['completed_at'] = now();
            }
        }
        
        // Si le match est marqué comme nul (is_draw), marquer comme terminé
        if (isset($data['is_draw']) && $data['is_draw'] === true) {
            $data['status'] = 'completed';
            if (!isset($data['completed_at']) || $data['completed_at'] === null) {
                $data['completed_at'] = now();
            }
        }
        
        return $data;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du match')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('round')
                                    ->label('Round')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1),
                                Forms\Components\TextInput::make('table_number')
                                    ->label('Numéro de table')
                                    ->numeric()
                                    ->minValue(1),
                            ]),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'in_progress' => 'En cours',
                                'completed' => 'Terminé',
                            ])
                            ->required()
                            ->default('pending'),
                    ]),

                Forms\Components\Section::make('Joueur 1')
                    ->schema([
                        Forms\Components\Select::make('player1_id')
                            ->label('Joueur')
                            ->relationship('player1', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('player1_army_list_id')
                            ->label('Liste d\'armée')
                            ->relationship('player1ArmyList', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('player1_score')
                            ->label('Score')
                            ->integer()
                            ->minValue(0)
                            ->maxValue(999)
                            ->reactive()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                // Pré-remplir les PV avec le score par défaut
                                if ($state !== null && $state !== '') {
                                    $currentVP = $get('player1_victory_points');
                                    if ($currentVP === null || $currentVP === '') {
                                        $set('player1_victory_points', $state);
                                    }
                                }
                                static::updateWinner($set, $get);
                            }),
                        Forms\Components\TextInput::make('player1_victory_points')
                            ->label('Points de victoire')
                            ->integer()
                            ->minValue(0)
                            ->maxValue(999)
                            ->helperText('Rempli automatiquement avec le score, modifiable pour départage')
                            ->reactive()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                static::updateWinner($set, $get);
                            }),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Joueur 2')
                    ->schema([
                        Forms\Components\Select::make('player2_id')
                            ->label('Joueur')
                            ->relationship('player2', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('player2_army_list_id')
                            ->label('Liste d\'armée')
                            ->relationship('player2ArmyList', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('player2_score')
                            ->label('Score')
                            ->integer()
                            ->minValue(0)
                            ->maxValue(999)
                            ->reactive()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                // Pré-remplir les PV avec le score par défaut
                                if ($state !== null && $state !== '') {
                                    $currentVP = $get('player2_victory_points');
                                    if ($currentVP === null || $currentVP === '') {
                                        $set('player2_victory_points', $state);
                                    }
                                }
                                static::updateWinner($set, $get);
                            }),
                        Forms\Components\TextInput::make('player2_victory_points')
                            ->label('Points de victoire')
                            ->integer()
                            ->minValue(0)
                            ->maxValue(999)
                            ->helperText('Rempli automatiquement avec le score, modifiable pour départage')
                            ->reactive()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                static::updateWinner($set, $get);
                            }),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Résultat')
                    ->schema([
                        Forms\Components\Placeholder::make('result_info')
                            ->label('Résultat du match')
                            ->content(function (Forms\Get $get) {
                                $score1 = $get('player1_score');
                                $score2 = $get('player2_score');
                                $vp1 = $get('player1_victory_points');
                                $vp2 = $get('player2_victory_points');
                                
                                if ($score1 === null || $score2 === null) {
                                    return '⏳ Saisissez les scores pour voir le résultat';
                                }
                                
                                if ($score1 == $score2) {
                                    if ($vp1 !== null && $vp2 !== null) {
                                        if ($vp1 > $vp2) {
                                            return '🏆 Victoire Joueur 1 (égalité départagée aux PV: ' . $vp1 . ' vs ' . $vp2 . ')';
                                        } elseif ($vp2 > $vp1) {
                                            return '🏆 Victoire Joueur 2 (égalité départagée aux PV: ' . $vp2 . ' vs ' . $vp1 . ')';
                                        }
                                    }
                                    return '🤝 Match nul (' . $score1 . ' - ' . $score2 . ')';
                                }
                                
                                if ($score1 > $score2) {
                                    return '🏆 Victoire Joueur 1 (' . $score1 . ' - ' . $score2 . ')';
                                } else {
                                    return '🏆 Victoire Joueur 2 (' . $score2 . ' - ' . $score1 . ')';
                                }
                            })
                            ->columnSpanFull(),
                        
                        Forms\Components\Toggle::make('is_draw')
                            ->label('Forcer un match nul')
                            ->helperText('Cochez cette case uniquement si le match est vraiment nul (même score et même PV)')
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if ($state) {
                                    $set('winner_id', null);
                                    // Si le match est marqué comme nul, le marquer comme terminé
                                    $set('status', 'completed');
                                    if (!$set('completed_at')) {
                                        $set('completed_at', now());
                                    }
                                }
                            }),
                        
                        Forms\Components\Select::make('winner_id')
                            ->label('Vainqueur')
                            ->options(function (Forms\Get $get) {
                                $player1Id = $get('player1_id');
                                $player2Id = $get('player2_id');
                                
                                $options = [];
                                if ($player1Id) {
                                    $player1 = \App\Models\User::find($player1Id);
                                    if ($player1) {
                                        $options[$player1Id] = $player1->name . ' (Joueur 1)';
                                    }
                                }
                                if ($player2Id) {
                                    $player2 = \App\Models\User::find($player2Id);
                                    if ($player2) {
                                        $options[$player2Id] = $player2->name . ' (Joueur 2)';
                                    }
                                }
                                
                                return $options;
                            })
                            ->searchable()
                            ->hidden(fn (Forms\Get $get) => $get('is_draw'))
                            ->helperText('Le vainqueur sera calculé automatiquement en fonction des scores')
                            ->disabled()
                            ->dehydrated(),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('started_at')
                                    ->label('Début du match'),
                                Forms\Components\DateTimePicker::make('completed_at')
                                    ->label('Fin du match'),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('round')
            ->columns([
                Tables\Columns\TextColumn::make('round')
                    ->label('Round')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('table_number')
                    ->label('Table')
                    ->sortable(),
                Tables\Columns\TextColumn::make('player1.name')
                    ->label('Joueur 1')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('player1_score')
                    ->label('Score')
                    ->alignCenter()
                    ->sortable()
                    ->color(fn ($record) => $record->winner_id === $record->player1_id ? 'success' : null)
                    ->weight(fn ($record) => $record->winner_id === $record->player1_id ? 'bold' : null),
                Tables\Columns\TextColumn::make('vs')
                    ->label('')
                    ->default('VS')
                    ->alignCenter()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('player2_score')
                    ->label('Score')
                    ->alignCenter()
                    ->sortable()
                    ->color(fn ($record) => $record->winner_id === $record->player2_id ? 'success' : null)
                    ->weight(fn ($record) => $record->winner_id === $record->player2_id ? 'bold' : null),
                Tables\Columns\TextColumn::make('player2.name')
                    ->label('Joueur 2')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'in_progress' => 'En cours',
                        'completed' => 'Terminé',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('is_draw')
                    ->label('Nul')
                    ->boolean()
                    ->trueIcon('heroicon-o-minus-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('winner.name')
                    ->label('Vainqueur')
                    ->searchable()
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-trophy')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('round')
                    ->label('Round')
                    ->options(fn () => range(1, 10)),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'in_progress' => 'En cours',
                        'completed' => 'Terminé',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('generate_league_matches')
                    ->label('Générer matchs de ligue')
                    ->icon('heroicon-o-sparkles')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Générer les matchs de ligue')
                    ->modalDescription('Cette action va créer tous les matchs nécessaires pour que chaque joueur affronte tous les autres joueurs une fois. Les matchs déjà existants ne seront pas dupliqués.')
                    ->modalSubmitActionLabel('Générer les matchs')
                    ->visible(fn () => $this->getOwnerRecord()->format === 'league')
                    ->action(function () {
                        $tournament = $this->getOwnerRecord();
                        $generator = new \App\Services\LeagueMatchGenerator();
                        
                        try {
                            $result = $generator->generateLeagueMatches($tournament, true);
                            
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Matchs générés avec succès')
                                ->body("✓ {$result['created']} matchs créés\n" . 
                                       ($result['skipped'] > 0 ? "⚠ {$result['skipped']} matchs ignorés (déjà existants)\n" : "") .
                                       "📊 Total: {$result['total']} matchs possibles pour {$result['players']} joueurs")
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Erreur')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
                
                Tables\Actions\Action::make('league_stats')
                    ->label('Statistiques de la ligue')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->visible(fn () => $this->getOwnerRecord()->format === 'league')
                    ->modalHeading('Statistiques de la ligue')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer')
                    ->modalContent(function () {
                        $tournament = $this->getOwnerRecord();
                        $generator = new \App\Services\LeagueMatchGenerator();
                        $stats = $generator->getLeagueStats($tournament);
                        
                        return view('filament.resources.tournament-resource.modals.league-stats', [
                            'stats' => $stats,
                        ]);
                    }),
                
                Tables\Actions\CreateAction::make()
                    ->label('Créer un match')
                    ->icon('heroicon-o-plus')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Calculer automatiquement le vainqueur
                        $match = new TournamentMatch($data);
                        $match->determineWinner();
                        return array_merge($data, [
                            'winner_id' => $match->winner_id,
                            'is_draw' => $match->is_draw,
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('start')
                    ->label('Démarrer')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (TournamentMatch $record) {
                        $record->update([
                            'status' => 'in_progress',
                            'started_at' => now(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Match démarré')
                            ->body("Le match entre {$record->player1->name} et {$record->player2->name} a démarré.")
                            ->send();
                    })
                    ->visible(fn (TournamentMatch $record) => $record->status === 'pending'),
                
                Tables\Actions\Action::make('complete')
                    ->label('Terminer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (TournamentMatch $record) {
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Match terminé')
                            ->body("Le match a été marqué comme terminé.")
                            ->send();
                    })
                    ->visible(fn (TournamentMatch $record) => $record->status === 'in_progress'),
                
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Calculer automatiquement le vainqueur
                        $match = new TournamentMatch($data);
                        $match->determineWinner();
                        return array_merge($data, [
                            'winner_id' => $match->winner_id,
                            'is_draw' => $match->is_draw,
                        ]);
                    }),
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('start_all')
                        ->label('Démarrer tous')
                        ->icon('heroicon-o-play')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(function (TournamentMatch $record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'in_progress',
                                        'started_at' => now(),
                                    ]);
                                }
                            });
                            
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Matchs démarrés')
                                ->body("{$records->count()} match(s) ont été démarrés.")
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('round', 'asc')
            ->defaultGroup('round');
    }
    
    /**
     * Calculer automatiquement le vainqueur en fonction des scores
     */
    protected static function updateWinner(Forms\Set $set, Forms\Get $get): void
    {
        $score1 = $get('player1_score');
        $score2 = $get('player2_score');
        $vp1 = $get('player1_victory_points');
        $vp2 = $get('player2_victory_points');
        $player1Id = $get('player1_id');
        $player2Id = $get('player2_id');

        // Si les scores ne sont pas saisis, on ne fait rien
        if ($score1 === null || $score2 === null || $score1 === '' || $score2 === '') {
            $set('winner_id', null);
            $set('is_draw', false);
            return;
        }

        // Convertir en nombres
        $score1 = (int) $score1;
        $score2 = (int) $score2;

        // Si égalité de score
        if ($score1 === $score2) {
            // Vérifier les points de victoire pour départager
            if ($vp1 !== null && $vp2 !== null && $vp1 !== '' && $vp2 !== '') {
                $vp1 = (int) $vp1;
                $vp2 = (int) $vp2;
                
                if ($vp1 > $vp2) {
                    $set('winner_id', $player1Id);
                    $set('is_draw', false);
                } elseif ($vp2 > $vp1) {
                    $set('winner_id', $player2Id);
                    $set('is_draw', false);
                } else {
                    // Vraiment nul (même score et même PV)
                    $set('winner_id', null);
                    $set('is_draw', true);
                }
            } else {
                // Match nul (pas de PV pour départager)
                $set('winner_id', null);
                $set('is_draw', true);
            }
        } else {
            // Victoire claire
            $set('is_draw', false);
            if ($score1 > $score2) {
                $set('winner_id', $player1Id);
            } else {
                $set('winner_id', $player2Id);
            }
        }
        
        // Marquer le match comme terminé quand un gagnant est déterminé
        $set('status', 'completed');
        if (!$get('completed_at')) {
            $set('completed_at', now());
        }
    }
}
