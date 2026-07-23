<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('site_package_id')->nullable()->index();
            $table->string('status')->default('payment_pending')->index();
            $table->string('content_source');
            $table->date('due_date')->nullable()->index();
            $table->decimal('price', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->foreignId('assigned_editor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('content_source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
