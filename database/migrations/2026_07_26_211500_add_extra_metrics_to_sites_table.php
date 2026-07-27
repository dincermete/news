<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table): void {
            $table->decimal('ahrefs_rank_value', 12, 2)->nullable();
            $table->string('ahrefs_rank_source')->nullable();
            $table->dateTime('ahrefs_rank_updated_at')->nullable();
            $table->unsignedInteger('max_link_count')->nullable()->after('weekly_capacity');
            $table->string('estimated_delivery')->nullable()->after('max_link_count');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table): void {
            $table->dropColumn([
                'ahrefs_rank_value',
                'ahrefs_rank_source',
                'ahrefs_rank_updated_at',
                'max_link_count',
                'estimated_delivery',
            ]);
        });
    }
};
