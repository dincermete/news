<?php

namespace App\Services;

use App\Enums\Currency;
use App\Models\ExchangeRate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use SimpleXMLElement;

class TcmbExchangeRateService
{
    /**
     * @return list<ExchangeRate>
     */
    public function fetchAndStore(?Carbon $asOf = null): array
    {
        $xml = $this->fetchXml();
        $rateDate = $this->resolveRateDate($xml, $asOf);
        $field = (string) config('currency.tcmb.rate_field', 'ForexSelling');
        $source = (string) config('currency.tcmb.source', 'tcmb');
        $base = (string) config('currency.base_currency', 'TRY');

        $stored = [];

        foreach ($xml->Currency as $currencyNode) {
            $code = strtoupper(trim((string) ($currencyNode['CurrencyCode'] ?? '')));

            if ($code === '' || $code === $base) {
                continue;
            }

            if (! in_array($code, [Currency::Usd->value], true)) {
                continue;
            }

            $raw = trim((string) ($currencyNode->{$field} ?? ''));

            if ($raw === '' || ! is_numeric(str_replace(',', '.', $raw))) {
                continue;
            }

            $unit = max(1, (int) ((string) ($currencyNode->Unit ?? '1')));
            $rate = round(((float) str_replace(',', '.', $raw)) / $unit, 6);

            if ($rate <= 0) {
                continue;
            }

            $stored[] = ExchangeRate::query()->updateOrCreate(
                [
                    'quote_currency' => $code,
                    'rate_date' => $rateDate->toDateString(),
                    'source' => $source,
                ],
                [
                    'base_currency' => $base,
                    'rate' => $rate,
                    'fetched_at' => now(),
                ],
            );
        }

        if ($stored === []) {
            throw new RuntimeException('TCMB yanıtında geçerli USD kuru bulunamadı.');
        }

        return $stored;
    }

    public function fetchXml(): SimpleXMLElement
    {
        $url = (string) config('currency.tcmb.url');
        $timeout = (int) config('currency.tcmb.timeout', 15);

        $response = Http::timeout($timeout)
            ->accept('application/xml, text/xml, */*')
            ->get($url);

        if (! $response->successful()) {
            throw new RuntimeException('TCMB kur servisine ulaşılamadı (HTTP '.$response->status().').');
        }

        $body = $response->body();
        $xml = @simplexml_load_string($body);

        if (! $xml instanceof SimpleXMLElement) {
            Log::warning('TCMB XML parse failed', ['body_preview' => mb_substr($body, 0, 200)]);

            throw new RuntimeException('TCMB kur XML yanıtı okunamadı.');
        }

        return $xml;
    }

    /**
     * @return array{rate: float, rate_date: string, field: string, currencies: array<string, float>}
     */
    public function parseRates(SimpleXMLElement $xml): array
    {
        $field = (string) config('currency.tcmb.rate_field', 'ForexSelling');
        $currencies = [];

        foreach ($xml->Currency as $currencyNode) {
            $code = strtoupper(trim((string) ($currencyNode['CurrencyCode'] ?? '')));
            $raw = trim((string) ($currencyNode->{$field} ?? ''));

            if ($code === '' || $raw === '' || ! is_numeric(str_replace(',', '.', $raw))) {
                continue;
            }

            $unit = max(1, (int) ((string) ($currencyNode->Unit ?? '1')));
            $currencies[$code] = round(((float) str_replace(',', '.', $raw)) / $unit, 6);
        }

        $rateDate = $this->resolveRateDate($xml);

        return [
            'rate' => $currencies[Currency::Usd->value] ?? 0.0,
            'rate_date' => $rateDate->toDateString(),
            'field' => $field,
            'currencies' => $currencies,
        ];
    }

    protected function resolveRateDate(SimpleXMLElement $xml, ?Carbon $asOf = null): Carbon
    {
        $timezone = (string) config('currency.timezone', 'Europe/Istanbul');
        $attr = trim((string) ($xml['Date'] ?? ''));

        if ($attr !== '') {
            try {
                return Carbon::createFromFormat('m/d/Y', $attr, $timezone)->startOfDay();
            } catch (\Throwable) {
                // fall through
            }

            try {
                return Carbon::parse($attr, $timezone)->startOfDay();
            } catch (\Throwable) {
                // fall through
            }
        }

        return ($asOf ?? now($timezone))->startOfDay();
    }
}
