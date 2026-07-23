<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BankTransferNotificationController extends Controller
{
    public function __invoke(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'payment_id' => ['nullable', 'integer', 'exists:payments,id'],
            'bank_name' => ['required', 'string', 'max:255'],
            'payer_name' => ['required', 'string', 'max:255'],
            'payer_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = $request->user();

        if ($user === null) {
            throw ValidationException::withMessages([
                'payment_id' => 'Havale bildirimi için giriş yapmalısınız.',
            ]);
        }

        $paymentQuery = Payment::query()
            ->where('method', PaymentMethod::BankTransfer)
            ->where('status', PaymentStatus::Pending)
            ->where(function ($query) use ($user): void {
                $query->whereHas('order', fn ($orderQuery) => $orderQuery->where('user_id', $user->id))
                    ->orWhereHas('orderGroup', fn ($groupQuery) => $groupQuery->where('user_id', $user->id));
            })
            ->latest('id');

        if (filled($data['payment_id'] ?? null)) {
            $paymentQuery->whereKey($data['payment_id']);
        }

        $payment = $paymentQuery->first();

        if (! $payment) {
            throw new NotFoundHttpException('Bekleyen havale ödemesi bulunamadı.');
        }

        $payment->forceFill([
            'bank_name' => $data['bank_name'],
            'payer_name' => $data['payer_name'],
            'payer_note' => $data['payer_note'] ?? null,
            'status' => PaymentStatus::Notified,
        ])->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Havale bildiriminiz alındı.',
                'payment_id' => $payment->id,
                'status' => $payment->status->value,
            ]);
        }

        $orderGroupId = $payment->order_group_id;

        if ($orderGroupId) {
            return redirect()
                ->route('checkout.success', $orderGroupId)
                ->with('status', 'Havale bildiriminiz alındı.');
        }

        return redirect()
            ->route('account.orders')
            ->with('status', 'Havale bildiriminiz alındı.');
    }
}
