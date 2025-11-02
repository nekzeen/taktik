<?php

namespace App\Filament\Resources\PrimaryMissionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                        'action' => 'Action',
                        'scoring' => 'Scoring',
                        'objective' => 'Objectif',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('order')
                    ->label('Ordre')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->label('Titre (EN)')
                    ->maxLength(255),
                Forms\Components\TextInput::make('timing')
                    ->label('Timing (EN)')
                    ->maxLength(255),
                Forms\Components\Textarea::make('content')
                    ->label('Contenu (EN)')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('victory_points')
                    ->label('Points de victoire')
                    ->numeric()
                    ->nullable(),
                Forms\Components\Textarea::make('conditions')
                    ->label('Conditions (JSON)')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('title_fr')
                    ->label('Titre (FR)')
                    ->maxLength(255),
                Forms\Components\TextInput::make('timing_fr')
                    ->label('Timing (FR)')
                    ->maxLength(255),
                Forms\Components\Textarea::make('content_fr')
                    ->label('Contenu (FR)')
                    ->rows(4)
                    ->columnSpanFull(),
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
                        'action' => 'info',
                        'scoring' => 'success',
                        'objective' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'action' => 'Action',
                        'scoring' => 'Scoring',
                        'objective' => 'Objectif',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->limit(40)
                    ->wrap(),
                Tables\Columns\TextColumn::make('timing')
                    ->label('Timing')
                    ->limit(30)
                    ->wrap(),
                Tables\Columns\TextColumn::make('victory_points')
                    ->label('VP')
                    ->numeric(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'action' => 'Action',
                        'scoring' => 'Scoring',
                        'objective' => 'Objectif',
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
