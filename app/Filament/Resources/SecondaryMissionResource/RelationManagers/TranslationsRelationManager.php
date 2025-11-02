<?php

namespace App\Filament\Resources\SecondaryMissionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TranslationsRelationManager extends RelationManager
{
    protected static string $relationship = 'translations';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return 'Traductions';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('field')
                    ->label('Champ')
                    ->options([
                        'name' => 'Nom',
                        'description' => 'Description',
                        'full_text' => 'Texte complet',
                        'when_drawn' => 'When Drawn',
                        'when_condition' => 'Condition de timing',
                    ])
                    ->required()
                    ->disabled(fn ($record) => $record !== null),
                Forms\Components\Select::make('locale')
                    ->label('Langue')
                    ->options([
                        'fr' => 'Français',
                        'de' => 'Allemand',
                        'es' => 'Espagnol',
                        'it' => 'Italien',
                    ])
                    ->default('fr')
                    ->required()
                    ->disabled(fn ($record) => $record !== null),
                Forms\Components\Textarea::make('translated_text')
                    ->label('Texte traduit')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'auto' => 'Automatique (DeepL)',
                        'manual' => 'Modifiée manuellement',
                        'reviewed' => 'Révisée',
                        'approved' => 'Approuvée',
                    ])
                    ->default('pending')
                    ->required()
                    ->helperText('Le statut "Modifiée manuellement" est défini automatiquement quand vous changez la traduction'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('translated_text')
            ->columns([
                Tables\Columns\TextColumn::make('field')
                    ->label('Champ')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'name' => 'Nom',
                        'description' => 'Description',
                        'full_text' => 'Texte complet',
                        'when_drawn' => 'When Drawn',
                        'when_condition' => 'Condition de timing',
                        default => $state,
                    })
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('locale')
                    ->label('Langue')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('translated_text')
                    ->label('Texte traduit')
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'auto' => 'info',
                        'manual' => 'warning',
                        'reviewed' => 'success',
                        'approved' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'auto' => 'Automatique',
                        'manual' => 'Modifiée manuellement',
                        'reviewed' => 'Révisée',
                        'approved' => 'Approuvée',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('locale')
                    ->label('Langue')
                    ->options([
                        'fr' => 'Français',
                        'de' => 'Allemand',
                        'es' => 'Espagnol',
                        'it' => 'Italien',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'auto' => 'Automatique',
                        'manual' => 'Modifiée manuellement',
                        'reviewed' => 'Révisée',
                        'approved' => 'Approuvée',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Ajouter une traduction'),
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
}
