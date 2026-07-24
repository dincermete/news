<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_package_duration_options', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('months');
            $table->decimal('price_multiplier', 8, 4)->default(1);
            $table->string('bonus_label')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_package_duration_options');
    }
};
