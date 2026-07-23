<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\WalletRefundRequested;
use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Jobs\InvoiceGenerationJob;
use App\Jobs\ProcessSuccessfulPayment;
use App\Listeners\RefundToWallet;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\PaytrService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_payments(): void
    {
        $admin = User::factory()->admin()->create();
        $payments = Payment::factory()->count(3)->create();

        $this->actingAs($admin);

        Livewire::test(ListPayments::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($payments);
    }

    public function test_wallet_transaction_cannot_be_updated(): void
    {
        $wallet = Wallet::factory()->create(['balance' => 100]);
        $transaction = WalletTransaction::factory()->create([
            'wallet_id' => $wallet->id,
            'amount' => 50,
            'reason' => 'order_refund',
        ]);

        $updated = $transaction->update([
            'amount' => 999,
            'reason' => 'tampered',
        ]);

        $this->assertFalse($updated);
        $this->assertDatabaseHas(WalletTransaction::class, [
            'id' => $transaction->id,
            'amount' => 50,
            'reason' => 'order_refund',
        ]);
    }

    public function test_wallet_transaction_cannot_be_deleted(): void
    {
        $transaction = WalletTransaction::factory()->create();

        $this->assertFalse($transaction->delete());
        $this->assertDatabaseHas(WalletTransaction::class, [
            'id' => $transaction->id,
        ]);
    }

    public function test_refund_listener_credits_wallet_with_order_refund_reason(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'price' => 175.50,
            'currency' => Currency::Try,
            'status' => OrderStatus::Refunded,
        ]);

        $listener = new RefundToWallet;
        $listener->handle(new WalletRefundRequested($order));

        $wallet = Wallet::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($wallet);
        $this->assertSame('175.50', $wallet->balance);
        $this->assertDatabaseHas(WalletTransaction::class, [
            'wallet_id' => $wallet->id,
            'type' => 'credit',
            'amount' => 175.50,
            'reason' => 'order_refund',
            'related_order_id' => $order->id,
        ]);
    }

    public function test_refund_action_dispatches_wallet_refund_event(): void
    {
        Event::fake([WalletRefundRequested::class]);

        $admin = User::factory()->admin()->create();
        $order = Order::factory()->status(OrderStatus::Review)->create();

        $this->actingAs($admin);

        Livewire::test(\App\Filament\Resources\Orders\Pages\ListOrders::class)
            ->callTableAction('refund', $order)
            ->assertNotified();

        Event::assertDispatched(WalletRefundRequested::class);
    }

    public function test_invoice_numbers_increment_sequentially(): void
    {
        Storage::fake('local');
        Pdf::shouldReceive('loadView')->andReturnSelf();
        Pdf::shouldReceive('output')->andReturn('%PDF-fake');

        $year = now()->format('Y');

        $first = Invoice::nextInvoiceNumber((int) $year);
        Invoice::factory()->create(['invoice_number' => $first, 'pdf_path' => 'invoices/'.$first.'.pdf']);

        $second = Invoice::nextInvoiceNumber((int) $year);
        Invoice::factory()->create(['invoice_number' => $second, 'pdf_path' => 'invoices/'.$second.'.pdf']);

        $third = Invoice::nextInvoiceNumber((int) $year);

        $this->assertSame("INV-{$year}-000001", $first);
        $this->assertSame("INV-{$year}-000002", $second);
        $this->assertSame("INV-{$year}-000003", $third);
    }

    public function test_approve_bank_transfer_marks_payment_and_order_and_dispatches_job(): void
    {
        Queue::fake();

        $admin = User::factory()->admin()->create();
        $order = Order::factory()->status(OrderStatus::PaymentPending)->create();
        $payment = Payment::factory()->pendingBankTransfer()->create([
            'order_id' => $order->id,
            'amount' => $order->price,
            'currency' => $order->currency,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListPayments::class)
            ->callTableAction('approveBankTransfer', $payment)
            ->assertNotified();

        $this->assertDatabaseHas(Payment::class, [
            'id' => $payment->id,
            'status' => PaymentStatus::Paid->value,
        ]);

        $this->assertDatabaseHas(Order::class, [
            'id' => $order->id,
            'status' => OrderStatus::ContentPending->value,
        ]);

        Queue::assertPushed(ProcessSuccessfulPayment::class);
    }

    public function test_paytr_callback_verifies_hash_and_marks_payment_paid(): void
    {
        Queue::fake();
        config([
            'paytr.merchant_key' => 'test_key',
            'paytr.merchant_salt' => 'test_salt',
        ]);

        $order = Order::factory()->status(OrderStatus::PaymentPending)->create(['price' => 100]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'amount' => 100,
            'method' => PaymentMethod::Card,
            'status' => PaymentStatus::Pending,
            'paytr_merchant_oid' => 'ORDTEST123',
        ]);

        $status = 'success';
        $totalAmount = '10000';
        $hash = base64_encode(hash_hmac(
            'sha256',
            $payment->paytr_merchant_oid.'test_salt'.$status.$totalAmount,
            'test_key',
            true,
        ));

        $response = $this->post(route('paytr.callback'), [
            'merchant_oid' => $payment->paytr_merchant_oid,
            'status' => $status,
            'total_amount' => $totalAmount,
            'hash' => $hash,
        ]);

        $response->assertOk();
        $response->assertSee('OK');

        $this->assertDatabaseHas(Payment::class, [
            'id' => $payment->id,
            'status' => PaymentStatus::Paid->value,
        ]);

        $this->assertDatabaseHas(Order::class, [
            'id' => $order->id,
            'status' => OrderStatus::ContentPending->value,
        ]);

        Queue::assertPushed(ProcessSuccessfulPayment::class);
    }

    public function test_paytr_callback_rejects_invalid_hash(): void
    {
        config([
            'paytr.merchant_key' => 'test_key',
            'paytr.merchant_salt' => 'test_salt',
        ]);

        $response = $this->post(route('paytr.callback'), [
            'merchant_oid' => 'ORDX',
            'status' => 'success',
            'total_amount' => '10000',
            'hash' => 'invalid',
        ]);

        $response->assertStatus(400);
    }

    public function test_invoice_generation_job_creates_invoice_pdf(): void
    {
        Storage::fake('local');

        $order = Order::factory()->create(['price' => 120]);
        $payment = Payment::factory()->paid()->create([
            'order_id' => $order->id,
            'amount' => 120,
        ]);

        (new InvoiceGenerationJob($payment))->handle();

        $invoice = Invoice::query()->where('order_id', $order->id)->first();

        $this->assertNotNull($invoice);
        $this->assertMatchesRegularExpression('/^INV-\d{4}-\d{6}$/', $invoice->invoice_number);
        Storage::disk('local')->assertExists($invoice->pdf_path);
    }

    public function test_paytr_service_requests_iframe_token(): void
    {
        config([
            'paytr.merchant_id' => '123',
            'paytr.merchant_key' => 'key',
            'paytr.merchant_salt' => 'salt',
            'paytr.ok_url' => 'http://localhost/paytr/ok',
            'paytr.fail_url' => 'http://localhost/paytr/fail',
            'paytr.test_mode' => '1',
        ]);

        Http::fake([
            'https://www.paytr.com/odeme/api/get-token' => Http::response([
                'status' => 'success',
                'token' => 'iframe-token-xyz',
            ]),
        ]);

        $order = Order::factory()->create(['price' => 50, 'currency' => Currency::Try]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'order_group_id' => null,
            'amount' => 50,
            'currency' => Currency::Try,
            'method' => PaymentMethod::Card,
            'status' => PaymentStatus::Pending,
            'paytr_merchant_oid' => null,
            'paytr_token' => null,
        ]);

        $result = app(PaytrService::class)->getIframeToken($payment, '1.2.3.4');

        $this->assertSame('iframe-token-xyz', $result['token']);
        $this->assertDatabaseHas(Payment::class, [
            'id' => $payment->id,
            'order_id' => $order->id,
            'paytr_token' => 'iframe-token-xyz',
            'method' => PaymentMethod::Card->value,
        ]);
    }
}
