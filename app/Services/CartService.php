<?php

namespace App\Services;

use App\Enums\CartStatus;
use App\Enums\ContentMode;
use App\Enums\ProductType;
use App\Enums\SiteStatus;
use App\Exceptions\InvalidCouponException;
use App\Models\ArticleWordPackage;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\DiscountTier;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CartService
{
    public const SESSION_TOKEN_KEY = 'cart_session_token';

    public const SESSION_COUPON_KEY = 'cart_coupon_code';

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

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }

    /**
     * @param  array{
     *     content_mode: string,
     *     target_url?: string|null,
     *     keywords?: string|null,
     *     brief?: string|null,
     *     article_word_package_id?: int|null,
     *     file?: \Illuminate\Http\UploadedFile|null
     * }  $data
     */
    public function updateContent(CartItem $item, array $data): CartItem
    {
        $mode = ContentMode::from($data['content_mode']);
        $payload = is_array($item->content_payload) ? $item->content_payload : [];

        $payload['target_url'] = filled($data['target_url'] ?? null)
            ? (string) $data['target_url']
            : ($payload['target_url'] ?? null);

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

        $item->forceFill([
            'content_mode' => $mode,
            'content_payload' => $payload,
            'article_word_package_id' => $packageId,
            'price' => $price,
        ])->save();

        return $item->fresh(['site', 'articleWordPackage']) ?? $item;
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
        $item->loadMissing('site');

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
