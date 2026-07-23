<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Site;
use Illuminate\Support\Str;

class SeoMetaService
{
    /**
     * @return array{title: string, description: string, og_image: string|null}
     */
    public function forSite(Site $site): array
    {
        $title = $site->domain.' | '.config('app.name');
        $description = filled($site->description)
            ? Str::limit(strip_tags($site->description), 160)
            : $site->domain.' sitesinde backlink ve yazı paketi fırsatları.';

        return [
            'title' => $title,
            'description' => $description,
            'og_image' => $this->faviconUrl($site->domain),
        ];
    }

    /**
     * @return array{title: string, description: string, og_image: string|null}
     */
    public function forPage(Page $page): array
    {
        return [
            'title' => $page->meta_title ?: $page->title.' | '.config('app.name'),
            'description' => $page->meta_description
                ?: Str::limit(strip_tags((string) $page->content), 160),
            'og_image' => null,
        ];
    }

    /**
     * @return array{title: string, description: string, og_image: string|null}
     */
    public function forDefault(): array
    {
        return [
            'title' => config('app.name'),
            'description' => 'Kaliteli backlink, yazı ve medya paketleri — '.config('app.name'),
            'og_image' => null,
        ];
    }

    public function faviconUrl(string $domain): string
    {
        return 'https://www.google.com/s2/favicons?domain='.urlencode($domain).'&sz=128';
    }
}
