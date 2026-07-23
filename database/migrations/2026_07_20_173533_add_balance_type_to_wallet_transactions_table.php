<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->string('balance_type')
                ->default('main')
                ->after('reason');

            $table->foreignId('related_payment_id')
                ->nullable()
                ->after('related_order_id')
                ->constrained('payments')
                ->nullOnDelete();

            $table->index(['wallet_id', 'balance_type']);
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('related_payment_id');
            $table->dropIndex(['wallet_id', 'balance_type']);
            $table->dropColumn('balance_type');
        });
    }
};
