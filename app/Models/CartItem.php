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
    'content_mode',
    'content_payload',
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
            'price' => 'decimal:2',
            'currency' => Currency::class,
            'created_at' => 'datetime',
        ];
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
}
