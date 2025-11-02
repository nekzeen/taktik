<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsymmetricPrimaryMissionResource\Pages;
use App\Filament\Resources\AsymmetricPrimaryMissionResource\RelationManagers;
use App\Models\AsymmetricPrimaryMission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class AsymmetricPrimaryMissionResource extends Resource
{
    protected static ?string $model = AsymmetricPrimaryMission::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'Warhammer 40k';
    protected static ?string $navigationLabel = 'Missions Primaires Asymétriques';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Données de base')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom de la mission (EN)')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if ($state) {
                                    $set('slug', \Illuminate\Support\Str::slug($state));
                                }
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Textarea::make('description')
                            ->label('Description courte (EN)')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Texte complet')
                    ->schema([
                        Forms\Components\Textarea::make('full_text')
                            ->label('Texte complet de la mission (EN)')
                            ->required()
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Objectifs')
                    ->schema([
                        Forms\Components\Textarea::make('objectives')
                            ->label('Objectifs généraux (EN)')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('attacker_objective')
                            ->label('Objectif de l\'attaquant (EN)')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('defender_objective')
                            ->label('Objectif du défenseur (EN)')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Scoring')
                    ->schema([
                        Forms\Components\TextInput::make('max_vp')
                            ->label('Points de victoire max')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Textarea::make('scoring_conditions')
                            ->label('Conditions de scoring (EN)')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('timing')
                            ->label('Timing (EN)')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Métadonnées')
                    ->schema([
                        Forms\Components\TextInput::make('edition')
                            ->label('Édition')
                            ->default('10ed')
                            ->required(),
                        Forms\Components\TextInput::make('source')
                            ->label('Source')
                            ->default('chapter-approved-2025-26')
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom (EN)')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name_fr')
                    ->label('Nom (FR)')
                    ->getStateUsing(function (AsymmetricPrimaryMission $record) {
                        $translation = DB::table('translations')
                            ->where('resource_type', 'AsymmetricPrimaryMission')
                            ->where('resource_id', $record->id)
                            ->where('field', 'name')
                            ->where('locale', 'fr')
                            ->first();
                        return $translation?->translated_text ?? '—';
                    })
                    ->searchable(query: function (Builder $query, string $search) {
                        return $query->whereHas('translations', function (Builder $q) use ($search) {
                            $q->where('field', 'name')
                              ->where('locale', 'fr')
                              ->where('translated_text', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction) {
                        return $query->leftJoin('translations', function ($join) {
                            $join->on('asymmetric_primary_missions.id', '=', 'translations.resource_id')
                                 ->where('translations.resource_type', 'AsymmetricPrimaryMission')
                                 ->where('translations.field', 'name')
                                 ->where('translations.locale', 'fr');
                        })->orderBy('translations.translated_text', $direction);
                    }),
                Tables\Columns\TextColumn::make('max_vp')
                    ->label('VP Max')
                    ->numeric(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'chapter-approved-2025-26' => 'Chapter Approved 2025-26',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut'),
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
            RelationManagers\TranslationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAsymmetricPrimaryMissions::route('/'),
            'create' => Pages\CreateAsymmetricPrimaryMission::route('/create'),
            'edit' => Pages\EditAsymmetricPrimaryMission::route('/{record}/edit'),
        ];
    }
}
