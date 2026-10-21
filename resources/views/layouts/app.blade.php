<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'PnL' }} · PnL Trading</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="{{ auth()->check() ? route('dashboard') : route('home') }}">
                <span class="brand-mark">P</span>
                <span>PnL<span class="brand-light">/trade</span></span>
            </a>
            @auth
                <div class="nav-label">WORKSPACE</div>
                <nav class="primary-nav" aria-label="Main navigation">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="nav-icon">◫</span> Overview</a>
                    <a class="nav-link {{ request()->routeIs('trades.*') ? 'active' : '' }}" href="{{ route('trades.index') }}"><span class="nav-icon">↗</span> Trades</a>
                    <a class="nav-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}" href="{{ route('accounts.index') }}"><span class="nav-icon">▤</span> Accounts</a>
                </nav>
                <div class="sidebar-bottom">
                    <div class="user-card">
                        <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="user-meta"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></span>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button class="icon-button" aria-label="Sign out" title="Sign out">↗</button></form>
                    </div>
                </div>
            @else
                <div class="sidebar-note">A clear view of every position.</div>
                <div class="sidebar-bottom sidebar-footnote">Multi-market journal<br>Built for better decisions.</div>
            @endauth
        </aside>

        <main class="main-area">
            <header class="topbar">
                <div class="breadcrumb"><span>Workspace</span><span class="crumb-slash">/</span><strong>{{ $title ?? 'Overview' }}</strong></div>
                <div class="topbar-right">
                    @auth
                        <span class="live-dot">Market data · coming soon</span>
                        <span class="topbar-date">{{ now(auth()->user()->timezone)->format('D, M j') }}</span>
                    @else
                        <a class="topbar-link" href="{{ route('login') }}">Sign in</a>
                        <a class="button button-small button-dark" href="{{ route('register') }}">Create account <span>↗</span></a>
                    @endauth
                </div>
            </header>
            <section class="page-content">
                @if (session('status'))
                    <div class="flash-message">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="error-message"><strong>Please check the form.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                @yield('content')
            </section>
            <footer class="page-footer"><span>PnL Trading · Private trading journal</span><span>All times shown in your local reporting timezone</span></footer>
        </main>
    </div>
</body>
</html>
