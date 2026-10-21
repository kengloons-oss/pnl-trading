@extends('layouts.app')

@section('content')
    <div class="hero-grid">
        <div class="hero-copy">
            <div class="eyebrow"><span class="eyebrow-dot"></span> YOUR TRADING, IN FOCUS</div>
            <h1>Make every<br>trade <em>count.</em></h1>
            <p class="hero-lede">A calm, clear workspace to record positions, understand your performance, and build better trading habits across every market.</p>
            <div class="hero-actions">
                <a class="button button-dark" href="{{ route('register') }}">Start your journal <span>↗</span></a>
                <a class="text-link" href="{{ route('login') }}">I already have an account <span>→</span></a>
            </div>
            <div class="hero-caption"><span class="caption-rule"></span> FOREX · CRYPTO · EQUITIES · MORE</div>
        </div>
        <div class="hero-visual">
            <div class="visual-orbit orbit-one"></div><div class="visual-orbit orbit-two"></div>
            <div class="market-card">
                <div class="market-card-top"><span class="card-overline">PORTFOLIO SNAPSHOT</span><span class="period-chip">MONTH <span>⌄</span></span></div>
                <div class="market-value">$— <span class="value-dash">·</span></div>
                <div class="market-subtitle">Net performance <span class="muted">· No trades yet</span></div>
                <svg class="chart-art" viewBox="0 0 500 130" preserveAspectRatio="none" aria-label="Decorative chart preview">
                    <defs><linearGradient id="area" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#54a98b" stop-opacity=".24"/><stop offset="1" stop-color="#54a98b" stop-opacity="0"/></linearGradient></defs>
                    <path d="M0,103 C35,99 39,83 72,87 S112,101 142,76 S177,65 202,72 S236,94 268,55 S314,69 345,45 S384,67 414,28 S462,42 500,10 L500,130 L0,130 Z" fill="url(#area)"/>
                    <path d="M0,103 C35,99 39,83 72,87 S112,101 142,76 S177,65 202,72 S236,94 268,55 S314,69 345,45 S384,67 414,28 S462,42 500,10" fill="none" stroke="#45a184" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <div class="chart-axis"><span>OCT 01</span><span>OCT 08</span><span>OCT 15</span><span>OCT 21</span></div>
                <div class="market-card-bottom"><span><i class="legend-dot"></i> Performance overview</span><span>Sample view</span></div>
            </div>
            <div class="floating-pill pill-top"><span class="pill-icon">↗</span><span><small>MARKETS</small><strong>One clear view</strong></span></div>
            <div class="floating-pill pill-bottom"><span class="pill-icon pill-icon-gold">◎</span><span><small>YOUR PROCESS</small><strong>Track every position</strong></span></div>
        </div>
    </div>
    <div class="feature-strip">
        <div><span class="feature-number">01</span><span><strong>One trading journal</strong><small>Bring every market together.</small></span></div>
        <div><span class="feature-number">02</span><span><strong>Honest performance</strong><small>Review results on your terms.</small></span></div>
        <div><span class="feature-number">03</span><span><strong>Your data stays yours</strong><small>Private workspace by default.</small></span></div>
    </div>
@endsection
