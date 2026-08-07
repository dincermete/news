<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\MetricSource;
use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Observers\SiteObserver;
use App\Support\PublicImagePaths;
use App\Support\SiteSeoMetrics;
use Database\Factories\SiteFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

#[ObservedBy([SiteObserver::class])]
class Site extends Model
{
    /** @use HasFactory<SiteFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'domain',
        'logo_path',
        'analytics_image_paths',
        'site_category_id',
        'description',
        'short_description',
        'age',
        'opened_at',
        'is_dofollow',
        'is_news_approved',
        'is_google_indexed',
        'status',
        'daily_capacity',
        'weekly_capacity',
        'max_link_count',
        'internal_notes',
        'site_owner_name',
        'site_owner_contact',
        'site_owner_payment_info',
        'da_value',
        'da_source',
        'da_updated_at',
        'pa_value',
        'pa_source',
        'pa_updated_at',
        'spam_score_value',
        'spam_score_source',
        'spam_score_updated_at',
        'moz_rank_value',
        'moz_rank_source',
        'moz_rank_updated_at',
        'majestic_cf_value',
        'majestic_cf_source',
        'majestic_cf_updated_at',
        'majestic_tf_value',
        'majestic_tf_source',
        'majestic_tf_updated_at',
        'ahrefs_dr_value',
        'ahrefs_dr_source',
        'ahrefs_dr_updated_at',
        'ahrefs_keywords_value',
        'ahrefs_keywords_source',
        'ahrefs_keywords_updated_at',
        'semrush_authority_score_value',
        'semrush_authority_score_source',
        'semrush_authority_score_updated_at',
        'monthly_traffic_value',
        'monthly_traffic_source',
        'monthly_traffic_updated_at',
        'backlinks_value',
        'backlinks_source',
        'backlinks_updated_at',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_dofollow' => true,
        'is_news_approved' => false,
        'is_google_indexed' => true,
        'status' => 'draft',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        $casts = [
            'age' => 'integer',
            'opened_at' => 'date',
            'is_dofollow' => 'boolean',
            'is_news_approved' => 'boolean',
            'is_google_indexed' => 'boolean',
            'status' => SiteStatus::class,
            'daily_capacity' => 'integer',
            'weekly_capacity' => 'integer',
            'max_link_count' => 'integer',
            'analytics_image_paths' => 'array',
        ];

        foreach (SiteSeoMetrics::keys() as $metric) {
            $casts["{$metric}_value"] = 'decimal:2';
            $casts["{$metric}_source"] = MetricSource::class;
            $casts["{$metric}_updated_at"] = 'datetime';
        }

        return $casts;
    }

    public function logoUrl(): ?string
    {
        if (! filled($this->logo_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SiteCategory::class, 'site_category_id');
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'site_label');
    }

    public function provinces(): BelongsToMany
    {
        return $this->belongsToMany(Province::class);
    }

    public function bundles(): BelongsToMany
    {
        return $this->belongsToMany(SiteBundle::class, 'site_bundle_site');
    }

    public function promotionalListings(): HasMany
    {
        return $this->hasMany(PromotionalListing::class);
    }

    public function articleListing(): HasOne
    {
        return $this->hasOne(PromotionalListing::class)
            ->where('type', PromotionalListingType::SiteArticle);
    }

    public function pressReleaseListing(): HasOne
    {
        return $this->hasOne(PromotionalListing::class)
            ->where('type', PromotionalListingType::PressRelease);
    }

    public function footerLinkListing(): HasOne
    {
        return $this->hasOne(PromotionalListing::class)
            ->where('type', PromotionalListingType::FooterLink);
    }

    public function listingOf(PromotionalListingType $type): ?PromotionalListing
    {
        return $this->promotionalListings
            ->first(fn (PromotionalListing $listing): bool => $listing->type === $type);
    }

    /**
     * Copy sale pricing from a listing onto this site instance for storefront display.
     */
    public function applyListingPricing(PromotionalListing $listing): static
    {
        $this->setAttribute('price', $listing->price);
        $this->setAttribute('discount_price', $listing->discount_price);
        $this->setAttribute(
            'currency',
            $listing->currency instanceof Currency
                ? $listing->currency
                : Currency::tryFrom((string) $listing->currency) ?? Currency::Try,
        );

        if (filled($listing->short_description)) {
            $this->setAttribute('short_description', $listing->short_description);
        }

        if (filled($listing->description)) {
            $this->setAttribute('description', $listing->description);
        }

        $this->setAttribute('delivery_details', $listing->delivery_details);
        $this->setAttribute('estimated_delivery', $listing->estimated_delivery);
        $this->setAttribute('listing_name', $listing->name);
        $this->setAttribute('reference_content_url', $listing->reference_content_url);
        $this->setAttribute('reference_content_label', $listing->reference_content_label);
        $this->setAttribute('cta_cart_color', $listing->cta_cart_color);
        $this->setAttribute('cta_buy_color', $listing->cta_buy_color);
        $this->setAttribute('cta_whatsapp_color', $listing->cta_whatsapp_color);

        $this->setRelation('activeListing', $listing);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function analyticsImageUrls(): array
    {
        return PublicImagePaths::urls($this->analytics_image_paths);
    }

    public function normalizeJoinedPricingAttributes(): static
    {
        $attrs = $this->getAttributes();

        if (isset($attrs['currency']) && ! ($attrs['currency'] instanceof Currency)) {
            $this->setAttribute(
                'currency',
                Currency::tryFrom((string) $attrs['currency']) ?? Currency::Try,
            );
        }

        // Cached rows are rehydrated from attributesToArray(), so JSON columns
        // arrive already decoded and must be re-encoded for their casts.
        foreach ($attrs as $key => $value) {
            if (is_array($value) && $this->hasCast($key, ['array', 'json', 'object', 'collection'])) {
                $this->setAttribute($key, $value);
            }
        }

        return $this;
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SiteQuestion::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(SiteReview::class);
    }
}
