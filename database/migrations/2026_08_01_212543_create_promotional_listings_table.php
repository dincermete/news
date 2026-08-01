<?php

use App\Enums\Currency;
use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotional_listings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->decimal('price', 12, 2);
            $table->decimal('discount_price', 12, 2)->nullable();
            $table->string('currency', 3)->default(Currency::Try->value);
            $table->string('status')->default(SiteStatus::Draft->value)->index();
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'type']);
            $table->index(['type', 'status']);
        });

        $now = now();

        DB::table('sites')->orderBy('id')->chunkById(100, function ($sites) use ($now): void {
            $rows = [];

            foreach ($sites as $site) {
                $basePrice = (float) $site->price;
                $discount = $site->discount_price !== null ? (float) $site->discount_price : null;
                $effectiveBase = ($discount !== null && $discount < $basePrice) ? $discount : $basePrice;
                $currency = $site->currency ?: Currency::Try->value;
                $status = $site->status ?: SiteStatus::Draft->value;

                $rows[] = [
                    'site_id' => $site->id,
                    'type' => PromotionalListingType::SiteArticle->value,
                    'price' => $basePrice,
                    'discount_price' => $discount,
                    'currency' => $currency,
                    'status' => $status,
                    'short_description' => $site->short_description,
                    'description' => $site->description,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if ($site->press_release_price !== null) {
                    $rows[] = [
                        'site_id' => $site->id,
                        'type' => PromotionalListingType::PressRelease->value,
                        'price' => (float) $site->press_release_price,
                        'discount_price' => null,
                        'currency' => $currency,
                        'status' => $status,
                        'short_description' => $site->short_description,
                        'description' => $site->description,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                $rows[] = [
                    'site_id' => $site->id,
                    'type' => PromotionalListingType::FooterLink->value,
                    'price' => $effectiveBase,
                    'discount_price' => null,
                    'currency' => $currency,
                    'status' => $status,
                    'short_description' => $site->short_description,
                    'description' => $site->description,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($rows !== []) {
                DB::table('promotional_listings')->insert($rows);
            }
        });

        Schema::table('sites', function (Blueprint $table): void {
            $table->dropColumn([
                'price',
                'discount_price',
                'press_release_price',
                'currency',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table): void {
            $table->decimal('price', 12, 2)->nullable()->after('status');
            $table->decimal('discount_price', 12, 2)->nullable()->after('price');
            $table->decimal('press_release_price', 12, 2)->nullable()->after('discount_price');
            $table->string('currency', 3)->default(Currency::Try->value)->after('press_release_price');
        });

        $articles = DB::table('promotional_listings')
            ->where('type', PromotionalListingType::SiteArticle->value)
            ->get();

        foreach ($articles as $listing) {
            $press = DB::table('promotional_listings')
                ->where('site_id', $listing->site_id)
                ->where('type', PromotionalListingType::PressRelease->value)
                ->first();

            DB::table('sites')->where('id', $listing->site_id)->update([
                'price' => $listing->price,
                'discount_price' => $listing->discount_price,
                'press_release_price' => $press?->price,
                'currency' => $listing->currency,
            ]);
        }

        Schema::table('sites', function (Blueprint $table): void {
            $table->decimal('price', 12, 2)->nullable(false)->change();
        });

        Schema::dropIfExists('promotional_listings');
    }
};
