<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Single-column indexes on status/price/da_value/site_category_id already
 * exist from create_sites_table. These composites match public catalog
 * filter + sort patterns (Faz 11b).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->index(['status', 'price'], 'sites_status_price_index');
            $table->index(['status', 'da_value'], 'sites_status_da_value_index');
            $table->index(['status', 'site_category_id'], 'sites_status_site_category_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropIndex('sites_status_price_index');
            $table->dropIndex('sites_status_da_value_index');
            $table->dropIndex('sites_status_site_category_id_index');
        });
    }
};
