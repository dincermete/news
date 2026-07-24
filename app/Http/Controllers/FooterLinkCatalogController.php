<?php

namespace App\Http\Controllers;

use App\Enums\SiteStatus;
use App\Models\FooterLinkDurationOption;
use App\Models\Site;
use App\Models\SiteCategory;
use App\Services\CartService;
use App\Services\SeoMetaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FooterLinkCatalogController extends Controller
{
    public const PER_PAGE = 24;

    public function __invoke(Request $request, SeoMetaService $seo, CartService $carts): View
    {
        $q = trim((string) $request->query('q', ''));
        $kategori = $request->query('kategori');
        $kategori = is_string($kategori) && $kategori !== '' ? $kategori : null;
        $sort = (string) $request->query('sort', 'price_asc');

        $query = Site::query()
            ->with(['category'])
            ->where('status', SiteStatus::Active);

        if ($q !== '') {
            $escaped = addcslashes($q, '%_\\');
            $query->where('domain', 'like', "%{$escaped}%");
        }

        if ($kategori !== null) {
            $query->whereHas('category', fn ($category) => $category->where('slug', $kategori));
        }

        match ($sort) {
            'price_desc' => $query->orderByDesc('price')->orderBy('id'),
            default => $query->orderBy('price')->orderBy('id'),
        };

        $sites = $query->paginate(self::PER_PAGE)->withQueryString();

        foreach ($sites as $site) {
            $site->setAttribute('base_price', $carts->siteBasePrice($site));
        }

        $categories = SiteCategory::query()->orderBy('name')->get(['id', 'name', 'slug']);
        $durationOptions = FooterLinkDurationOption::query()
            ->where('is_active', true)
            ->orderBy('months')
            ->get();

        return view('footer-links.index', [
            'sites' => $sites,
            'categories' => $categories,
            'durationOptions' => $durationOptions,
            'q' => $q !== '' ? $q : null,
            'kategori' => $kategori,
            'sort' => $sort,
            'meta' => [
                ...$seo->forDefault(),
                'title' => 'Footer Link | '.config('app.name'),
                'description' => 'Kalıcı ve süreli footer link yerleşimleri için uygun siteler.',
            ],
        ]);
    }
}
