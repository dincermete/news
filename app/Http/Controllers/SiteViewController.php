<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\SiteViewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteViewController extends Controller
{
    public function __invoke(Request $request, Site $site, SiteViewService $siteViews): JsonResponse
    {
        $data = $request->validate([
            'session_token' => ['nullable', 'string', 'max:128'],
        ]);

        $siteViews->record($site, $data['session_token'] ?? null);

        return response()->json([
            'ok' => true,
            'today_count' => $siteViews->todayCount($site),
            'total_count' => $siteViews->totalCount($site),
        ]);
    }
}
