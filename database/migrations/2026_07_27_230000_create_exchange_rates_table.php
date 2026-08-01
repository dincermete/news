<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('base_currency', 3)->default('TRY');
            $table->string('quote_currency', 3);
            $table->decimal('rate', 16, 6);
            $table->date('rate_date');
            $table->string('source', 32)->default('tcmb');
            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->unique(['quote_currency', 'rate_date', 'source']);
            $table->index(['quote_currency', 'rate_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
