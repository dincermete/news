<?php

namespace App\Http\Controllers;

use App\Services\SeoMetaService;
use App\Support\AgencyServicePages;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AgencyServiceController extends Controller
{
    public function index(SeoMetaService $seo): View
    {
        return view('agency-services.index', [
            'services' => AgencyServicePages::all(),
            'meta' => $seo->forRoute('agency-services.index', 'Hizmetlerimiz | '.site_setting('site_name')),
        ]);
    }

    public function show(string $slug, SeoMetaService $seo): View
    {
        $service = AgencyServicePages::find($slug);

        if ($service === null) {
            throw new NotFoundHttpException;
        }

        $brand = site_setting('site_name');

        return view('agency-services.show', [
            'service' => $service,
            'meta' => [
                'title' => $service['name'].' | '.$brand,
                'description' => $service['excerpt'],
                'keywords' => null,
                'og_image' => null,
                'og_url' => route('agency-services.show', $service['slug']),
            ],
        ]);
    }
}
