<?php

namespace App\Services;

use App\Contracts\SmsServiceInterface;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class NetgsmService implements SmsServiceInterface
{
    public function send(string $phone, string $message): bool
    {
        $username = (string) config('netgsm.username');
        $password = (string) config('netgsm.password');
        $header = (string) config('netgsm.header');

        if ($username === '' || $password === '' || $header === '') {
            throw new RuntimeException('Netgsm bilgileri yapılandırılmamış.');
        }

        $normalizedPhone = $this->normalizePhone($phone);

        try {
            $response = Http::withBasicAuth($username, $password)
                ->acceptJson()
                ->asJson()
                ->timeout(20)
                ->post((string) config('netgsm.send_url'), [
                    'msgheader' => $header,
                    'encoding' => (string) config('netgsm.encoding', 'TR'),
                    'messages' => [
                        [
                            'msg' => $message,
                            'no' => $normalizedPhone,
                        ],
                    ],
                ])
                ->throw();
        } catch (RequestException $exception) {
            throw new RuntimeException('Netgsm SMS isteği başarısız: '.$exception->getMessage(), previous: $exception);
        }

        $payload = $response->json();
        $code = (string) ($payload['code'] ?? $payload['status'] ?? $response->body());

        if ($this->isSuccessCode($code)) {
            return true;
        }

        throw new RuntimeException('Netgsm SMS gönderilemedi: '.$code);
    }

    protected function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '90') && strlen($digits) === 12) {
            return substr($digits, 2);
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return substr($digits, 1);
        }

        return $digits;
    }

    protected function isSuccessCode(string $code): bool
    {
        $code = trim($code);

        return $code === '00'
            || $code === '0'
            || str_starts_with($code, '00 ')
            || is_numeric($code) && (int) $code > 1000;
    }
}
