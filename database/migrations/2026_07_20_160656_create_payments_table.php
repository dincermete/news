<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('TRY');
            $table->string('method');
            $table->string('status')->default('pending')->index();
            $table->string('paytr_merchant_oid')->nullable()->unique();
            $table->text('paytr_token')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('receipt_path')->nullable();
            $table->timestamps();

            $table->index(['method', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
