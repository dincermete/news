<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_content_reviews', function (Blueprint $table) {
            $table->dropForeign(['editor_id']);
        });

        Schema::table('order_content_reviews', function (Blueprint $table) {
            $table->foreignId('editor_id')
                ->nullable()
                ->change();
        });

        Schema::table('order_content_reviews', function (Blueprint $table) {
            $table->foreign('editor_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_content_reviews', function (Blueprint $table) {
            $table->dropForeign(['editor_id']);
        });

        Schema::table('order_content_reviews', function (Blueprint $table) {
            $table->foreignId('editor_id')
                ->nullable(false)
                ->change();
        });

        Schema::table('order_content_reviews', function (Blueprint $table) {
            $table->foreign('editor_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
