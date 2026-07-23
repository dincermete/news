<?php

namespace App\Exceptions;

use InvalidArgumentException;

class InvalidOrderProductException extends InvalidArgumentException
{
    public static function make(string $message): self
    {
        return new self($message);
    }
}
