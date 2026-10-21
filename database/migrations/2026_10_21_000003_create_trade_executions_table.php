<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_id')->constrained()->cascadeOnDelete();
            $table->string('execution_type', 10);
            $table->timestampTz('executed_at');
            $table->decimal('price', 30, 12)->nullable();
            $table->decimal('quantity', 30, 12)->nullable();
            $table->decimal('manual_profit_loss', 30, 12)->nullable();
            $table->decimal('fees', 30, 12)->default(0);
            $table->char('currency_code', 3)->default('USD');
            $table->string('external_execution_id')->nullable();
            $table->timestamps();

            $table->unique(['trade_id', 'external_execution_id']);
            $table->index(['trade_id', 'executed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_executions');
    }
};
