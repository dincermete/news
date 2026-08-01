<?php

namespace App\Services;

use App\Enums\SiteStatus;
use App\Models\BacklinkPackage;
use App\Models\Page;
use App\Models\PromotionalListing;
use App\Models\SeoPackage;
use App\Models\Site;
use App\Models\SiteBundle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class ProductPublicUrl
{
    public const PATH_PATTERN = '^[a-z0-9]+(?:-[a-z0-9]+)*$';

    /**
     * @var list<class-string<Model>>
     */
    public const PRODUCT_MODELS = [
        PromotionalListing::class,
        SiteBundle::class,
        SeoPackage::class,
        BacklinkPackage::class,
    ];

    /**
     * First-level path segments that must never be used as product public_path.
     *
     * @return list<string>
     */
    public function reservedPaths(): array
    {
        $static = [
            'siteler',
            'site',
            'basin-bulteni',
            'tanitim-paketleri',
            'footer-linkler',
            'seo-paketleri',
            'backlink-paketleri',
            'hizmetler',
            'geo',
            'ucretsiz-analiz',
            'hakkimizda',
            'iletisim',
            'sepet',
            'odeme',
            'giris',
            'kayitol',
            'cikis',
            'hesabim',
            'auth',
            'email',
            'sayfa',
            'blog',
            'sitemap.xml',
            'paytr',
            'live',
            'chatbot',
            'admin',
            'filament',
            'storage',
            'build',
            'up',
        ];

        $pageSlugs = Page::query()
            ->whereNotNull('slug')
            ->pluck('slug')
            ->map(fn (string $slug): string => strtolower($slug))
            ->all();

        return array_values(array_unique([...$static, ...$pageSlugs]));
    }

    public function normalize(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $path = strtolower(trim($path, " \t\n\r\0\x0B/"));

        return $path === '' ? null : $path;
    }

    public function isValidFormat(?string $path): bool
    {
        $path = $this->normalize($path);

        if ($path === null) {
            return true;
        }

        return (bool) preg_match('/'.self::PATH_PATTERN.'/u', $path);
    }

    /**
     * @param  class-string<Model>|null  $exceptType
     */
    public function assertPathAvailable(?string $path, ?Model $except = null): void
    {
        $path = $this->normalize($path);

        if ($path === null) {
            return;
        }

        if (! $this->isValidFormat($path)) {
            throw ValidationException::withMessages([
                'public_path' => 'URL yalnızca küçük harf, rakam ve tire içerebilir (ör. hurriyetcom-tr-tanitimyazisi).',
            ]);
        }

        if (in_array($path, $this->reservedPaths(), true)) {
            throw ValidationException::withMessages([
                'public_path' => 'Bu URL sistem tarafından rezerve edilmiş.',
            ]);
        }

        foreach (self::PRODUCT_MODELS as $modelClass) {
            $query = $modelClass::query()->where('public_path', $path);

            if ($except instanceof $modelClass && $except->exists) {
                $query->whereKeyNot($except->getKey());
            }

            if ($query->exists()) {
                throw ValidationException::withMessages([
                    'public_path' => 'Bu URL başka bir üründe kullanılıyor.',
                ]);
            }
        }
    }

    public function resolve(string $path): ?Model
    {
        $path = $this->normalize($path);

        if ($path === null || in_array($path, $this->reservedPaths(), true)) {
            return null;
        }

        foreach (self::PRODUCT_MODELS as $modelClass) {
            $product = $modelClass::query()
                ->where('public_path', $path)
                ->first();

            if ($product !== null) {
                return $product;
            }
        }

        return null;
    }

    public function urlFor(Model $product): string
    {
        $path = $this->normalize($product->getAttribute('public_path'));

        if ($path !== null) {
            return url('/'.$path);
        }

        return $this->technicalUrlFor($product);
    }

    public function technicalUrlFor(Model $product): string
    {
        return match (true) {
            $product instanceof PromotionalListing => route('sites.show', $product->site?->domain ?? $product->site_id),
            $product instanceof SiteBundle => route('bundles.show', $product->slug),
            $product instanceof SeoPackage => route('seo-packages.show', $product->slug),
            $product instanceof BacklinkPackage => route('backlink-packages.show', $product->slug),
            default => url('/'),
        };
    }

    /**
     * Canonical storefront URL for a site's active site_article listing (İncele links).
     */
    public function urlForSite(Site $site): string
    {
        if ($site->relationLoaded('articleListing') && $site->articleListing instanceof PromotionalListing) {
            return $this->urlFor($site->articleListing);
        }

        if ($site->relationLoaded('activeListing') && $site->getRelation('activeListing') instanceof PromotionalListing) {
            return $this->urlFor($site->getRelation('activeListing'));
        }

        $joinedPath = $this->normalize($site->getAttribute('listing_public_path'));
        if ($joinedPath !== null) {
            return url('/'.$joinedPath);
        }

        // Joined catalog rows already know the listing; avoid N+1 when path is empty.
        if ($site->getAttribute('promotional_listing_id') !== null) {
            return route('sites.show', $site->domain);
        }

        $listing = $site->articleListing()
            ->where('status', SiteStatus::Active)
            ->first(['id', 'site_id', 'public_path', 'type', 'status']);

        if ($listing instanceof PromotionalListing) {
            return $this->urlFor($listing);
        }

        return route('sites.show', $site->domain);
    }

    public function redirectTargetIfCanonicalDiffers(Model $product): ?string
    {
        if (! $product->getAttribute('public_path')) {
            return null;
        }

        $canonical = $this->urlFor($product);
        $current = url()->current();

        if (rtrim($canonical, '/') === rtrim($current, '/')) {
            return null;
        }

        return $canonical;
    }

    /**
     * Named routes that occupy first-level segments (for docs / debugging).
     *
     * @return list<string>
     */
    public function registeredFirstLevelRouteNames(): array
    {
        return collect(Route::getRoutes())
            ->map(fn ($route) => $route->getName())
            ->filter()
            ->values()
            ->all();
    }

    public function listingTypeLabel(PromotionalListing $listing): string
    {
        return $listing->type?->getLabel() ?? 'Ürün';
    }
}
