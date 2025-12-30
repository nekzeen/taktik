<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\PlayerMatchRequest;
use App\Models\BsdataDetachment;
use App\Models\WarhammerGlossary;
use App\Models\Translation;
use App\Models\TournamentMatch;
use App\Observers\BsdataDetachmentObserver;
use App\Observers\WarhammerGlossaryObserver;
use App\Observers\TranslationObserver;
use App\Observers\TournamentMatchObserver;

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
        RateLimiter::for('security-login', function (Request $request) {
            $perMinute = (int) config('security.rate_limits.login.per_minute', 10);
            $email = (string) $request->input('email', '');

            return Limit::perMinute($perMinute)->by($request->ip().'|'.$email);
        });

        RateLimiter::for('security-auth', function (Request $request) {
            $perMinute = (int) config('security.rate_limits.auth.per_minute', 30);

            return Limit::perMinute($perMinute)->by($request->ip());
        });

        RateLimiter::for('security-spectator', function (Request $request) {
            $perMinute = (int) config('security.rate_limits.spectator_polling.per_minute', 60);

            return Limit::perMinute($perMinute)->by($request->ip());
        });

        RateLimiter::for('security-match-api', function (Request $request) {
            $perMinute = (int) config('security.rate_limits.match_api.per_minute', 120);

            return Limit::perMinute($perMinute)->by($request->ip());
        });

        \Illuminate\Support\Facades\Route::model('playerMatchRequest', PlayerMatchRequest::class);
        
        // Enregistrer l'Observer pour les détachements
        BsdataDetachment::observe(BsdataDetachmentObserver::class);
        
        // Enregistrer l'Observer pour le glossaire Warhammer
        WarhammerGlossary::observe(WarhammerGlossaryObserver::class);
        
        // Enregistrer l'Observer pour les traductions
        Translation::observe(TranslationObserver::class);
        
        // Enregistrer l'Observer pour les matchs de tournoi
        TournamentMatch::observe(TournamentMatchObserver::class);
    }
}
