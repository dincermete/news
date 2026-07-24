<?php

namespace App\Http\Controllers;

use App\Services\SeoMetaService;
use Illuminate\View\View;

class GeoPageController extends Controller
{
    public function __invoke(SeoMetaService $seo): View
    {
        return view('geo.index', [
            'meta' => [
                ...$seo->forDefault(),
                'title' => 'GEO (Generative Engine Optimization) Hizmeti | '.config('app.name'),
                'description' => 'ChatGPT, Gemini, Claude, Perplexity ve Copilot gibi yapay zeka asistanlarında kaynak gösterilen, önerilen ve güvenilen marka olun.',
            ],
        ]);
    }
}
