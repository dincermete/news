<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('receipt_path');
            $table->string('payer_name')->nullable()->after('bank_name');
            $table->text('payer_note')->nullable()->after('payer_name');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'payer_name', 'payer_note']);
        });
    }
};
