<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameMatchResource\Pages;
use App\Filament\Resources\GameMatchResource\RelationManagers;
use App\Models\GameMatch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GameMatchResource extends Resource
{
    protected static ?string $model = GameMatch::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    
    protected static ?string $navigationLabel = 'Matchs';
    
    protected static ?string $modelLabel = 'match';
    
    protected static ?string $pluralModelLabel = 'matchs';
    
    protected static ?string $navigationGroup = 'Gestion des Tournois';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tournament_id')
                    ->relationship('tournament', 'name')
                    ->default(null),
                Forms\Components\TextInput::make('type')
                    ->required(),
                Forms\Components\TextInput::make('round')
                    ->numeric()
                    ->default(null),
                Forms\Components\Select::make('player1_id')
                    ->relationship('player1', 'name')
                    ->required(),
                Forms\Components\Select::make('player2_id')
                    ->relationship('player2', 'name')
                    ->default(null),
                Forms\Components\TextInput::make('player1_score')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('player2_score')
                    ->numeric()
                    ->default(null),
                Forms\Components\Select::make('winner_id')
                    ->relationship('winner', 'name')
                    ->default(null),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\DateTimePicker::make('scheduled_at'),
                Forms\Components\DateTimePicker::make('played_at'),
                Forms\Components\TextInput::make('location')
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tournament.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('round')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('player1.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('player2.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('player1_score')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('player2_score')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('winner.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('played_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            'index' => Pages\ListGameMatches::route('/'),
            'create' => Pages\CreateGameMatch::route('/create'),
            'edit' => Pages\EditGameMatch::route('/{record}/edit'),
        ];
    }
}
