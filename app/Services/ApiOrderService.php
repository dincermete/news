<?php

namespace App\Services;

use App\Enums\CartStatus;
use App\Enums\ContentMode;
use App\Enums\PaymentMethod;
use App\Enums\ProductType;
use App\Enums\SiteStatus;
use App\Models\BillingProfile;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Site;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ApiOrderService
{
    public function __construct(
        protected CartCheckoutService $checkout,
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

        $price = $site->discount_price !== null
            ? (float) $site->discount_price
            : (float) $site->price;

        $cart = Cart::query()->create([
            'user_id' => $user->id,
            'status' => CartStatus::Active,
        ]);

        CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_type' => ProductType::SiteArticle,
            'site_id' => $site->id,
            'content_mode' => ContentMode::FileUpload,
            'content_payload' => $data['content_payload'] ?? null,
            'price' => $price,
            'currency' => $site->currency,
        ]);

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
