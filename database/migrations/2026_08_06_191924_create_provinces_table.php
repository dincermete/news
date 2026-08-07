<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('plate_code', 2)->unique();
            $table->string('name_locative');
            $table->timestamps();
        });

        Schema::create('province_site', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->unique(['province_id', 'site_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('province_site');
        Schema::dropIfExists('provinces');
    }
};
