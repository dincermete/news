<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Models\Concerns\HasStorefrontSeo;
use App\Observers\PromotionalListingObserver;
use App\Support\PublicImagePaths;
use Database\Factories\PromotionalListingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ObservedBy([PromotionalListingObserver::class])]
#[Fillable([
    'site_id',
    'type',
    'name',
    'price',
    'discount_price',
    'currency',
    'status',
    'short_description',
    'description',
    'delivery_details',
    'estimated_delivery',
    'reference_content_url',
    'reference_content_label',
    'reference_content_image_paths',
    'cta_cart_color',
    'cta_buy_color',
    'cta_whatsapp_color',
    'public_path',
    'meta_title',
    'meta_description',
    'meta_keywords',
    'og_image',
])]
class PromotionalListing extends Model
{
    /** @use HasFactory<PromotionalListingFactory> */
    use HasFactory;

    use HasStorefrontSeo;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency' => 'TRY',
        'status' => 'draft',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PromotionalListingType::class,
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'currency' => Currency::class,
            'status' => SiteStatus::class,
            'reference_content_image_paths' => 'array',
        ];
    }

    /**
     * Public URLs for the reference content screenshots shown in the lightbox.
     *
     * @return list<string>
     */
    public function referenceContentImageUrls(): array
    {
        return PublicImagePaths::urls($this->reference_content_image_paths);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function relatedListings(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'promotional_listing_related',
            'promotional_listing_id',
            'related_listing_id',
        )->withPivot('sort_order')->withTimestamps();
    }

    public function recommendedListings(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'promotional_listing_recommended',
            'promotional_listing_id',
            'recommended_listing_id',
        )->withPivot('sort_order')->withTimestamps();
    }

    public function getOptionLabelAttribute(): string
    {
        if (filled($this->name)) {
            return (string) $this->name;
        }

        $domain = $this->site?->domain ?? ('#'.$this->site_id);
        $type = $this->type?->getLabel() ?? $this->type?->value;

        return $domain.($type ? ' — '.$type : '');
    }

    public function effectivePrice(): float
    {
        if ($this->discount_price !== null && (float) $this->discount_price < (float) $this->price) {
            return round((float) $this->discount_price, 2);
        }

        return round((float) $this->price, 2);
    }

    public function hasDiscount(): bool
    {
        return $this->discount_price !== null
            && (float) $this->discount_price < (float) $this->price;
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActiveForSale(Builder $query): Builder
    {
        return $query
            ->where('status', SiteStatus::Active)
            ->whereHas('site', fn (Builder $sites): Builder => $sites->where('status', SiteStatus::Active));
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOfType(Builder $query, PromotionalListingType $type): Builder
    {
        return $query->where('type', $type);
    }
}
