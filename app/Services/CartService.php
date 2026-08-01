<?php

namespace App\Services;

use App\Enums\CartStatus;
use App\Enums\ContentMode;
use App\Enums\Currency;
use App\Enums\ProductType;
use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Exceptions\InvalidCouponException;
use App\Models\ArticleWordPackage;
use App\Models\BacklinkPackage;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\DiscountTier;
use App\Models\FooterLinkDurationOption;
use App\Models\PromotionalListing;
use App\Models\SeoPackage;
use App\Models\SeoPackageDurationOption;
use App\Models\Site;
use App\Models\SiteBundle;
use App\Models\User;
use App\Models\WalletTopupPackage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CartService
{
    public const SESSION_TOKEN_KEY = 'cart_session_token';

    public const SESSION_COUPON_KEY = 'cart_coupon_code';

    public const MIN_WALLET_TOPUP_AMOUNT = 50.0;

    public function __construct(
        protected CurrencyConverter $converter,
    ) {}

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
        $listing = $this->resolveActiveListing($site, PromotionalListingType::SiteArticle);

        return CartItem::query()->create(array_merge([
            'cart_id' => $cart->id,
            'product_type' => ProductType::SiteArticle,
            'site_id' => $site->id,
            'content_mode' => ContentMode::FileUpload,
            'content_payload' => null,
        ], $this->converter->pricingPayload($listing->effectivePrice(), $listing->currency)));
    }

    public function addPressRelease(Cart $cart, Site $site): CartItem
    {
        $listing = $this->resolveActiveListing($site, PromotionalListingType::PressRelease);

        return CartItem::query()->create(array_merge([
            'cart_id' => $cart->id,
            'product_type' => ProductType::PressRelease,
            'site_id' => $site->id,
            'content_mode' => ContentMode::FileUpload,
            'content_payload' => null,
        ], $this->converter->pricingPayload($listing->effectivePrice(), $listing->currency)));
    }

    public function addBundle(Cart $cart, SiteBundle $bundle): CartItem
    {
        if ($bundle->status !== SiteStatus::Active) {
            throw ValidationException::withMessages([
                'site_bundle_id' => 'Bu paket sepete eklenemez.',
            ]);
        }

        return CartItem::query()->create(array_merge([
            'cart_id' => $cart->id,
            'product_type' => ProductType::Bundle,
            'site_bundle_id' => $bundle->id,
            'content_mode' => ContentMode::FileUpload,
            'content_payload' => null,
        ], $this->converter->pricingPayload(round((float) $bundle->price, 2), $bundle->currency)));
    }

    public function addFooterLink(Cart $cart, Site $site, FooterLinkDurationOption $option): CartItem
    {
        $listing = $this->resolveActiveListing($site, PromotionalListingType::FooterLink);

        if (! $option->is_active) {
            throw ValidationException::withMessages([
                'footer_link_duration_option_id' => 'Geçerli bir süre seçin.',
            ]);
        }

        return CartItem::query()->create(array_merge([
            'cart_id' => $cart->id,
            'product_type' => ProductType::FooterLink,
            'site_id' => $site->id,
            'footer_link_duration_option_id' => $option->id,
            'content_mode' => ContentMode::None,
            'content_payload' => null,
        ], $this->converter->pricingPayload(
            $listing->effectivePrice(),
            $listing->currency,
        )));
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

        return CartItem::query()->create(array_merge([
            'cart_id' => $cart->id,
            'product_type' => ProductType::SeoPackage,
            'seo_package_id' => $package->id,
            'seo_package_duration_option_id' => $option->id,
            'content_mode' => ContentMode::None,
            'content_payload' => null,
        ], $this->converter->pricingPayload(
            $option->resolvePrice($package->monthly_price),
            $package->currency,
        )));
    }

    public function addBacklinkPackage(Cart $cart, BacklinkPackage $package): CartItem
    {
        if ($package->status !== SiteStatus::Active) {
            throw ValidationException::withMessages([
                'backlink_package_id' => 'Bu paket sepete eklenemez.',
            ]);
        }

        return CartItem::query()->create(array_merge([
            'cart_id' => $cart->id,
            'product_type' => ProductType::BacklinkPackage,
            'backlink_package_id' => $package->id,
            'content_mode' => ContentMode::None,
            'content_payload' => null,
        ], $this->converter->pricingPayload(round((float) $package->price, 2), $package->currency)));
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

        return CartItem::query()->create(array_merge([
            'cart_id' => $cart->id,
            'product_type' => ProductType::Balance,
            'wallet_topup_package_id' => $package?->id,
            'content_mode' => ContentMode::None,
            'content_payload' => null,
            'configured_at' => now(),
        ], $this->converter->pricingPayload($amount, Currency::Try)));
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
     *     file?: UploadedFile|null,
     *     image?: UploadedFile|null,
     *     publish_at?: string|null,
     *     note?: string|null
     * }  $data
     */
    public function updateContent(CartItem $item, array $data): CartItem
    {
        return match ($item->product_type) {
            ProductType::FooterLink => $this->updateFooterLinkContent($item, $data),
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
        $addonTry = 0.0;

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
            $addonTry = round((float) $package->price, 2);
            $packageId = $package->id;
        }

        $configured = $mode === ContentMode::AiArticle
            ? $packageId !== null
            : (filled($payload['file_path'] ?? null) || filled($payload['target_url'] ?? null));

        $pricing = $this->pricingForSourceAmount(
            $this->itemSourceBasePrice($item),
            $this->itemSourceCurrency($item),
            $addonTry,
        );

        $item->forceFill(array_merge([
            'content_mode' => $mode,
            'content_payload' => $payload,
            'article_word_package_id' => $packageId,
            'configured_at' => $configured ? now() : null,
        ], $pricing))->save();

        return $item->fresh(['site', 'siteBundle', 'articleWordPackage', 'exchangeRateRecord']) ?? $item;
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
     * Reprice foreign-currency cart lines with the latest TCMB rate.
     */
    public function repriceCart(Cart $cart): Cart
    {
        $cart->loadMissing(['items.articleWordPackage']);

        foreach ($cart->items as $item) {
            $this->repriceItem($item);
        }

        return $cart->load('items');
    }

    public function repriceItem(CartItem $item): CartItem
    {
        $sourceCurrency = $this->itemSourceCurrency($item);
        $sourcePrice = $this->resolveStoredSourcePrice($item);
        $addonTry = $this->wordPackageAddonTry($item);

        $pricing = $this->pricingForSourceAmount($sourcePrice, $sourceCurrency, $addonTry);

        $item->forceFill($pricing)->save();

        return $item;
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
     *     net: float,
     *     vat_rate: float,
     *     vat_amount: float,
     *     total: float
     * }
     */
    public function summarize(Cart $cart, ?string $couponCode = null): array
    {
        $this->repriceCart($cart);
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

        $totals = $this->applyVat($subtotal, $tierDiscount, $couponDiscount);

        return [
            'subtotal' => $subtotal,
            'tier' => $tier,
            'tier_discount' => $tierDiscount,
            'coupon' => $coupon,
            'coupon_discount' => $couponDiscount,
            'coupon_code' => $normalizedCode,
            'coupon_error' => $couponError,
            ...$totals,
        ];
    }

    /**
     * Apply VAT on top of the discounted net amount.
     *
     * @return array{net: float, vat_rate: float, vat_amount: float, total: float}
     */
    public function applyVat(float $subtotal, float $tierDiscount = 0.0, float $couponDiscount = 0.0): array
    {
        $net = max(0, round($subtotal - $tierDiscount - $couponDiscount, 2));
        $vatRate = (float) config('payment.vat_rate', 20);
        $vatAmount = round($net * ($vatRate / 100), 2);

        return [
            'net' => $net,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'total' => round($net + $vatAmount, 2),
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
        return $this->listingBasePrice($site, PromotionalListingType::SiteArticle);
    }

    public function listingBasePrice(Site $site, PromotionalListingType $type): float
    {
        return $this->resolveActiveListing($site, $type)->effectivePrice();
    }

    public function resolveActiveListing(Site $site, PromotionalListingType $type): PromotionalListing
    {
        if ($site->status !== SiteStatus::Active) {
            throw ValidationException::withMessages([
                'site_id' => 'Bu site sepete eklenemez.',
            ]);
        }

        $listing = PromotionalListing::query()
            ->where('site_id', $site->id)
            ->where('type', $type)
            ->where('status', SiteStatus::Active)
            ->first();

        if ($listing === null) {
            $message = match ($type) {
                PromotionalListingType::PressRelease => 'Bu site basın bülteni satmıyor.',
                PromotionalListingType::FooterLink => 'Bu site footer link satmıyor.',
                default => 'Bu site sepete eklenemez.',
            };

            throw ValidationException::withMessages([
                'site_id' => $message,
            ]);
        }

        return $listing;
    }

    /**
     * Catalog base price in the product's native currency (no FX, no AI add-on).
     */
    protected function itemSourceBasePrice(CartItem $item): float
    {
        $item->loadMissing(['site', 'siteBundle', 'seoPackage', 'seoPackageDurationOption', 'backlinkPackage']);

        $listingType = PromotionalListingType::fromProductType($item->product_type);

        if ($listingType !== null && $item->site instanceof Site) {
            $listing = PromotionalListing::query()
                ->where('site_id', $item->site->id)
                ->where('type', $listingType)
                ->where('status', SiteStatus::Active)
                ->first();

            if ($listing !== null) {
                return $listing->effectivePrice();
            }
        }

        if ($item->product_type === ProductType::Bundle && $item->siteBundle instanceof SiteBundle) {
            return round((float) $item->siteBundle->price, 2);
        }

        if ($item->product_type === ProductType::SeoPackage && $item->seoPackage && $item->seoPackageDurationOption) {
            return $item->seoPackageDurationOption->resolvePrice($item->seoPackage->monthly_price);
        }

        if ($item->product_type === ProductType::BacklinkPackage && $item->backlinkPackage) {
            return round((float) $item->backlinkPackage->price, 2);
        }

        return round((float) ($item->source_price ?? $item->price), 2);
    }

    protected function itemSourceCurrency(CartItem $item): Currency
    {
        if ($item->source_currency instanceof Currency) {
            return $item->source_currency;
        }

        if ($item->currency instanceof Currency) {
            return $item->currency;
        }

        $item->loadMissing(['site', 'siteBundle', 'seoPackage', 'backlinkPackage']);

        $listingType = PromotionalListingType::fromProductType($item->product_type);

        if ($listingType !== null && $item->site instanceof Site) {
            $listing = PromotionalListing::query()
                ->where('site_id', $item->site->id)
                ->where('type', $listingType)
                ->first();

            if ($listing?->currency instanceof Currency) {
                return $listing->currency;
            }
        }

        return match ($item->product_type) {
            ProductType::Bundle => $item->siteBundle?->currency ?? Currency::Try,
            ProductType::SeoPackage => $item->seoPackage?->currency ?? Currency::Try,
            ProductType::BacklinkPackage => $item->backlinkPackage?->currency ?? Currency::Try,
            ProductType::Balance => Currency::Try,
            default => Currency::Try,
        };
    }

    protected function resolveStoredSourcePrice(CartItem $item): float
    {
        if ($item->source_price !== null) {
            return round((float) $item->source_price, 2);
        }

        // Legacy cart lines: price is already in item.currency
        return round((float) $item->price, 2);
    }

    protected function wordPackageAddonTry(CartItem $item): float
    {
        $item->loadMissing('articleWordPackage');

        if ($item->articleWordPackage === null) {
            return 0.0;
        }

        return round((float) $item->articleWordPackage->price, 2);
    }

    /**
     * @return array{price: float, currency: Currency, source_price: float, source_currency: Currency, exchange_rate: float, exchange_rate_id: ?int}
     */
    protected function pricingForSourceAmount(float $sourceAmount, Currency $sourceCurrency, float $addonTry = 0.0): array
    {
        $pricing = $this->converter->pricingPayload($sourceAmount, $sourceCurrency);
        $pricing['price'] = round((float) $pricing['price'] + $addonTry, 2);

        return $pricing;
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
