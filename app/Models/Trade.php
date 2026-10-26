<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'trading_account_id', 'market', 'symbol', 'side', 'status', 'opened_at', 'closed_at',
    'entry_price', 'exit_price', 'quantity', 'manual_profit_loss', 'computed_profit_loss', 'fees',
    'pnl_includes_fees', 'currency_code', 'price_precision', 'quantity_precision', 'amount_precision',
    'external_position_id', 'import_fingerprint', 'strategy', 'notes',
])]
class Trade extends Model
{
    protected function casts(): array
    {
        return [
            'opened_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
            'entry_price' => 'decimal:12',
            'exit_price' => 'decimal:12',
            'quantity' => 'decimal:12',
            'manual_profit_loss' => 'decimal:12',
            'computed_profit_loss' => 'decimal:12',
            'fees' => 'decimal:12',
            'pnl_includes_fees' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tradingAccount(): BelongsTo
    {
        return $this->belongsTo(TradingAccount::class);
    }

    public function executions(): HasMany
    {
        return $this->hasMany(TradeExecution::class);
    }

    public function reportProfitLoss(): ?string
    {
        return $this->manual_profit_loss ?? $this->computed_profit_loss;
    }

    public function reportNetProfitLoss(): ?float
    {
        $profitLoss = $this->reportProfitLoss();

        if ($profitLoss === null) {
            return null;
        }

        return (float) $profitLoss - ($this->pnl_includes_fees ? 0 : (float) $this->fees);
    }
}
