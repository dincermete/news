<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotional_listing_related', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('promotional_listing_id')->constrained('promotional_listings')->cascadeOnDelete();
            $table->foreignId('related_listing_id')->constrained('promotional_listings')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['promotional_listing_id', 'related_listing_id'], 'pl_related_unique');
        });

        Schema::create('promotional_listing_recommended', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('promotional_listing_id')->constrained('promotional_listings')->cascadeOnDelete();
            $table->foreignId('recommended_listing_id')->constrained('promotional_listings')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['promotional_listing_id', 'recommended_listing_id'], 'pl_recommended_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotional_listing_recommended');
        Schema::dropIfExists('promotional_listing_related');
    }
};
