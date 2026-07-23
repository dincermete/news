<?php

namespace App\Models;

use App\Enums\SpinPrizeType;
use Database\Factories\SpinWheelPrizeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'type',
    'value',
    'probability_weight',
    'stock',
    'is_active',
])]
class SpinWheelPrize extends Model
{
    /** @use HasFactory<SpinWheelPrizeFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'probability_weight' => 1,
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => SpinPrizeType::class,
            'value' => 'decimal:2',
            'probability_weight' => 'integer',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function spins(): HasMany
    {
        return $this->hasMany(SpinWheelSpin::class);
    }

    /**
     * @param  Builder<SpinWheelPrize>  $query
     * @return Builder<SpinWheelPrize>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query->whereNull('stock')
                    ->orWhere('stock', '>', 0);
            });
    }
}
