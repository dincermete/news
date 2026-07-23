<?php

namespace App\Http\Controllers;

use App\Enums\ContentMode;
use App\Exceptions\InvalidCouponException;
use App\Models\ArticleWordPackage;
use App\Models\CartItem;
use App\Models\Site;
use App\Services\CartService;
use App\Services\SeoMetaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
        $cart->load(['items.site', 'items.articleWordPackage', 'items.siteBundle', 'items.footerLinkDurationOption']);

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
        $data = $request->validate([
            'site_id' => ['required', 'integer', 'exists:sites,id'],
        ]);

        $site = Site::query()->findOrFail($data['site_id']);
        $cart = $this->carts->resolveOrCreateCart($request);
        $this->carts->addSiteArticle($cart, $site);

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

        $data = $request->validate([
            'content_mode' => ['required', Rule::enum(ContentMode::class)],
            'target_url' => ['nullable', 'url', 'max:2048'],
            'keywords' => ['nullable', 'string', 'max:500'],
            'brief' => ['nullable', 'string', 'max:5000'],
            'article_word_package_id' => [
                'nullable',
                'integer',
                Rule::requiredIf(fn (): bool => $request->input('content_mode') === ContentMode::AiArticle->value),
                'exists:article_word_packages,id',
            ],
            'file' => ['nullable', 'file', 'mimes:doc,docx,pdf,txt,rtf', 'max:10240'],
        ]);

        $this->carts->updateContent($cartItem, $data);

        return redirect()
            ->route('cart.index')
            ->with('status', 'İçerik ayarları kaydedildi.');
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
