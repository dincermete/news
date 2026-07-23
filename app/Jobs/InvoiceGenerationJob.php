<?php

namespace App\Jobs;

use App\Models\BillingProfile;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class InvoiceGenerationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Payment $payment) {}

    public function handle(): void
    {
        $payment = $this->payment->loadMissing([
            'order.user',
            'order.site',
            'orderGroup.user',
            'orderGroup.orders.site',
            'orderGroup.billingProfile',
        ]);

        if ($payment->order_group_id !== null) {
            $this->generateForOrderGroup($payment);

            return;
        }

        $this->generateForOrder($payment);
    }

    protected function generateForOrder(Payment $payment): void
    {
        $order = $payment->order;

        if (! $order) {
            return;
        }

        if (Invoice::query()->where('order_id', $order->id)->whereNull('order_group_id')->exists()) {
            return;
        }

        $billingProfile = BillingProfile::query()
            ->where('user_id', $order->user_id)
            ->latest()
            ->first();

        $this->storeInvoice(
            payment: $payment,
            orders: collect([$order]),
            billingProfile: $billingProfile,
            orderId: $order->id,
            orderGroupId: null,
            customerName: $order->user?->name,
            customerEmail: $order->user?->email,
        );
    }

    protected function generateForOrderGroup(Payment $payment): void
    {
        $orderGroup = $payment->orderGroup;

        if (! $orderGroup) {
            return;
        }

        if (Invoice::query()->where('order_group_id', $orderGroup->id)->exists()) {
            return;
        }

        $orders = $orderGroup->orders;

        if ($orders->isEmpty()) {
            return;
        }

        $billingProfile = $orderGroup->billingProfile
            ?? BillingProfile::query()
                ->where('user_id', $orderGroup->user_id)
                ->latest()
                ->first();

        $this->storeInvoice(
            payment: $payment,
            orders: $orders,
            billingProfile: $billingProfile,
            orderId: null,
            orderGroupId: $orderGroup->id,
            customerName: $orderGroup->user?->name,
            customerEmail: $orderGroup->user?->email,
        );
    }

    /**
     * @param  Collection<int, Order>  $orders
     */
    protected function storeInvoice(
        Payment $payment,
        Collection $orders,
        ?BillingProfile $billingProfile,
        ?int $orderId,
        ?int $orderGroupId,
        ?string $customerName,
        ?string $customerEmail,
    ): void {
        $invoiceNumber = Invoice::nextInvoiceNumber();
        $pdfPath = 'invoices/'.$invoiceNumber.'.pdf';

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoiceNumber' => $invoiceNumber,
            'orders' => $orders,
            'order' => $orders->first(),
            'payment' => $payment,
            'billingProfile' => $billingProfile,
            'customerName' => $customerName,
            'customerEmail' => $customerEmail,
            'issuedAt' => now(),
        ]);

        Storage::disk('local')->put($pdfPath, $pdf->output());

        Invoice::query()->create([
            'order_id' => $orderId,
            'order_group_id' => $orderGroupId,
            'invoice_number' => $invoiceNumber,
            'pdf_path' => $pdfPath,
            'billing_profile_id' => $billingProfile?->id,
        ]);
    }
}
