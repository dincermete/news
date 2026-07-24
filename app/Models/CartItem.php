<?php

namespace App\Models;

use App\Enums\ContentMode;
use App\Enums\Currency;
use App\Enums\ProductType;
use Database\Factories\CartItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'cart_id',
    'product_type',
    'site_id',
    'site_bundle_id',
    'footer_link_duration_option_id',
    'article_word_package_id',
    'instagram_account_id',
    'instagram_story_price_id',
    'seo_package_id',
    'seo_package_duration_option_id',
    'backlink_package_id',
    'wallet_topup_package_id',
    'content_mode',
    'content_payload',
    'configured_at',
    'price',
    'currency',
])]
class CartItem extends Model
{
    /** @use HasFactory<CartItemFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'product_type' => 'site_article',
        'currency' => 'TRY',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'product_type' => ProductType::class,
            'content_mode' => ContentMode::class,
            'content_payload' => 'array',
            'configured_at' => 'datetime',
            'price' => 'decimal:2',
            'currency' => Currency::class,
            'created_at' => 'datetime',
        ];
    }

    public function isConfigured(): bool
    {
        return $this->configured_at !== null;
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function siteBundle(): BelongsTo
    {
        return $this->belongsTo(SiteBundle::class);
    }

    public function footerLinkDurationOption(): BelongsTo
    {
        return $this->belongsTo(FooterLinkDurationOption::class);
    }

    public function articleWordPackage(): BelongsTo
    {
        return $this->belongsTo(ArticleWordPackage::class);
    }

    public function instagramAccount(): BelongsTo
    {
        return $this->belongsTo(InstagramAccount::class);
    }

    public function instagramStoryPrice(): BelongsTo
    {
        return $this->belongsTo(InstagramStoryPrice::class);
    }

    public function seoPackage(): BelongsTo
    {
        return $this->belongsTo(SeoPackage::class);
    }

    public function seoPackageDurationOption(): BelongsTo
    {
        return $this->belongsTo(SeoPackageDurationOption::class);
    }

    public function backlinkPackage(): BelongsTo
    {
        return $this->belongsTo(BacklinkPackage::class);
    }

    public function walletTopupPackage(): BelongsTo
    {
        return $this->belongsTo(WalletTopupPackage::class);
    }
}
