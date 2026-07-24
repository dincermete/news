<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\SiteStatus;
use Database\Factories\SeoPackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'description',
    'keyword_count',
    'monthly_price',
    'currency',
    'features',
    'is_featured',
    'status',
    'sort_order',
])]
class SeoPackage extends Model
{
    /** @use HasFactory<SeoPackageFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency' => 'TRY',
        'is_featured' => false,
        'status' => 'draft',
        'sort_order' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'keyword_count' => 'integer',
            'monthly_price' => 'decimal:2',
            'currency' => Currency::class,
            'features' => 'array',
            'is_featured' => 'boolean',
            'status' => SiteStatus::class,
            'sort_order' => 'integer',
        ];
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
