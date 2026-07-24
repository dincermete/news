<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('wallet_topup_package_id')
                ->nullable()
                ->after('backlink_package_id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('wallet_topup_package_id')
                ->nullable()
                ->after('backlink_package_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('wallet_topup_package_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('wallet_topup_package_id');
        });
    }
};
