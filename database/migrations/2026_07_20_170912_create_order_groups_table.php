<?php

use App\Enums\Currency;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_tier_amount', 14, 2)->default(0);
            $table->decimal('coupon_discount_amount', 14, 2)->default(0);
            $table->decimal('vat_amount', 14, 2)->nullable();
            $table->decimal('vat_withholding_amount', 14, 2)->nullable();
            $table->decimal('total', 14, 2)->default(0);
            $table->string('currency', 3)->default(Currency::Try->value);
            $table->foreignId('billing_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('contract_accepted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_groups');
    }
};
