@extends('layouts.app', ['title' => 'Trades'])

@section('content')
    <div class="page-heading-row compact-heading"><div><div class="eyebrow"><span class="eyebrow-dot"></span> TRADE JOURNAL</div><h1>Every position,<br><em>one clear view.</em></h1><p>Track full positions across accounts and markets.</p></div><a class="button button-dark" href="{{ route('trades.create') }}">Record a trade <span>＋</span></a></div>
    <form class="filter-bar" method="GET" action="{{ route('trades.index') }}">
        <label class="search-field"><span>⌕</span><input name="symbol" value="{{ request('symbol') }}" placeholder="Search symbol..."></label>
        <select name="account"><option value="">All accounts</option>@foreach ($accounts as $account)<option value="{{ $account->id }}" @selected((string) request('account') === (string) $account->id)>{{ $account->name }}</option>@endforeach</select>
        <select name="status"><option value="">All statuses</option><option value="open" @selected(request('status') === 'open')>Open</option><option value="closed" @selected(request('status') === 'closed')>Closed</option></select>
        <button class="button button-dark button-small" type="submit">Filter</button><a class="clear-filter" href="{{ route('trades.index') }}">Clear</a>
    </form>
    <div class="panel table-panel trade-table-panel">
        @if ($trades->isEmpty())
            <div class="empty-state"><div class="empty-icon">↗</div><h3>No positions match yet.</h3><p>Record your first trade or adjust the filters.</p><a class="button button-dark button-small" href="{{ route('trades.create') }}">Record a trade <span>＋</span></a></div>
        @else
            <div class="table-scroll"><table><thead><tr><th>POSITION</th><th>ACCOUNT</th><th>OPENED</th><th>CLOSED</th><th>QTY</th><th>STATUS</th><th class="align-right">PNL</th><th></th></tr></thead><tbody>
                @foreach ($trades as $trade)
                    @php($pnl = $trade->reportNetProfitLoss())
                    <tr><td><span class="symbol-cell"><span class="symbol-icon">{{ strtoupper(substr($trade->symbol, 0, 1)) }}</span><span><strong>{{ $trade->symbol }}</strong><small>{{ ucfirst($trade->market) }} · {{ ucfirst($trade->side) }}</small></span></span></td><td>{{ $trade->tradingAccount?->name ?? '—' }}</td><td>{{ $trade->opened_at->setTimezone(auth()->user()->timezone)->format('M j, Y H:i') }}</td><td>{{ $trade->closed_at?->setTimezone(auth()->user()->timezone)->format('M j, Y H:i') ?? '—' }}</td><td>{{ $trade->quantity ?? '—' }}</td><td><span class="status-badge {{ $trade->status }}"><i></i>{{ ucfirst($trade->status) }}</span></td><td class="align-right {{ $pnl === null ? 'muted' : ($pnl < 0 ? 'negative' : 'positive') }}">{{ $pnl === null ? '—' : $trade->currency_code.' '.number_format($pnl, 2) }}</td><td class="row-actions"><a href="{{ route('trades.edit', $trade) }}" aria-label="Edit {{ $trade->symbol }}">Edit</a><form method="POST" action="{{ route('trades.destroy', $trade) }}" onsubmit="return confirm('Delete this position?')">@csrf @method('DELETE')<button type="submit">Delete</button></form></td></tr>
                @endforeach
            </tbody></table></div>
            <div class="pagination-row"><span>Showing {{ $trades->firstItem() }}–{{ $trades->lastItem() }} of {{ $trades->total() }}</span><div>@if($trades->previousPageUrl())<a href="{{ $trades->previousPageUrl() }}">← Previous</a>@endif @if($trades->nextPageUrl())<a href="{{ $trades->nextPageUrl() }}">Next →</a>@endif</div></div>
        @endif
    </div>
@endsection
