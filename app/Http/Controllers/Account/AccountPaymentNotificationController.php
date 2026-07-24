<?php

namespace App\Http\Controllers\Account;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Payment;
use App\Services\SeoMetaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountPaymentNotificationController extends Controller
{
    public function __invoke(Request $request, SeoMetaService $seo): View
    {
        $user = $request->user();

        $pendingPayments = Payment::query()
            ->where('method', PaymentMethod::BankTransfer)
            ->where('status', PaymentStatus::Pending)
            ->where(function ($query) use ($user): void {
                $query->whereHas('order', fn ($order) => $order->where('user_id', $user->id))
                    ->orWhereHas('orderGroup', fn ($group) => $group->where('user_id', $user->id));
            })
            ->latest('id')
            ->get();

        $history = Payment::query()
            ->where('method', PaymentMethod::BankTransfer)
            ->whereIn('status', [PaymentStatus::Notified, PaymentStatus::Paid])
            ->where(function ($query) use ($user): void {
                $query->whereHas('order', fn ($order) => $order->where('user_id', $user->id))
                    ->orWhereHas('orderGroup', fn ($group) => $group->where('user_id', $user->id));
            })
            ->whereNotNull('bank_name')
            ->latest('id')
            ->limit(5)
            ->get();

        return view('account.payment-notification', [
            'meta' => $seo->forDefault(),
            'pendingPayments' => $pendingPayments,
            'history' => $history,
            'banks' => BankAccount::query()->active()->ordered()->get(),
        ]);
    }
}
