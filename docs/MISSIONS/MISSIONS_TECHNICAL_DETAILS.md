# 🔧 Détails Techniques : Missions, Péripéties et Cartes

## Architecture de la base de données

### Diagramme des tables

```
┌─────────────────────────┐
│   secondary_missions    │
├─────────────────────────┤
│ id (PK)                 │
│ name (VARCHAR 255)      │
│ slug (VARCHAR 255)      │
│ full_text (LONGTEXT)    │
│ can_be_fixed (TINYINT)  │
│ edition (VARCHAR 255)   │
│ source (VARCHAR 255)    │
│ is_active (TINYINT)     │
│ created_at (TIMESTAMP)  │
│ updated_at (TIMESTAMP)  │
└─────────────────────────┘
         │
         │ (1:N)
         │
┌─────────────────────────┐
│    translations         │
├─────────────────────────┤
│ id (PK)                 │
│ resource_type (VARCHAR) │
│ resource_id (BIGINT)    │
│ field (VARCHAR)         │
│ locale (VARCHAR)        │
│ source_text (LONGTEXT)  │
│ translated_text (TEXT)  │
│ status (VARCHAR)        │
│ created_at (TIMESTAMP)  │
│ updated_at (TIMESTAMP)  │
│ UNIQUE(resource_type,   │
│   resource_id, field,   │
│   locale)               │
└─────────────────────────┘
```

### Schéma SQL complet

```sql
-- Missions Secondaires
CREATE TABLE secondary_missions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    full_text LONGTEXT NOT NULL,
    can_be_fixed TINYINT(1) DEFAULT 0,
    edition VARCHAR(255) NULL,
    source VARCHAR(255) NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_name (name),
    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active)
);

-- Missions Primaires
CREATE TABLE primary_missions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    full_text LONGTEXT NOT NULL,
    edition VARCHAR(255) NULL,
    source VARCHAR(255) NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_name (name),
    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active)
);

-- Missions Asymétriques
CREATE TABLE asymmetric_primary_missions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    full_text LONGTEXT NOT NULL,
    edition VARCHAR(255) NULL,
    source VARCHAR(255) NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_name (name),
    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active)
);

-- Péripéties
CREATE TABLE twist_missions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    full_text LONGTEXT NOT NULL,
    edition VARCHAR(255) NULL,
    source VARCHAR(255) NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_name (name),
    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active)
);

-- Traductions (Polymorphe)
CREATE TABLE translations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    resource_type VARCHAR(255) NOT NULL,
    resource_id BIGINT UNSIGNED NOT NULL,
    field VARCHAR(255) NOT NULL,
    locale VARCHAR(10) NOT NULL DEFAULT 'fr',
    source_text LONGTEXT NOT NULL,
    translated_text LONGTEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY unique_translation (resource_type, resource_id, field, locale),
    INDEX idx_resource (resource_type, resource_id),
    INDEX idx_locale (locale),
    INDEX idx_field (field),
    INDEX idx_status (status)
);
```

---

## Flux de traitement des imports

### 1. Réception du formulaire

```php
// File: ImportMissions.php (Page)
public function import(): void
{
    $data = $this->form->getState();
    $text = $data['missions_text'] ?? '';
    $replaceExisting = $data['replace_existing'] ?? false;
    
    // Validation
    if (empty(trim($text))) {
        Notification::make()->danger()->send();
        return;
    }
    
    // Parsing
    $missions = $this->parseMissions($text);
    
    // Import
    foreach ($missions as $mission) {
        $model = Model::updateOrCreate([...]);
        $this->triggerAutoTranslations($model);
    }
}
```

### 2. Parsing du texte

```php
protected function parseMissions($text)
{
    $missions = [];
    
    // Diviser par --- (avec ou sans espaces)
    // Regex: /\s*\n\s*-{3,}\s*\n\s*/
    $blocks = preg_split('/\s*\n\s*-{3,}\s*\n\s*/', $text);
    
    foreach ($blocks as $block) {
        $block = trim($block);
        if (empty($block)) continue;
        
        $mission = $this->parseMission($block);
        if ($mission) {
            $missions[] = $mission;
        }
    }
    
    return $missions;
}

protected function parseMission($block)
{
    $lines = explode("\n", $block);
    $name = null;
    
    // Méthode 1: Format **Nom**
    if (preg_match('/\*\*([^*]+)\*\*/', $block, $matches)) {
        $name = trim($matches[1]);
    } else {
        // Méthode 2: Première ligne non-vide
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $name = $line;
                break;
            }
        }
    }
    
    if (empty($name)) {
        return null;
    }
    
    return [
        'name' => $name,
        'full_text' => $block,
    ];
}
```

### 3. Création/Mise à jour

```php
$mission = SecondaryMission::updateOrCreate(
    ['name' => $mission['name']],  // Clé de recherche
    [
        'full_text' => $mission['full_text'],
        'slug' => Str::slug($mission['name']),
        'edition' => '10ed',
        'source' => 'chapter-approved-2025-26',
        'is_active' => true,
    ]
);
```

### 4. Traductions automatiques

```php
protected function triggerAutoTranslations(SecondaryMission $mission): void
{
    $translationService = new TranslationService();
    
    // Champs à traduire
    $fields = ['name', 'full_text'];
    
    foreach ($fields as $field) {
        try {
            $sourceText = $mission->{$field};
            
            if (empty($sourceText)) {
                continue;
            }
            
            // Vérifier si existe
            $existing = Translation::where('resource_type', 'SecondaryMission')
                ->where('resource_id', $mission->id)
                ->where('field', $field)
                ->where('locale', 'fr')
                ->first();
            
            if ($existing) {
                continue;
            }
            
            // Traduire via DeepL
            $translatedText = $translationService->translate(
                $sourceText,
                'en',  // Source
                'fr'   // Target
            );
            
            // Créer la traduction
            Translation::create([
                'source_text' => $sourceText,
                'translated_text' => $translatedText,
                'locale' => 'fr',
                'resource_type' => 'SecondaryMission',
                'resource_id' => $mission->id,
                'field' => $field,
                'status' => 'auto',
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Traduction échouée: {$e->getMessage()}");
        }
    }
}
```

---

## Service de traduction

### TranslationService

```php
namespace App\Services;

class TranslationService
{
    /**
     * Traduit un texte
     */
    public function translate(
        string $text,
        string $sourceLanguage,
        string $targetLanguage,
        string $translator = 'deepl'
    ): string {
        // Même langue
        if ($sourceLanguage === $targetLanguage) {
            return $text;
        }
        
        // Vérifier le cache
        $cacheKey = "translation:{$translator}:{$sourceLanguage}:{$targetLanguage}:" . md5($text);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        try {
            // Appeler le traducteur
            $translated = match($translator) {
                'deepl' => $this->translateWithDeepL($text, $sourceLanguage, $targetLanguage),
                'google' => $this->translateWithGoogle($text, $sourceLanguage, $targetLanguage),
                default => throw new Exception("Traducteur inconnu: $translator"),
            };
            
            // Mettre en cache (30 jours)
            Cache::put($cacheKey, $translated, now()->addDays(30));
            
            return $translated;
        } catch (Exception $e) {
            // En cas d'erreur, retourner le texte original
            \Log::error("Erreur de traduction: {$e->getMessage()}");
            return $text;
        }
    }
    
    /**
     * Traduit via DeepL
     */
    private function translateWithDeepL(
        string $text,
        string $sourceLanguage,
        string $targetLanguage
    ): string {
        $response = Http::withHeaders([
            'Authorization' => 'DeepL-Auth-Key ' . config('services.deepl.api_key'),
        ])->post('https://api-free.deepl.com/v1/document', [
            'text' => $text,
            'source_language' => strtoupper($sourceLanguage),
            'target_language' => strtoupper($targetLanguage),
        ]);
        
        if ($response->failed()) {
            throw new Exception("DeepL API error: {$response->body()}");
        }
        
        return $response->json('text');
    }
}
```

---

## RelationManager pour traductions

### TranslationsRelationManager

```php
namespace App\Filament\Resources\SecondaryMissionResource\RelationManagers;

class TranslationsRelationManager extends RelationManager
{
    protected static string $relationship = 'translations';
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Champs visibles
                Forms\Components\Select::make('field')
                    ->label('Champ')
                    ->options([
                        'name' => 'Nom',
                        'full_text' => 'Texte complet',
                    ])
                    ->required(),
                
                Forms\Components\TextInput::make('locale')
                    ->label('Langue')
                    ->default('fr')
                    ->required(),
                
                Forms\Components\Textarea::make('translated_text')
                    ->label('Texte traduit')
                    ->required(),
                
                Forms\Components\Select::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'auto' => 'Automatique',
                        'manual' => 'Modifiée manuellement',
                        'reviewed' => 'Révisée',
                        'approved' => 'Approuvée',
                    ]),
                
                // Champs cachés
                Forms\Components\Hidden::make('resource_type')
                    ->default('SecondaryMission'),
                
                Forms\Components\Hidden::make('source_text'),
            ]);
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('field')
                    ->label('Champ'),
                
                Tables\Columns\TextColumn::make('locale')
                    ->label('Langue'),
                
                Tables\Columns\TextColumn::make('translated_text')
                    ->label('Traduction')
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Ajouter une traduction')
                    ->using(function (array $data) {
                        return $this->getOwnerRecord()->translations()->updateOrCreate(
                            [
                                'resource_type' => $data['resource_type'],
                                'field' => $data['field'],
                                'locale' => $data['locale'],
                            ],
                            [
                                'translated_text' => $data['translated_text'],
                                'status' => $data['status'] ?? 'pending',
                                'source_text' => $data['source_text'] ?? '',
                            ]
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
```

---

## Observer (Désactivé)

### TranslationObserver

```php
namespace App\Observers;

class TranslationObserver
{
    /**
     * Quand une traduction est créée
     * 
     * ⚠️ GLOSSAIRE COMPLÈTEMENT DÉSACTIVÉ
     * Raison: Le glossaire n'est plus utilisé
     * Les traductions se font uniquement via DeepL
     */
    public function created(Translation $translation): void
    {
        // Glossaire désactivé - ne rien faire
        return;
    }
    
    /**
     * Quand une traduction est mise à jour
     * 
     * ⚠️ GLOSSAIRE COMPLÈTEMENT DÉSACTIVÉ
     */
    public function updated(Translation $translation): void
    {
        // Glossaire désactivé - ne rien faire
        return;
    }
}
```

---

## Filament Resource

### SecondaryMissionResource

```php
namespace App\Filament\Resources;

class SecondaryMissionResource extends Resource
{
    protected static ?string $model = SecondaryMission::class;
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Données de base')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom (EN)')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if ($state) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                    ])->columns(2),
                
                Forms\Components\Section::make('Texte complet')
                    ->schema([
                        Forms\Components\Textarea::make('full_text')
                            ->label('Texte (EN)')
                            ->required()
                            ->rows(6),
                    ]),
                
                Forms\Components\Section::make('Options')
                    ->schema([
                        Forms\Components\Toggle::make('can_be_fixed')
                            ->label('Peut être Mission Fixe'),
                    ]),
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
                    ->getStateUsing(function (SecondaryMission $record) {
                        return DB::table('translations')
                            ->where('resource_type', 'SecondaryMission')
                            ->where('resource_id', $record->id)
                            ->where('field', 'name')
                            ->where('locale', 'fr')
                            ->value('translated_text') ?? '—';
                    }),
                
                Tables\Columns\IconColumn::make('can_be_fixed')
                    ->label('Fixe')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
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
            'index' => Pages\ListSecondaryMissions::route('/'),
            'create' => Pages\CreateSecondaryMission::route('/create'),
            'edit' => Pages\EditSecondaryMission::route('/{record}/edit'),
            'import' => Pages\ImportMissions::route('/import'),
        ];
    }
}
```

---

## Migrations

### Exemple de migration

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter les colonnes manquantes
        Schema::table('secondary_missions', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique();
            $table->string('edition')->nullable();
            $table->string('source')->nullable();
        });
    }
    
    public function down(): void
    {
        Schema::table('secondary_missions', function (Blueprint $table) {
            $table->dropColumn(['slug', 'edition', 'source']);
        });
    }
};
```

---

## Performance et optimisation

### Indexes recommandés

```sql
-- Missions
CREATE INDEX idx_missions_name ON secondary_missions(name);
CREATE INDEX idx_missions_slug ON secondary_missions(slug);
CREATE INDEX idx_missions_is_active ON secondary_missions(is_active);
CREATE INDEX idx_missions_edition ON secondary_missions(edition);

-- Traductions
CREATE INDEX idx_translations_resource ON translations(resource_type, resource_id);
CREATE INDEX idx_translations_locale ON translations(locale);
CREATE INDEX idx_translations_field ON translations(field);
CREATE INDEX idx_translations_status ON translations(status);
```

### Requêtes optimisées

```php
// ❌ Mauvais - N+1 queries
$missions = SecondaryMission::all();
foreach ($missions as $mission) {
    $translation = $mission->translations()->first();
}

// ✅ Bon - Eager loading
$missions = SecondaryMission::with('translations')->get();

// ✅ Meilleur - Avec filtres
$missions = SecondaryMission::with([
    'translations' => function ($query) {
        $query->where('locale', 'fr')
              ->where('field', 'name');
    }
])->get();
```

---

## Caching

### Stratégies de cache

```php
// Cache les traductions (30 jours)
$cacheKey = "translation:{$translator}:{$sourceLang}:{$targetLang}:" . md5($text);
Cache::put($cacheKey, $translated, now()->addDays(30));

// Cache les missions (1 jour)
$missions = Cache::remember('missions.secondary', 86400, function () {
    return SecondaryMission::with('translations')->get();
});

// Invalider le cache
Cache::forget('missions.secondary');
Cache::flush();
```

---

## Logs et debugging

### Fichier de log

```
storage/logs/laravel.log
```

### Commandes de debug

```bash
# Voir les logs en temps réel
tail -f storage/logs/laravel.log

# Compter les missions
php artisan tinker
> App\Models\SecondaryMission::count()
> App\Models\Translation::count()

# Vérifier les traductions
> App\Models\Translation::where('resource_type', 'SecondaryMission')->count()

# Voir les erreurs
> App\Models\Translation::where('status', 'pending')->get()
```

---

**Dernière mise à jour** : 2025-11-05
**Version** : 1.0
