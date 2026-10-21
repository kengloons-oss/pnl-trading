<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trading_account_id')->constrained()->cascadeOnDelete();
            $table->string('market', 30);
            $table->string('symbol', 40);
            $table->string('side', 10);
            $table->string('status', 10)->default('open');
            $table->timestampTz('opened_at');
            $table->timestampTz('closed_at')->nullable();
            $table->decimal('entry_price', 30, 12)->nullable();
            $table->decimal('exit_price', 30, 12)->nullable();
            $table->decimal('quantity', 30, 12)->nullable();
            $table->decimal('manual_profit_loss', 30, 12)->nullable();
            $table->decimal('computed_profit_loss', 30, 12)->nullable();
            $table->decimal('fees', 30, 12)->default(0);
            $table->boolean('pnl_includes_fees')->default(false);
            $table->char('currency_code', 3)->default('USD');
            $table->unsignedTinyInteger('price_precision')->nullable();
            $table->unsignedTinyInteger('quantity_precision')->nullable();
            $table->unsignedTinyInteger('amount_precision')->nullable();
            $table->string('external_position_id')->nullable();
            $table->string('strategy')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'closed_at']);
            $table->index(['user_id', 'symbol']);
            $table->unique(['trading_account_id', 'external_position_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
