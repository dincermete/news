<?php

namespace App\Http\Controllers;

use App\Enums\BillingProfileType;
use App\Enums\Currency;
use App\Enums\PaymentMethod;
use App\Exceptions\EmptyCartException;
use App\Exceptions\InsufficientWalletBalanceException;
use App\Exceptions\InvalidCouponException;
use App\Models\CartItem;
use App\Models\OrderGroup;
use App\Models\Payment;
use App\Models\Wallet;
use App\Services\BillingProfileResolver;
use App\Services\CartCheckoutService;
use App\Services\CartService;
use App\Services\PaymentDiscountCalculator;
use App\Services\PaytrService;
use App\Services\SeoMetaService;
use App\Services\WalletPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $carts,
        protected CartCheckoutService $checkout,
        protected PaymentDiscountCalculator $discounts,
        protected PaytrService $paytr,
        protected WalletPaymentService $walletPayments,
        protected SeoMetaService $seo,
        protected BillingProfileResolver $billingProfiles,
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $cart = $this->carts->resolveOrCreateCart($request);
        $cart->load(['items.site', 'items.siteBundle', 'items.footerLinkDurationOption', 'items.instagramAccount', 'items.instagramStoryPrice', 'items.seoPackage', 'items.seoPackageDurationOption']);

        if ($cart->items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->withErrors(['cart' => 'Sepetiniz boş.']);
        }

        if ($cart->items->contains(fn (CartItem $item): bool => ! $item->isConfigured())) {
            return redirect()
                ->route('cart.index')
                ->withErrors(['cart' => 'Ödemeye geçmeden önce sepetinizdeki tüm ürünleri yapılandırmalısınız.']);
        }

        $user = $request->user();

        $summary = $this->carts->summarize($cart, $this->carts->rememberedCoupon());
        $payable = $this->payableByMethod($summary['total']);
        $wallet = Wallet::forUser($user, Currency::Try);

        return view('checkout.show', [
            'meta' => $this->seo->forDefault(),
            'lineItems' => $cart->items,
            'summary' => $summary,
            'payable' => $payable,
            'walletBalance' => $wallet->totalAvailableBalance(),
            'bankTransferDiscountPercent' => (float) config('payment.bank_transfer_discount_percent', 0),
            'banks' => config('payment.banks', []),
            'postSubmitMethod' => null,
            'paytrToken' => null,
            'bankTransferPayment' => null,
            'orderGroup' => null,
            'payment' => null,
        ]);
    }

    public function process(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'billing_profile_id' => [
                'nullable',
                'integer',
                Rule::exists('billing_profiles', 'id')->where('user_id', $user->id),
            ],
            'billing_type' => ['nullable', Rule::enum(BillingProfileType::class)],
            'tax_id' => ['nullable', 'string', 'max:32'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'tax_office' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'contracts_accepted' => ['accepted'],
        ], [
            'contracts_accepted.accepted' => 'Devam etmek için sözleşmeleri onaylamalısınız.',
        ]);

        $cart = $this->carts->resolveOrCreateCart($request);
        $cart->load('items');

        if ($cart->items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->withErrors(['cart' => 'Sepetiniz boş.']);
        }

        if ($cart->items->contains(fn (CartItem $item): bool => ! $item->isConfigured())) {
            return redirect()
                ->route('cart.index')
                ->withErrors(['cart' => 'Ödemeye geçmeden önce sepetinizdeki tüm ürünleri yapılandırmalısınız.']);
        }

        $billingProfile = $this->billingProfiles->resolveOptional($request, $data);
        $method = PaymentMethod::from($data['payment_method']);
        $couponCode = $this->carts->rememberedCoupon();
        $summary = $this->carts->summarize($cart, $couponCode);
        $payableAmount = $this->discounts->applyDiscount($summary['total'], $method);

        if ($method === PaymentMethod::Balance) {
            $wallet = Wallet::forUser($user, Currency::Try);

            if ($wallet->totalAvailableBalance() + 0.00001 < $payableAmount) {
                return redirect()
                    ->route('checkout.show')
                    ->withErrors([
                        'payment_method' => InsufficientWalletBalanceException::make()->getMessage(),
                    ])
                    ->with('wallet_topup', true);
            }
        }

        try {
            $orderGroup = $this->checkout->checkout($cart, $billingProfile, $couponCode, $method);
        } catch (EmptyCartException $exception) {
            return redirect()->route('cart.index')->withErrors(['cart' => $exception->getMessage()]);
        } catch (InvalidCouponException $exception) {
            $this->carts->rememberCoupon(null);

            return redirect()
                ->route('checkout.show')
                ->withErrors(['coupon_code' => $exception->getMessage()]);
        }

        $this->carts->rememberCoupon(null);

        $payment = $orderGroup->payments->first();

        if ($payment === null) {
            throw ValidationException::withMessages([
                'payment_method' => 'Ödeme kaydı oluşturulamadı.',
            ]);
        }

        return match ($method) {
            PaymentMethod::Card => $this->handleCardPayment($payment, $orderGroup),
            PaymentMethod::BankTransfer => $this->handleBankTransfer($orderGroup, $payment),
            PaymentMethod::Balance => $this->handleWalletPayment($payment, $orderGroup),
        };
    }

    public function success(Request $request, OrderGroup $orderGroup): View
    {
        abort_unless((int) $orderGroup->user_id === (int) $request->user()->id, 403);

        $orderGroup->load(['orders.site', 'payments', 'billingProfile']);

        return view('checkout.success', [
            'meta' => $this->seo->forDefault(),
            'orderGroup' => $orderGroup,
        ]);
    }

    protected function handleCardPayment(Payment $payment, OrderGroup $orderGroup): View
    {
        $result = $this->paytr->getIframeToken($payment);

        return view('checkout.show', [
            ...$this->postSubmitViewData($orderGroup, PaymentMethod::Card),
            'paytrToken' => $result['token'],
        ]);
    }

    protected function handleBankTransfer(OrderGroup $orderGroup, Payment $payment): View
    {
        return view('checkout.show', [
            ...$this->postSubmitViewData($orderGroup, PaymentMethod::BankTransfer),
            'bankTransferPayment' => $payment,
        ]);
    }

    protected function handleWalletPayment(Payment $payment, OrderGroup $orderGroup): RedirectResponse
    {
        try {
            $this->walletPayments->payWithWallet($payment);
        } catch (InsufficientWalletBalanceException $exception) {
            return redirect()
                ->route('checkout.success', $orderGroup)
                ->withErrors([
                    'payment_method' => $exception->getMessage(),
                ])
                ->with('wallet_topup', true);
        }

        return redirect()->route('checkout.success', $orderGroup);
    }

    /**
     * @return array<string, mixed>
     */
    protected function postSubmitViewData(OrderGroup $orderGroup, PaymentMethod $method): array
    {
        $orderGroup->loadMissing(['user', 'payments', 'orders.site', 'orders.siteBundle', 'orders.footerLinkDurationOption', 'orders.instagramAccount', 'orders.instagramStoryPrice', 'orders.seoPackage', 'orders.seoPackageDurationOption']);

        $payment = $orderGroup->payments->firstWhere('method', $method) ?? $orderGroup->payments->first();

        return [
            'meta' => $this->seo->forDefault(),
            'lineItems' => $orderGroup->orders,
            'summary' => [
                'subtotal' => (float) $orderGroup->subtotal,
                'tier_discount' => (float) $orderGroup->discount_tier_amount,
                'coupon_discount' => (float) $orderGroup->coupon_discount_amount,
                'coupon' => null,
                'coupon_code' => null,
                'coupon_error' => null,
                'total' => (float) $orderGroup->total,
            ],
            'payable' => $this->payableByMethod((float) $orderGroup->total),
            'walletBalance' => Wallet::forUser($orderGroup->user, Currency::Try)->totalAvailableBalance(),
            'bankTransferDiscountPercent' => (float) config('payment.bank_transfer_discount_percent', 0),
            'banks' => config('payment.banks', []),
            'postSubmitMethod' => $method,
            'paytrToken' => null,
            'bankTransferPayment' => null,
            'orderGroup' => $orderGroup,
            'payment' => $payment,
        ];
    }

    /**
     * @return array<string, float>
     */
    protected function payableByMethod(float $total): array
    {
        return [
            PaymentMethod::Card->value => $this->discounts->applyDiscount($total, PaymentMethod::Card),
            PaymentMethod::BankTransfer->value => $this->discounts->applyDiscount($total, PaymentMethod::BankTransfer),
            PaymentMethod::Balance->value => $this->discounts->applyDiscount($total, PaymentMethod::Balance),
        ];
    }
}
