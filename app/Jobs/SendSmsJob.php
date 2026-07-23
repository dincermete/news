<?php

namespace App\Jobs;

use App\Contracts\SmsServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendSmsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $phone,
        public string $message,
    ) {}

    public function handle(SmsServiceInterface $sms): void
    {
        $sms->send($this->phone, $this->message);
    }
}
