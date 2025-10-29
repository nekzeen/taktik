<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email('L\'email doit être valide.')
                    ->required('L\'email est requis.')
                    ->maxLength(255)
                    ->unique(
                        table: 'users',
                        column: 'email',
                        ignoreRecord: true
                    )
                    ->validationMessages([
                        'unique' => 'Cet email est déjà utilisé par un autre utilisateur.',
                    ]),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\Section::make('Gestion des tournois et matchs')
                    ->description('Contrôlez la capacité de cet utilisateur à créer des tournois et des matchs')
                    ->schema([
                        Forms\Components\Toggle::make('can_create_tournaments')
                            ->label('Peut créer des tournois')
                            ->helperText('Désactiver pour empêcher cet utilisateur de créer des tournois')
                            ->default(true),
                        Forms\Components\Toggle::make('can_create_matches')
                            ->label('Peut créer des matchs')
                            ->helperText('Désactiver pour empêcher cet utilisateur de créer des matchs')
                            ->default(true),
                        Forms\Components\TextInput::make('max_open_tournaments')
                            ->label('Nombre maximum de tournois ouverts')
                            ->helperText('Laisser vide pour utiliser la limite par défaut du rôle')
                            ->numeric()
                            ->minValue(0)
                            ->default(null),
                    ]),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('consent_at'),
                Forms\Components\DateTimePicker::make('last_activity_at'),
                Forms\Components\TextInput::make('theme')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('can_create_tournaments')
                    ->boolean(),
                Tables\Columns\IconColumn::make('can_create_matches')
                    ->boolean(),
                Tables\Columns\TextColumn::make('max_open_tournaments')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('consent_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_activity_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('theme'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
