<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\DepartmentMiddleware;
use App\Http\Middleware\SecurityMiddleware;
use App\Http\Middleware\ApiRateLimitMiddleware;
use App\Http\Middleware\Authenticate;

class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register middleware aliases
        Route::aliasMiddleware('auth', Authenticate::class);
        Route::aliasMiddleware('role', RoleMiddleware::class);
        Route::aliasMiddleware('department', DepartmentMiddleware::class);
        Route::aliasMiddleware('security', SecurityMiddleware::class);
        Route::aliasMiddleware('api.throttle', ApiRateLimitMiddleware::class);
    }
}
