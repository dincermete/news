<?php

namespace App\Http\Controllers;

use App\Enums\SiteStatus;
use App\Models\Site;
use App\Models\SiteCategory;
use App\Services\SeoMetaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PressReleaseCatalogController extends Controller
{
    public const PER_PAGE = 24;

    public function __invoke(Request $request, SeoMetaService $seo): View
    {
        $q = trim((string) $request->query('q', ''));
        $kategori = $request->query('kategori');
        $kategori = is_string($kategori) && $kategori !== '' ? $kategori : null;
        $sort = (string) $request->query('sort', 'price_asc');

        $query = Site::query()
            ->with(['category'])
            ->where('status', SiteStatus::Active)
            ->whereNotNull('press_release_price');

        if ($q !== '') {
            $escaped = addcslashes($q, '%_\\');
            $query->where('domain', 'like', "%{$escaped}%");
        }

        if ($kategori !== null) {
            $query->whereHas('category', fn ($category) => $category->where('slug', $kategori));
        }

        match ($sort) {
            'price_desc' => $query->orderByDesc('press_release_price')->orderBy('id'),
            'newest' => $query->orderByDesc('created_at')->orderByDesc('id'),
            default => $query->orderBy('press_release_price')->orderBy('id'),
        };

        $sites = $query->paginate(self::PER_PAGE)->withQueryString();

        $categories = SiteCategory::query()->orderBy('name')->get(['id', 'name', 'slug']);

        return view('press-release.index', [
            'sites' => $sites,
            'categories' => $categories,
            'q' => $q !== '' ? $q : null,
            'kategori' => $kategori,
            'sort' => $sort,
            'meta' => [
                ...$seo->forDefault(),
                'title' => 'Basın Bülteni | '.config('app.name'),
                'description' => 'Haber sitelerinde basın bülteni yayınlama fırsatları.',
            ],
        ]);
    }
}
