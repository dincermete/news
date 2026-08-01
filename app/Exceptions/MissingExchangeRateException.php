<?php

namespace App\Exceptions;

use RuntimeException;

class MissingExchangeRateException extends RuntimeException
{
    public static function forCurrency(string $currency): self
    {
        return new self("{$currency} için TCMB kuru bulunamadı. Lütfen daha sonra tekrar deneyin.");
    }
}
