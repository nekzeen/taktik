<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TournamentMatchResource\Pages;
use App\Models\TournamentMatch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TournamentMatchResource extends Resource
{
    protected static ?string $model = TournamentMatch::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $modelLabel = 'Match';

    protected static ?string $pluralModelLabel = 'Matchs';

    protected static ?string $navigationGroup = 'Gestion des tournois';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du match')
                    ->schema([
                        Forms\Components\Select::make('tournament_id')
                            ->label('Tournoi')
                            ->relationship('tournament', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('round')
                                    ->label('Round')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1),
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
                            ->searchable(),
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
                            ->required()
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
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Joueur 2')
                    ->schema([
                        Forms\Components\Select::make('player2_id')
                            ->label('Joueur')
                            ->relationship('player2', 'name')
                            ->required()
                            ->searchable(),
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
                            ->required()
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
                    ])
                    ->columns(1),

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
                        
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('player1_victory_points')
                                    ->label('Points de victoire Joueur 1')
                                    ->integer()
                                    ->minValue(0)
                                    ->maxValue(999)
                                    ->helperText('Rempli automatiquement avec le score, modifiable pour départage')
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                        static::updateWinner($set, $get);
                                    }),
                                Forms\Components\TextInput::make('player2_victory_points')
                                    ->label('Points de victoire Joueur 2')
                                    ->integer()
                                    ->minValue(0)
                                    ->maxValue(999)
                                    ->helperText('Rempli automatiquement avec le score, modifiable pour départage')
                                    ->reactive()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                        static::updateWinner($set, $get);
                                    }),
                            ]),
                        
                        Forms\Components\Toggle::make('is_draw')
                            ->label('Forcer un match nul')
                            ->helperText('Cochez cette case uniquement si le match est vraiment nul (même score et même PV)')
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if ($state) {
                                    $set('winner_id', null);
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tournament.name')
                    ->label('Tournoi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('round')
                    ->label('Round')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('table_number')
                    ->label('Table')
                    ->sortable(),
                Tables\Columns\TextColumn::make('player1.name')
                    ->label('Joueur 1')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('player1_score')
                    ->label('Score J1')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('player2.name')
                    ->label('Joueur 2')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('player2_score')
                    ->label('Score J2')
                    ->alignCenter()
                    ->sortable(),
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
                    ->boolean(),
                Tables\Columns\TextColumn::make('winner.name')
                    ->label('Vainqueur')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tournament_id')
                    ->label('Tournoi')
                    ->relationship('tournament', 'name'),
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
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('round', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTournamentMatches::route('/'),
            'create' => Pages\CreateTournamentMatch::route('/create'),
            'edit' => Pages\EditTournamentMatch::route('/{record}/edit'),
        ];
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
    }
}
