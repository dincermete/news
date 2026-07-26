<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('meta_keywords')->nullable()->after('meta_description');
            $table->string('og_image')->nullable()->after('meta_keywords');
            $table->string('route_key')->nullable()->unique()->after('slug');
            $table->boolean('is_system')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropUnique(['route_key']);
            $table->dropColumn(['meta_keywords', 'og_image', 'route_key', 'is_system']);
        });
    }
};
