@extends('layouts.app', ['title' => $formTitle])

@section('content')
    <div class="page-heading-row compact-heading"><div><div class="eyebrow"><span class="eyebrow-dot"></span> TRADE JOURNAL</div><h1>{{ $formTitle }}<em>.</em></h1><p>Record one complete position. Partial exits can be added as executions later.</p></div><a class="text-link" href="{{ route('trades.index') }}">← Back to trades</a></div>
    <form class="panel trade-form" method="POST" action="{{ $formAction }}">
        @csrf @if ($formMethod !== 'POST') @method($formMethod) @endif
        <div class="form-section"><div class="form-section-title"><span class="section-index">01</span><span><strong>Position details</strong><small>What did you trade?</small></span></div><div class="form-grid">
            <label class="field"><span>Trading account</span><select name="trading_account_id" required>@foreach ($accounts as $account)<option value="{{ $account->id }}" @selected((string) old('trading_account_id', $trade->trading_account_id) === (string) $account->id)>{{ $account->name }}</option>@endforeach</select></label>
            <label class="field"><span>Market</span><select name="market" required>@foreach (['forex' => 'Forex', 'crypto' => 'Crypto', 'stock' => 'Stock', 'other' => 'Other'] as $value => $label)<option value="{{ $value }}" @selected(old('market', $trade->market ?: 'forex') === $value)>{{ $label }}</option>@endforeach</select></label>
            <label class="field"><span>Symbol</span><input name="symbol" value="{{ old('symbol', $trade->symbol) }}" maxlength="40" required placeholder="e.g. EURUSD, BTCUSD"></label>
            <label class="field"><span>Direction</span><select name="side" required><option value="long" @selected(old('side', $trade->side ?: 'long') === 'long')>Long</option><option value="short" @selected(old('side', $trade->side) === 'short')>Short</option></select></label>
            <label class="field"><span>Status</span><select name="status" required><option value="open" @selected(old('status', $trade->status ?: 'open') === 'open')>Open</option><option value="closed" @selected(old('status', $trade->status) === 'closed')>Closed</option></select></label>
            <label class="field"><span>Strategy <small>· optional</small></span><input name="strategy" value="{{ old('strategy', $trade->strategy) }}" maxlength="100" placeholder="e.g. Breakout"></label>
        </div></div>
        <div class="form-section"><div class="form-section-title"><span class="section-index">02</span><span><strong>Execution and result</strong><small>Prices, size and reported PnL</small></span></div><div class="form-grid">
            <label class="field"><span>Opened at <small>· {{ $timezone }}</small></span><input type="datetime-local" name="opened_at" value="{{ old('opened_at', $trade->opened_at?->setTimezone($timezone)->format('Y-m-d\TH:i')) }}" required></label>
            <label class="field"><span>Closed at <small>· required for closed positions</small></span><input type="datetime-local" name="closed_at" value="{{ old('closed_at', $trade->closed_at?->setTimezone($timezone)->format('Y-m-d\TH:i')) }}"></label>
            <label class="field"><span>Entry price</span><input type="number" name="entry_price" value="{{ old('entry_price', $trade->entry_price) }}" step="any" min="0" placeholder="0.00"></label>
            <label class="field"><span>Exit price</span><input type="number" name="exit_price" value="{{ old('exit_price', $trade->exit_price) }}" step="any" min="0" placeholder="0.00"></label>
            <label class="field"><span>Position quantity</span><input type="number" name="quantity" value="{{ old('quantity', $trade->quantity) }}" step="any" min="0" placeholder="0.00"></label>
            <label class="field"><span>Reported PnL <small>· manual value is primary</small></span><input type="number" name="manual_profit_loss" value="{{ old('manual_profit_loss', $trade->manual_profit_loss) }}" step="any" placeholder="e.g. 125.50"></label>
            <label class="field"><span>Fees / costs</span><input type="number" name="fees" value="{{ old('fees', $trade->fees ?? '0') }}" step="any" min="0" placeholder="0.00"></label>
            <label class="field"><span>Currency code</span><input name="currency_code" value="{{ old('currency_code', $trade->currency_code ?: 'USD') }}" maxlength="3" minlength="3" pattern="[A-Za-z]{3}" required placeholder="USD"></label>
            <div class="field"><span>Cost treatment</span><input type="hidden" name="pnl_includes_fees" value="0"><label class="check-box-field"><input type="checkbox" name="pnl_includes_fees" value="1" @checked(old('pnl_includes_fees', $trade->pnl_includes_fees))><span>Reported PnL already includes fees</span></label></div>
            <label class="field"><span>Price precision <small>· optional</small></span><input type="number" name="price_precision" value="{{ old('price_precision', $trade->price_precision) }}" min="0" max="12" placeholder="e.g. 5"></label>
            <label class="field"><span>Quantity precision <small>· optional</small></span><input type="number" name="quantity_precision" value="{{ old('quantity_precision', $trade->quantity_precision) }}" min="0" max="12" placeholder="e.g. 2"></label>
            <label class="field"><span>Amount precision <small>· optional</small></span><input type="number" name="amount_precision" value="{{ old('amount_precision', $trade->amount_precision) }}" min="0" max="12" placeholder="e.g. 2"></label>
        </div><p class="form-hint">Your manual PnL is the reporting value. If fees are not already included, they are deducted when net performance is shown. Values remain stored in their original currency.</p></div>
        <div class="form-section"><div class="form-section-title"><span class="section-index">03</span><span><strong>Review notes</strong><small>Capture the reason behind the trade</small></span></div><label class="field"><span>Notes</span><textarea name="notes" rows="4" maxlength="5000" placeholder="Setup, entry reason, emotions, lessons...">{{ old('notes', $trade->notes) }}</textarea></label></div>
        <div class="form-actions"><a class="text-link" href="{{ route('trades.index') }}">Cancel</a><button class="button button-dark" type="submit">Save position <span>↗</span></button></div>
    </form>
@endsection
