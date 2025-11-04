<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SecondaryMissionResource\Pages;
use App\Filament\Resources\SecondaryMissionResource\RelationManagers;
use App\Models\SecondaryMission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class SecondaryMissionResource extends Resource
{
    protected static ?string $model = SecondaryMission::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Warhammer 40k';
    protected static ?string $navigationLabel = 'Missions Secondaires';
    protected static ?int $navigationSort = 2;

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

                Forms\Components\Section::make('Conditions')
                    ->schema([
                        Forms\Components\Textarea::make('when_drawn')
                            ->label('Condition "When Drawn" (EN)')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('when_condition')
                            ->label('Condition de timing (EN)')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('timing')
                            ->label('Timing')
                            ->options([
                                'any_battle_round' => 'N\'importe quel round',
                                'second_battle_round_onwards' => 'À partir du 2e round',
                                'from_battle_round_two' => 'À partir du round 2',
                            ])
                            ->default('any_battle_round')
                            ->required(),
                    ]),

                Forms\Components\Section::make('Scoring')
                    ->schema([
                        Forms\Components\TextInput::make('max_vp')
                            ->label('Points de victoire maximum par tour')
                            ->numeric()
                            ->default(15)
                            ->required(),
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
                        Forms\Components\Toggle::make('can_be_fixed')
                            ->label('Peut être une Mission Fixe')
                            ->default(true)
                            ->helperText('Si activé, cette mission peut être sélectionnée comme Mission Fixe'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom (EN)')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (SecondaryMission $record): string => 
                        $record->can_be_fixed ? 'success' : 'gray'
                    ),
                Tables\Columns\TextColumn::make('name_fr')
                    ->label('Nom (FR)')
                    ->getStateUsing(function (SecondaryMission $record) {
                        $translation = DB::table('translations')
                            ->where('resource_type', 'SecondaryMission')
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
                            $join->on('secondary_missions.id', '=', 'translations.resource_id')
                                 ->where('translations.resource_type', 'SecondaryMission')
                                 ->where('translations.field', 'name')
                                 ->where('translations.locale', 'fr');
                        })->orderBy('translations.translated_text', $direction);
                    })
                    ->badge()
                    ->color(fn (SecondaryMission $record): string => 
                        $record->can_be_fixed ? 'success' : 'gray'
                    ),
                Tables\Columns\TextColumn::make('timing')
                    ->label('Timing')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('max_vp')
                    ->label('Max VP')
                    ->numeric(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                Tables\Columns\IconColumn::make('can_be_fixed')
                    ->label('Peut être Fixe')
                    ->boolean()
                    ->tooltip('Peut être utilisée comme Mission Fixe')
                    ->color(fn (SecondaryMission $record): string => 
                        $record->can_be_fixed ? 'success' : 'gray'
                    ),
                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->badge(),
            ])
            ->defaultSort('can_be_fixed', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('source')
                    ->label('Source')
                    ->options([
                        'chapter-approved-2025-26' => 'Chapter Approved 2025-26',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut'),
                Tables\Filters\TernaryFilter::make('can_be_fixed')
                    ->label('Peut être Fixe'),
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
            RelationManagers\SectionsRelationManager::class,
            RelationManagers\TranslationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSecondaryMissions::route('/'),
            'create' => Pages\CreateSecondaryMission::route('/create'),
            'edit' => Pages\EditSecondaryMission::route('/{record}/edit'),
            'import' => Pages\ImportMissions::route('/import'),
        ];
    }
}
