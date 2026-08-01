<?php

namespace Tests\Unit;

use App\Enums\Currency;
use App\Services\TcmbExchangeRateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TcmbExchangeRateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_parse_rates_reads_forex_selling_usd(): void
    {
        $xml = simplexml_load_string(<<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<Tarih_Date Date="07/27/2026" BulletinNo="2026/1">
  <Currency CrossOrder="0" Kod="USD" CurrencyCode="USD">
    <Unit>1</Unit>
    <Isim>ABD DOLARI</Isim>
    <CurrencyName>US DOLLAR</CurrencyName>
    <ForexBuying>33.5000</ForexBuying>
    <ForexSelling>34.1200</ForexSelling>
  </Currency>
</Tarih_Date>
XML);

        $parsed = app(TcmbExchangeRateService::class)->parseRates($xml);

        $this->assertSame(34.12, $parsed['rate']);
        $this->assertSame('2026-07-27', $parsed['rate_date']);
        $this->assertSame('ForexSelling', $parsed['field']);
        $this->assertSame(34.12, $parsed['currencies']['USD']);
    }

    public function test_fetch_and_store_upserts_usd_rate(): void
    {
        Http::fake([
            'www.tcmb.gov.tr/*' => Http::response(<<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<Tarih_Date Date="07/27/2026">
  <Currency CurrencyCode="USD">
    <Unit>1</Unit>
    <ForexBuying>33.5000</ForexBuying>
    <ForexSelling>34.1200</ForexSelling>
  </Currency>
</Tarih_Date>
XML, 200, ['Content-Type' => 'application/xml']),
        ]);

        $rates = app(TcmbExchangeRateService::class)->fetchAndStore();

        $this->assertCount(1, $rates);
        $this->assertSame(34.12, (float) $rates[0]->rate);
        $this->assertSame('2026-07-27', $rates[0]->rate_date?->toDateString());
        $this->assertSame(Currency::Usd, $rates[0]->quote_currency);
        $this->assertSame('tcmb', $rates[0]->source);
    }
}
