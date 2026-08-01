<?php

namespace Tests\Unit;

use App\Enums\Currency;
use App\Exceptions\MissingExchangeRateException;
use App\Models\ExchangeRate;
use App\Services\CurrencyConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyConverterTest extends TestCase
{
    use RefreshDatabase;

    public function test_try_amounts_keep_rate_of_one(): void
    {
        $payload = app(CurrencyConverter::class)->convertToTry(100, Currency::Try);

        $this->assertSame(100.0, $payload['price']);
        $this->assertSame(Currency::Try, $payload['currency']);
        $this->assertSame(1.0, $payload['exchange_rate']);
        $this->assertNull($payload['exchange_rate_id']);
    }

    public function test_usd_converts_with_latest_rate(): void
    {
        $rate = ExchangeRate::query()->create([
            'base_currency' => Currency::Try,
            'quote_currency' => Currency::Usd,
            'rate' => 34.12,
            'rate_date' => now()->toDateString(),
            'source' => 'tcmb',
            'fetched_at' => now(),
        ]);

        $payload = app(CurrencyConverter::class)->convertToTry(50, Currency::Usd);

        $this->assertSame(1706.0, $payload['price']);
        $this->assertSame(Currency::Try, $payload['currency']);
        $this->assertSame(50.0, $payload['source_price']);
        $this->assertSame(Currency::Usd, $payload['source_currency']);
        $this->assertSame(34.12, $payload['exchange_rate']);
        $this->assertSame($rate->id, $payload['exchange_rate_id']);
    }

    public function test_missing_rate_throws(): void
    {
        $this->expectException(MissingExchangeRateException::class);

        app(CurrencyConverter::class)->convertToTry(10, Currency::Usd);
    }
}
