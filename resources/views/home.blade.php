@extends('layouts.app')

@section('title', __('ui.home.title'))
@section('page', 'home')
@section('topbar-label', __('ui.home.topbar'))

@section('content')
@php($selectedStatusKey = match ($selectedStatus) { 'Belum hadir' => 'not_attended', 'Sudah masuk' => 'checked_in', 'Selesai' => 'complete', default => 'choose_user' })
<main class="page-wrap">
    <section class="welcome-row">
        <div>
            <p class="eyebrow">{{ __('ui.home.eyebrow') }}</p>
            <h1>{{ __('ui.home.greeting') }}<span class="heading-period">.</span></h1>
            <p class="welcome-subtitle">{{ __('ui.home.welcome') }}</p>
        </div>
        <div class="time-card">
            <span class="time-card-label">{{ __('ui.home.time_now') }}</span>
            <strong id="live-clock">--:--:--</strong>
            <span class="time-card-foot"><span class="live-dot"></span> {{ __('ui.home.time_zone') }}</span>
        </div>
    </section>

    <section class="stats-grid" aria-label="{{ __('ui.home.today_summary') }}">
        <article class="stat-card"><div class="stat-top"><span class="stat-icon icon-green">↗</span><span class="stat-label">{{ __('ui.home.present') }}</span></div><strong id="stat-present" class="stat-value">{{ $presentCount }}</strong><span class="stat-foot">{{ __('ui.home.people_today') }}</span></article>
        <article class="stat-card"><div class="stat-top"><span class="stat-icon icon-blue">◷</span><span class="stat-label">{{ __('ui.home.not_checked_in') }}</span></div><strong id="stat-away" class="stat-value">{{ $awayCount }}</strong><span class="stat-foot">{{ __('ui.home.people_not_recorded') }}</span></article>
        <article class="stat-card"><div class="stat-top"><span class="stat-icon icon-orange">◎</span><span class="stat-label">{{ __('ui.home.total_users') }}</span></div><strong id="stat-users" class="stat-value">{{ $totalUsers }}</strong><span class="stat-foot">{{ __('ui.home.registered_at_canteen') }}</span></article>
        <article class="stat-card stat-card-accent"><div class="stat-top"><span class="stat-icon icon-white">▦</span><span class="stat-label">{{ __('ui.home.last_attendance') }}</span></div><strong id="stat-last" class="stat-value stat-last-value">{{ $lastAttendance?->checked_at?->format('H:i') ?? '—' }}</strong><span id="stat-last-name" class="stat-foot">{{ $lastAttendance ? $lastAttendance->user_name.' · '.__('ui.status.'.($lastAttendance->type === 'Masuk' ? 'check_in' : 'check_out')) : __('ui.home.no_records_today') }}</span></article>
    </section>

    <section class="content-grid">
        <div class="main-column">
            <article class="panel attendance-panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">{{ __('ui.home.start_here') }}</p><h2>{{ __('ui.home.record_attendance') }}</h2></div>
                    <span class="secure-chip"><span class="live-dot"></span> {{ __('ui.home.ready') }}</span>
                </div>
                <div class="scan-box">
                    <div class="scan-visual" aria-hidden="true"><span class="scan-corner corner-tl"></span><span class="scan-corner corner-tr"></span><span class="scan-corner corner-bl"></span><span class="scan-corner corner-br"></span><svg class="qr-mark" viewBox="0 0 64 64"><rect x="5" y="5" width="20" height="20"/><rect class="qr-cutout" x="9" y="9" width="12" height="12"/><rect x="12" y="12" width="6" height="6"/><rect x="39" y="5" width="20" height="20"/><rect class="qr-cutout" x="43" y="9" width="12" height="12"/><rect x="46" y="12" width="6" height="6"/><rect x="5" y="39" width="20" height="20"/><rect class="qr-cutout" x="9" y="43" width="12" height="12"/><rect x="12" y="46" width="6" height="6"/><path d="M31 5h5v5h-5zM31 15h5v5h-5zM29 29h6v6h-6zM39 31h5v5h-5zM49 29h5v6h-5zM59 31h2v5h-2zM31 41h5v5h-5zM39 41h8v5h-8zM51 39h8v6h-8zM29 51h6v5h-6zM39 51h5v8h-5zM49 49h5v5h-5zM57 51h4v8h-4z"/></svg></div>
                    <div class="scan-copy">
                        <span class="mini-label">{{ __('ui.home.scanner_label') }}</span>
                        <h3>{{ __('ui.home.scan_question') }}</h3>
                        <p>{{ __('ui.home.scan_description') }}</p>
                        <button id="open-camera" class="button button-primary" type="button"><span class="button-icon">⌗</span> {{ __('ui.home.open_camera') }}</button>
                    </div>
                </div>
                <form id="scan-form" class="manual-scan-form" method="POST" action="{{ route('attendance.store') }}">
                    @csrf
                    <input type="hidden" name="source" id="scan-source" value="QR / input">
                    <label for="scan-input">{{ __('ui.home.user_id') }}</label>
                    <div class="input-with-action">
                        <span class="input-prefix">⌕</span>
                        <input id="scan-input" name="scan" autocomplete="off" maxlength="24" pattern="[A-Za-z0-9. $/+%-]+" placeholder="{{ __('ui.home.scan_placeholder') }}" aria-describedby="scan-hint" required>
                        <button class="button button-dark" type="submit">{{ __('ui.home.record_button') }} <span>→</span></button>
                    </div>
                    <span id="scan-hint" class="input-hint">{{ __('ui.home.scanner_hint') }}</span>
                    @error('scan')<span class="form-error">{{ $message }}</span>@enderror
                </form>
            </article>

            <article class="panel recent-panel">
                <div class="panel-heading panel-heading-tight">
                    <div><p class="eyebrow">{{ __('ui.home.recent_activity') }}</p><h2>{{ __('ui.home.today_records') }}</h2></div>
                    <a class="text-link" href="{{ route('admin.index', ['tab' => 'history']) }}">{{ __('ui.home.see_all') }} <span>→</span></a>
                </div>
                <div class="table-scroll">
                    <table class="data-table">
                        <thead><tr><th>{{ __('ui.home.table_user') }}</th><th>{{ __('ui.home.table_id') }}</th><th>{{ __('ui.home.table_time') }}</th><th>{{ __('ui.home.table_status') }}</th></tr></thead>
                        <tbody>
                        @forelse ($todayLogs as $log)
                            <tr>
                                <td><div class="user-cell"><span class="avatar">{{ collect(preg_split('/\s+/', trim($log->user_name)))->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</span><span><strong>{{ $log->user_name }}</strong><small>{{ $log->canteenUser?->department ?? __('ui.common.user') }}</small></span></div></td>
                                <td><span class="code-text">{{ $log->barcode_id }}</span></td>
                                <td>{{ $log->checked_at->format('H:i') }} WITA</td>
                                <td><span class="status-pill {{ $log->type === 'Masuk' ? 'status-green' : 'status-blue' }}">{{ __('ui.status.'.($log->type === 'Masuk' ? 'check_in' : 'check_out')) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="table-empty">{{ __('ui.home.no_attendance_yet') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </div>

        <aside class="side-column">
            <article class="panel profile-panel">
                <div class="panel-heading panel-heading-tight">
                    <div><p class="eyebrow">{{ __('ui.home.profile') }}</p><h2>{{ __('ui.home.attendance_card') }}</h2></div>
                    <span id="profile-status" class="status-pill {{ $selectedUser ? ($selectedStatus === 'Selesai' ? 'status-blue' : ($selectedStatus === 'Sudah masuk' ? 'status-green' : 'status-neutral')) : 'status-neutral' }}">{{ $selectedUser ? __('ui.status.'.$selectedStatusKey) : __('ui.status.choose_user') }}</span>
                </div>
                @if ($selectedUser)
                    <div class="profile-person"><span class="avatar">{{ collect(preg_split('/\s+/', trim($selectedUser->name)))->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</span><div><strong>{{ $selectedUser->name }}</strong><small>{{ $selectedUser->department ?: __('ui.common.unknown_department') }}</small></div></div>
                    <div class="profile-details">
                        <div><span class="detail-label">{{ __('ui.home.user_id') }}</span><span class="detail-value code-text">{{ $selectedUser->barcode_id }}</span></div>
                        <div><span class="detail-label">{{ __('ui.home.today_status') }}</span><span class="detail-value">{{ __('ui.status.'.$selectedStatusKey) }}</span></div>
                    </div>
                    <div class="barcode-wrap"><x-qr-code :value="$selectedUser->barcode_id" :size="160"/><small>{{ $selectedUser->barcode_id }}</small></div>
                    <div class="profile-actions">
                        @if ($selectedStatus !== 'Selesai')
                            <form method="POST" action="{{ route('attendance.store') }}">
                                @csrf
                                <input type="hidden" name="scan" value="{{ $selectedUser->barcode_id }}">
                                <input type="hidden" name="source" value="Halaman absensi">
                                <button class="button button-primary" type="submit">{{ $selectedStatus === 'Belum hadir' ? __('ui.home.check_in') : __('ui.home.check_out') }}</button>
                            </form>
                        @else
                            <button class="button button-primary" type="button" disabled>{{ __('ui.home.complete') }}</button>
                        @endif
                    </div>
                @else
                    <div id="profile-empty" class="profile-empty"><div class="profile-empty-icon">◎</div><strong>{{ __('ui.home.find_name') }}</strong><p>{{ __('ui.home.find_name_tip') }}</p></div>
                @endif
            </article>

            <article class="panel directory-panel">
                <div class="panel-heading panel-heading-tight"><div><p class="eyebrow">{{ __('ui.home.directory') }}</p><h2>{{ __('ui.home.canteen_users') }}</h2></div><span id="directory-count" class="count-badge">{{ $users->count() }}</span></div>
                <form class="directory-search" method="GET" action="{{ route('home') }}">
                    <span>⌕</span>
                    <input id="directory-search" type="search" name="q" value="{{ $search }}" placeholder="{{ __('ui.home.search_users') }}" aria-label="{{ __('ui.admin.search_user_label') }}">
                    @if ($selectedUser)<input type="hidden" name="user" value="{{ $selectedUser->getKey() }}">@endif
                    <button class="sr-only" type="submit">{{ __('ui.admin.search_user_label') }}</button>
                </form>
                <div id="directory-list" class="directory-list">
                    @forelse ($users as $user)
                        <a class="directory-item {{ $selectedUser?->is($user) ? 'selected' : '' }}" href="{{ route('home', array_filter(['q' => $search ?: null, 'user' => $user->getKey()])) }}">
                            <span class="avatar">{{ collect(preg_split('/\s+/', trim($user->name)))->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</span>
                            <span class="directory-item-copy"><strong>{{ $user->name }}</strong><small>{{ $user->barcode_id }} · {{ $user->department ?: __('ui.common.unknown_department') }}</small></span>
                            <span class="directory-arrow">›</span>
                        </a>
                    @empty
                        <div class="directory-empty">{{ __('ui.home.no_active_users') }}</div>
                    @endforelse
                </div>
                <a href="{{ route('admin.index', ['tab' => 'users']) }}" class="directory-admin-link">{{ __('ui.home.manage_users') }} <span>↗</span></a>
            </article>
        </aside>
    </section>
    <footer class="page-footer"><span>© {{ now()->year }} {{ __('ui.common.app_name') }}</span><span>{{ __('ui.common.footer_database') }}</span><a href="{{ route('admin.index') }}">{{ __('ui.home.footer_home') }} ↗</a></footer>
</main>

<div id="camera-modal" class="modal-backdrop hidden" role="dialog" aria-modal="true" aria-labelledby="camera-title">
    <div class="modal camera-modal">
        <button class="modal-close" data-close-modal aria-label="{{ __('ui.common.close') }}" type="button">×</button>
        <p class="eyebrow">{{ __('ui.home.scanner_label') }}</p>
        <h2 id="camera-title">{{ __('ui.home.camera_title') }}</h2>
        <p class="modal-subtitle">{{ __('ui.home.camera_prompt') }}</p>
        <div class="camera-frame"><video id="camera-video" playsinline muted></video><div class="camera-reticle"><i></i></div><div id="camera-placeholder" class="camera-placeholder">⌗</div></div>
        <p id="camera-message" class="camera-message" aria-live="polite">{{ __('ui.home.camera_preparing') }}</p>
        <button class="button button-quiet" data-close-modal type="button">{{ __('ui.home.camera_close') }}</button>
    </div>
</div>
@endsection
