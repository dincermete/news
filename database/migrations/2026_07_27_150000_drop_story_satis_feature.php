<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('instagram_account_id');
            $table->dropConstrainedForeignId('instagram_story_price_id');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('instagram_account_id');
            $table->dropConstrainedForeignId('instagram_story_price_id');
        });

        Schema::dropIfExists('instagram_story_prices');
        Schema::dropIfExists('instagram_accounts');
    }

    public function down(): void
    {
        Schema::create('instagram_accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('handle')->unique();
            $table->string('name')->nullable();
            $table->string('avatar_url')->nullable();
            $table->unsignedInteger('follower_count')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('instagram_story_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instagram_account_id')->constrained()->cascadeOnDelete();
            $table->string('format');
            $table->decimal('price', 10, 2);
            $table->string('currency')->default('TRY');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['instagram_account_id', 'format']);
        });

        Schema::table('cart_items', function (Blueprint $table): void {
            $table->foreignId('instagram_account_id')->nullable()->after('site_id')->constrained()->nullOnDelete();
            $table->foreignId('instagram_story_price_id')->nullable()->after('article_word_package_id')->constrained()->nullOnDelete();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('instagram_account_id')->nullable()->after('site_id')->constrained()->nullOnDelete();
            $table->foreignId('instagram_story_price_id')->nullable()->after('article_word_package_id')->constrained()->nullOnDelete();
        });
    }
};
