<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientWalletBalanceException extends RuntimeException
{
    public static function make(): self
    {
        return new self('Bakiyeniz bu siparişi karşılamaya yeterli değil');
    }
}
