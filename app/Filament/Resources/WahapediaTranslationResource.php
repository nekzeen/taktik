<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WahapediaTranslationResource\Pages;
use App\Models\Translation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WahapediaTranslationResource extends Resource
{
    protected static ?string $model = Translation::class;

    protected static ?string $navigationIcon = 'heroicon-o-language';
    protected static ?string $navigationLabel = 'Traductions Wahapedia';
    protected static ?string $navigationGroup = 'Wahapedia';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Traduction')
                    ->schema([
                        Forms\Components\TextInput::make('source_text')
                            ->label('Texte Source (EN)')
                            ->required()
                            ->maxLength(255)
                            ->disabled(),

                        Forms\Components\TextInput::make('locale')
                            ->label('Langue Cible')
                            ->required()
                            ->maxLength(10)
                            ->disabled(),

                        Forms\Components\Textarea::make('translated_text')
                            ->label('Texte Traduit (FR)')
                            ->required()
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'auto' => 'Traduction automatique',
                                'reviewed' => 'Révisé',
                                'approved' => 'Approuvé',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('resource_type')
                            ->label('Type de Ressource')
                            ->maxLength(255)
                            ->disabled(),

                        Forms\Components\TextInput::make('field')
                            ->label('Champ')
                            ->maxLength(255)
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('source_text')
                    ->label('Texte Source')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('translated_text')
                    ->label('Traduction')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'auto',
                        'success' => 'reviewed',
                        'success' => 'approved',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'auto' => 'Automatique',
                        'reviewed' => 'Révisé',
                        'approved' => 'Approuvé',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('resource_type')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('field')
                    ->label('Champ')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('locale')
                    ->label('Langue')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'auto' => 'Traduction automatique',
                        'reviewed' => 'Révisé',
                        'approved' => 'Approuvé',
                    ]),

                Tables\Filters\SelectFilter::make('resource_type')
                    ->options([
                        'Detachment' => 'Détachement',
                        'DetachmentAbility' => 'Capacité Détachement',
                        'Faction' => 'Faction',
                        'Datasheet' => 'Datasheet',
                        'Stratagem' => 'Stratagème',
                    ]),

                Tables\Filters\SelectFilter::make('locale')
                    ->options([
                        'fr' => 'Français',
                        'en' => 'Anglais',
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWahapediaTranslations::route('/'),
            'edit' => Pages\EditWahapediaTranslation::route('/{record}/edit'),
        ];
    }
}
