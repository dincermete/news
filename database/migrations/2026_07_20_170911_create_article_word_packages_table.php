<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_word_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('word_count');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique('word_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_word_packages');
    }
};
