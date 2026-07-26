<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name', 120)->default('Tanıtım Yazısı');
            $table->string('site_domain', 120)->default('tanitimyazisi.com.tr');
            $table->string('legal_name', 180)->nullable();
            $table->string('tagline')->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('logo_dark_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('og_image_path')->nullable();
            $table->string('support_phone', 32)->nullable();
            $table->string('support_phone_display', 64)->nullable();
            $table->string('support_email', 180)->nullable();
            $table->string('whatsapp_number', 32)->nullable();
            $table->string('address')->nullable();
            $table->string('social_instagram')->nullable();
            $table->string('social_x')->nullable();
            $table->string('social_youtube')->nullable();
            $table->string('social_linkedin')->nullable();
            $table->string('paytr_merchant_id')->nullable();
            $table->string('paytr_merchant_key')->nullable();
            $table->string('paytr_merchant_salt')->nullable();
            $table->boolean('paytr_test_mode')->default(true);
            $table->string('netgsm_username')->nullable();
            $table->string('netgsm_password')->nullable();
            $table->string('netgsm_header', 20)->nullable();
            $table->string('openai_api_key')->nullable();
            $table->string('openai_model', 80)->nullable();
            $table->string('openai_chatbot_model', 80)->nullable();
            $table->string('openai_article_model', 80)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
