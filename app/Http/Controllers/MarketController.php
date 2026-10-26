<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketController extends Controller
{
    private const SYMBOLS = [
        'BTCUSD' => ['label' => 'Bitcoin / U.S. Dollar', 'chart_symbol' => 'BITSTAMP:BTCUSD', 'market' => 'Crypto'],
        'ETHUSD' => ['label' => 'Ethereum / U.S. Dollar', 'chart_symbol' => 'COINBASE:ETHUSD', 'market' => 'Crypto'],
        'USDJPY' => ['label' => 'U.S. Dollar / Japanese Yen', 'chart_symbol' => 'OANDA:USDJPY', 'market' => 'Forex'],
        'XAUUSD' => ['label' => 'Gold Spot / U.S. Dollar', 'chart_symbol' => 'OANDA:XAUUSD', 'market' => 'Metals'],
    ];

    public function __invoke(Request $request): View
    {
        $selectedSymbol = strtoupper($request->query('symbol', 'BTCUSD'));

        if (! array_key_exists($selectedSymbol, self::SYMBOLS)) {
            $selectedSymbol = 'BTCUSD';
        }

        return view('markets.index', [
            'symbols' => self::SYMBOLS,
            'selectedSymbol' => $selectedSymbol,
        ]);
    }
}
