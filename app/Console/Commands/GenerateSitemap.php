<?php

namespace App\Console\Commands;

use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Models\BacklinkPackage;
use App\Models\BlogPost;
use App\Models\Page;
use App\Models\PromotionalListing;
use App\Models\Province;
use App\Models\SeoPackage;
use App\Models\SiteBundle;
use App\Models\SiteCategory;
use App\Services\ProductPublicUrl;
use App\Services\ProvinceStatsService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

#[Signature('sitemap:generate')]
#[Description('Aktif ürün ve sayfalar için public/sitemap.xml üretir (canlı istekte üretme — günlük schedule).')]
class GenerateSitemap extends Command
{
    public function handle(ProductPublicUrl $publicUrls, ProvinceStatsService $provinceStats): int
    {
        $sitemap = Sitemap::create()
            ->add(Url::create(url('/'))->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)->setPriority(1.0))
            ->add(Url::create(url('/siteler'))->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)->setPriority(0.9))
            ->add(Url::create(url('/blog'))->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)->setPriority(0.85));

        SiteCategory::query()
            ->orderBy('id')
            ->each(function (SiteCategory $category) use ($sitemap): void {
                $sitemap->add(
                    Url::create(route('sites.category', ['kategori' => $category->slug]))
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.85),
                );
            });

        $provinceStats->provincesWithCounts()
            ->filter(fn (Province $province): bool => $province->isIndexable())
            ->each(function (Province $province) use ($sitemap): void {
                $sitemap->add(
                    Url::create($province->url())
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.7),
                );
            });

        Page::query()
            ->active()
            ->orderBy('id')
            ->each(function (Page $page) use ($sitemap): void {
                $sitemap->add(
                    Url::create(url('/'.$page->slug))
                        ->setLastModificationDate($page->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.7),
                );
            });

        BlogPost::query()
            ->published()
            ->orderBy('id')
            ->each(function (BlogPost $post) use ($sitemap): void {
                $sitemap->add(
                    Url::create($post->url())
                        ->setLastModificationDate($post->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.75),
                );
            });

        PromotionalListing::query()
            ->activeForSale()
            ->ofType(PromotionalListingType::SiteArticle)
            ->with('site')
            ->orderBy('id')
            ->each(function (PromotionalListing $listing) use ($sitemap, $publicUrls): void {
                $sitemap->add(
                    Url::create($publicUrls->urlFor($listing))
                        ->setLastModificationDate($listing->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8),
                );
            }, 250);

        SiteBundle::query()
            ->where('status', SiteStatus::Active)
            ->orderBy('id')
            ->each(function (SiteBundle $bundle) use ($sitemap, $publicUrls): void {
                $sitemap->add(
                    Url::create($publicUrls->urlFor($bundle))
                        ->setLastModificationDate($bundle->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.75),
                );
            });

        SeoPackage::query()
            ->where('status', SiteStatus::Active)
            ->orderBy('id')
            ->each(function (SeoPackage $package) use ($sitemap, $publicUrls): void {
                $sitemap->add(
                    Url::create($publicUrls->urlFor($package))
                        ->setLastModificationDate($package->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.7),
                );
            });

        BacklinkPackage::query()
            ->where('status', SiteStatus::Active)
            ->orderBy('id')
            ->each(function (BacklinkPackage $package) use ($sitemap, $publicUrls): void {
                $sitemap->add(
                    Url::create($publicUrls->urlFor($package))
                        ->setLastModificationDate($package->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.7),
                );
            });

        $path = public_path('sitemap.xml');
        $sitemap->writeToFile($path);

        $this->info('Sitemap yazıldı: '.$path);

        return self::SUCCESS;
    }
}
