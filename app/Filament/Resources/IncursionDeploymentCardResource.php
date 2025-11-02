<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncursionDeploymentCardResource\Pages;
use App\Filament\Resources\IncursionDeploymentCardResource\RelationManagers;
use App\Models\IncursionDeploymentCard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class IncursionDeploymentCardResource extends Resource
{
    protected static ?string $model = IncursionDeploymentCard::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Warhammer 40k';
    protected static ?string $navigationLabel = 'Incursion - Cartes de Déploiement';
    protected static ?int $navigationSort = 7;
    protected static ?string $modelLabel = 'Carte Incursion';
    protected static ?string $pluralModelLabel = 'Cartes Incursion';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Données de base')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom de la carte (EN)')
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
                            ->label('Texte complet de la carte (EN)')
                            ->required()
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Contenu de la carte')
                    ->schema([
                        Forms\Components\Textarea::make('card_content')
                            ->label('Contenu détaillé (EN)')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('rules')
                            ->label('Règles spéciales (EN)')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Image')
                    ->schema([
                        Forms\Components\TextInput::make('image_url')
                            ->label('URL de l\'image originale')
                            ->url()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('image_filename')
                            ->label('Nom du fichier image')
                            ->helperText('Ex: CA6_INC_Ambush.png'),
                        Forms\Components\TextInput::make('image_path')
                            ->label('Chemin local de l\'image')
                            ->helperText('Chemin relatif dans storage/app/public/ (ex: incursion-cards/CA6_INC_Ambush.png)')
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
                    ->getStateUsing(function (IncursionDeploymentCard $record) {
                        $translation = DB::table('translations')
                            ->where('resource_type', 'IncursionDeploymentCard')
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
                            $join->on('incursion_deployment_cards.id', '=', 'translations.resource_id')
                                 ->where('translations.resource_type', 'IncursionDeploymentCard')
                                 ->where('translations.field', 'name')
                                 ->where('translations.locale', 'fr');
                        })->orderBy('translations.translated_text', $direction);
                    }),
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->width(100)
                    ->height(100),
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
            'index' => Pages\ListIncursionDeploymentCards::route('/'),
            'create' => Pages\CreateIncursionDeploymentCard::route('/create'),
            'edit' => Pages\EditIncursionDeploymentCard::route('/{record}/edit'),
        ];
    }
}
