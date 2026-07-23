<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientSpinCreditsException extends RuntimeException
{
    public static function forUser(): self
    {
        return new self('Yetersiz çark kredisi.');
    }
}
