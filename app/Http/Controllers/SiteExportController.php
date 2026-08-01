<?php

namespace App\Http\Controllers;

use App\Support\CatalogQuery;
use App\Support\SiteCatalogFilters;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SiteExportController extends Controller
{
    /**
     * Streams the currently filtered/sorted site catalog as a CSV file
     * (Excel opens .csv natively — no extra dependency required).
     */
    public function __invoke(Request $request): StreamedResponse
    {
        $filters = SiteCatalogFilters::fromRequest($request);

        $sites = CatalogQuery::catalog($filters)->get();
        $sites->each(fn ($site) => $site->normalizeJoinedPricingAttributes());

        $columns = [
            'Site Adı', 'Kategori', 'Google Index', 'Google News',
            'Moz DA', 'Moz PA', 'Ahrefs DR', 'Semrush Authority Score', 'Site Yaşı',
            'Link Türü', 'Fiyat', 'Para Birimi',
            'Aylık Trafik', 'Moz Rank', 'Majestic CF', 'Majestic TF',
            'Backlink', 'Link Çıkışı', 'Spam Score', 'Ahrefs Kelime',
        ];

        $filename = 'siteler-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($sites, $columns): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM, Excel Türkçe karakterleri doğru okusun
            fputcsv($handle, $columns);

            foreach ($sites as $site) {
                fputcsv($handle, [
                    $site->domain,
                    $site->category?->name ?? 'Kategorisiz',
                    $site->is_google_indexed ? 'Var' : 'Yok',
                    $site->is_news_approved ? 'Var' : 'Yok',
                    $site->da_value,
                    $site->pa_value,
                    $site->ahrefs_dr_value,
                    $site->semrush_authority_score_value,
                    $site->age,
                    $site->is_dofollow ? 'Dofollow' : 'Nofollow',
                    $site->price,
                    $site->currency?->value ?? (string) $site->currency,
                    $site->monthly_traffic_value,
                    $site->moz_rank_value,
                    $site->majestic_cf_value,
                    $site->majestic_tf_value,
                    $site->backlinks_value,
                    $site->max_link_count,
                    $site->spam_score_value,
                    $site->ahrefs_keywords_value,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
