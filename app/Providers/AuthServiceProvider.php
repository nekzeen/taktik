<?php

namespace App\Providers;

use App\Models\PlayerMatch;
use App\Policies\PlayerMatchPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        PlayerMatch::class => PlayerMatchPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
