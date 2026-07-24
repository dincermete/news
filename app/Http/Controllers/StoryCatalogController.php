<?php

namespace App\Http\Controllers;

use App\Enums\SiteStatus;
use App\Models\InstagramAccount;
use App\Services\SeoMetaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoryCatalogController extends Controller
{
    public const PER_PAGE = 24;

    public function __invoke(Request $request, SeoMetaService $seo): View
    {
        $q = trim((string) $request->query('q', ''));

        $query = InstagramAccount::query()
            ->with(['storyPrices' => fn ($storyPrices) => $storyPrices->where('is_active', true)])
            ->where('status', SiteStatus::Active)
            ->whereHas('storyPrices', fn ($storyPrices) => $storyPrices->where('is_active', true));

        if ($q !== '') {
            $escaped = addcslashes($q, '%_\\');
            $query->where(function ($account) use ($escaped): void {
                $account->where('handle', 'like', "%{$escaped}%")
                    ->orWhere('name', 'like', "%{$escaped}%");
            });
        }

        $accounts = $query->orderBy('handle')->paginate(self::PER_PAGE)->withQueryString();

        return view('story.index', [
            'accounts' => $accounts,
            'q' => $q !== '' ? $q : null,
            'meta' => [
                ...$seo->forDefault(),
                'title' => 'Story Satış | '.config('app.name'),
                'description' => 'Instagram Post ve Story formatında tanıtım için uygun hesaplar.',
            ],
        ]);
    }
}
