<?php

namespace App\Http\Controllers;

use App\Enums\SiteStatus;
use App\Models\BlogPost;
use App\Models\FaqEntry;
use App\Models\SiteBundle;
use App\Models\SiteQuestion;
use App\Models\SiteReview;
use App\Services\ProductPublicUrl;
use App\Services\SeoMetaService;
use App\Services\WhatsAppRedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SiteBundleCatalogController extends Controller
{
    public function index(Request $request, SeoMetaService $seo): View
    {
        $q = trim((string) $request->query('q', ''));

        $query = SiteBundle::query()
            ->withCount('sites')
            ->with(['sites' => fn ($sites) => $sites->orderBy('domain')->with('category')])
            ->where('status', SiteStatus::Active);

        if ($q !== '') {
            $escaped = addcslashes($q, '%_\\');
            $query->where('name', 'like', "%{$escaped}%");
        }

        $bundles = $query->orderBy('price')->get();

        return view('bundles.index', [
            'bundles' => $bundles,
            'q' => $q !== '' ? $q : null,
            'meta' => $seo->forRoute('bundles.index', 'Tanıtım Paketleri | '.site_setting('site_name')),
        ]);
    }

    public function show(string $slug, SeoMetaService $seo, ProductPublicUrl $publicUrls): View|RedirectResponse
    {
        $bundle = SiteBundle::query()
            ->where('slug', $slug)
            ->where('status', SiteStatus::Active)
            ->first();

        if ($bundle === null) {
            throw new NotFoundHttpException;
        }

        $canonical = $publicUrls->redirectTargetIfCanonicalDiffers($bundle);
        if ($canonical !== null) {
            return redirect()->to($canonical, 301);
        }

        return $this->showBundle($bundle, $seo);
    }

    public function showBundle(
        SiteBundle $bundle,
        SeoMetaService $seo,
        ?WhatsAppRedirectService $whatsApp = null,
    ): View {
        $whatsApp ??= app(WhatsAppRedirectService::class);

        $bundle->loadCount('sites');
        $bundle->load(['sites' => fn ($sites) => $sites->orderBy('domain')->with('category')]);

        $faqs = FaqEntry::query()
            ->active()
            ->where('category', 'bundle-'.$bundle->slug)
            ->orderBy('id')
            ->get();

        $relatedBundles = SiteBundle::query()
            ->where('status', SiteStatus::Active)
            ->where('id', '!=', $bundle->id)
            ->withCount('sites')
            ->with(['sites' => fn ($sites) => $sites->orderBy('domain')->with('category')])
            ->orderBy('price')
            ->limit(4)
            ->get();

        $latestBlogPosts = BlogPost::query()
            ->published()
            ->with('category')
            ->latest('published_at')
            ->limit(5)
            ->get(['id', 'title', 'slug', 'featured_image', 'published_at', 'blog_category_id']);

        $questions = SiteQuestion::query()
            ->publicAnswered()
            ->where('site_bundle_id', $bundle->id)
            ->latest('answered_at')
            ->limit(20)
            ->get(['id', 'question', 'answer', 'answered_at', 'guest_email', 'user_id']);

        $reviews = SiteReview::query()
            ->approved()
            ->where('site_bundle_id', $bundle->id)
            ->latest('approved_at')
            ->limit(50)
            ->get(['id', 'name', 'message', 'approved_at', 'created_at']);

        $whatsappUrl = null;
        try {
            $whatsappUrl = $whatsApp->buildLink(
                "Merhaba, {$bundle->name} paketi hakkında sipariş vermek istiyorum."
            );
        } catch (\RuntimeException) {
            $whatsappUrl = null;
        }

        return view('bundles.show', [
            'bundle' => $bundle,
            'faqs' => $faqs,
            'relatedBundles' => $relatedBundles,
            'latestBlogPosts' => $latestBlogPosts,
            'questions' => $questions,
            'reviews' => $reviews,
            'whatsappUrl' => $whatsappUrl,
            'meta' => $seo->forBundle($bundle),
        ]);
    }
}
