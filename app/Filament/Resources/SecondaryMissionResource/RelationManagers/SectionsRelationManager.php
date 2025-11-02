<?php

namespace App\Filament\Resources\SecondaryMissionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options([
                        'condition' => 'Condition',
                        'scoring' => 'Scoring',
                        'note' => 'Note',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('order')
                    ->label('Ordre')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->label('Titre')
                    ->maxLength(255),
                Forms\Components\Textarea::make('content')
                    ->label('Contenu')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('victory_points')
                    ->label('Points de victoire')
                    ->numeric()
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('Ordre')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'condition' => 'warning',
                        'scoring' => 'success',
                        'note' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'condition' => 'Condition',
                        'scoring' => 'Scoring',
                        'note' => 'Note',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->limit(40)
                    ->wrap(),
                Tables\Columns\TextColumn::make('victory_points')
                    ->label('VP')
                    ->numeric(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'condition' => 'Condition',
                        'scoring' => 'Scoring',
                        'note' => 'Note',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Ajouter une section'),
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
            ->reorderable('order');
    }
}
