<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        RateLimiter::for('admin-login', function (Request $request): Limit {
            $email = $request->input('email');
            $identity = is_string($email) ? Str::lower($email) : '';

            return Limit::perMinute(5)->by(hash('sha256', $identity.'|'.$request->ip()));
        });

        RateLimiter::for('attendance', function (Request $request): Limit {
            return Limit::perMinute(12)->by($request->ip());
        });
    }
}
