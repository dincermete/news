<?php

namespace App\Http\Controllers;

use App\Enums\ContentMode;
use App\Enums\ProductType;
use App\Exceptions\InvalidCouponException;
use App\Models\ArticleWordPackage;
use App\Models\BacklinkPackage;
use App\Models\CartItem;
use App\Models\FooterLinkDurationOption;
use App\Models\InstagramAccount;
use App\Models\InstagramStoryPrice;
use App\Models\SeoPackage;
use App\Models\SeoPackageDurationOption;
use App\Models\Site;
use App\Models\SiteBundle;
use App\Models\WalletTopupPackage;
use App\Services\CartService;
use App\Services\SeoMetaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $carts,
        protected SeoMetaService $seo,
    ) {}

    public function index(Request $request): View
    {
        $cart = $this->carts->resolveOrCreateCart($request);
        $cart->load(['items.site', 'items.articleWordPackage', 'items.siteBundle', 'items.footerLinkDurationOption', 'items.instagramAccount', 'items.instagramStoryPrice', 'items.seoPackage', 'items.seoPackageDurationOption', 'items.backlinkPackage']);

        $summary = $this->carts->summarize($cart, $this->carts->rememberedCoupon());

        $wordPackages = ArticleWordPackage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('word_count')
            ->get();

        return view('cart.index', [
            'meta' => $this->seo->forDefault(),
            'cart' => $cart,
            'summary' => $summary,
            'wordPackages' => $wordPackages,
        ]);
    }

    public function addItem(Request $request): RedirectResponse
    {
        if ($request->user() === null) {
            return redirect()
                ->guest(route('login'))
                ->with('status', 'Sepete ürün eklemek için önce giriş yapmalısınız.');
        }

        $data = $request->validate([
            'product_type' => ['required', Rule::enum(ProductType::class)],
            'site_id' => [
                'nullable', 'integer', 'exists:sites,id',
                Rule::requiredIf(fn (): bool => in_array($request->input('product_type'), [
                    ProductType::SiteArticle->value,
                    ProductType::PressRelease->value,
                    ProductType::FooterLink->value,
                ], true)),
            ],
            'site_bundle_id' => [
                'nullable', 'integer', 'exists:site_bundles,id',
                Rule::requiredIf(fn (): bool => $request->input('product_type') === ProductType::Bundle->value),
            ],
            'footer_link_duration_option_id' => [
                'nullable', 'integer', 'exists:footer_link_duration_options,id',
                Rule::requiredIf(fn (): bool => $request->input('product_type') === ProductType::FooterLink->value),
            ],
            'instagram_account_id' => [
                'nullable', 'integer', 'exists:instagram_accounts,id',
                Rule::requiredIf(fn (): bool => $request->input('product_type') === ProductType::Story->value),
            ],
            'instagram_story_price_id' => [
                'nullable', 'integer', 'exists:instagram_story_prices,id',
                Rule::requiredIf(fn (): bool => $request->input('product_type') === ProductType::Story->value),
            ],
            'seo_package_id' => [
                'nullable', 'integer', 'exists:seo_packages,id',
                Rule::requiredIf(fn (): bool => $request->input('product_type') === ProductType::SeoPackage->value),
            ],
            'seo_package_duration_option_id' => [
                'nullable', 'integer', 'exists:seo_package_duration_options,id',
                Rule::requiredIf(fn (): bool => $request->input('product_type') === ProductType::SeoPackage->value),
            ],
            'backlink_package_id' => [
                'nullable', 'integer', 'exists:backlink_packages,id',
                Rule::requiredIf(fn (): bool => $request->input('product_type') === ProductType::BacklinkPackage->value),
            ],
            'wallet_topup_package_id' => ['nullable', 'integer', 'exists:wallet_topup_packages,id'],
            'custom_topup_amount' => ['nullable', 'numeric', 'min:'.CartService::MIN_WALLET_TOPUP_AMOUNT, 'max:250000'],
        ]);

        $productType = ProductType::from($data['product_type']);

        if ($productType === ProductType::Balance) {
            $hasPackage = filled($data['wallet_topup_package_id'] ?? null);
            $hasCustomAmount = filled($data['custom_topup_amount'] ?? null);

            if ($hasPackage === $hasCustomAmount) {
                throw ValidationException::withMessages([
                    'wallet_topup_package_id' => 'Bir bakiye paketi seçin veya tutarı elle girin (ikisi birden değil).',
                ]);
            }
        }

        $cart = $this->carts->resolveOrCreateCart($request);

        match ($productType) {
            ProductType::SiteArticle => $this->carts->addSiteArticle(
                $cart,
                Site::query()->findOrFail($data['site_id']),
            ),
            ProductType::PressRelease => $this->carts->addPressRelease(
                $cart,
                Site::query()->findOrFail($data['site_id']),
            ),
            ProductType::Bundle => $this->carts->addBundle(
                $cart,
                SiteBundle::query()->findOrFail($data['site_bundle_id']),
            ),
            ProductType::FooterLink => $this->carts->addFooterLink(
                $cart,
                Site::query()->findOrFail($data['site_id']),
                FooterLinkDurationOption::query()->findOrFail($data['footer_link_duration_option_id']),
            ),
            ProductType::Story => $this->carts->addStory(
                $cart,
                InstagramAccount::query()->findOrFail($data['instagram_account_id']),
                InstagramStoryPrice::query()->findOrFail($data['instagram_story_price_id']),
            ),
            ProductType::SeoPackage => $this->carts->addSeoPackage(
                $cart,
                SeoPackage::query()->findOrFail($data['seo_package_id']),
                SeoPackageDurationOption::query()->findOrFail($data['seo_package_duration_option_id']),
            ),
            ProductType::BacklinkPackage => $this->carts->addBacklinkPackage(
                $cart,
                BacklinkPackage::query()->findOrFail($data['backlink_package_id']),
            ),
            ProductType::Balance => $this->carts->addWalletTopup(
                $cart,
                filled($data['wallet_topup_package_id'] ?? null)
                    ? WalletTopupPackage::query()->findOrFail($data['wallet_topup_package_id'])
                    : null,
                filled($data['custom_topup_amount'] ?? null) ? (float) $data['custom_topup_amount'] : null,
            ),
        };

        return redirect()
            ->route('cart.index')
            ->with('status', 'Ürün sepete eklendi.');
    }

    public function removeItem(Request $request, CartItem $cartItem): RedirectResponse
    {
        $cart = $this->carts->resolveOrCreateCart($request);
        $this->carts->assertOwnsItem($cart, $cartItem);
        $this->carts->removeItem($cartItem);

        return redirect()
            ->route('cart.index')
            ->with('status', 'Ürün sepetten kaldırıldı.');
    }

    public function updateContent(Request $request, CartItem $cartItem): RedirectResponse
    {
        $cart = $this->carts->resolveOrCreateCart($request);
        $this->carts->assertOwnsItem($cart, $cartItem);

        $needsMode = in_array($cartItem->product_type, [
            ProductType::SiteArticle,
            ProductType::PressRelease,
            ProductType::Bundle,
        ], true);

        $data = $request->validate([
            'content_mode' => [Rule::requiredIf($needsMode), 'nullable', Rule::enum(ContentMode::class)],
            'target_url' => ['nullable', 'url', 'max:2048'],
            'keywords' => ['nullable', 'string', 'max:500'],
            'brief' => ['nullable', 'string', 'max:5000'],
            'note' => ['nullable', 'string', 'max:2000'],
            'publish_at' => ['nullable', 'date'],
            'article_word_package_id' => [
                'nullable',
                'integer',
                Rule::requiredIf(fn (): bool => $request->input('content_mode') === ContentMode::AiArticle->value),
                'exists:article_word_packages,id',
            ],
            'file' => ['nullable', 'file', 'mimes:doc,docx,pdf,txt,rtf', 'max:10240'],
            'image' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:20480'],
            'site_address' => ['nullable', 'url', 'max:2048'],
            'seo_keywords' => ['nullable', 'json'],
        ]);

        if (in_array($cartItem->product_type, [ProductType::SeoPackage, ProductType::BacklinkPackage], true)) {
            $data['keywords'] = $this->parseSeoKeywords($data['seo_keywords'] ?? null);
        }

        $this->carts->updateContent($cartItem, $data);

        return redirect()
            ->route('cart.index')
            ->with('status', 'İçerik ayarları kaydedildi.');
    }

    /**
     * @return array<int, array{word: string, target_url: string|null}>
     */
    protected function parseSeoKeywords(?string $json): array
    {
        if (blank($json)) {
            return [];
        }

        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            return [];
        }

        $keywords = [];

        foreach ($decoded as $entry) {
            $word = trim((string) ($entry['word'] ?? ''));

            if ($word === '') {
                continue;
            }

            $targetUrl = trim((string) ($entry['target_url'] ?? ''));

            $keywords[] = [
                'word' => mb_substr($word, 0, 100),
                'target_url' => $targetUrl !== '' ? mb_substr($targetUrl, 0, 500) : null,
            ];
        }

        return $keywords;
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'coupon_code' => ['required', 'string', 'max:64'],
        ]);

        $cart = $this->carts->resolveOrCreateCart($request);

        try {
            $this->carts->previewCoupon($cart, $data['coupon_code']);
            $this->carts->rememberCoupon($data['coupon_code']);
        } catch (InvalidCouponException $exception) {
            $this->carts->rememberCoupon(null);

            return redirect()
                ->route('cart.index')
                ->withErrors(['coupon_code' => $exception->getMessage()]);
        }

        return redirect()
            ->route('cart.index')
            ->with('status', 'Kupon uygulandı (önizleme).');
    }
}
