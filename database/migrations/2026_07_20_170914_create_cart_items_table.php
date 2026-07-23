<?php

use App\Enums\Currency;
use App\Enums\ProductType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->string('product_type')->default(ProductType::SiteArticle->value);
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('site_bundle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('footer_link_duration_option_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('article_word_package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('content_mode')->nullable();
            $table->json('content_payload')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('currency', 3)->default(Currency::Try->value);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
