<?php

namespace App\Http\Controllers;

use App\Enums\BillingProfileType;
use App\Enums\Currency;
use App\Enums\PaymentMethod;
use App\Enums\ProductType;
use App\Exceptions\EmptyCartException;
use App\Exceptions\InsufficientWalletBalanceException;
use App\Exceptions\InvalidCouponException;
use App\Exceptions\MissingExchangeRateException;
use App\Models\BankAccount;
use App\Models\CartItem;
use App\Models\Order;
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
use Illuminate\Database\Eloquent\Collection;
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
        $cart->load(['items.site', 'items.siteBundle', 'items.footerLinkDurationOption', 'items.seoPackage', 'items.seoPackageDurationOption', 'items.backlinkPackage']);

        if ($cart->items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->withErrors(['cart' => 'Sepetiniz boş.']);
        }

        $user = $request->user();

        try {
            $summary = $this->carts->summarize($cart, $this->carts->rememberedCoupon());
        } catch (MissingExchangeRateException $exception) {
            return redirect()
                ->route('cart.index')
                ->with('error', $exception->getMessage());
        }

        $payable = $this->payableByMethod($summary['total']);
        $wallet = Wallet::forUser($user, Currency::Try);

        return view('checkout.show', [
            'meta' => $this->seo->forDefault(),
            'lineItems' => $cart->items,
            'summary' => $summary,
            'payable' => $payable,
            'walletBalance' => $wallet->totalAvailableBalance(),
            'bankTransferDiscountPercent' => (float) config('payment.bank_transfer_discount_percent', 0),
            'banks' => $this->activeBanks(),
            'hasWalletTopupItem' => $cart->items->contains(fn (CartItem $item): bool => $item->product_type === ProductType::Balance),
            'billingProfiles' => $user->billingProfiles()->latest('id')->get(),
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
            'billing_type' => ['required_without:billing_profile_id', Rule::enum(BillingProfileType::class)],
            'tax_id' => ['required_without:billing_profile_id', 'string', 'max:32'],
            'company_name' => [
                'nullable',
                'required_if:billing_type,'.BillingProfileType::Corporate->value,
                'string',
                'max:255',
            ],
            'tax_office' => [
                'nullable',
                'required_if:billing_type,'.BillingProfileType::Corporate->value,
                'string',
                'max:255',
            ],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'contracts_accepted' => ['accepted'],
        ], [
            'contracts_accepted.accepted' => 'Devam etmek için sözleşmeleri onaylamalısınız.',
            'billing_type.required_without' => 'Fatura tipi zorunludur.',
            'tax_id.required_without' => 'TCKN / VKN zorunludur.',
            'company_name.required_if' => 'Kurumsal faturalarda ünvan zorunludur.',
            'tax_office.required_if' => 'Kurumsal faturalarda vergi dairesi zorunludur.',
        ]);

        $cart = $this->carts->resolveOrCreateCart($request);
        $cart->load('items');

        if ($cart->items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->withErrors(['cart' => 'Sepetiniz boş.']);
        }

        $billingProfile = $this->billingProfiles->resolveRequired($request, $data);
        $method = PaymentMethod::from($data['payment_method']);
        $couponCode = $this->carts->rememberedCoupon();

        try {
            $summary = $this->carts->summarize($cart, $couponCode);
        } catch (MissingExchangeRateException $exception) {
            return redirect()
                ->route('cart.index')
                ->with('error', $exception->getMessage());
        }

        $payableAmount = $this->discounts->applyDiscount($summary['total'], $method);

        $hasWalletTopupItem = $cart->items->contains(fn (CartItem $item): bool => $item->product_type === ProductType::Balance);

        if ($method === PaymentMethod::Balance && $hasWalletTopupItem) {
            return redirect()
                ->route('checkout.show')
                ->withErrors([
                    'payment_method' => 'Sepetinizde bakiye paketi varken bakiye ile ödeme yapamazsınız.',
                ]);
        }

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
        } catch (MissingExchangeRateException $exception) {
            return redirect()->route('cart.index')->with('error', $exception->getMessage());
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
        $orderGroup->loadMissing(['user', 'payments', 'orders.site', 'orders.siteBundle', 'orders.footerLinkDurationOption', 'orders.seoPackage', 'orders.seoPackageDurationOption', 'orders.backlinkPackage']);

        $payment = $orderGroup->payments->firstWhere('method', $method) ?? $orderGroup->payments->first();

        return [
            'meta' => $this->seo->forDefault(),
            'lineItems' => $orderGroup->orders,
            'summary' => [
                'subtotal' => (float) $orderGroup->subtotal,
                'tier_discount' => (float) $orderGroup->discount_tier_amount,
                'coupon_discount' => (float) $orderGroup->coupon_discount_amount,
                'vat_amount' => (float) ($orderGroup->vat_amount ?? 0),
                'vat_rate' => (float) config('payment.vat_rate', 20),
                'coupon' => null,
                'coupon_code' => null,
                'coupon_error' => null,
                'total' => (float) $orderGroup->total,
            ],
            'payable' => $this->payableByMethod((float) $orderGroup->total),
            'walletBalance' => Wallet::forUser($orderGroup->user, Currency::Try)->totalAvailableBalance(),
            'bankTransferDiscountPercent' => (float) config('payment.bank_transfer_discount_percent', 0),
            'banks' => $this->activeBanks(),
            'hasWalletTopupItem' => $orderGroup->orders->contains(fn (Order $order): bool => $order->product_type === ProductType::Balance),
            'billingProfiles' => $orderGroup->user->billingProfiles()->latest('id')->get(),
            'postSubmitMethod' => $method,
            'paytrToken' => null,
            'bankTransferPayment' => null,
            'orderGroup' => $orderGroup,
            'payment' => $payment,
        ];
    }

    /**
     * @return Collection<int, BankAccount>
     */
    protected function activeBanks(): Collection
    {
        return BankAccount::query()->active()->ordered()->get();
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
