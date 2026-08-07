<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\PaymentCompletionService;
use App\Services\PaytrService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaytrCallbackController extends Controller
{
    public function __invoke(Request $request, PaytrService $paytr, PaymentCompletionService $completion): Response
    {
        $payload = $request->all();

        if (! $paytr->verifyCallbackHash($payload)) {
            return response('PAYTR notification failed: bad hash', 400);
        }

        $merchantOid = (string) $request->input('merchant_oid');
        $status = (string) $request->input('status');

        $payment = Payment::query()
            ->where('paytr_merchant_oid', $merchantOid)
            ->with(['order', 'orderGroup.orders'])
            ->first();

        if (! $payment) {
            return response('OK');
        }

        if ($status === 'success') {
            $completion->complete($payment);

            return response('OK');
        }

        if ($payment->status !== PaymentStatus::Paid) {
            $payment->forceFill([
                'status' => PaymentStatus::Failed,
            ])->save();
        }

        return response('OK');
    }
}
