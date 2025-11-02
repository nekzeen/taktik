<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TournamentMissionPoolResource\Pages;
use App\Filament\Resources\TournamentMissionPoolResource\RelationManagers;
use App\Models\TournamentMissionPool;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TournamentMissionPoolResource extends Resource
{
    protected static ?string $model = TournamentMissionPool::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationGroup = 'Warhammer 40k';
    protected static ?string $navigationLabel = 'Pool de Missions de Tournoi';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\TextInput::make('pool_number')
                            ->label('Numéro du pool')
                            ->numeric()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if ($state) {
                                    $set('slug', \Illuminate\Support\Str::slug($state));
                                }
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ])->columns(3),
                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\Select::make('primary_mission_id')
                            ->label('Mission primaire')
                            ->relationship('primaryMission', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('deployment_mode')
                            ->label('Mode de déploiement')
                            ->placeholder('Ex: Hammer and Anvil, Dawn of War'),
                        Forms\Components\Select::make('terrain_layout_id')
                            ->label('Disposition de terrain')
                            ->relationship('terrainLayout', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Toggle::make('use_twist_deck')
                            ->label('Utiliser le deck de péripéties')
                            ->default(false),
                    ])->columns(2),
                Forms\Components\Section::make('Missions secondaires')
                    ->schema([
                        Forms\Components\CheckboxList::make('secondaryMissions')
                            ->label('Sélectionner les missions secondaires')
                            ->relationship('secondaryMissions', 'name')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Dispositions de terrain disponibles')
                    ->schema([
                        Forms\Components\CheckboxList::make('availableTerrainLayouts')
                            ->label('Sélectionner les dispositions de terrain')
                            ->relationship('availableTerrainLayouts', 'name')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Contenu')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Métadonnées')
                    ->schema([
                        Forms\Components\TextInput::make('source')
                            ->label('Source')
                            ->default('chapter-approved-2025-26')
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pool_number')
                    ->label('N°')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('primaryMission.name')
                    ->label('Mission primaire')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('deployment_mode')
                    ->label('Déploiement')
                    ->badge(),
                Tables\Columns\TextColumn::make('availableTerrainLayouts')
                    ->label('Dispositions de terrain')
                    ->state(function ($record) {
                        return $record->availableTerrainLayouts
                            ->pluck('layout_number')
                            ->join(', ');
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTournamentMissionPools::route('/'),
            'create' => Pages\CreateTournamentMissionPool::route('/create'),
            'edit' => Pages\EditTournamentMissionPool::route('/{record}/edit'),
        ];
    }
}
