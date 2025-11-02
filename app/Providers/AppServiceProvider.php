<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\PlayerMatchRequest;
use App\Models\BsdataDetachment;
use App\Models\WarhammerGlossary;
use App\Models\Translation;
use App\Observers\BsdataDetachmentObserver;
use App\Observers\WarhammerGlossaryObserver;
use App\Observers\TranslationObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Route::model('playerMatchRequest', PlayerMatchRequest::class);
        
        // Enregistrer l'Observer pour les détachements
        BsdataDetachment::observe(BsdataDetachmentObserver::class);
        
        // Enregistrer l'Observer pour le glossaire Warhammer
        WarhammerGlossary::observe(WarhammerGlossaryObserver::class);
        
        // Enregistrer l'Observer pour les traductions
        Translation::observe(TranslationObserver::class);
    }
}
