<?php

namespace App\Providers;

use App\Models\AnimeName;
use App\Models\AnimeVideo;
use App\Models\User;
use App\Observers\AnimeNameObserver;
use App\Observers\AnimeVideoObserver;
use App\Observers\RegisterObserver;
use Illuminate\Support\ServiceProvider;

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
        User::observe(RegisterObserver::class);
        AnimeName::observe(AnimeNameObserver::class);
        AnimeVideo::observe(AnimeVideoObserver::class);
    }
}
