<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\WalletTopupPackage;
use App\Services\CartService;
use App\Services\SeoMetaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountWalletTopupController extends Controller
{
    public function __invoke(Request $request, SeoMetaService $seo): View
    {
        $packages = WalletTopupPackage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('amount')
            ->get();

        return view('account.wallet-topup', [
            'meta' => $seo->forDefault(),
            'packages' => $packages,
            'minAmount' => CartService::MIN_WALLET_TOPUP_AMOUNT,
        ]);
    }
}
