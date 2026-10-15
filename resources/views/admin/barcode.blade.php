<!doctype html>
<html lang="{{ app()->currentLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('ui.print.title') }} {{ $canteenUser->barcode_id }}</title>
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/site.css?v=blue-lang-1') }}">
    <script defer src="{{ asset('assets/app.js?v=blue-lang-1') }}"></script>
</head>
<body data-page="print-barcode" data-auto-print="true">
    <main class="barcode-print-page">
        <form method="POST" action="{{ route('language.update') }}" class="language-switch language-switch-print">
            @csrf
            <input type="hidden" name="locale" value="{{ app()->currentLocale() === 'id' ? 'en' : 'id' }}">
            <button class="language-switch-button" type="submit" aria-label="{{ app()->currentLocale() === 'id' ? __('ui.common.switch_to_english') : __('ui.common.switch_to_indonesian') }}" title="{{ app()->currentLocale() === 'id' ? __('ui.common.switch_to_english') : __('ui.common.switch_to_indonesian') }}">
                <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18m0-18a14 14 0 0 0 0 18"/></svg><span>{{ app()->currentLocale() === 'id' ? 'EN' : 'ID' }}</span>
            </button>
        </form>
        <article class="barcode-print-card">
            <h1>{{ $canteenUser->name }}</h1>
            <p>{{ $canteenUser->department ?: __('ui.print.user') }}</p>
            <div class="barcode"><x-qr-code :value="$canteenUser->barcode_id" :size="240"/></div>
            <div class="barcode-label">{{ $canteenUser->barcode_id }}</div>
        </article>
        <button class="button button-outline print-action" type="button" data-print-now>{{ __('ui.print.print_button') }}</button>
    </main>
</body>
</html>
