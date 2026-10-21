@extends('layouts.app', ['title' => 'Overview'])

@section('content')
    <div class="page-heading-row">
        <div><div class="eyebrow"><span class="eyebrow-dot"></span> YOUR PERFORMANCE</div><h1>Good trading starts<br>with <em>good review.</em></h1><p>A focused view of your positions and progress.</p></div>
        <a class="button button-dark" href="{{ route('trades.create') }}">Record a trade <span>＋</span></a>
    </div>
    <div class="toolbar-row">
        <div class="period-tabs"><a class="{{ $period === 'month' ? 'selected' : '' }}" href="{{ route('dashboard', ['period' => 'month']) }}">This month</a><a class="{{ $period === 'year' ? 'selected' : '' }}" href="{{ route('dashboard', ['period' => 'year']) }}">This year</a></div>
        <span class="range-label">{{ $periodStart->format($period === 'year' ? 'Y' : 'F Y') }} <span>·</span> {{ auth()->user()->timezone }}</span>
    </div>
    <div class="stats-grid">
        <article class="stat-card stat-featured"><div class="stat-label">NET PERFORMANCE <span class="stat-symbol">↗</span></div><div class="stat-value-group">
            @forelse ($netByCurrency as $currency => $amount)
                <div class="stat-value {{ $amount < 0 ? 'negative' : '' }}">{{ $amount < 0 ? '−' : '' }}{{ $currency }} {{ number_format(abs($amount), 2) }}</div>
            @empty
                <div class="stat-value">$—</div><div class="stat-caption">Add closed trades to see performance.</div>
            @endforelse
        </div><div class="stat-foot"><span>Manual PnL is your reporting source</span><span class="stat-accent">●</span></div></article>
        <article class="stat-card"><div class="stat-label">CLOSED POSITIONS <span class="stat-symbol">⌁</span></div><div class="stat-value">{{ number_format($closedTradeCount) }}</div><div class="stat-caption">This {{ $period }}</div><div class="mini-bars" aria-hidden="true"><i style="height:26%"></i><i style="height:44%"></i><i style="height:37%"></i><i style="height:66%"></i><i style="height:51%"></i><i style="height:78%"></i><i style="height:60%"></i><i style="height:90%"></i></div></article>
        <article class="stat-card"><div class="stat-label">WIN RATE <span class="stat-symbol">◎</span></div><div class="stat-value">{{ $winRate === null ? '—' : number_format($winRate, 1).'%' }}</div><div class="stat-caption">By completed position</div><div class="progress-track"><span style="width:{{ $winRate ?? 0 }}%"></span></div><div class="stat-foot subtle-foot"><span>{{ $winRate === null ? 'No completed PnL yet' : 'Based on positions with PnL' }}</span></div></article>
        <article class="stat-card"><div class="stat-label">OPEN POSITIONS <span class="stat-symbol">◌</span></div><div class="stat-value">{{ number_format($openTradeCount) }}</div><div class="stat-caption">Currently being tracked</div><div class="stat-foot subtle-foot"><span>{{ $accountCount }} {{ Str::plural('account', $accountCount) }} connected</span><a href="{{ route('accounts.index') }}">Manage →</a></div></article>
    </div>
    <div class="section-heading"><div><div class="eyebrow">RECENT ACTIVITY</div><h2>Your latest positions</h2></div><a class="text-link" href="{{ route('trades.index') }}">View all trades <span>→</span></a></div>
    <div class="panel table-panel">
        @if ($recentTrades->isEmpty())
            <div class="empty-state"><div class="empty-icon">↗</div><h3>Your first trade starts the story.</h3><p>Record a completed or open position to see your trading history here.</p><a class="button button-dark button-small" href="{{ route('trades.create') }}">Record your first trade <span>＋</span></a></div>
        @else
            <div class="table-scroll"><table><thead><tr><th>POSITION</th><th>ACCOUNT</th><th>OPENED</th><th>STATUS</th><th class="align-right">REPORTED PNL</th></tr></thead><tbody>
                @foreach ($recentTrades as $trade)
                    @php($pnl = $trade->reportNetProfitLoss())
                    <tr><td><span class="symbol-cell"><span class="symbol-icon">{{ strtoupper(substr($trade->symbol, 0, 1)) }}</span><span><strong>{{ $trade->symbol }}</strong><small>{{ ucfirst($trade->market) }} · {{ ucfirst($trade->side) }}</small></span></span></td><td>{{ $trade->tradingAccount?->name ?? '—' }}</td><td>{{ $trade->opened_at->setTimezone(auth()->user()->timezone)->format('M j, Y') }}</td><td><span class="status-badge {{ $trade->status === 'closed' ? 'closed' : 'open' }}"><i></i>{{ ucfirst($trade->status) }}</span></td><td class="align-right {{ $pnl === null ? 'muted' : ($pnl < 0 ? 'negative' : 'positive') }}">{{ $pnl === null ? '—' : $trade->currency_code.' '.number_format($pnl, 2) }}</td></tr>
                @endforeach
            </tbody></table></div>
        @endif
    </div>
@endsection
