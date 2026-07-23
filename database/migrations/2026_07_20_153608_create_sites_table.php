<?php

use App\Support\SiteSeoMetrics;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique();
            $table->foreignId('site_category_id')->constrained()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('age')->nullable();
            $table->boolean('is_dofollow')->default(true);
            $table->boolean('is_news_approved')->default(false);
            $table->string('status')->default('draft')->index();

            $table->decimal('price', 12, 2);
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');

            $table->unsignedInteger('daily_capacity')->nullable();
            $table->unsignedInteger('weekly_capacity')->nullable();

            foreach (SiteSeoMetrics::keys() as $metric) {
                $table->decimal("{$metric}_value", 14, 2)->nullable();
                $table->string("{$metric}_source")->default('manual');
                $table->timestamp("{$metric}_updated_at")->nullable();
            }

            $table->text('internal_notes')->nullable();
            $table->string('site_owner_name')->nullable();
            $table->string('site_owner_contact')->nullable();
            $table->text('site_owner_payment_info')->nullable();

            $table->timestamps();

            $table->index('site_category_id');
            $table->index('is_dofollow');
            $table->index('is_news_approved');
            $table->index('price');
            $table->index('da_value');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
