<?php

namespace App\Services;

use App\Enums\Currency;
use App\Enums\WalletBalanceType;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Validation\ValidationException;

class WalletAdjustmentService
{
    public function adjust(
        User $customer,
        WalletBalanceType $type,
        float $amount,
        string $reason,
        User $admin,
    ): WalletTransaction {
        if (round($amount, 2) === 0.0) {
            throw ValidationException::withMessages([
                'amount' => 'Tutar sıfır olamaz.',
            ]);
        }

        $reason = trim($reason);

        if ($reason === '') {
            throw ValidationException::withMessages([
                'reason' => 'Sebep zorunludur.',
            ]);
        }

        $wallet = Wallet::forUser($customer, Currency::Try);
        $auditReason = sprintf('manual_adjustment: %s: %s', $admin->name, $reason);

        if ($amount > 0) {
            return $wallet->credit($amount, $auditReason, balanceType: $type);
        }

        return $wallet->debit(abs($amount), $auditReason, balanceType: $type);
    }
}
