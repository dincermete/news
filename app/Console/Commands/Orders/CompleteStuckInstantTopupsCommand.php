<?php

namespace App\Console\Commands\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductType;
use App\Models\Order;
use App\Services\PaymentCompletionService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('orders:complete-stuck-instant-topups {--dry-run : Preview without writing}')]
#[Description('Complete stuck Balance (Instant) orders that have a Paid payment')]
class CompleteStuckInstantTopupsCommand extends Command
{
    public function handle(PaymentCompletionService $completion): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $orders = Order::query()
            ->where('product_type', ProductType::Balance)
            ->whereIn('status', [OrderStatus::ContentPending, OrderStatus::PaymentPending])
            ->with(['payments', 'orderGroup.payments', 'user', 'walletTopupPackage'])
            ->orderBy('id')
            ->get()
            ->filter(function (Order $order): bool {
                $payments = $order->payments
                    ->merge($order->orderGroup?->payments ?? collect());

                return $payments->contains(
                    fn ($payment): bool => $payment->status === PaymentStatus::Paid
                );
            });

        if ($orders->isEmpty()) {
            $this->info('Takılı Instant bakiye siparişi bulunamadı.');

            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[dry-run] ' : '').$orders->count().' sipariş işlenecek.');

        $ok = 0;
        $failed = 0;

        foreach ($orders as $order) {
            if ($dryRun) {
                $this->line("Would complete order #{$order->id} (status={$order->status->value}, price={$order->price})");
                $ok++;

                continue;
            }

            try {
                if ($completion->recoverStuckInstantOrder($order)) {
                    $this->line("Completed order #{$order->id}");
                    $ok++;
                } else {
                    $this->warn("Skipped order #{$order->id}");
                    $failed++;
                }
            } catch (\Throwable $e) {
                $this->error("Order #{$order->id}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Done. ok={$ok} failed={$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
