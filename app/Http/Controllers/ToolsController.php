<?php

namespace App\Http\Controllers;

use App\Services\SeoMetaService;
use App\Support\Tools;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ToolsController extends Controller
{
    public function index(SeoMetaService $seo): View
    {
        return view('tools.index', [
            'categories' => Tools::categories(),
            'grouped' => Tools::grouped(),
            'meta' => $seo->forRoute(
                'tools.index',
                'Ücretsiz SEO ve İçerik Araçları | '.site_setting('site_name'),
                'SEO, AI görünürlüğü ve içerik üretimi için ücretsiz, üyeliksiz araçlar: ROI hesaplayıcı, CTR hesaplayıcı, llms.txt oluşturucu ve daha fazlası.',
            ),
        ]);
    }

    public function show(string $slug, SeoMetaService $seo): View
    {
        $tool = Tools::find($slug);

        if ($tool === null) {
            throw new NotFoundHttpException;
        }

        $brand = site_setting('site_name');

        return view('tools.show', [
            'tool' => $tool,
            'categories' => Tools::categories(),
            'meta' => [
                'title' => $tool['name'].' | '.$brand,
                'description' => $tool['excerpt'],
                'keywords' => null,
                'og_image' => null,
                'og_url' => route('tools.show', $tool['slug']),
            ],
        ]);
    }
}
