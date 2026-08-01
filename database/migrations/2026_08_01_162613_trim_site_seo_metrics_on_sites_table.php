<?php

use App\Enums\MetricSource;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private const DROP_METRICS = [
        'moz_trust',
        'ahrefs_rank',
        'ahrefs_traffic',
        'organic_traffic',
    ];

    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table): void {
            $drop = [];

            foreach (self::DROP_METRICS as $metric) {
                foreach (["{$metric}_value", "{$metric}_source", "{$metric}_updated_at"] as $column) {
                    if (Schema::hasColumn('sites', $column)) {
                        $drop[] = $column;
                    }
                }
            }

            if (Schema::hasColumn('sites', 'estimated_delivery')) {
                $drop[] = 'estimated_delivery';
            }

            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });

        Schema::table('sites', function (Blueprint $table): void {
            if (! Schema::hasColumn('sites', 'ahrefs_keywords_value')) {
                $table->decimal('ahrefs_keywords_value', 14, 2)->nullable()->after('ahrefs_dr_updated_at');
                $table->string('ahrefs_keywords_source')->default(MetricSource::Manual->value)->after('ahrefs_keywords_value');
                $table->timestamp('ahrefs_keywords_updated_at')->nullable()->after('ahrefs_keywords_source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table): void {
            foreach (['ahrefs_keywords_value', 'ahrefs_keywords_source', 'ahrefs_keywords_updated_at'] as $column) {
                if (Schema::hasColumn('sites', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('sites', function (Blueprint $table): void {
            foreach (self::DROP_METRICS as $metric) {
                if (! Schema::hasColumn('sites', "{$metric}_value")) {
                    $table->decimal("{$metric}_value", 14, 2)->nullable();
                    $table->string("{$metric}_source")->default(MetricSource::Manual->value);
                    $table->timestamp("{$metric}_updated_at")->nullable();
                }
            }

            if (! Schema::hasColumn('sites', 'estimated_delivery')) {
                $table->string('estimated_delivery')->nullable();
            }
        });
    }
};
