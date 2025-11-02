<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\DetachmentSelect;
use App\Filament\Resources\DetachmentAbilityResource\Pages;
use App\Models\DetachmentAbility;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DetachmentAbilityResource extends Resource
{
    protected static ?string $model = DetachmentAbility::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'Capacités Détachement';
    protected static ?string $navigationGroup = 'Wahapedia';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('wahapedia_id')
                            ->label('Wahapedia ID')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        DetachmentSelect::make('detachment_id'),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('detachment.name')
                    ->label('Détachement')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('detachment.faction.name')
                    ->label('Faction')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('wahapedia_id')
                    ->label('Wahapedia ID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('detachment')
                    ->relationship('detachment', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDetachmentAbilities::route('/'),
            'create' => Pages\CreateDetachmentAbility::route('/create'),
            'edit' => Pages\EditDetachmentAbility::route('/{record}/edit'),
        ];
    }
}
