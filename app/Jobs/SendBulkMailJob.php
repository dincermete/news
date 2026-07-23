<?php

namespace App\Jobs;

use App\Mail\BulkUserMessage;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendBulkMailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user,
        public string $subject,
        public string $message,
    ) {}

    public function handle(): void
    {
        Mail::to($this->user->email)->send(
            new BulkUserMessage($this->subject, $this->message),
        );
    }
}
