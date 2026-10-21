<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'trade_id', 'execution_type', 'executed_at', 'price', 'quantity', 'manual_profit_loss', 'fees',
    'currency_code', 'external_execution_id',
])]
class TradeExecution extends Model
{
    protected function casts(): array
    {
        return [
            'executed_at' => 'immutable_datetime',
            'price' => 'decimal:12',
            'quantity' => 'decimal:12',
            'manual_profit_loss' => 'decimal:12',
            'fees' => 'decimal:12',
        ];
    }

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }
}
