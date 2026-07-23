<?php

namespace App\Http\Controllers\Account;

use App\Enums\AffiliateCommissionStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SeoMetaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AccountAffiliateController extends Controller
{
    public function __invoke(Request $request, SeoMetaService $seo): View
    {
        /** @var User $user */
        $user = $request->user();
        $user->ensureAffiliateCode();

        $referralUrl = url('/kayitol').'?ref='.$user->affiliate_code;

        $shareMessage = 'NewsTanıtım\'a bu linkten ücretsiz kayıt olun: '.$referralUrl;
        $whatsappShareUrl = 'https://wa.me/?text='.rawurlencode($shareMessage);
        $emailShareUrl = 'mailto:?subject='.rawurlencode('NewsTanıtım davet linki')
            .'&body='.rawurlencode($shareMessage);

        $monthStart = Carbon::now()->startOfMonth();

        $referralsTotal = User::query()
            ->where('referred_by_id', $user->id)
            ->count();

        $referralsThisMonth = User::query()
            ->where('referred_by_id', $user->id)
            ->where('created_at', '>=', $monthStart)
            ->count();

        $commissionTotal = (float) $user->affiliateCommissions()
            ->where('status', AffiliateCommissionStatus::Approved)
            ->sum('amount');

        $commissionThisMonth = (float) $user->affiliateCommissions()
            ->where('status', AffiliateCommissionStatus::Approved)
            ->where('created_at', '>=', $monthStart)
            ->sum('amount');

        $commissions = $user->affiliateCommissions()
            ->with(['referredUser', 'order'])
            ->latest('id')
            ->limit(50)
            ->get();

        return view('account.affiliate', [
            'meta' => $seo->forDefault(),
            'referralUrl' => $referralUrl,
            'whatsappShareUrl' => $whatsappShareUrl,
            'emailShareUrl' => $emailShareUrl,
            'commissionRate' => $user->affiliate_commission_rate,
            'referralsTotal' => $referralsTotal,
            'referralsThisMonth' => $referralsThisMonth,
            'commissionTotal' => round($commissionTotal, 2),
            'commissionThisMonth' => round($commissionThisMonth, 2),
            'commissions' => $commissions,
        ]);
    }
}
