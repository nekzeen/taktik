<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FactionResource\Pages;
use App\Filament\Resources\FactionResource\RelationManagers;
use App\Models\Faction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FactionResource extends Resource
{
    protected static ?string $model = Faction::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';
    
    protected static ?string $navigationLabel = 'Factions';
    
    protected static ?string $modelLabel = 'faction';
    
    protected static ?string $pluralModelLabel = 'factions';
    
    protected static ?string $navigationGroup = 'Configuration';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('bsdata_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name_fr')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('version')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DateTimePicker::make('imported_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bsdata_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_fr')
                    ->searchable(),
                Tables\Columns\TextColumn::make('version')
                    ->searchable(),
                Tables\Columns\TextColumn::make('imported_at')
                    ->dateTime()
                    ->sortable(),
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
            'index' => Pages\ListFactions::route('/'),
            'create' => Pages\CreateFaction::route('/create'),
            'edit' => Pages\EditFaction::route('/{record}/edit'),
        ];
    }
}
