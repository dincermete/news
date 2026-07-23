<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('footer_link_duration_options', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('months');
            $table->decimal('price_multiplier', 8, 4)->nullable();
            $table->decimal('flat_price', 12, 2)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_link_duration_options');
    }
};
