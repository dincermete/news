<?php

namespace App\Http\Controllers;

use App\Models\FaqEntry;
use App\Models\Page;
use App\Services\CatalogCache;
use App\Services\SeoMetaService;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * CMS pages at /{slug} (canonical). /sayfa/{slug} redirects to /{slug}.
 */
class PageController extends Controller
{
    public function __invoke(
        string $slug,
        CatalogCache $cache,
        SeoMetaService $seo,
    ): View {
        $page = $cache->rememberPage($slug, function () use ($slug): ?Page {
            return Page::query()->active()->where('slug', $slug)->first();
        });

        if ($page === null) {
            throw new NotFoundHttpException;
        }

        return view('pages.show', [
            'page' => $page,
            'meta' => $seo->forPage($page),
            'faqs' => $this->faqsForPage($slug),
        ]);
    }

    /**
     * @return Collection<int, FaqEntry>
     */
    protected function faqsForPage(string $slug): Collection
    {
        return FaqEntry::query()
            ->active()
            ->where('category', $slug)
            ->orderBy('id')
            ->get();
    }
}
