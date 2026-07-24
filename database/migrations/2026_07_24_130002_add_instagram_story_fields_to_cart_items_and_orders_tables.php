<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('instagram_account_id')
                ->nullable()
                ->after('site_id')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('instagram_story_price_id')
                ->nullable()
                ->after('article_word_package_id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('instagram_account_id')
                ->nullable()
                ->after('site_id')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('instagram_story_price_id')
                ->nullable()
                ->after('article_word_package_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('instagram_account_id');
            $table->dropConstrainedForeignId('instagram_story_price_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('instagram_account_id');
            $table->dropConstrainedForeignId('instagram_story_price_id');
        });
    }
};
