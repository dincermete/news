<?php

namespace App\Models;

use Database\Factories\FooterLinkDurationOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'months',
    'price_multiplier',
    'flat_price',
    'is_active',
])]
class FooterLinkDurationOption extends Model
{
    /** @use HasFactory<FooterLinkDurationOptionFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'months' => 'integer',
            'price_multiplier' => 'decimal:4',
            'flat_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function resolvePrice(float|string $basePrice): float
    {
        if ($this->flat_price !== null) {
            return (float) $this->flat_price;
        }

        return round((float) $basePrice * (float) ($this->price_multiplier ?? 1), 2);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
