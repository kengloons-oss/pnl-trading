<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TradingAccountController extends Controller
{
    public function index(Request $request): View
    {
        return view('accounts.index', [
            'accounts' => $request->user()->tradingAccounts()->withCount('trades')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['base_currency' => strtoupper((string) $request->input('base_currency', ''))]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'platform' => ['nullable', 'string', 'max:100'],
            'account_type' => ['nullable', 'string', 'max:100'],
            'base_currency' => ['required', 'string', 'size:3', 'alpha'],
        ]);

        $request->user()->tradingAccounts()->create($validated);

        return redirect()->route('accounts.index')->with('status', 'Trading account added.');
    }
}
