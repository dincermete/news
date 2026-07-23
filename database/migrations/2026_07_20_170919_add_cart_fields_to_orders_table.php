<?php

use App\Enums\ProductType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('product_type')
                ->default(ProductType::SiteArticle->value)
                ->after('assigned_editor_id')
                ->index();
            $table->foreignId('site_bundle_id')
                ->nullable()
                ->after('product_type')
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('footer_link_duration_option_id')
                ->nullable()
                ->after('site_bundle_id')
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('article_word_package_id')
                ->nullable()
                ->after('footer_link_duration_option_id')
                ->constrained()
                ->nullOnDelete();
            $table->string('content_mode')
                ->nullable()
                ->after('article_word_package_id');
            $table->json('content_payload')
                ->nullable()
                ->after('content_mode');
            $table->foreignId('order_group_id')
                ->nullable()
                ->after('content_payload')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_group_id');
            $table->dropConstrainedForeignId('article_word_package_id');
            $table->dropConstrainedForeignId('footer_link_duration_option_id');
            $table->dropConstrainedForeignId('site_bundle_id');
            $table->dropColumn([
                'product_type',
                'content_mode',
                'content_payload',
            ]);
        });
    }
};
