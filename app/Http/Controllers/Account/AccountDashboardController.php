<?php

namespace App\Http\Controllers\Account;

use App\Enums\AffiliateCommissionStatus;
use App\Enums\Currency;
use App\Enums\SpinPrizeType;
use App\Enums\WalletBalanceType;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Services\SeoMetaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccountDashboardController extends Controller
{
    public function __invoke(Request $request, SeoMetaService $seo): View
    {
        $user = $request->user();
        $wallet = Wallet::forUser($user, Currency::Try);

        $ordersQuery = $user->orders()->latest('id');
        $orderCount = (clone $ordersQuery)->count();
        $latestOrder = (clone $ordersQuery)->with('site')->first();
        $recentOrders = $user->orders()
            ->with('site')
            ->latest('id')
            ->limit(5)
            ->get();

        $spinWinnings = (float) DB::table('spin_wheel_spins')
            ->join('spin_wheel_prizes', 'spin_wheel_prizes.id', '=', 'spin_wheel_spins.spin_wheel_prize_id')
            ->where('spin_wheel_spins.user_id', $user->id)
            ->where('spin_wheel_prizes.type', SpinPrizeType::Balance->value)
            ->sum('spin_wheel_prizes.value');

        $affiliateCommissionTotal = (float) $user->affiliateCommissions()
            ->where('status', AffiliateCommissionStatus::Approved)
            ->sum('amount');

        return view('account.dashboard', [
            'meta' => $seo->forDefault(),
            'totalBalance' => $wallet->totalAvailableBalance(),
            'mainBalance' => $wallet->bucketBalance(WalletBalanceType::Main),
            'orderCount' => $orderCount,
            'latestOrder' => $latestOrder,
            'recentOrders' => $recentOrders,
            'spinWinnings' => round($spinWinnings, 2),
            'spinCredits' => $user->spinCreditBalance(),
            'affiliateCommissionRate' => $user->affiliate_commission_rate,
            'affiliateCommissionTotal' => round($affiliateCommissionTotal, 2),
        ]);
    }
}
