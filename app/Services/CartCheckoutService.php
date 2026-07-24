<?php

namespace App\Services;

use App\Enums\CartStatus;
use App\Enums\ContentSource;
use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\EmptyCartException;
use App\Exceptions\InvalidCouponException;
use App\Models\BillingProfile;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\DiscountTier;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class CartCheckoutService
{
    public function __construct(
        protected PaymentDiscountCalculator $discountCalculator,
    ) {}

    public function checkout(
        Cart $cart,
        ?BillingProfile $billingProfile = null,
        ?string $couponCode = null,
        PaymentMethod $method = PaymentMethod::Card,
    ): OrderGroup {
        $cart->loadMissing('items');

        if ($cart->items->isEmpty()) {
            throw EmptyCartException::make();
        }

        if ($cart->status !== CartStatus::Active) {
            throw new \RuntimeException('Sepet checkout için uygun değil.');
        }

        return DB::transaction(function () use ($cart, $billingProfile, $couponCode, $method): OrderGroup {
            $cart = Cart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();
            $cart->load('items');

            $subtotal = round((float) $cart->items->sum(fn (CartItem $item): float => (float) $item->price), 2);
            $currency = $cart->items->first()?->currency ?? Currency::Try;

            $tier = DiscountTier::bestForAmount($subtotal);
            $tierDiscount = $tier?->discountAmount($subtotal) ?? 0.0;

            $coupon = null;
            $couponDiscount = 0.0;

            if (filled($couponCode)) {
                $coupon = Coupon::query()
                    ->whereRaw('LOWER(code) = ?', [mb_strtolower(trim($couponCode))])
                    ->lockForUpdate()
                    ->first();

                if (! $coupon) {
                    throw InvalidCouponException::make('Kupon bulunamadı.');
                }

                $coupon->assertApplicable($subtotal);
                $couponDiscount = $coupon->discountAmount($subtotal);
            }

            $total = max(0, round($subtotal - $tierDiscount - $couponDiscount, 2));

            $orderGroup = OrderGroup::query()->create([
                'user_id' => $cart->user_id ?? $billingProfile?->user_id,
                'subtotal' => $subtotal,
                'discount_tier_amount' => $tierDiscount,
                'coupon_discount_amount' => $couponDiscount,
                'vat_amount' => null,
                'vat_withholding_amount' => null,
                'total' => $total,
                'currency' => $currency,
                'billing_profile_id' => $billingProfile?->id,
                'contract_accepted_at' => now(),
            ]);

            foreach ($cart->items as $item) {
                Order::query()->create([
                    'user_id' => $orderGroup->user_id,
                    'site_id' => $item->site_id,
                    'status' => OrderStatus::PaymentPending,
                    'content_source' => ContentSource::CustomerProvided,
                    'price' => $item->price,
                    'currency' => $item->currency,
                    'product_type' => $item->product_type,
                    'site_bundle_id' => $item->site_bundle_id,
                    'footer_link_duration_option_id' => $item->footer_link_duration_option_id,
                    'article_word_package_id' => $item->article_word_package_id,
                    'instagram_account_id' => $item->instagram_account_id,
                    'instagram_story_price_id' => $item->instagram_story_price_id,
                    'seo_package_id' => $item->seo_package_id,
                    'seo_package_duration_option_id' => $item->seo_package_duration_option_id,
                    'backlink_package_id' => $item->backlink_package_id,
                    'wallet_topup_package_id' => $item->wallet_topup_package_id,
                    'content_mode' => $item->content_mode,
                    'content_payload' => $item->content_payload,
                    'order_group_id' => $orderGroup->id,
                ]);
            }

            Payment::query()->create([
                'order_id' => null,
                'order_group_id' => $orderGroup->id,
                'amount' => $this->discountCalculator->calculateFinalAmount($orderGroup, $method),
                'currency' => $orderGroup->currency,
                'method' => $method,
                'status' => PaymentStatus::Pending,
            ]);

            if ($coupon) {
                CouponRedemption::query()->create([
                    'coupon_id' => $coupon->id,
                    'order_group_id' => $orderGroup->id,
                    'user_id' => $orderGroup->user_id,
                    'discount_amount' => $couponDiscount,
                ]);

                $coupon->increment('used_count');
            }

            $cart->forceFill([
                'status' => CartStatus::Converted,
                'user_id' => $cart->user_id ?? $orderGroup->user_id,
            ])->save();

            return $orderGroup->load(['orders', 'payments']);
        });
    }
}
