<?php

namespace App\Http\Controllers;

use App\Services\PublicStatsService;
use App\Services\SeoMetaService;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(SeoMetaService $seo, PublicStatsService $stats): View
    {
        return view('about.index', [
            'stats' => $stats->all(),
            'meta' => [
                ...$seo->forDefault(),
                'title' => 'Hakkımızda | '.config('app.name'),
                'description' => 'NewsTanıtım; site yazısı, basın bülteni, backlink, story satış ve SEO/GEO hizmetlerini tek panelde birleştiren dijital tanıtım platformudur.',
            ],
        ]);
    }
}
