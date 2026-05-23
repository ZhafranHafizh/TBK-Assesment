<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->string('original_currency', 3)->default('IDR')->after('credit');
            $table->decimal('original_amount', 15, 2)->nullable()->after('original_currency');
            $table->decimal('exchange_rate', 15, 6)->nullable()->after('original_amount');
        });
    }

    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropColumn(['original_currency', 'original_amount', 'exchange_rate']);
        });
    }
};
