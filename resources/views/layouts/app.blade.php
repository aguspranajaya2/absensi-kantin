<!doctype html>
<html lang="{{ app()->currentLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fb">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('ui.common.app_name'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/site.css?v=blue-lang-1') }}">
    <script defer src="{{ asset('assets/app.js?v=blue-lang-1') }}"></script>
</head>
<body data-page="@yield('page', 'home')"
      data-camera-preparing="{{ __('ui.home.camera_preparing') }}"
      data-camera-unsupported="{{ __('ui.javascript.camera_unsupported') }}"
      data-camera-unavailable="{{ __('ui.javascript.camera_unavailable') }}"
      data-qr-unsupported="{{ __('ui.javascript.qr_unsupported') }}"
      data-camera-open-failed="{{ __('ui.javascript.camera_open_failed') }}"
      data-aim-qr="{{ __('ui.javascript.aim_qr') }}"
      data-importing="{{ __('ui.javascript.importing') }}"
      data-try-again="{{ __('ui.javascript.try_again') }}"
      data-legacy-import-failed="{{ __('ui.javascript.legacy_import_failed') }}"
      data-confirm-action="{{ __('ui.javascript.confirm_action') }}">
    <div class="app-shell">
        <header class="topbar">
            <a class="brand" href="{{ route('home') }}" aria-label="{{ __('ui.common.home_aria') }}">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 32 32"><path d="M8 5v9M12 5v9M16 5v9M12 14v13M23 5v22M23 5c4 4 4 9 0 12"/></svg>
                </span>
                <span><strong>kantin<span class="brand-dot">.</span></strong><small>{{ __('ui.common.portal') }}</small></span>
            </a>
            <nav class="main-nav" aria-label="{{ __('ui.common.main_navigation') }}">
                <a class="nav-link @if (request()->routeIs('home')) active @endif" href="{{ route('home') }}">{{ __('ui.common.attendance') }}</a>
                <a class="nav-link @if (request()->routeIs('admin.*')) active @endif" href="{{ route('admin.index') }}">{{ __('ui.common.admin') }}</a>
            </nav>
            <div class="topbar-right">
                <span class="live-dot"></span>
                <span class="topbar-label">@yield('topbar-label', __('ui.common.local_system'))</span>
                <span class="topbar-divider"></span>
                <span id="today-label" class="topbar-date"></span>
                @auth
                    <form method="POST" action="{{ route('logout') }}" class="topbar-logout">
                        @csrf
                        <button class="text-link" type="submit">{{ __('ui.common.logout') }}</button>
                    </form>
                @endauth
            </div>
            <form method="POST" action="{{ route('language.update') }}" class="language-switch">
                @csrf
                <input type="hidden" name="locale" value="{{ app()->currentLocale() === 'id' ? 'en' : 'id' }}">
                <button class="language-switch-button" type="submit" aria-label="{{ app()->currentLocale() === 'id' ? __('ui.common.switch_to_english') : __('ui.common.switch_to_indonesian') }}" title="{{ app()->currentLocale() === 'id' ? __('ui.common.switch_to_english') : __('ui.common.switch_to_indonesian') }}">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18m0-18a14 14 0 0 0 0 18"/></svg><span>{{ app()->currentLocale() === 'id' ? 'EN' : 'ID' }}</span>
                </button>
            </form>
            <button class="mobile-nav-toggle" aria-label="{{ __('ui.common.open_navigation') }}" aria-expanded="false" type="button">☰</button>
        </header>

        @yield('content')

        <div id="toast-region" class="toast-region" aria-live="polite" aria-atomic="true">
            @if (session('status'))
                <div class="toast success">{{ session('status') }}</div>
            @elseif (session('error'))
                <div class="toast error">{{ session('error') }}</div>
            @endif
        </div>
    </div>
</body>
</html>
