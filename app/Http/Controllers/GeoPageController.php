<?php

namespace App\Http\Controllers;

use App\Services\SeoMetaService;
use Illuminate\View\View;

class GeoPageController extends Controller
{
    public function __invoke(SeoMetaService $seo): View
    {
        return view('geo.index', [
            'meta' => $seo->forRoute('geo.index', 'GEO (Generative Engine Optimization) Hizmeti | '.site_setting('site_name')),
        ]);
    }
}
