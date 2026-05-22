<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('coa_categories', function (Blueprint $table) {
            $table->timestamp('restored_at')->nullable();
        });
        Schema::table('coas', function (Blueprint $table) {
            $table->timestamp('restored_at')->nullable();
        });
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->timestamp('restored_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropColumn('restored_at');
        });
        Schema::table('coas', function (Blueprint $table) {
            $table->dropColumn('restored_at');
        });
        Schema::table('coa_categories', function (Blueprint $table) {
            $table->dropColumn('restored_at');
        });
    }
};
