<?php

namespace App\Http\Controllers;

use App\Enums\BillingProfileType;
use App\Enums\PaymentMethod;
use App\Exceptions\EmptyCartException;
use App\Exceptions\InsufficientWalletBalanceException;
use App\Exceptions\InvalidCouponException;
use App\Enums\Currency;
use App\Models\BillingProfile;
use App\Models\OrderGroup;
use App\Models\Payment;
use App\Models\Wallet;
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
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $cart = $this->carts->resolveOrCreateCart($request);
        $cart->load(['items.site']);

        if ($cart->items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->withErrors(['cart' => 'Sepetiniz boş.']);
        }

        $user = $request->user();
        $billingProfiles = BillingProfile::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->get();

        $summary = $this->carts->summarize($cart, $this->carts->rememberedCoupon());
        $payable = [
            PaymentMethod::Card->value => $this->discounts->applyDiscount($summary['total'], PaymentMethod::Card),
            PaymentMethod::BankTransfer->value => $this->discounts->applyDiscount($summary['total'], PaymentMethod::BankTransfer),
            PaymentMethod::Balance->value => $this->discounts->applyDiscount($summary['total'], PaymentMethod::Balance),
        ];

        return view('checkout.show', [
            'meta' => $this->seo->forDefault(),
            'cart' => $cart,
            'summary' => $summary,
            'billingProfiles' => $billingProfiles,
            'payable' => $payable,
            'bankTransferDiscountPercent' => (float) config('payment.bank_transfer_discount_percent', 0),
            'banks' => config('payment.banks', []),
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
            'company_name' => ['nullable', 'string', 'max:255'],
            'address' => ['required_without:billing_profile_id', 'string', 'max:2000'],
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

        $billingProfile = $this->resolveBillingProfile($request, $data);
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

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveBillingProfile(Request $request, array $data): BillingProfile
    {
        if (filled($data['billing_profile_id'] ?? null)) {
            return BillingProfile::query()
                ->whereKey($data['billing_profile_id'])
                ->where('user_id', $request->user()->id)
                ->firstOrFail();
        }

        $type = BillingProfileType::from($data['billing_type']);

        return BillingProfile::query()->create([
            'user_id' => $request->user()->id,
            'type' => $type,
            'tax_id' => $data['tax_id'],
            'company_name' => $type === BillingProfileType::Corporate ? ($data['company_name'] ?? null) : null,
            'address' => $data['address'],
            'tax_office' => $type === BillingProfileType::Corporate ? ($data['tax_office'] ?? null) : null,
        ]);
    }

    protected function handleCardPayment(Payment $payment, OrderGroup $orderGroup): View
    {
        $result = $this->paytr->getIframeToken($payment);

        return view('checkout.paytr', [
            'meta' => $this->seo->forDefault(),
            'orderGroup' => $orderGroup,
            'token' => $result['token'],
            'payment' => $result['payment'],
        ]);
    }

    protected function handleBankTransfer(OrderGroup $orderGroup, Payment $payment): View
    {
        return view('checkout.bank-transfer', [
            'meta' => $this->seo->forDefault(),
            'orderGroup' => $orderGroup->load(['orders.site']),
            'payment' => $payment,
            'banks' => config('payment.banks', []),
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
}
