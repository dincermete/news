<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_bundles', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->longText('content')->nullable()->after('description');
        });

        $used = [];
        foreach (DB::table('site_bundles')->orderBy('id')->get(['id', 'name']) as $row) {
            $base = Str::slug($row->name) ?: 'paket';
            $slug = $base;
            $suffix = 2;
            while (in_array($slug, $used, true)) {
                $slug = $base.'-'.$suffix;
                $suffix++;
            }
            $used[] = $slug;

            DB::table('site_bundles')->where('id', $row->id)->update(['slug' => $slug]);
        }

        DB::statement('ALTER TABLE site_bundles MODIFY slug VARCHAR(255) NOT NULL');

        Schema::table('site_bundles', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_bundles', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'content']);
        });
    }
};
