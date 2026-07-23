<?php

namespace App\Console\Commands;

use App\Enums\SiteStatus;
use App\Models\Page;
use App\Models\Site;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

#[Signature('sitemap:generate')]
#[Description('Aktif site ve sayfalar için public/sitemap.xml üretir (canlı istekte üretme — günlük schedule).')]
class GenerateSitemap extends Command
{
    public function handle(): int
    {
        $sitemap = Sitemap::create()
            ->add(Url::create(url('/'))->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)->setPriority(1.0))
            ->add(Url::create(url('/siteler'))->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)->setPriority(0.9));

        Page::query()
            ->active()
            ->orderBy('id')
            ->each(function (Page $page) use ($sitemap): void {
                $sitemap->add(
                    Url::create(route('pages.show', $page->slug))
                        ->setLastModificationDate($page->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.7),
                );
            });

        Site::query()
            ->where('status', SiteStatus::Active)
            ->orderBy('id')
            ->select(['id', 'domain', 'updated_at'])
            ->each(function (Site $site) use ($sitemap): void {
                $sitemap->add(
                    Url::create(route('sites.show', $site->domain))
                        ->setLastModificationDate($site->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8),
                );
            }, 250);

        $path = public_path('sitemap.xml');
        $sitemap->writeToFile($path);

        $this->info('Sitemap yazıldı: '.$path);

        return self::SUCCESS;
    }
}
