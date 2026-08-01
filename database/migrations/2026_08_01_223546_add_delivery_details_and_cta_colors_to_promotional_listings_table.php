<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotional_listings', function (Blueprint $table): void {
            $table->longText('delivery_details')->nullable()->after('description');
            $table->string('cta_cart_color', 20)->nullable()->after('delivery_details');
            $table->string('cta_buy_color', 20)->nullable()->after('cta_cart_color');
            $table->string('cta_whatsapp_color', 20)->nullable()->after('cta_buy_color');
        });
    }

    public function down(): void
    {
        Schema::table('promotional_listings', function (Blueprint $table): void {
            $table->dropColumn([
                'delivery_details',
                'cta_cart_color',
                'cta_buy_color',
                'cta_whatsapp_color',
            ]);
        });
    }
};
