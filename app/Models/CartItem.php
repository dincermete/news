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
    'seo_package_id',
    'seo_package_duration_option_id',
    'backlink_package_id',
    'wallet_topup_package_id',
    'content_mode',
    'content_payload',
    'configured_at',
    'price',
    'currency',
    'source_price',
    'source_currency',
    'exchange_rate',
    'exchange_rate_id',
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
            'source_price' => 'decimal:2',
            'source_currency' => Currency::class,
            'exchange_rate' => 'decimal:6',
            'created_at' => 'datetime',
        ];
    }

    public function exchangeRateRecord(): BelongsTo
    {
        return $this->belongsTo(ExchangeRate::class, 'exchange_rate_id');
    }

    public function hasForeignSourceCurrency(): bool
    {
        $source = $this->source_currency ?? $this->currency;

        return $source instanceof Currency && $source !== Currency::Try;
    }

    public function isConfigured(): bool
    {
        return $this->configured_at !== null;
    }

    public function displayTitle(): string
    {
        return $this->site?->domain
            ?? $this->siteBundle?->name
            ?? $this->seoPackage?->name
            ?? $this->backlinkPackage?->name
            ?? ($this->walletTopupPackage
                ? 'Bakiye · '.number_format((float) $this->walletTopupPackage->amount, 2, ',', '.').' '.($this->currency?->value ?? 'TRY')
                : null)
            ?? $this->product_type?->getLabel()
            ?? 'Ürün #'.$this->id;
    }

    public function durationLabel(): ?string
    {
        return $this->footerLinkDurationOption?->name
            ?? $this->seoPackageDurationOption?->name;
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
