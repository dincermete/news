<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WalletRefundRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(public Order $order) {}
}
