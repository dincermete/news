<?php

namespace App\Models;

use App\Enums\Currency;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'base_currency',
    'quote_currency',
    'rate',
    'rate_date',
    'source',
    'fetched_at',
])]
class ExchangeRate extends Model
{
    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'base_currency' => 'TRY',
        'source' => 'tcmb',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_currency' => Currency::class,
            'quote_currency' => Currency::class,
            'rate' => 'decimal:6',
            'rate_date' => 'date',
            'fetched_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<ExchangeRate>  $query
     * @return Builder<ExchangeRate>
     */
    public function scopeForQuote(Builder $query, Currency|string $currency): Builder
    {
        $code = $currency instanceof Currency ? $currency->value : $currency;

        return $query->where('quote_currency', $code);
    }

    public static function latestFor(Currency|string $currency, ?string $source = null): ?self
    {
        $source ??= (string) config('currency.tcmb.source', 'tcmb');

        return static::query()
            ->forQuote($currency)
            ->where('source', $source)
            ->orderByDesc('rate_date')
            ->orderByDesc('id')
            ->first();
    }
}
