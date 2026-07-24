<?php

namespace App\View\Composers;

use App\Enums\Currency;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\SeoAnalysisStatus;
use App\Enums\SupportTicketStatus;
use App\Enums\WalletBalanceType;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Wallet;
use Illuminate\View\View;

class AccountLayoutComposer
{
    public function compose(View $view): void
    {
        $user = request()->user();

        if ($user === null) {
            return;
        }

        $wallet = Wallet::forUser($user, Currency::Try);

        $pendingNotifications = Payment::query()
            ->where('method', PaymentMethod::BankTransfer)
            ->where('status', PaymentStatus::Pending)
            ->where(function ($query) use ($user): void {
                $query->whereHas('order', fn ($order) => $order->where('user_id', $user->id))
                    ->orWhereHas('orderGroup', fn ($group) => $group->where('user_id', $user->id));
            })
            ->count();

        $invoiceCount = Invoice::query()
            ->where(function ($query) use ($user): void {
                $query->whereHas('order', fn ($order) => $order->where('user_id', $user->id))
                    ->orWhereHas('orderGroup', fn ($group) => $group->where('user_id', $user->id));
            })
            ->count();

        $view->with([
            'accountUser' => $user,
            'accountTotalBalance' => $wallet->totalAvailableBalance(),
            'accountMainBalance' => $wallet->bucketBalance(WalletBalanceType::Main),
            'accountNavCounts' => [
                'orders' => $user->orders()->count(),
                'invoices' => $invoiceCount,
                'favorites' => $user->favorites()->count(),
                'support' => $user->supportTickets()->where('status', '!=', SupportTicketStatus::Closed)->count(),
                'payments' => $pendingNotifications,
                'seo_analyses' => $user->seoAnalysisRequests()->where('status', '!=', SeoAnalysisStatus::Completed)->count(),
            ],
        ]);
    }
}
