<?php

namespace App\Exceptions;

use RuntimeException;

class NoAvailableSpinPrizesException extends RuntimeException
{
    public static function make(): self
    {
        return new self('Çevrilecek uygun ödül bulunamadı.');
    }
}
