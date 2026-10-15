<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = config('app.supported_locales', ['id', 'en']);
        $defaultLocale = config('app.locale', 'id');

        if (! in_array($defaultLocale, $supportedLocales, true)) {
            $defaultLocale = 'id';
        }

        $locale = $request->session()->get('locale', $defaultLocale);

        if (! is_string($locale) || ! in_array($locale, $supportedLocales, true)) {
            $locale = $defaultLocale;
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
