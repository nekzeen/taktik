<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarhammerGlossaryResource\Pages;
use App\Filament\Resources\WarhammerGlossaryResource\RelationManagers;
use App\Models\WarhammerGlossary;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WarhammerGlossaryResource extends Resource
{
    protected static ?string $model = WarhammerGlossary::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Warhammer 40k';
    protected static ?string $navigationLabel = 'Glossaire Warhammer';
    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return 'Glossaire Warhammer';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Glossaires Warhammer';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Terme')
                    ->schema([
                        Forms\Components\TextInput::make('english_term')
                            ->label('Terme anglais')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('category')
                            ->label('Catégorie')
                            ->options([
                                'unit' => 'Unité',
                                'ability' => 'Capacité',
                                'keyword' => 'Mot-clé',
                                'condition' => 'Condition',
                                'action' => 'Action',
                                'general' => 'Général',
                            ])
                            ->default('general')
                            ->required(),
                        Forms\Components\Select::make('context')
                            ->label('Contexte')
                            ->options([
                                'primary_mission' => 'Mission Primaire',
                                'secondary_mission' => 'Mission Secondaire',
                                'general' => 'Général',
                            ])
                            ->default('general')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Traductions')
                    ->schema([
                        Forms\Components\TextInput::make('french_translation')
                            ->label('Traduction française')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('german_translation')
                            ->label('Traduction allemande')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('spanish_translation')
                            ->label('Traduction espagnole')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('italian_translation')
                            ->label('Traduction italienne')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('example')
                            ->label('Exemple d\'utilisation')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Statut')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'approved' => 'Approuvé',
                                'rejected' => 'Rejeté',
                            ])
                            ->default('pending')
                            ->required(),
                        Forms\Components\TextInput::make('usage_count')
                            ->label('Nombre d\'utilisations')
                            ->numeric()
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('english_term')
                    ->label('Terme anglais')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('french_translation')
                    ->label('Français')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('context')
                    ->label('Contexte')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'approved' => 'Approuvé',
                        'rejected' => 'Rejeté',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('usage_count')
                    ->label('Utilisations')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options([
                        'unit' => 'Unité',
                        'ability' => 'Capacité',
                        'keyword' => 'Mot-clé',
                        'condition' => 'Condition',
                        'action' => 'Action',
                        'general' => 'Général',
                    ]),
                Tables\Filters\SelectFilter::make('context')
                    ->label('Contexte')
                    ->options([
                        'primary_mission' => 'Mission Primaire',
                        'secondary_mission' => 'Mission Secondaire',
                        'general' => 'Général',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'approved' => 'Approuvé',
                        'rejected' => 'Rejeté',
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
            'index' => Pages\ListWarhammerGlossaries::route('/'),
            'create' => Pages\CreateWarhammerGlossary::route('/create'),
            'edit' => Pages\EditWarhammerGlossary::route('/{record}/edit'),
        ];
    }
}
