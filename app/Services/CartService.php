<?php

namespace App\Services;

use App\Enums\CartStatus;
use App\Enums\ContentMode;
use App\Enums\Currency;
use App\Enums\ProductType;
use App\Enums\SiteStatus;
use App\Exceptions\InvalidCouponException;
use App\Models\ArticleWordPackage;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\DiscountTier;
use App\Models\FooterLinkDurationOption;
use App\Models\BacklinkPackage;
use App\Models\InstagramAccount;
use App\Models\InstagramStoryPrice;
use App\Models\SeoPackage;
use App\Models\SeoPackageDurationOption;
use App\Models\Site;
use App\Models\SiteBundle;
use App\Models\User;
use App\Models\WalletTopupPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CartService
{
    public const SESSION_TOKEN_KEY = 'cart_session_token';

    public const SESSION_COUPON_KEY = 'cart_coupon_code';

    public const MIN_WALLET_TOPUP_AMOUNT = 50.0;

    public function sessionToken(Request $request): string
    {
        $token = $request->session()->get(self::SESSION_TOKEN_KEY);

        if (! is_string($token) || $token === '') {
            $token = (string) Str::uuid();
            $request->session()->put(self::SESSION_TOKEN_KEY, $token);
        }

        return $token;
    }

    public function resolveOrCreateCart(Request $request): Cart
    {
        $user = $request->user();

        if ($user instanceof User) {
            $cart = Cart::query()
                ->where('user_id', $user->id)
                ->where('status', CartStatus::Active)
                ->latest('id')
                ->first();

            if ($cart) {
                return $cart;
            }

            return Cart::query()->create([
                'user_id' => $user->id,
                'session_token' => $this->sessionToken($request),
                'status' => CartStatus::Active,
            ]);
        }

        $token = $this->sessionToken($request);

        return Cart::query()->firstOrCreate(
            [
                'session_token' => $token,
                'status' => CartStatus::Active,
                'user_id' => null,
            ],
            [],
        );
    }

    public function resolveCart(Request $request): ?Cart
    {
        $user = $request->user();

        if ($user instanceof User) {
            return Cart::query()
                ->where('user_id', $user->id)
                ->where('status', CartStatus::Active)
                ->latest('id')
                ->first();
        }

        $token = $request->session()->get(self::SESSION_TOKEN_KEY);

        if (! is_string($token) || $token === '') {
            return null;
        }

        return Cart::query()
            ->where('session_token', $token)
            ->whereNull('user_id')
            ->where('status', CartStatus::Active)
            ->latest('id')
            ->first();
    }

    public function itemCount(Request $request): int
    {
        $cart = $this->resolveCart($request);

        if ($cart === null) {
            return 0;
        }

        return (int) $cart->items()->count();
    }

    public function assertOwnsItem(Cart $cart, CartItem $item): void
    {
        if ((int) $item->cart_id !== (int) $cart->id) {
            throw new AccessDeniedHttpException('Bu sepet kalemi size ait değil.');
        }
    }

    public function addSiteArticle(Cart $cart, Site $site): CartItem
    {
        if ($site->status !== SiteStatus::Active) {
            throw ValidationException::withMessages([
                'site_id' => 'Bu site sepete eklenemez.',
            ]);
        }

        return CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_type' => ProductType::SiteArticle,
            'site_id' => $site->id,
            'content_mode' => ContentMode::FileUpload,
            'content_payload' => null,
            'price' => $this->siteBasePrice($site),
            'currency' => $site->currency,
        ]);
    }

    public function addPressRelease(Cart $cart, Site $site): CartItem
    {
        if ($site->status !== SiteStatus::Active) {
            throw ValidationException::withMessages([
                'site_id' => 'Bu site sepete eklenemez.',
            ]);
        }

        if ($site->press_release_price === null) {
            throw ValidationException::withMessages([
                'site_id' => 'Bu site basın bülteni satmıyor.',
            ]);
        }

        return CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_type' => ProductType::PressRelease,
            'site_id' => $site->id,
            'content_mode' => ContentMode::FileUpload,
            'content_payload' => null,
            'price' => round((float) $site->press_release_price, 2),
            'currency' => $site->currency,
        ]);
    }

    public function addBundle(Cart $cart, SiteBundle $bundle): CartItem
    {
        if ($bundle->status !== SiteStatus::Active) {
            throw ValidationException::withMessages([
                'site_bundle_id' => 'Bu paket sepete eklenemez.',
            ]);
        }

        return CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_type' => ProductType::Bundle,
            'site_bundle_id' => $bundle->id,
            'content_mode' => ContentMode::FileUpload,
            'content_payload' => null,
            'price' => round((float) $bundle->price, 2),
            'currency' => $bundle->currency,
        ]);
    }

    public function addFooterLink(Cart $cart, Site $site, FooterLinkDurationOption $option): CartItem
    {
        if ($site->status !== SiteStatus::Active) {
            throw ValidationException::withMessages([
                'site_id' => 'Bu site sepete eklenemez.',
            ]);
        }

        if (! $option->is_active) {
            throw ValidationException::withMessages([
                'footer_link_duration_option_id' => 'Geçerli bir süre seçin.',
            ]);
        }

        return CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_type' => ProductType::FooterLink,
            'site_id' => $site->id,
            'footer_link_duration_option_id' => $option->id,
            'content_mode' => ContentMode::None,
            'content_payload' => null,
            'price' => $option->resolvePrice($this->siteBasePrice($site)),
            'currency' => $site->currency,
        ]);
    }

    public function addStory(Cart $cart, InstagramAccount $account, InstagramStoryPrice $storyPrice): CartItem
    {
        if ($account->status !== SiteStatus::Active) {
            throw ValidationException::withMessages([
                'instagram_account_id' => 'Bu hesap sepete eklenemez.',
            ]);
        }

        if (! $storyPrice->is_active || (int) $storyPrice->instagram_account_id !== (int) $account->id) {
            throw ValidationException::withMessages([
                'instagram_story_price_id' => 'Geçerli bir story fiyatı seçin.',
            ]);
        }

        return CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_type' => ProductType::Story,
            'instagram_account_id' => $account->id,
            'instagram_story_price_id' => $storyPrice->id,
            'content_mode' => ContentMode::None,
            'content_payload' => null,
            'price' => round((float) $storyPrice->price, 2),
            'currency' => $storyPrice->currency,
        ]);
    }

    public function addSeoPackage(Cart $cart, SeoPackage $package, SeoPackageDurationOption $option): CartItem
    {
        if ($package->status !== SiteStatus::Active) {
            throw ValidationException::withMessages([
                'seo_package_id' => 'Bu paket sepete eklenemez.',
            ]);
        }

        if (! $option->is_active) {
            throw ValidationException::withMessages([
                'seo_package_duration_option_id' => 'Geçerli bir süre seçin.',
            ]);
        }

        return CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_type' => ProductType::SeoPackage,
            'seo_package_id' => $package->id,
            'seo_package_duration_option_id' => $option->id,
            'content_mode' => ContentMode::None,
            'content_payload' => null,
            'price' => $option->resolvePrice($package->monthly_price),
            'currency' => $package->currency,
        ]);
    }

    public function addBacklinkPackage(Cart $cart, BacklinkPackage $package): CartItem
    {
        if ($package->status !== SiteStatus::Active) {
            throw ValidationException::withMessages([
                'backlink_package_id' => 'Bu paket sepete eklenemez.',
            ]);
        }

        return CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_type' => ProductType::BacklinkPackage,
            'backlink_package_id' => $package->id,
            'content_mode' => ContentMode::None,
            'content_payload' => null,
            'price' => round((float) $package->price, 2),
            'currency' => $package->currency,
        ]);
    }

    public function addWalletTopup(Cart $cart, ?WalletTopupPackage $package, ?float $customAmount): CartItem
    {
        if ($package !== null) {
            if (! $package->is_active) {
                throw ValidationException::withMessages([
                    'wallet_topup_package_id' => 'Bu bakiye paketi şu anda satışta değil.',
                ]);
            }

            $amount = round((float) $package->amount, 2);
        } else {
            $amount = round((float) $customAmount, 2);

            if ($amount < self::MIN_WALLET_TOPUP_AMOUNT) {
                throw ValidationException::withMessages([
                    'custom_topup_amount' => 'En az '.number_format(self::MIN_WALLET_TOPUP_AMOUNT, 0, ',', '.').' ₺ bakiye yükleyebilirsiniz.',
                ]);
            }
        }

        return CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_type' => ProductType::Balance,
            'wallet_topup_package_id' => $package?->id,
            'content_mode' => ContentMode::None,
            'content_payload' => null,
            'price' => $amount,
            'currency' => Currency::Try,
            'configured_at' => now(),
        ]);
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    /**
     * @param  array{
     *     content_mode?: string|null,
     *     target_url?: string|null,
     *     keywords?: string|null,
     *     brief?: string|null,
     *     article_word_package_id?: int|null,
     *     file?: \Illuminate\Http\UploadedFile|null,
     *     image?: \Illuminate\Http\UploadedFile|null,
     *     publish_at?: string|null,
     *     note?: string|null
     * }  $data
     */
    public function updateContent(CartItem $item, array $data): CartItem
    {
        return match ($item->product_type) {
            ProductType::FooterLink => $this->updateFooterLinkContent($item, $data),
            ProductType::Story => $this->updateStoryContent($item, $data),
            ProductType::SeoPackage, ProductType::BacklinkPackage => $this->updateKeywordTargetingContent($item, $data),
            default => $this->updateArticleLikeContent($item, $data),
        };
    }

    protected function updateArticleLikeContent(CartItem $item, array $data): CartItem
    {
        $mode = ContentMode::from($data['content_mode']);
        $payload = is_array($item->content_payload) ? $item->content_payload : [];

        $payload['target_url'] = filled($data['target_url'] ?? null)
            ? (string) $data['target_url']
            : ($payload['target_url'] ?? null);
        $payload['publish_at'] = filled($data['publish_at'] ?? null)
            ? (string) $data['publish_at']
            : ($payload['publish_at'] ?? null);
        $payload['note'] = filled($data['note'] ?? null)
            ? (string) $data['note']
            : ($payload['note'] ?? null);

        if (isset($data['image']) && $data['image'] !== null) {
            $payload['image_path'] = $data['image']->store('cart-content/'.$item->id, 'local');
        }

        $packageId = null;
        $price = $this->itemBasePrice($item);

        if ($mode === ContentMode::FileUpload) {
            if (isset($data['file']) && $data['file'] !== null) {
                $path = $data['file']->store('cart-content/'.$item->id, 'local');
                $payload['file_path'] = $path;
            }

            unset($payload['keywords'], $payload['brief']);
        }

        if ($mode === ContentMode::AiArticle) {
            $packageId = (int) ($data['article_word_package_id'] ?? 0);
            $package = ArticleWordPackage::query()
                ->whereKey($packageId)
                ->where('is_active', true)
                ->first();

            if ($package === null) {
                throw ValidationException::withMessages([
                    'article_word_package_id' => 'Geçerli bir makale paketi seçin.',
                ]);
            }

            $payload['keywords'] = filled($data['keywords'] ?? null)
                ? (string) $data['keywords']
                : ($payload['keywords'] ?? null);
            $payload['brief'] = filled($data['brief'] ?? null)
                ? (string) $data['brief']
                : ($payload['brief'] ?? null);

            unset($payload['file_path']);
            $price = round($price + (float) $package->price, 2);
            $packageId = $package->id;
        }

        $configured = $mode === ContentMode::AiArticle
            ? $packageId !== null
            : (filled($payload['file_path'] ?? null) || filled($payload['target_url'] ?? null));

        $item->forceFill([
            'content_mode' => $mode,
            'content_payload' => $payload,
            'article_word_package_id' => $packageId,
            'price' => $price,
            'configured_at' => $configured ? now() : null,
        ])->save();

        return $item->fresh(['site', 'siteBundle', 'articleWordPackage']) ?? $item;
    }

    protected function updateFooterLinkContent(CartItem $item, array $data): CartItem
    {
        $payload = is_array($item->content_payload) ? $item->content_payload : [];

        $payload['target_url'] = filled($data['target_url'] ?? null)
            ? (string) $data['target_url']
            : ($payload['target_url'] ?? null);
        $payload['keywords'] = filled($data['keywords'] ?? null)
            ? (string) $data['keywords']
            : ($payload['keywords'] ?? null);
        $payload['note'] = filled($data['note'] ?? null)
            ? (string) $data['note']
            : ($payload['note'] ?? null);

        $item->forceFill([
            'content_mode' => ContentMode::None,
            'content_payload' => $payload,
            'configured_at' => filled($payload['target_url'] ?? null) ? now() : null,
        ])->save();

        return $item->fresh(['site', 'footerLinkDurationOption']) ?? $item;
    }

    protected function updateStoryContent(CartItem $item, array $data): CartItem
    {
        $payload = is_array($item->content_payload) ? $item->content_payload : [];

        $payload['target_url'] = filled($data['target_url'] ?? null)
            ? (string) $data['target_url']
            : ($payload['target_url'] ?? null);
        $payload['note'] = filled($data['note'] ?? null)
            ? (string) $data['note']
            : ($payload['note'] ?? null);

        if (isset($data['image']) && $data['image'] !== null) {
            $payload['image_path'] = $data['image']->store('cart-content/'.$item->id, 'local');
        }

        $item->forceFill([
            'content_mode' => ContentMode::None,
            'content_payload' => $payload,
            'configured_at' => (filled($payload['target_url'] ?? null) || filled($payload['image_path'] ?? null)) ? now() : null,
        ])->save();

        return $item->fresh(['instagramAccount', 'instagramStoryPrice']) ?? $item;
    }

    /**
     * Shared by SeoPackage and BacklinkPackage cart items — both are ordered with
     * a target site address plus a set of keywords (each with an optional landing page).
     *
     * @param  array{site_address?: string|null, keywords?: array<int, array{word: string, target_url?: string|null}>|null, note?: string|null}  $data
     */
    protected function updateKeywordTargetingContent(CartItem $item, array $data): CartItem
    {
        $payload = is_array($item->content_payload) ? $item->content_payload : [];

        $payload['site_address'] = filled($data['site_address'] ?? null)
            ? (string) $data['site_address']
            : ($payload['site_address'] ?? null);
        $payload['keywords'] = ! empty($data['keywords'])
            ? array_values($data['keywords'])
            : ($payload['keywords'] ?? []);
        $payload['note'] = filled($data['note'] ?? null)
            ? (string) $data['note']
            : ($payload['note'] ?? null);

        $item->forceFill([
            'content_mode' => ContentMode::None,
            'content_payload' => $payload,
            'configured_at' => (filled($payload['site_address'] ?? null) && ! empty($payload['keywords'])) ? now() : null,
        ])->save();

        $relations = $item->product_type === ProductType::BacklinkPackage
            ? ['backlinkPackage']
            : ['seoPackage', 'seoPackageDurationOption'];

        return $item->fresh($relations) ?? $item;
    }

    /**
     * @return array{
     *     subtotal: float,
     *     tier: ?DiscountTier,
     *     tier_discount: float,
     *     coupon: ?Coupon,
     *     coupon_discount: float,
     *     coupon_code: ?string,
     *     coupon_error: ?string,
     *     total: float
     * }
     */
    public function summarize(Cart $cart, ?string $couponCode = null): array
    {
        $cart->loadMissing('items');

        $subtotal = round((float) $cart->items->sum(fn (CartItem $item): float => (float) $item->price), 2);
        $tier = DiscountTier::bestForAmount($subtotal);
        $tierDiscount = $tier?->discountAmount($subtotal) ?? 0.0;

        $coupon = null;
        $couponDiscount = 0.0;
        $couponError = null;
        $normalizedCode = filled($couponCode) ? trim($couponCode) : null;

        if ($normalizedCode !== null) {
            try {
                $coupon = $this->findApplicableCoupon($normalizedCode, $subtotal);
                $couponDiscount = $coupon->discountAmount($subtotal);
            } catch (InvalidCouponException $exception) {
                $couponError = $exception->getMessage();
                $normalizedCode = null;
            }
        }

        $total = max(0, round($subtotal - $tierDiscount - $couponDiscount, 2));

        return [
            'subtotal' => $subtotal,
            'tier' => $tier,
            'tier_discount' => $tierDiscount,
            'coupon' => $coupon,
            'coupon_discount' => $couponDiscount,
            'coupon_code' => $normalizedCode,
            'coupon_error' => $couponError,
            'total' => $total,
        ];
    }

    public function previewCoupon(Cart $cart, string $code): array
    {
        $summary = $this->summarize($cart, $code);

        if ($summary['coupon_error'] !== null) {
            throw InvalidCouponException::make($summary['coupon_error']);
        }

        return $summary;
    }

    public function rememberCoupon(?string $code): void
    {
        if (blank($code)) {
            session()->forget(self::SESSION_COUPON_KEY);

            return;
        }

        session()->put(self::SESSION_COUPON_KEY, trim($code));
    }

    public function rememberedCoupon(): ?string
    {
        $code = session()->get(self::SESSION_COUPON_KEY);

        return is_string($code) && $code !== '' ? $code : null;
    }

    public function siteBasePrice(Site $site): float
    {
        if ($site->discount_price !== null && (float) $site->discount_price < (float) $site->price) {
            return round((float) $site->discount_price, 2);
        }

        return round((float) $site->price, 2);
    }

    protected function itemBasePrice(CartItem $item): float
    {
        $item->loadMissing(['site', 'siteBundle']);

        if ($item->product_type === ProductType::PressRelease && $item->site instanceof Site) {
            return round((float) ($item->site->press_release_price ?? $item->price), 2);
        }

        if ($item->product_type === ProductType::Bundle && $item->siteBundle instanceof SiteBundle) {
            return round((float) $item->siteBundle->price, 2);
        }

        if ($item->site instanceof Site) {
            return $this->siteBasePrice($item->site);
        }

        return round((float) $item->price, 2);
    }

    protected function findApplicableCoupon(string $code, float $subtotal): Coupon
    {
        $coupon = Coupon::query()
            ->whereRaw('LOWER(code) = ?', [mb_strtolower(trim($code))])
            ->first();

        if ($coupon === null) {
            throw InvalidCouponException::make('Kupon bulunamadı.');
        }

        $coupon->assertApplicable($subtotal);

        return $coupon;
    }
}
