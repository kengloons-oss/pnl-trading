<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $period = $request->string('period')->value() ?: 'month';
        $now = CarbonImmutable::now($user->timezone);
        $start = $period === 'year' ? $now->startOfYear() : $now->startOfMonth();
        $end = $period === 'year' ? $now->endOfYear() : $now->endOfMonth();
        $startUtc = $start->utc();
        $endUtc = $end->utc();

        $closedTrades = $user->trades()
            ->with('tradingAccount')
            ->where('status', 'closed')
            ->whereBetween('closed_at', [$startUtc, $endUtc])
            ->orderByDesc('closed_at')
            ->get();

        $reportedTrades = $closedTrades->filter(fn (Trade $trade): bool => $trade->reportNetProfitLoss() !== null);
        $wins = $reportedTrades->filter(fn (Trade $trade): bool => $trade->reportNetProfitLoss() > 0)->count();
        $losses = $reportedTrades->filter(fn (Trade $trade): bool => $trade->reportNetProfitLoss() < 0)->count();
        $netByCurrency = $reportedTrades
            ->groupBy('currency_code')
            ->map(fn ($trades): float => $trades->sum(fn (Trade $trade): float => $trade->reportNetProfitLoss()));

        $recentTrades = $user->trades()
            ->with('tradingAccount')
            ->latest('opened_at')
            ->limit(6)
            ->get();

        return view('dashboard', [
            'period' => $period,
            'periodStart' => $start,
            'closedTradeCount' => $closedTrades->count(),
            'openTradeCount' => $user->trades()->where('status', 'open')->count(),
            'winRate' => ($wins + $losses) > 0 ? ($wins / ($wins + $losses)) * 100 : null,
            'netByCurrency' => $netByCurrency,
            'recentTrades' => $recentTrades,
            'accountCount' => $user->tradingAccounts()->count(),
        ]);
    }
}
