<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LanguageController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(config('app.supported_locales', ['id', 'en']))],
        ]);

        $request->session()->put('locale', $validated['locale']);

        $previousUrl = $request->session()->previousUrl();
        $path = $previousUrl ? parse_url($previousUrl, PHP_URL_PATH) : null;
        $query = $previousUrl ? parse_url($previousUrl, PHP_URL_QUERY) : null;

        if (! is_string($path) || ! str_starts_with($path, '/') || str_starts_with($path, '//') || str_contains($path, '\\')) {
            return redirect()->route('home');
        }

        return redirect()->to($path.($query ? '?'.$query : ''));
    }
}
