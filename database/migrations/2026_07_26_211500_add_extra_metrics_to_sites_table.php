<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table): void {
            if (! Schema::hasColumn('sites', 'max_link_count')) {
                $table->unsignedInteger('max_link_count')->nullable()->after('weekly_capacity');
            }

            if (! Schema::hasColumn('sites', 'estimated_delivery')) {
                $table->string('estimated_delivery')->nullable()->after('max_link_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table): void {
            $columns = array_values(array_filter([
                Schema::hasColumn('sites', 'max_link_count') ? 'max_link_count' : null,
                Schema::hasColumn('sites', 'estimated_delivery') ? 'estimated_delivery' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
