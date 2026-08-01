<?php

namespace App\Services;

use App\Enums\Currency;
use App\Exceptions\MissingExchangeRateException;
use App\Models\ExchangeRate;

class CurrencyConverter
{
    /**
     * @return array{price: float, currency: Currency, source_price: float, source_currency: Currency, exchange_rate: float, exchange_rate_id: ?int}
     */
    public function convertToTry(float $amount, Currency|string $from): array
    {
        $source = $from instanceof Currency ? $from : Currency::from(strtoupper((string) $from));
        $sourcePrice = round($amount, 2);

        if ($source === Currency::Try) {
            return [
                'price' => $sourcePrice,
                'currency' => Currency::Try,
                'source_price' => $sourcePrice,
                'source_currency' => Currency::Try,
                'exchange_rate' => 1.0,
                'exchange_rate_id' => null,
            ];
        }

        $rate = $this->latestRate($source);

        if ($rate === null) {
            throw MissingExchangeRateException::forCurrency($source->value);
        }

        $multiplier = (float) $rate->rate;

        return [
            'price' => round($sourcePrice * $multiplier, 2),
            'currency' => Currency::Try,
            'source_price' => $sourcePrice,
            'source_currency' => $source,
            'exchange_rate' => $multiplier,
            'exchange_rate_id' => $rate->id,
        ];
    }

    public function toTry(float $amount, Currency|string $from, ?ExchangeRate $rate = null): float
    {
        $source = $from instanceof Currency ? $from : Currency::from(strtoupper((string) $from));

        if ($source === Currency::Try) {
            return round($amount, 2);
        }

        $rate ??= $this->latestRate($source);

        if ($rate === null) {
            throw MissingExchangeRateException::forCurrency($source->value);
        }

        return round($amount * (float) $rate->rate, 2);
    }

    public function latestRate(Currency|string $currency): ?ExchangeRate
    {
        $code = $currency instanceof Currency ? $currency : Currency::from(strtoupper((string) $currency));

        if ($code === Currency::Try) {
            return null;
        }

        return ExchangeRate::latestFor($code);
    }

    /**
     * @return array{price: float, currency: Currency, source_price: float, source_currency: Currency, exchange_rate: float, exchange_rate_id: ?int}
     */
    public function pricingPayload(float $amount, Currency|string $from): array
    {
        return $this->convertToTry($amount, $from);
    }
}
