<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allowed_currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // e.g. USD, EUR
            $table->string('name');              // e.g. US Dollar
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allowed_currencies');
    }
};
