<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $productTables = [
        'promotional_listings',
        'site_bundles',
        'seo_packages',
        'backlink_packages',
    ];

    public function up(): void
    {
        foreach ($this->productTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                if (! Schema::hasColumn($tableName, 'public_path')) {
                    $table->string('public_path')->nullable()->unique()->after('id');
                }
                if (! Schema::hasColumn($tableName, 'meta_title')) {
                    $table->string('meta_title')->nullable();
                }
                if (! Schema::hasColumn($tableName, 'meta_description')) {
                    $table->string('meta_description', 512)->nullable();
                }
                if (! Schema::hasColumn($tableName, 'meta_keywords')) {
                    $table->string('meta_keywords')->nullable();
                }
                if (! Schema::hasColumn($tableName, 'og_image')) {
                    $table->string('og_image')->nullable();
                }
            });
        }

        Schema::table('seo_packages', function (Blueprint $table): void {
            if (! Schema::hasColumn('seo_packages', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }
        });

        Schema::table('backlink_packages', function (Blueprint $table): void {
            if (! Schema::hasColumn('backlink_packages', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }
        });

        $this->backfillSlugs('seo_packages');
        $this->backfillSlugs('backlink_packages');
    }

    public function down(): void
    {
        foreach ($this->productTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
                $columns = array_values(array_filter(
                    ['public_path', 'meta_title', 'meta_description', 'meta_keywords', 'og_image'],
                    fn (string $column): bool => Schema::hasColumn($tableName, $column),
                ));

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }

        Schema::table('seo_packages', function (Blueprint $table): void {
            if (Schema::hasColumn('seo_packages', 'slug')) {
                $table->dropColumn('slug');
            }
        });

        Schema::table('backlink_packages', function (Blueprint $table): void {
            if (Schema::hasColumn('backlink_packages', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }

    private function backfillSlugs(string $table): void
    {
        $rows = DB::table($table)->select(['id', 'name', 'slug'])->orderBy('id')->get();
        $used = [];

        foreach ($rows as $row) {
            if (filled($row->slug)) {
                $used[$row->slug] = true;

                continue;
            }

            $base = Str::slug((string) $row->name) ?: 'paket-'.$row->id;
            $slug = $base;
            $i = 2;

            while (isset($used[$slug])) {
                $slug = $base.'-'.$i;
                $i++;
            }

            $used[$slug] = true;
            DB::table($table)->where('id', $row->id)->update(['slug' => $slug]);
        }
    }
};
