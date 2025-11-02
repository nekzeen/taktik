<?php

namespace App\Filament\Resources;

use App\Models\Translation;
use App\Filament\Resources\TranslationResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

class TranslationResource extends Resource
{
    protected static ?string $model = Translation::class;

    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $navigationGroup = 'Gestion des données';

    protected static ?string $label = 'Traduction';

    protected static ?string $pluralLabel = 'Traductions';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Texte source')
                    ->schema([
                        Select::make('source_language')
                            ->label('Langue source')
                            ->options([
                                'en' => 'Anglais',
                                'fr' => 'Français',
                                'de' => 'Allemand',
                                'es' => 'Espagnol',
                                'it' => 'Italien',
                            ])
                            ->required()
                            ->disabled(fn($record) => $record !== null),

                        Textarea::make('source_text')
                            ->label('Texte source')
                            ->required()
                            ->disabled(fn($record) => $record !== null),
                    ]),

                Section::make('Traduction')
                    ->schema([
                        Select::make('target_language')
                            ->label('Langue cible')
                            ->options([
                                'en' => 'Anglais',
                                'fr' => 'Français',
                                'de' => 'Allemand',
                                'es' => 'Espagnol',
                                'it' => 'Italien',
                            ])
                            ->required()
                            ->disabled(fn($record) => $record !== null),

                        Textarea::make('translated_text')
                            ->label('Texte traduit')
                            ->required(),

                        Select::make('translator')
                            ->label('Traducteur')
                            ->options([
                                'deepl' => 'DeepL',
                                'google' => 'Google Translate',
                                'manual' => 'Manuel',
                            ])
                            ->required()
                            ->disabled(fn($record) => $record !== null),
                    ]),

                Section::make('Révision')
                    ->schema([
                        Toggle::make('reviewed')
                            ->label('Révisée')
                            ->default(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source_text')
                    ->label('Texte source')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('translated_text')
                    ->label('Traduction')
                    ->limit(50)
                    ->searchable(),

                BadgeColumn::make('source_language')
                    ->label('Langue source')
                    ->colors([
                        'primary' => 'en',
                        'success' => 'fr',
                    ]),

                BadgeColumn::make('target_language')
                    ->label('Langue cible')
                    ->colors([
                        'primary' => 'en',
                        'success' => 'fr',
                    ]),

                BadgeColumn::make('translator')
                    ->label('Traducteur')
                    ->colors([
                        'info' => 'deepl',
                        'warning' => 'google',
                        'success' => 'manual',
                    ]),

                BadgeColumn::make('reviewed')
                    ->label('Révisée')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->formatStateUsing(fn($state) => $state ? 'Oui' : 'Non'),

                TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('source_language')
                    ->label('Langue source')
                    ->options([
                        'en' => 'Anglais',
                        'fr' => 'Français',
                        'de' => 'Allemand',
                        'es' => 'Espagnol',
                        'it' => 'Italien',
                    ]),

                SelectFilter::make('target_language')
                    ->label('Langue cible')
                    ->options([
                        'en' => 'Anglais',
                        'fr' => 'Français',
                        'de' => 'Allemand',
                        'es' => 'Espagnol',
                        'it' => 'Italien',
                    ]),

                SelectFilter::make('translator')
                    ->label('Traducteur')
                    ->options([
                        'deepl' => 'DeepL',
                        'google' => 'Google Translate',
                        'manual' => 'Manuel',
                    ]),

                TernaryFilter::make('reviewed')
                    ->label('Révisée')
                    ->placeholder('Toutes')
                    ->trueLabel('Révisées')
                    ->falseLabel('En attente'),
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTranslations::route('/'),
            'create' => Pages\CreateTranslation::route('/create'),
            'edit' => Pages\EditTranslation::route('/{record}/edit'),
        ];
    }
}
