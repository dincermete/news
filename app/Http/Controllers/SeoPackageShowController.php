<?php

namespace App\Http\Controllers;

use App\Enums\SiteStatus;
use App\Models\BlogPost;
use App\Models\SeoPackage;
use App\Models\SeoPackageDurationOption;
use App\Models\SiteQuestion;
use App\Models\SiteReview;
use App\Services\ProductPublicUrl;
use App\Services\SeoMetaService;
use App\Services\WhatsAppRedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SeoPackageShowController extends Controller
{
    public function __invoke(string $slug, SeoMetaService $seo, ProductPublicUrl $publicUrls): View|RedirectResponse
    {
        $package = SeoPackage::query()
            ->where('slug', $slug)
            ->where('status', SiteStatus::Active)
            ->first();

        if ($package === null) {
            throw new NotFoundHttpException;
        }

        $canonical = $publicUrls->redirectTargetIfCanonicalDiffers($package);
        if ($canonical !== null) {
            return redirect()->to($canonical, 301);
        }

        return $this->showPackage($package, $seo);
    }

    public function showPackage(
        SeoPackage $package,
        SeoMetaService $seo,
        ?WhatsAppRedirectService $whatsApp = null,
    ): View {
        $whatsApp ??= app(WhatsAppRedirectService::class);

        $durationOptions = SeoPackageDurationOption::query()
            ->where('is_active', true)
            ->orderBy('months')
            ->get();

        $relatedPackages = SeoPackage::query()
            ->where('status', SiteStatus::Active)
            ->whereKeyNot($package->id)
            ->orderBy('sort_order')
            ->orderBy('monthly_price')
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
            ->where('seo_package_id', $package->id)
            ->latest('answered_at')
            ->limit(20)
            ->get(['id', 'question', 'answer', 'answered_at', 'guest_email', 'user_id']);

        $reviews = SiteReview::query()
            ->approved()
            ->where('seo_package_id', $package->id)
            ->latest('approved_at')
            ->limit(50)
            ->get(['id', 'name', 'message', 'approved_at', 'created_at']);

        $whatsappUrl = null;
        try {
            $whatsappUrl = $whatsApp->buildLink(
                "Merhaba, {$package->name} SEO paketi hakkında sipariş vermek istiyorum."
            );
        } catch (\RuntimeException) {
            $whatsappUrl = null;
        }

        return view('seo-packages.show', [
            'package' => $package,
            'durationOptions' => $durationOptions,
            'relatedPackages' => $relatedPackages,
            'latestBlogPosts' => $latestBlogPosts,
            'questions' => $questions,
            'reviews' => $reviews,
            'whatsappUrl' => $whatsappUrl,
            'meta' => $seo->forSeoPackage($package),
        ]);
    }
}
