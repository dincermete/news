<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_bundles', function (Blueprint $table): void {
            $table->string('icon')->nullable()->after('content');
            $table->string('bg_color_from', 7)->nullable()->after('icon');
            $table->string('bg_color_to', 7)->nullable()->after('bg_color_from');
        });
    }

    public function down(): void
    {
        Schema::table('site_bundles', function (Blueprint $table): void {
            $table->dropColumn(['icon', 'bg_color_from', 'bg_color_to']);
        });
    }
};
