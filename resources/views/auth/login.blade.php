@extends('layouts.app')

@section('title', __('ui.login.title'))
@section('page', 'login')
@section('topbar-label', __('ui.login.topbar'))

@section('content')
<main class="page-wrap admin-wrap login-wrap">
    <section class="panel login-panel">
        <p class="eyebrow">{{ __('ui.login.restricted') }}</p>
        <h1>{{ __('ui.common.admin_sign_in') }}<span class="heading-period">.</span></h1>
        <p class="welcome-subtitle">{{ __('ui.login.subtitle') }}</p>
        <form method="POST" action="{{ route('login.store') }}" class="login-form">
            @csrf
            <label class="form-field"><span>{{ __('ui.login.email') }} <i>*</i></span><input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required maxlength="100">@error('email')<small class="field-error">{{ $message }}</small>@enderror</label>
            <label class="form-field"><span>{{ __('ui.login.password') }} <i>*</i></span><input type="password" name="password" autocomplete="current-password" required maxlength="200">@error('password')<small class="field-error">{{ $message }}</small>@enderror</label>
            <button class="button button-primary" type="submit">{{ __('ui.login.submit') }}</button>
        </form>
        <a href="{{ route('home') }}" class="text-link">← {{ __('ui.common.back_to_attendance') }}</a>
    </section>
</main>
@endsection
