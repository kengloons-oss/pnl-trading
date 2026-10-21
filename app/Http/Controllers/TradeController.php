<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TradeController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['open', 'closed'])],
            'symbol' => ['nullable', 'string', 'max:40'],
            'account' => ['nullable', 'integer'],
        ]);

        $trades = $request->user()->trades()
            ->with('tradingAccount')
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['symbol'] ?? null, fn (Builder $query, string $symbol) => $query->where('symbol', 'like', '%'.strtoupper($symbol).'%'))
            ->when($filters['account'] ?? null, fn (Builder $query, int $accountId) => $query->where('trading_account_id', $accountId))
            ->latest('opened_at')
            ->paginate(12)
            ->withQueryString();

        return view('trades.index', [
            'trades' => $trades,
            'accounts' => $request->user()->tradingAccounts()->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $accounts = $request->user()->tradingAccounts()->orderBy('name')->get();

        if ($accounts->isEmpty()) {
            return redirect()->route('accounts.index')->with('status', 'Add a trading account before recording a trade.');
        }

        return view('trades.form', [
            'trade' => new Trade(['opened_at' => now($request->user()->timezone)]),
            'accounts' => $accounts,
            'timezone' => $request->user()->timezone,
            'formTitle' => 'Record a trade',
            'formAction' => route('trades.store'),
            'formMethod' => 'POST',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTrade($request);
        $validated['opened_at'] = CarbonImmutable::parse($validated['opened_at'], $request->user()->timezone)->utc();
        $validated['closed_at'] = filled($validated['closed_at'] ?? null)
            ? CarbonImmutable::parse($validated['closed_at'], $request->user()->timezone)->utc()
            : null;
        $validated['user_id'] = $request->user()->id;
        $validated['currency_code'] = strtoupper($validated['currency_code']);
        $validated['symbol'] = strtoupper($validated['symbol']);

        $request->user()->trades()->create($validated);

        return redirect()->route('trades.index')->with('status', 'Trade recorded.');
    }

    public function edit(Request $request, Trade $trade): View
    {
        abort_unless($trade->user_id === $request->user()->id, 404);

        return view('trades.form', [
            'trade' => $trade,
            'accounts' => $request->user()->tradingAccounts()->orderBy('name')->get(),
            'timezone' => $request->user()->timezone,
            'formTitle' => 'Edit trade',
            'formAction' => route('trades.update', $trade),
            'formMethod' => 'PUT',
        ]);
    }

    public function update(Request $request, Trade $trade): RedirectResponse
    {
        abort_unless($trade->user_id === $request->user()->id, 404);

        $validated = $this->validateTrade($request);
        $validated['opened_at'] = CarbonImmutable::parse($validated['opened_at'], $request->user()->timezone)->utc();
        $validated['closed_at'] = filled($validated['closed_at'] ?? null)
            ? CarbonImmutable::parse($validated['closed_at'], $request->user()->timezone)->utc()
            : null;
        $validated['currency_code'] = strtoupper($validated['currency_code']);
        $validated['symbol'] = strtoupper($validated['symbol']);

        $trade->update($validated);

        return redirect()->route('trades.index')->with('status', 'Trade updated.');
    }

    public function destroy(Request $request, Trade $trade): RedirectResponse
    {
        abort_unless($trade->user_id === $request->user()->id, 404);

        $trade->delete();

        return redirect()->route('trades.index')->with('status', 'Trade deleted.');
    }

    private function validateTrade(Request $request): array
    {
        return $request->validate([
            'trading_account_id' => ['required', 'integer', Rule::exists('trading_accounts', 'id')->where('user_id', $request->user()->id)],
            'market' => ['required', Rule::in(['forex', 'crypto', 'stock', 'other'])],
            'symbol' => ['required', 'string', 'max:40'],
            'side' => ['required', Rule::in(['long', 'short'])],
            'status' => ['required', Rule::in(['open', 'closed'])],
            'opened_at' => ['required', 'date'],
            'closed_at' => ['nullable', 'required_if:status,closed', 'date', 'after_or_equal:opened_at'],
            'entry_price' => ['nullable', 'numeric', 'min:0'],
            'exit_price' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'manual_profit_loss' => ['nullable', 'numeric'],
            'fees' => ['nullable', 'numeric', 'min:0'],
            'pnl_includes_fees' => ['nullable', 'boolean'],
            'currency_code' => ['required', 'string', 'size:3', 'alpha'],
            'price_precision' => ['nullable', 'integer', 'between:0,12'],
            'quantity_precision' => ['nullable', 'integer', 'between:0,12'],
            'amount_precision' => ['nullable', 'integer', 'between:0,12'],
            'strategy' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
