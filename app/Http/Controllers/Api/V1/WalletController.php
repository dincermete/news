<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Currency;
use App\Enums\WalletBalanceType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\WalletBalanceResource;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Kullanıcının cüzdan kova bakiyelerini ve toplamı döner.
     */
    public function balance(Request $request): WalletBalanceResource
    {
        $wallet = Wallet::forUser($request->user(), Currency::Try);

        $buckets = [];

        foreach (WalletBalanceType::cases() as $type) {
            $buckets[$type->value] = $wallet->bucketBalance($type);
        }

        return new WalletBalanceResource([
            'currency' => $wallet->currency->value,
            'total' => $wallet->totalAvailableBalance(),
            'buckets' => $buckets,
        ]);
    }
}
