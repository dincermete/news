<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('published_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('published_url');
            $table->boolean('is_live')->default(true);
            $table->boolean('is_dofollow_verified')->default(true);
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('guarantee_until')->nullable()->index();
            $table->timestamps();

            $table->index('order_id');
            $table->index(['is_live', 'is_dofollow_verified']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('published_links');
    }
};
