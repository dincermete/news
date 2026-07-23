<?php

namespace App\Services;

use App\Enums\Currency;
use App\Enums\SpinCreditTransactionType;
use App\Enums\SpinPrizeType;
use App\Enums\WalletBalanceType;
use App\Exceptions\InsufficientSpinCreditsException;
use App\Exceptions\NoAvailableSpinPrizesException;
use App\Models\SpinCreditTransaction;
use App\Models\SpinWheelPrize;
use App\Models\SpinWheelSpin;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SpinWheelService
{
    public function spin(User $user): SpinWheelSpin
    {
        return DB::transaction(function () use ($user): SpinWheelSpin {
            // Serialize concurrent spins for the same user.
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($user->spinCreditBalance() <= 0) {
                throw InsufficientSpinCreditsException::forUser();
            }

            SpinCreditTransaction::query()->create([
                'user_id' => $user->id,
                'type' => SpinCreditTransactionType::Debit,
                'amount' => 1,
                'reason' => 'spin_wheel_spin',
            ]);

            $prize = $this->pickPrize();

            if ($prize->stock !== null) {
                $lockedPrize = SpinWheelPrize::query()
                    ->whereKey($prize->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $lockedPrize->is_active || ($lockedPrize->stock !== null && $lockedPrize->stock <= 0)) {
                    throw NoAvailableSpinPrizesException::make();
                }

                $lockedPrize->decrement('stock');
                $prize = $lockedPrize->fresh();
            }

            $spin = SpinWheelSpin::query()->create([
                'user_id' => $user->id,
                'spin_wheel_prize_id' => $prize->id,
            ]);

            if ($prize->type === SpinPrizeType::Balance && filled($prize->value) && (float) $prize->value > 0) {
                Wallet::forUser($user, Currency::Try)->credit(
                    amount: (float) $prize->value,
                    reason: 'spin_wheel_prize',
                    balanceType: WalletBalanceType::SpinPrize,
                );
            }

            return $spin->load('prize');
        });
    }

    protected function pickPrize(): SpinWheelPrize
    {
        /** @var Collection<int, SpinWheelPrize> $prizes */
        $prizes = SpinWheelPrize::query()
            ->available()
            ->lockForUpdate()
            ->get();

        if ($prizes->isEmpty()) {
            throw NoAvailableSpinPrizesException::make();
        }

        $totalWeight = (int) $prizes->sum('probability_weight');

        if ($totalWeight <= 0) {
            throw NoAvailableSpinPrizesException::make();
        }

        $ticket = random_int(1, $totalWeight);
        $running = 0;

        foreach ($prizes as $prize) {
            $running += (int) $prize->probability_weight;

            if ($ticket <= $running) {
                return $prize;
            }
        }

        return $prizes->last();
    }
}
