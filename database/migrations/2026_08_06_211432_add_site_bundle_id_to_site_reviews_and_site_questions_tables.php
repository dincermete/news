<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_reviews', function (Blueprint $table): void {
            $table->dropForeign(['site_id']);
        });

        Schema::table('site_reviews', function (Blueprint $table): void {
            $table->foreignId('site_id')->nullable()->change();
        });

        Schema::table('site_reviews', function (Blueprint $table): void {
            $table->foreign('site_id')
                ->references('id')
                ->on('sites')
                ->cascadeOnDelete();
            $table->foreignId('site_bundle_id')
                ->nullable()
                ->after('site_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->index(['site_bundle_id', 'is_approved']);
        });

        Schema::table('site_questions', function (Blueprint $table): void {
            $table->dropForeign(['site_id']);
        });

        Schema::table('site_questions', function (Blueprint $table): void {
            $table->foreignId('site_id')->nullable()->change();
        });

        Schema::table('site_questions', function (Blueprint $table): void {
            $table->foreign('site_id')
                ->references('id')
                ->on('sites')
                ->cascadeOnDelete();
            $table->foreignId('site_bundle_id')
                ->nullable()
                ->after('site_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->index(['site_bundle_id', 'is_public', 'answered_at']);
        });
    }

    public function down(): void
    {
        Schema::table('site_reviews', function (Blueprint $table): void {
            $table->dropIndex(['site_bundle_id', 'is_approved']);
            $table->dropConstrainedForeignId('site_bundle_id');
            $table->dropForeign(['site_id']);
        });

        Schema::table('site_reviews', function (Blueprint $table): void {
            $table->foreignId('site_id')->nullable(false)->change();
        });

        Schema::table('site_reviews', function (Blueprint $table): void {
            $table->foreign('site_id')
                ->references('id')
                ->on('sites')
                ->cascadeOnDelete();
        });

        Schema::table('site_questions', function (Blueprint $table): void {
            $table->dropIndex(['site_bundle_id', 'is_public', 'answered_at']);
            $table->dropConstrainedForeignId('site_bundle_id');
            $table->dropForeign(['site_id']);
        });

        Schema::table('site_questions', function (Blueprint $table): void {
            $table->foreignId('site_id')->nullable(false)->change();
        });

        Schema::table('site_questions', function (Blueprint $table): void {
            $table->foreign('site_id')
                ->references('id')
                ->on('sites')
                ->cascadeOnDelete();
        });
    }
};
