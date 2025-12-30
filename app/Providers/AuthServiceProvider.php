<?php

namespace App\Providers;

use App\Models\ArmyList;
use App\Models\GameMatch;
use App\Models\Page;
use App\Models\PlayerMatch;
use App\Models\Tournament;
use App\Policies\ArmyListPolicy;
use App\Policies\GameMatchPolicy;
use App\Policies\PagePolicy;
use App\Policies\PlayerMatchPolicy;
use App\Policies\TournamentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        ArmyList::class => ArmyListPolicy::class,
        GameMatch::class => GameMatchPolicy::class,
        Page::class => PagePolicy::class,
        PlayerMatch::class => PlayerMatchPolicy::class,
        Tournament::class => TournamentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
