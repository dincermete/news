<?php

namespace Tests\Feature;

use App\Enums\CartStatus;
use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductType;
use App\Jobs\ProcessSuccessfulPayment;
use App\Models\BillingProfile;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Site;
use App\Models\User;
use App\Services\CartCheckoutService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CartCheckoutPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_pending_payment_for_order_group(): void
    {
        $user = User::factory()->create();
        $billing = BillingProfile::factory()->create(['user_id' => $user->id]);
        $cart = Cart::factory()->create(['user_id' => $user->id, 'status' => CartStatus::Active]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_type' => ProductType::SiteArticle,
            'site_id' => Site::factory(),
            'price' => 100,
            'currency' => Currency::Try,
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_type' => ProductType::SiteArticle,
            'site_id' => Site::factory(),
            'price' => 150,
            'currency' => Currency::Try,
        ]);

        $group = app(CartCheckoutService::class)->checkout(
            $cart,
            $billing,
            method: PaymentMethod::BankTransfer,
        );

        $payment = Payment::query()->where('order_group_id', $group->id)->first();

        $this->assertNotNull($payment);
        $this->assertNull($payment->order_id);
        $this->assertSame('245.00', $payment->amount);
        $this->assertSame(PaymentMethod::BankTransfer, $payment->method);
        $this->assertSame(PaymentStatus::Pending, $payment->status);
        $this->assertSame(2, $group->orders()->count());
    }

    public function test_paytr_callback_marks_all_order_group_orders_content_pending_and_creates_one_invoice(): void
    {
        Storage::fake('local');
        Pdf::shouldReceive('loadView')->andReturnSelf();
        Pdf::shouldReceive('output')->andReturn('%PDF-fake');

        config([
            'paytr.merchant_key' => 'test_key',
            'paytr.merchant_salt' => 'test_salt',
        ]);

        $user = User::factory()->create();
        $billing = BillingProfile::factory()->create(['user_id' => $user->id]);
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'site_id' => Site::factory(),
            'price' => 80,
            'currency' => Currency::Try,
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'site_id' => Site::factory(),
            'price' => 120,
            'currency' => Currency::Try,
        ]);

        $group = app(CartCheckoutService::class)->checkout($cart, $billing, method: PaymentMethod::Card);
        $payment = Payment::query()->where('order_group_id', $group->id)->firstOrFail();

        $payment->forceFill([
            'paytr_merchant_oid' => 'GRPTEST123',
        ])->save();

        $status = 'success';
        $totalAmount = '20000';
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

        $this->assertSame(
            2,
            Order::query()
                ->where('order_group_id', $group->id)
                ->where('status', OrderStatus::ContentPending)
                ->count(),
        );

        // ProcessSuccessfulPayment is queued; run it synchronously for invoice assertion
        (new ProcessSuccessfulPayment($payment->fresh()))->handle();

        $invoice = Invoice::query()->where('order_group_id', $group->id)->first();

        $this->assertNotNull($invoice);
        $this->assertNull($invoice->order_id);
        $this->assertSame(1, Invoice::query()->where('order_group_id', $group->id)->count());
        Storage::disk('local')->assertExists($invoice->pdf_path);
    }
}
