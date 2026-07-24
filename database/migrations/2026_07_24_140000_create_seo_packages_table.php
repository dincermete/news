<?php

use App\Enums\Currency;
use App\Enums\SiteStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('keyword_count');
            $table->decimal('monthly_price', 12, 2);
            $table->string('currency', 3)->default(Currency::Try->value);
            $table->json('features')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('status')->default(SiteStatus::Draft->value)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_packages');
    }
};
