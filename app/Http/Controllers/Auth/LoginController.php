<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:100'],
            'password' => ['required', 'string', 'max:200'],
        ]);
        $credentials['email'] = mb_strtolower($credentials['email']);
        $credentials['is_admin'] = true;

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('admin.index'));
        }

        return back()
            ->withErrors(['email' => __('ui.messages.login_failed')])
            ->onlyInput('email');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $locale = $request->session()->get('locale');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (is_string($locale) && in_array($locale, config('app.supported_locales', ['id', 'en']), true)) {
            $request->session()->put('locale', $locale);
        }

        return redirect()->route('login');
    }
}
