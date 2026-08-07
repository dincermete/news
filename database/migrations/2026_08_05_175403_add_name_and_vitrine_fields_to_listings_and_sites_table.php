<?php

use App\Enums\PromotionalListingType;
use App\Models\PromotionalListing;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotional_listings', function (Blueprint $table): void {
            $table->string('name')->nullable()->after('type');
            $table->string('estimated_delivery', 100)->nullable()->after('delivery_details');
            $table->string('reference_content_url')->nullable()->after('estimated_delivery');
            $table->string('reference_content_label')->nullable()->after('reference_content_url');
        });

        Schema::table('sites', function (Blueprint $table): void {
            $table->json('analytics_image_paths')->nullable()->after('logo_path');
        });

        PromotionalListing::query()
            ->with('site:id,domain')
            ->orderBy('id')
            ->chunkById(200, function ($listings): void {
                foreach ($listings as $listing) {
                    /** @var PromotionalListing $listing */
                    if (filled($listing->name)) {
                        continue;
                    }

                    $domain = $listing->site?->domain ?? ('#'.$listing->site_id);
                    $type = $listing->type instanceof PromotionalListingType
                        ? $listing->type->getLabel()
                        : (string) $listing->type;

                    $listing->forceFill([
                        'name' => trim($domain.($type !== '' ? ' — '.$type : '')),
                    ])->saveQuietly();
                }
            });

        // SQLite-safe: keep nullable for existing rows already backfilled; new writes require name via app validation.
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE promotional_listings MODIFY name VARCHAR(255) NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('promotional_listings', function (Blueprint $table): void {
            $table->dropColumn([
                'name',
                'estimated_delivery',
                'reference_content_url',
                'reference_content_label',
            ]);
        });

        Schema::table('sites', function (Blueprint $table): void {
            $table->dropColumn('analytics_image_paths');
        });
    }
};
