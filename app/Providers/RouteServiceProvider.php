<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = 'en/clinic/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // HexaDash template demo pages (chat, ecommerce, customer CRUD, ...).
            // They stay registered because the template's top navigation links to them,
            // but nobody can open them: "template.demo" is not granted to any role.
            foreach (['dashboard', 'changelog', 'application', 'uielement', 'feature', 'pages', 'customer'] as $demoRoutes) {
                Route::middleware(['web', 'auth', 'permission:template.demo'])
                    ->group(base_path("routes/{$demoRoutes}.php"));
            }

            Route::middleware('web')
                ->group(base_path('routes/clinic.php'));
        });
    }
}
