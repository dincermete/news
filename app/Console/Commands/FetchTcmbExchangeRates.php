<?php

namespace App\Console\Commands;

use App\Services\TcmbExchangeRateService;
use Illuminate\Console\Command;
use Throwable;

class FetchTcmbExchangeRates extends Command
{
    protected $signature = 'currency:fetch-tcmb';

    protected $description = 'Fetch and store TCMB daily exchange rates (USD/TRY ForexSelling)';

    public function handle(TcmbExchangeRateService $service): int
    {
        try {
            $rates = $service->fetchAndStore();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        foreach ($rates as $rate) {
            $this->info(sprintf(
                '%s: 1 %s = %s %s (%s)',
                strtoupper((string) $rate->source),
                $rate->quote_currency instanceof \BackedEnum ? $rate->quote_currency->value : $rate->quote_currency,
                $rate->rate,
                $rate->base_currency instanceof \BackedEnum ? $rate->base_currency->value : $rate->base_currency,
                $rate->rate_date?->toDateString(),
            ));
        }

        return self::SUCCESS;
    }
}
