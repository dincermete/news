<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\PublicStatsService;
use App\Services\SeoMetaService;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(SeoMetaService $seo, PublicStatsService $stats): View
    {
        $page = Page::findByRouteKey('about.show');

        return view('about.index', [
            'stats' => $stats->all(),
            'page' => $page,
            'meta' => $seo->forRoute('about.show', 'Hakkımızda | '.site_setting('site_name')),
        ]);
    }
}
