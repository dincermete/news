<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('seo_package_id')
                ->nullable()
                ->after('instagram_story_price_id')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('seo_package_duration_option_id')
                ->nullable()
                ->after('seo_package_id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('seo_package_id')
                ->nullable()
                ->after('instagram_story_price_id')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('seo_package_duration_option_id')
                ->nullable()
                ->after('seo_package_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seo_package_id');
            $table->dropConstrainedForeignId('seo_package_duration_option_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seo_package_id');
            $table->dropConstrainedForeignId('seo_package_duration_option_id');
        });
    }
};
