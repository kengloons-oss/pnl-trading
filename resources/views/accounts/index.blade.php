@extends('layouts.app', ['title' => 'Accounts'])

@section('content')
    <div class="page-heading-row compact-heading"><div><div class="eyebrow"><span class="eyebrow-dot"></span> YOUR WORKSPACE</div><h1>Trading <em>accounts.</em></h1><p>Keep positions organized by platform or portfolio.</p></div></div>
    <div class="split-layout">
        <form class="panel form-panel" method="POST" action="{{ route('accounts.store') }}">
            @csrf
            <div class="panel-heading"><span class="eyebrow">NEW ACCOUNT</span><h2>Add an account</h2><p>Use a label that helps you recognize it.</p></div>
            <label class="field"><span>Account name</span><input name="name" value="{{ old('name') }}" required maxlength="100" placeholder="e.g. Vantage — Live"></label>
            <div class="field-row"><label class="field"><span>Platform</span><input name="platform" value="{{ old('platform') }}" placeholder="Vantage, Elev8..."></label><label class="field"><span>Account type</span><input name="account_type" value="{{ old('account_type') }}" placeholder="Live, Demo..."></label></div>
            <label class="field"><span>Base currency</span><input name="base_currency" value="{{ old('base_currency', 'USD') }}" maxlength="3" minlength="3" pattern="[A-Za-z]{3}" required></label>
            <button class="button button-dark button-full" type="submit">Add account <span>＋</span></button>
        </form>
        <div class="accounts-list">
            <div class="list-heading"><div><div class="eyebrow">CONNECTED</div><h2>{{ $accounts->count() }} {{ Str::plural('account', $accounts->count()) }}</h2></div><span class="tiny-count">{{ str_pad((string) $accounts->count(), 2, '0', STR_PAD_LEFT) }}</span></div>
            @forelse ($accounts as $account)
                <article class="panel account-card"><span class="account-icon">{{ strtoupper(substr($account->platform ?: $account->name, 0, 1)) }}</span><div class="account-info"><h3>{{ $account->name }}</h3><p>{{ $account->platform ?: 'Manual account' }}@if($account->account_type) <span>·</span> {{ $account->account_type }}@endif</p></div><div class="account-count"><strong>{{ $account->trades_count }}</strong><small>positions</small></div><span class="currency-tag">{{ $account->base_currency }}</span></article>
            @empty
                <div class="panel empty-card"><div class="empty-icon">▤</div><h3>No accounts yet</h3><p>Add your first trading account. You can record positions manually before connecting a platform.</p></div>
            @endforelse
        </div>
    </div>
@endsection
