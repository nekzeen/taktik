<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\DetachmentSelect;
use App\Filament\Resources\PlayerMatchResource\Pages;
use App\Models\PlayerMatch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlayerMatchResource extends Resource
{
    protected static ?string $model = PlayerMatch::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'Match libre';

    protected static ?string $pluralLabel = 'Matchs libres';

    protected static ?string $navigationGroup = 'Gestion des matchs simples';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\Select::make('creator_id')
                            ->relationship('creator', 'name')
                            ->label('Créateur'),
                        Forms\Components\Select::make('opponent_id')
                            ->relationship('opponent', 'name')
                            ->label('Adversaire'),
                        Forms\Components\Select::make('type')
                            ->options([
                                'competitive' => 'Compétitif',
                                'narrative' => 'Narratif',
                            ])
                            ->label('Type'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'open' => 'Ouvert',
                                'confirmed' => 'Confirmé',
                                'completed' => 'Terminé',
                                'cancelled' => 'Annulé',
                            ])
                            ->label('Statut'),
                    ])->columns(2),

                Forms\Components\Section::make('Armée')
                    ->schema([
                        Forms\Components\TextInput::make('army_points')
                            ->numeric()
                            ->label('Points d\'armée'),
                        Forms\Components\TextInput::make('faction')
                            ->label('Faction'),
                        Forms\Components\TextInput::make('detachment')
                            ->label('Détachement'),
                    ])->columns(3),

                Forms\Components\Section::make('Localisation')
                    ->schema([
                        Forms\Components\TextInput::make('city')
                            ->label('Ville'),
                        Forms\Components\TextInput::make('department')
                            ->label('Département'),
                    ])->columns(2),

                Forms\Components\Section::make('Disponibilité')
                    ->schema([
                        Forms\Components\Select::make('availability_type')
                            ->options([
                                'single' => 'Date fixe',
                                'period' => 'Période',
                            ])
                            ->label('Type de disponibilité')
                            ->reactive(),
                        Forms\Components\DateTimePicker::make('available_at')
                            ->visible(fn (Forms\Get $get) => $get('availability_type') === 'single')
                            ->label('Date et heure'),
                        Forms\Components\DateTimePicker::make('available_from')
                            ->visible(fn (Forms\Get $get) => $get('availability_type') === 'period')
                            ->label('Du'),
                        Forms\Components\DateTimePicker::make('available_to')
                            ->visible(fn (Forms\Get $get) => $get('availability_type') === 'period')
                            ->label('Au'),
                    ]),

                Forms\Components\Section::make('Résultat')
                    ->schema([
                        Forms\Components\TextInput::make('creator_score')
                            ->numeric()
                            ->label('Score créateur'),
                        Forms\Components\TextInput::make('opponent_score')
                            ->numeric()
                            ->label('Score adversaire'),
                        Forms\Components\Select::make('winner_id')
                            ->relationship('winner', 'name')
                            ->label('Gagnant'),
                        Forms\Components\Checkbox::make('is_draw')
                            ->label('Match nul'),
                        Forms\Components\DateTimePicker::make('played_at')
                            ->label('Date du match'),
                    ])->columns(2),

                Forms\Components\Section::make('Configuration du match')
                    ->schema([
                        Forms\Components\Select::make('primary_mission_id')
                            ->relationship('primaryMission', 'name')
                            ->label('Mission primaire')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('twist_mission_id')
                            ->relationship('twistMission', 'name')
                            ->label('Péripétie')
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Créateur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('opponent.name')
                    ->label('Adversaire')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'competitive' ? 'Compétitif' : 'Narratif')
                    ->color(fn (string $state) => $state === 'competitive' ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('faction')
                    ->label('Faction')
                    ->searchable(),
                Tables\Columns\TextColumn::make('army_points')
                    ->label('Points')
                    ->sortable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('Ville')
                    ->searchable(),
                Tables\Columns\TextColumn::make('primaryMission.name')
                    ->label('Mission primaire')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('twistMission.name')
                    ->label('Péripétie')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match($state) {
                        'open' => 'Ouvert',
                        'confirmed' => 'Confirmé',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                        default => $state,
                    })
                    ->color(fn (string $state) => match($state) {
                        'open' => 'info',
                        'confirmed' => 'success',
                        'completed' => 'gray',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('available_at')
                    ->label('Disponibilité')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Ouvert',
                        'confirmed' => 'Confirmé',
                        'completed' => 'Terminé',
                        'cancelled' => 'Annulé',
                    ])
                    ->label('Statut'),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'competitive' => 'Compétitif',
                        'narrative' => 'Narratif',
                    ])
                    ->label('Type'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (PlayerMatch $record) => $record->status === 'open' && !$record->is_setup_validated),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPlayerMatches::route('/'),
            'create' => Pages\CreatePlayerMatch::route('/create'),
            'edit' => Pages\EditPlayerMatch::route('/{record}/edit'),
        ];
    }
}
