<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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
        // Define rate limiting for API requests en general
       /* RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(5)->by(
                $request->user()?->id ?: $request->ip()
            );
        });*/

        // Define rate limiting for specific routes
        RateLimiter::for('per-route', function (Request $request) {
            return Limit::perMinute(5)->by(
                $request->user()?->id. '|' . $request->route()->uri()
            );
        });

        RateLimiter::for('without-auth', function (Request $request) {
            return Limit::perMinute(5)->by(
                $request->ip(). '|' . $request->route()->uri()
            );
        });

        RateLimiter::for('asset-upload', function (Request $request) {
            return Limit::perMinute(9)->by(
                $request->user()->id . '|' . $request->route()->uri()
            );
        });
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(1000)->by($request->ip());
        });
    }
}
