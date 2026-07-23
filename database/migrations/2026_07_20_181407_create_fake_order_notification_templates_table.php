<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fake_order_notification_templates', function (Blueprint $table) {
            $table->id();
            $table->text('message_template');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('display_interval_seconds')->default(30);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fake_order_notification_templates');
    }
};
