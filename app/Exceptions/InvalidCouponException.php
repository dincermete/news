<?php

namespace App\Exceptions;

use RuntimeException;

class InvalidCouponException extends RuntimeException
{
    public static function make(string $message): self
    {
        return new self($message);
    }
}
