<?php

namespace App\Filament\Resources\FactionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DetachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'detachments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\Textarea::make('description')
                    ->maxLength(1000),
                
                Forms\Components\Toggle::make('is_manual')
                    ->label('Entrée manuelle')
                    ->helperText('Marquer comme entrée manuelle pour éviter la suppression lors des mises à jour BSData'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Détachement')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50),
                
                Tables\Columns\IconColumn::make('is_manual')
                    ->label('Manuel')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('manually_modified')
                    ->label('Modifié')
                    ->boolean()
                    ->tooltip('Protégé des mises à jour BSData'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_manual')
                    ->label('Entrées manuelles'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
