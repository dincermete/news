<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('wallet_topup_package_id')
                ->nullable()
                ->after('receipt_path')
                ->constrained()
                ->nullOnDelete();
            $table->decimal('custom_topup_amount', 12, 2)
                ->nullable()
                ->after('wallet_topup_package_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('wallet_topup_package_id');
            $table->dropColumn('custom_topup_amount');
        });
    }
};
