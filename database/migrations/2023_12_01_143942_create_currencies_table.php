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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->nullable(false);
            $table->text('countryname')->nullable(false);
            $table->string('name', 255)->nullable(false);
            $table->string('symbol', 255)->nullable(false);
            $table->boolean('prefix')->nullable(false)->default(true);
            $table->float('conversion_rates', 10, 4)->nullable(false)->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
