<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotional_listings', function (Blueprint $table): void {
            $table->json('reference_content_image_paths')->nullable()->after('reference_content_label');
        });
    }

    public function down(): void
    {
        Schema::table('promotional_listings', function (Blueprint $table): void {
            $table->dropColumn('reference_content_image_paths');
        });
    }
};
