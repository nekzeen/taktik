<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\PlayerMatchRequest;

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
    }
}
