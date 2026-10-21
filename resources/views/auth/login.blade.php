@extends('layouts.app', ['title' => 'Sign in'])

@section('content')
    <div class="auth-wrap">
        <div class="auth-intro"><div class="eyebrow"><span class="eyebrow-dot"></span> WELCOME BACK</div><h1>Your next insight<br>starts <em>here.</em></h1><p>Sign in to pick up where your trading journal left off.</p></div>
        <form class="panel auth-panel" method="POST" action="{{ route('login.store') }}">
            @csrf
            <div class="panel-heading"><span class="eyebrow">ACCOUNT ACCESS</span><h2>Sign in</h2><p>Enter your account details below.</p></div>
            <label class="field"><span>Email address</span><input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus placeholder="you@example.com"></label>
            <label class="field"><span>Password</span><input type="password" name="password" autocomplete="current-password" required placeholder="Enter your password"></label>
            <label class="check-line"><input type="checkbox" name="remember" value="1"><span>Keep me signed in</span></label>
            <button class="button button-dark button-full" type="submit">Continue <span>↗</span></button>
            <p class="auth-switch">New to PnL? <a href="{{ route('register') }}">Create an account</a></p>
        </form>
    </div>
@endsection
