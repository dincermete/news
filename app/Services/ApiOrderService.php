<?php

namespace App\Services;

use App\Enums\CartStatus;
use App\Enums\ContentMode;
use App\Enums\PaymentMethod;
use App\Enums\ProductType;
use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Models\BillingProfile;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\PromotionalListing;
use App\Models\Site;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ApiOrderService
{
    public function __construct(
        protected CartCheckoutService $checkout,
        protected CurrencyConverter $converter,
    ) {}

    /**
     * @param  array{site_id: int, billing_profile_id: int, coupon_code?: string|null, payment_method?: string|null, content_payload?: array<string, mixed>|null}  $data
     */
    public function create(User $user, array $data): Order
    {
        $site = Site::query()
            ->whereKey($data['site_id'])
            ->where('status', SiteStatus::Active)
            ->first();

        if ($site === null) {
            throw ValidationException::withMessages([
                'site_id' => 'Aktif site bulunamadı.',
            ]);
        }

        $listing = PromotionalListing::query()
            ->where('site_id', $site->id)
            ->where('type', PromotionalListingType::SiteArticle)
            ->where('status', SiteStatus::Active)
            ->first();

        if ($listing === null) {
            throw ValidationException::withMessages([
                'site_id' => 'Aktif tanıtım yazısı ürünü bulunamadı.',
            ]);
        }

        $billingProfile = BillingProfile::query()
            ->whereKey($data['billing_profile_id'])
            ->where('user_id', $user->id)
            ->first();

        if ($billingProfile === null) {
            throw ValidationException::withMessages([
                'billing_profile_id' => 'Fatura profili bulunamadı.',
            ]);
        }

        $method = PaymentMethod::tryFrom((string) ($data['payment_method'] ?? PaymentMethod::Card->value))
            ?? PaymentMethod::Card;

        $cart = Cart::query()->create([
            'user_id' => $user->id,
            'status' => CartStatus::Active,
        ]);

        CartItem::query()->create(array_merge([
            'cart_id' => $cart->id,
            'product_type' => ProductType::SiteArticle,
            'site_id' => $site->id,
            'content_mode' => ContentMode::FileUpload,
            'content_payload' => $data['content_payload'] ?? null,
        ], $this->converter->pricingPayload($listing->effectivePrice(), $listing->currency)));

        $group = $this->checkout->checkout(
            $cart->fresh(['items']),
            $billingProfile,
            $data['coupon_code'] ?? null,
            $method,
        );

        $order = $group->orders->first();

        if ($order === null) {
            throw new \RuntimeException('Sipariş oluşturulamadı.');
        }

        return $order->load(['site', 'publishedLink']);
    }
}
