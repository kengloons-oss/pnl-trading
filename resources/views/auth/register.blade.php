@extends('layouts.app', ['title' => 'Create account'])

@section('content')
    <div class="auth-wrap">
        <div class="auth-intro"><div class="eyebrow"><span class="eyebrow-dot"></span> A BETTER TRADING HABIT</div><h1>Start with a<br><em>clear picture.</em></h1><p>Create your private workspace. Your journal is only visible to you.</p></div>
        <form class="panel auth-panel" method="POST" action="{{ route('register.store') }}">
            @csrf
            <div class="panel-heading"><span class="eyebrow">GET STARTED</span><h2>Create account</h2><p>It takes less than a minute.</p></div>
            <label class="field"><span>Your name</span><input type="text" name="name" value="{{ old('name') }}" autocomplete="name" required placeholder="How should we address you?"></label>
            <label class="field"><span>Email address</span><input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required placeholder="you@example.com"></label>
            <label class="field"><span>Reporting timezone</span><select name="timezone" required>@foreach (['Asia/Kuala_Lumpur' => 'Kuala Lumpur (UTC+8)', 'Asia/Singapore' => 'Singapore (UTC+8)', 'Asia/Tokyo' => 'Tokyo (UTC+9)', 'Europe/London' => 'London', 'America/New_York' => 'New York', 'UTC' => 'UTC'] as $value => $label)<option value="{{ $value }}" @selected(old('timezone', 'Asia/Kuala_Lumpur') === $value)>{{ $label }}</option>@endforeach</select></label>
            <label class="field"><span>Password <small>· 10 characters minimum</small></span><input type="password" name="password" autocomplete="new-password" required placeholder="Create a password"></label>
            <label class="field"><span>Confirm password</span><input type="password" name="password_confirmation" autocomplete="new-password" required placeholder="Enter it again"></label>
            <button class="button button-dark button-full" type="submit">Create my workspace <span>↗</span></button>
            <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
        </form>
    </div>
@endsection
