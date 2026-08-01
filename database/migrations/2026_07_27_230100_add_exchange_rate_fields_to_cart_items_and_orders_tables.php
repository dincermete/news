<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->decimal('source_price', 12, 2)->nullable()->after('price');
            $table->string('source_currency', 3)->nullable()->after('source_price');
            $table->decimal('exchange_rate', 16, 6)->nullable()->after('source_currency');
            $table->foreignId('exchange_rate_id')
                ->nullable()
                ->after('exchange_rate')
                ->constrained('exchange_rates')
                ->nullOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('source_price', 12, 2)->nullable()->after('price');
            $table->string('source_currency', 3)->nullable()->after('source_price');
            $table->decimal('exchange_rate', 16, 6)->nullable()->after('source_currency');
            $table->foreignId('exchange_rate_id')
                ->nullable()
                ->after('exchange_rate')
                ->constrained('exchange_rates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('exchange_rate_id');
            $table->dropColumn(['source_price', 'source_currency', 'exchange_rate']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('exchange_rate_id');
            $table->dropColumn(['source_price', 'source_currency', 'exchange_rate']);
        });
    }
};
