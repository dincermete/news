<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_reviews', function (Blueprint $table): void {
            $table->foreignId('seo_package_id')
                ->nullable()
                ->after('site_bundle_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('backlink_package_id')
                ->nullable()
                ->after('seo_package_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->index(['seo_package_id', 'is_approved']);
            $table->index(['backlink_package_id', 'is_approved']);
        });

        Schema::table('site_questions', function (Blueprint $table): void {
            $table->foreignId('seo_package_id')
                ->nullable()
                ->after('site_bundle_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('backlink_package_id')
                ->nullable()
                ->after('seo_package_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->index(['seo_package_id', 'is_public', 'answered_at']);
            $table->index(['backlink_package_id', 'is_public', 'answered_at']);
        });
    }

    public function down(): void
    {
        Schema::table('site_reviews', function (Blueprint $table): void {
            $table->dropIndex(['seo_package_id', 'is_approved']);
            $table->dropIndex(['backlink_package_id', 'is_approved']);
            $table->dropConstrainedForeignId('seo_package_id');
            $table->dropConstrainedForeignId('backlink_package_id');
        });

        Schema::table('site_questions', function (Blueprint $table): void {
            $table->dropIndex(['seo_package_id', 'is_public', 'answered_at']);
            $table->dropIndex(['backlink_package_id', 'is_public', 'answered_at']);
            $table->dropConstrainedForeignId('seo_package_id');
            $table->dropConstrainedForeignId('backlink_package_id');
        });
    }
};
