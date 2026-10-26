<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->char('import_fingerprint', 64)->nullable();
            $table->unique(['user_id', 'trading_account_id', 'import_fingerprint']);
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'trading_account_id', 'import_fingerprint']);
            $table->dropColumn('import_fingerprint');
        });
    }
};
