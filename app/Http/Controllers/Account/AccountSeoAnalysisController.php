<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\SeoAnalysisRequest;
use App\Services\SeoMetaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountSeoAnalysisController extends Controller
{
    public function index(Request $request, SeoMetaService $seo): View
    {
        $analyses = SeoAnalysisRequest::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->paginate(15);

        return view('account.seo-analyses', [
            'meta' => $seo->forDefault(),
            'analyses' => $analyses,
        ]);
    }
}
