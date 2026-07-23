<?php

namespace App\Services;

use RuntimeException;

class WhatsAppRedirectService
{
    public function buildLink(string $context = ''): string
    {
        $number = preg_replace('/\D+/', '', (string) config('whatsapp.support_number')) ?? '';

        if ($number === '') {
            throw new RuntimeException('WhatsApp destek numarası yapılandırılmamış.');
        }

        $url = 'https://wa.me/'.$number;

        if ($context !== '') {
            $url .= '?text='.rawurlencode($context);
        }

        return $url;
    }
}
