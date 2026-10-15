@extends('layouts.app')

@section('title', __('ui.admin.title'))
@section('page', 'admin')
@section('topbar-label', __('ui.admin.topbar'))

@section('content')
<main class="page-wrap admin-wrap">
    <section class="admin-title-row">
        <div><p class="eyebrow">{{ __('ui.admin.eyebrow') }}</p><h1>{{ __('ui.common.admin_panel') }}<span class="heading-period">.</span></h1><p class="welcome-subtitle">{{ __('ui.admin.subtitle') }}</p></div>
        <a href="{{ route('home') }}" class="button button-dark admin-back">← {{ __('ui.admin.back') }}</a>
    </section>
    <div class="local-notice"><span class="notice-icon">i</span><span><strong>{{ __('ui.admin.access_active') }}</strong> {{ __('ui.admin.access_description') }}</span></div>
    <div id="legacy-import-notice" class="local-notice hidden" data-import-url="{{ route('admin.legacy-import') }}">
        <span class="notice-icon">↻</span>
        <span><strong>{{ __('ui.admin.legacy_found') }}</strong> {{ __('ui.admin.legacy_description') }}</span>
        <button id="legacy-import-button" class="button button-outline" type="button">{{ __('ui.admin.legacy_import') }}</button>
    </div>

    <nav class="admin-tabs" aria-label="{{ __('ui.admin.menu') }}">
        <a class="admin-tab {{ $tab === 'users' ? 'active' : '' }}" href="{{ route('admin.index', ['tab' => 'users']) }}"><span>◎</span> {{ __('ui.admin.users_tab') }} <b>{{ $userCount }}</b></a>
        <a class="admin-tab {{ $tab === 'history' ? 'active' : '' }}" href="{{ route('admin.index', ['tab' => 'history']) }}"><span>◷</span> {{ __('ui.admin.history_tab') }}</a>
        <a class="admin-tab {{ $tab === 'recap' ? 'active' : '' }}" href="{{ route('admin.index', ['tab' => 'recap']) }}"><span>▤</span> {{ __('ui.admin.recap_tab') }}</a>
    </nav>

    @if ($tab === 'users')
        <section id="admin-users" class="admin-section">
            <div class="section-heading">
                <div><p class="eyebrow">{{ __('ui.admin.directory') }}</p><h2>{{ __('ui.admin.users_title') }}</h2><p>{{ __('ui.admin.users_description') }}</p></div>
                <a id="add-user-button" class="button button-primary" href="{{ route('admin.index', ['tab' => 'users', 'create' => 1]) }}"><span>＋</span> {{ __('ui.admin.add_user') }}</a>
            </div>
            <form class="admin-toolbar" method="GET" action="{{ route('admin.index') }}">
                <input type="hidden" name="tab" value="users">
                <div class="directory-search admin-search"><span>⌕</span><input id="admin-user-search" type="search" name="q" value="{{ $search }}" placeholder="{{ __('ui.admin.search_users') }}" aria-label="{{ __('ui.admin.search_user_label') }}"><button class="sr-only" type="submit">{{ __('ui.admin.search_user_label') }}</button></div>
                <span id="admin-user-total" class="toolbar-meta">{{ $users->total() }} {{ __('ui.common.users') }}</span>
            </form>
            <div class="panel admin-table-panel">
                <div class="table-scroll">
                    <table class="data-table admin-users-table">
                        <thead><tr><th>{{ __('ui.home.table_user') }}</th><th>{{ __('ui.admin.id_qr') }}</th><th>{{ __('ui.admin.department') }}</th><th>{{ __('ui.home.table_status') }}</th><th>{{ __('ui.admin.registered') }}</th><th class="align-right">{{ __('ui.admin.actions') }}</th></tr></thead>
                        <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td><div class="user-cell"><span class="avatar">{{ collect(preg_split('/\s+/', trim($user->name)))->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</span><span><strong>{{ $user->name }}</strong><small>{{ $user->email ?: ($user->phone ?: __('ui.admin.contact_missing')) }}</small></span></div></td>
                                <td><span class="barcode-mini"><x-qr-code :value="$user->barcode_id" :size="48"/><span class="code-text">{{ $user->barcode_id }}</span></span></td>
                                <td>{{ $user->department ?: __('ui.admin.department_missing') }}</td>
                                <td><span class="status-pill {{ $user->is_active ? 'status-green' : 'status-neutral' }}">{{ $user->is_active ? __('ui.status.active') : __('ui.status.inactive') }}</span></td>
                                <td>{{ $user->created_at->timezone(config('app.timezone'))->locale(app()->currentLocale())->translatedFormat('d M Y') }}</td>
                                <td>
                                    <div class="row-actions">
                                        <a class="icon-button" title="{{ __('ui.admin.print_qr') }}" aria-label="{{ __('ui.admin.print_qr') }} {{ $user->name }}" href="{{ route('admin.users.barcode', $user) }}" target="_blank" rel="noopener">▤</a>
                                        <a class="icon-button" title="{{ __('ui.admin.edit_user') }}" aria-label="{{ __('ui.admin.edit_user') }}: {{ $user->name }}" href="{{ route('admin.index', ['tab' => 'users', 'edit' => $user->id]) }}">✎</a>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="{{ __('ui.admin.confirm_delete', ['name' => $user->name]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="icon-button delete" type="submit" title="{{ __('ui.admin.delete_user') }}" aria-label="{{ __('ui.admin.delete_user') }}: {{ $user->name }}">×</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="table-empty">{{ __('ui.admin.no_users_yet') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($users->hasPages())
                    <div class="admin-pagination">
                        @if ($users->previousPageUrl())<a class="button button-quiet" href="{{ $users->previousPageUrl() }}">← {{ __('ui.admin.previous') }}</a>@endif
                        <span>{{ __('ui.admin.page_of', ['current' => $users->currentPage(), 'last' => $users->lastPage()]) }}</span>
                        @if ($users->nextPageUrl())<a class="button button-quiet" href="{{ $users->nextPageUrl() }}">{{ __('ui.admin.next') }} →</a>@endif
                    </div>
                @endif
            </div>
        </section>

        @php($showUserModal = $createModal || $editing !== null || $errors->hasAny(['name', 'department', 'phone', 'email']))
        <div id="user-modal" class="modal-backdrop {{ $showUserModal ? '' : 'hidden' }}" role="dialog" aria-modal="true" aria-labelledby="user-modal-title">
            <div class="modal user-form-modal">
                <a class="modal-close" href="{{ route('admin.index', ['tab' => 'users']) }}" aria-label="{{ __('ui.common.close') }}">×</a>
                <p class="eyebrow">{{ __('ui.admin.profile') }}</p>
                <h2 id="user-modal-title">{{ $editing ? __('ui.admin.edit_profile') : __('ui.admin.create_profile') }}</h2>
                <p class="modal-subtitle">{{ __('ui.admin.profile_description') }}</p>
                <form id="user-form" method="POST" action="{{ $editing ? route('admin.users.update', $editing) : route('admin.users.store') }}">
                    @csrf
                    @if ($editing) @method('PUT') @endif
                    <div class="form-grid">
                        <label class="form-field"><span>{{ __('ui.admin.full_name') }} <i>*</i></span><input id="user-name" name="name" required maxlength="80" value="{{ old('name', $editing?->name) }}" placeholder="{{ __('ui.admin.name_placeholder') }}">@error('name')<small class="field-error">{{ $message }}</small>@enderror</label>
                        <label class="form-field"><span>{{ __('ui.admin.id_qr') }}</span><input id="user-id" name="barcode_id" maxlength="24" value="{{ $editing?->barcode_id ?? $newBarcodeId }}" readonly aria-describedby="user-id-hint"><small id="user-id-hint">{{ __('ui.admin.id_hint') }}</small></label>
                        <label class="form-field"><span>{{ __('ui.admin.department') }}</span><input id="user-department" name="department" maxlength="60" value="{{ old('department', $editing?->department) }}" placeholder="{{ __('ui.admin.department_placeholder') }}">@error('department')<small class="field-error">{{ $message }}</small>@enderror</label>
                        <label class="form-field"><span>{{ __('ui.admin.phone') }}</span><input id="user-phone" name="phone" type="tel" maxlength="30" value="{{ old('phone', $editing?->phone) }}" placeholder="{{ __('ui.admin.phone_placeholder') }}">@error('phone')<small class="field-error">{{ $message }}</small>@enderror</label>
                        <label class="form-field form-field-wide"><span>Email</span><input id="user-email" name="email" type="email" maxlength="100" value="{{ old('email', $editing?->email) }}" placeholder="{{ __('ui.admin.email_placeholder') }}">@error('email')<small class="field-error">{{ $message }}</small>@enderror</label>
                        <label class="form-field form-field-wide checkbox-field"><input type="hidden" name="is_active" value="0"><input id="user-active" name="is_active" type="checkbox" value="1" @checked((bool) old('is_active', $editing?->is_active ?? true))><span>{{ __('ui.admin.active_user') }}</span></label>
                    </div>
                    @if ($errors->hasAny(['name', 'department', 'phone', 'email']))
                        <div id="user-form-error" class="form-error">{{ $errors->first() }}</div>
                    @endif
                    <div class="modal-actions"><a class="button button-quiet" href="{{ route('admin.index', ['tab' => 'users']) }}">{{ __('ui.common.cancel') }}</a><button class="button button-primary" type="submit">{{ __('ui.common.save_profile') }}</button></div>
                </form>
            </div>
        </div>
    @elseif ($tab === 'history')
        <section id="admin-history" class="admin-section">
            <div class="section-heading">
                <div><p class="eyebrow">{{ __('ui.admin.history_eyebrow') }}</p><h2>{{ __('ui.admin.history_title') }}</h2><p>{{ __('ui.admin.history_description') }}</p></div>
                <a id="export-history" class="button button-outline" href="{{ route('admin.history.export', request()->query()) }}">↓ {{ __('ui.admin.download_csv') }}</a>
            </div>
            <form class="admin-toolbar history-toolbar" method="GET" action="{{ route('admin.index') }}">
                <input type="hidden" name="tab" value="history">
                <div class="directory-search admin-search"><span>⌕</span><input id="history-search" type="search" name="q" value="{{ $search }}" placeholder="{{ __('ui.admin.search_history') }}" aria-label="{{ __('ui.admin.search_history_label') }}"><button class="sr-only" type="submit">{{ __('ui.admin.search_history_label') }}</button></div>
                <input id="history-date-filter" class="date-input" type="date" name="date" value="{{ $dateFilter }}" aria-label="{{ __('ui.admin.filter_date') }}">
                <select id="history-type-filter" class="select-input" name="type" aria-label="{{ __('ui.admin.filter_type') }}">
                    <option value="">{{ __('ui.admin.all_types') }}</option>
                    <option value="Masuk" @selected($typeFilter === 'Masuk')>{{ __('ui.status.check_in') }}</option>
                    <option value="Pulang" @selected($typeFilter === 'Pulang')>{{ __('ui.status.check_out') }}</option>
                </select>
                <button class="button button-quiet" type="submit">{{ __('ui.admin.apply_filter') }}</button>
                <span id="history-total" class="toolbar-meta">{{ $attendances->total() }} {{ __('ui.admin.records') }}</span>
            </form>
            <div class="panel admin-table-panel">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead><tr><th>{{ __('ui.admin.date') }}</th><th>{{ __('ui.home.table_user') }}</th><th>{{ __('ui.home.table_id') }}</th><th>{{ __('ui.admin.time') }}</th><th>{{ __('ui.admin.type') }}</th><th>{{ __('ui.admin.source') }}</th></tr></thead>
                        <tbody>
                        @forelse ($attendances as $attendance)
                            <tr><td>{{ $attendance->attendance_date->locale(app()->currentLocale())->translatedFormat('d M Y') }}</td><td><div class="user-cell"><span class="avatar">{{ collect(preg_split('/\s+/', trim($attendance->user_name)))->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</span><span><strong>{{ $attendance->user_name }}</strong></span></div></td><td><span class="code-text">{{ $attendance->barcode_id }}</span></td><td>{{ $attendance->checked_at->format('H:i') }} WITA</td><td><span class="status-pill {{ $attendance->type === 'Masuk' ? 'status-green' : 'status-blue' }}">{{ __('ui.status.'.($attendance->type === 'Masuk' ? 'check_in' : 'check_out')) }}</span></td><td>{{ match ($attendance->source) { 'Kamera' => __('ui.status.camera'), 'Halaman absensi' => __('ui.status.attendance_page'), 'QR / input', 'Barcode / input' => __('ui.status.qr_input'), 'Scanner' => __('ui.status.scanner'), 'Impor lama' => __('ui.status.legacy_import'), default => $attendance->source } }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="table-empty">{{ __('ui.admin.no_matching_records') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($attendances->hasPages())
                    <div class="admin-pagination">
                        @if ($attendances->previousPageUrl())<a class="button button-quiet" href="{{ $attendances->previousPageUrl() }}">← {{ __('ui.admin.previous') }}</a>@endif
                        <span>{{ __('ui.admin.page_of', ['current' => $attendances->currentPage(), 'last' => $attendances->lastPage()]) }}</span>
                        @if ($attendances->nextPageUrl())<a class="button button-quiet" href="{{ $attendances->nextPageUrl() }}">{{ __('ui.admin.next') }} →</a>@endif
                    </div>
                @endif
            </div>
        </section>
    @else
        <section id="admin-recap" class="admin-section">
            <div class="section-heading">
                <div><p class="eyebrow">{{ __('ui.admin.recap_eyebrow') }}</p><h2>{{ __('ui.admin.recap_title') }}</h2><p>{{ __('ui.admin.recap_description') }}</p></div>
                <form class="recap-actions" method="GET" action="{{ route('admin.index') }}">
                    <input type="hidden" name="tab" value="recap">
                    <label class="sr-only" for="recap-month">{{ __('ui.admin.choose_month') }}</label>
                    <input id="recap-month" class="date-input month-input" type="month" name="month" max="{{ now()->format('Y-m') }}" value="{{ $month }}" required>
                    <button class="button button-outline" type="submit">{{ __('ui.admin.show') }}</button>
                    <a id="export-recap" class="button button-primary" href="{{ route('admin.recap.export', ['month' => $month]) }}">↓ {{ __('ui.admin.download_recap') }}</a>
                </form>
            </div>
            <div class="recap-stats">
                <article class="recap-stat"><span class="stat-label">{{ __('ui.admin.present_users') }}</span><strong id="recap-present-users">{{ $summary['present_users'] }}</strong><span>{{ __('ui.admin.people_this_month') }}</span></article>
                <article class="recap-stat"><span class="stat-label">{{ __('ui.admin.attendance_days') }}</span><strong id="recap-attendance-days">{{ $summary['attendance_days'] }}</strong><span>{{ __('ui.admin.person_days') }}</span></article>
                <article class="recap-stat"><span class="stat-label">{{ __('ui.admin.attendance_records') }}</span><strong id="recap-checkins">{{ $summary['total_checkins'] }}</strong><span>{{ __('ui.admin.check_in_and_out') }}</span></article>
                <article class="recap-stat"><span class="stat-label">{{ __('ui.admin.average_check_in') }}</span><strong id="recap-average-time">{{ $summary['average_minutes'] === null ? '—' : sprintf('%02d:%02d', intdiv($summary['average_minutes'], 60), $summary['average_minutes'] % 60) }}</strong><span>{{ __('ui.admin.from_check_in_records') }}</span></article>
            </div>
            <div class="panel recap-panel">
                <div class="panel-heading panel-heading-tight"><div><p class="eyebrow">{{ __('ui.admin.per_user') }}</p><h3 id="recap-title">{{ __('ui.admin.summary') }} · {{ $summary['label'] }}</h3></div><span id="recap-user-count" class="count-badge">{{ count($summary['people']) }}</span></div>
                <div class="table-scroll">
                    <table class="data-table">
                        <thead><tr><th>{{ __('ui.home.table_user') }}</th><th>{{ __('ui.home.table_id') }}</th><th>{{ __('ui.admin.days_present') }}</th><th>{{ __('ui.status.check_in') }}</th><th>{{ __('ui.status.check_out') }}</th><th>{{ __('ui.admin.average_check_in') }}</th><th>{{ __('ui.admin.attendance') }}</th></tr></thead>
                        <tbody>
                        @forelse ($summary['people'] as $row)
                            <tr><td><div class="user-cell"><span class="avatar">{{ collect(preg_split('/\s+/', trim($row['user']->name)))->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</span><span><strong>{{ $row['user']->name }}</strong><small>{{ $row['user']->department ?: __('ui.common.user') }}{{ $row['user']->is_active ? '' : ' · '.__('ui.status.inactive') }}</small></span></div></td><td><span class="code-text">{{ $row['user']->barcode_id }}</span></td><td>{{ __('ui.admin.day_count', ['count' => $row['days']]) }}</td><td>{{ $row['ins'] }}</td><td>{{ $row['outs'] }}</td><td>{{ $row['average_minutes'] === null ? '—' : sprintf('%02d:%02d', intdiv($row['average_minutes'], 60), $row['average_minutes'] % 60) }}</td><td><div class="attendance-meter"><span class="meter-track"><i class="meter-fill" style="width: {{ $row['percentage'] }}%"></i></span><span class="meter-number">{{ $row['percentage'] }}%</span></div></td></tr>
                        @empty
                            <tr><td colspan="7" class="table-empty">{{ __('ui.admin.no_users') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($summary['total_checkins'] === 0)
                    <div id="recap-empty" class="empty-state"><span class="empty-icon">▤</span><strong>{{ __('ui.admin.no_data_month') }}</strong><span>{{ __('ui.admin.data_after_attendance') }}</span></div>
                @endif
            </div>
            <p class="recap-footnote">{{ __('ui.admin.recap_note') }}</p>
        </section>
    @endif

    <footer class="page-footer"><span>© {{ now()->year }} {{ __('ui.common.app_name') }}</span><span>{{ __('ui.common.footer_database') }}</span><a href="{{ route('home') }}">{{ __('ui.common.footer_home') }} ↗</a></footer>
</main>
@endsection
