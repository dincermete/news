<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_content_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('editor_id')->constrained('users')->cascadeOnDelete();
            $table->longText('content_body');
            $table->unsignedInteger('version');
            $table->string('status')->default('draft')->index();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['order_id', 'version']);
            $table->index('editor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_content_reviews');
    }
};
