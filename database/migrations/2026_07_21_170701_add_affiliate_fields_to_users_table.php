<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('affiliate_code')->nullable()->unique()->after('sms_consent');
            $table->decimal('affiliate_commission_rate', 5, 2)->nullable()->after('affiliate_code');
            $table->foreignId('referred_by_id')
                ->nullable()
                ->after('affiliate_commission_rate')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by_id');
            $table->dropColumn(['affiliate_code', 'affiliate_commission_rate']);
        });
    }
};
