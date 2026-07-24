<?php

namespace App\Http\Controllers;

use App\Enums\SeoAnalysisServiceType;
use App\Models\SeoAnalysisRequest;
use App\Services\SeoMetaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FreeAnalysisController extends Controller
{
    public function show(SeoMetaService $seo): View
    {
        return view('free-analysis.index', [
            'serviceTypes' => SeoAnalysisServiceType::cases(),
            'meta' => [
                ...$seo->forDefault(),
                'title' => 'Ücretsiz SEO ve AI Görünürlük Analizi | '.config('app.name'),
                'description' => 'Sitenizi analiz edelim; hizmete ve hedeflerinize göre size en uygun yol haritasını panelinizde paylaşalım.',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_url' => ['required', 'url', 'max:2048'],
            'service_type' => ['required', 'in:'.implode(',', array_map(fn ($case) => $case->value, SeoAnalysisServiceType::cases()))],
            'brief' => ['nullable', 'string', 'max:2000'],
        ]);

        SeoAnalysisRequest::query()->create([
            'user_id' => $request->user()->id,
            'site_url' => $data['site_url'],
            'service_type' => $data['service_type'],
            'brief' => $data['brief'] ?? null,
        ]);

        return redirect()
            ->route('account.seo-analyses')
            ->with('status', 'Analiz talebiniz alındı. 24 saat içinde panelinizde sonuçlanacak.');
    }
}
