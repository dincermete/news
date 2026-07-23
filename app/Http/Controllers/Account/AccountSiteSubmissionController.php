<?php

namespace App\Http\Controllers\Account;

use App\Enums\SiteSubmissionStatus;
use App\Http\Controllers\Controller;
use App\Models\SiteCategory;
use App\Models\SiteSubmission;
use App\Services\SeoMetaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountSiteSubmissionController extends Controller
{
    public function index(Request $request, SeoMetaService $seo): View
    {
        $submissions = $request->user()
            ->siteSubmissions()
            ->with('category')
            ->latest('id')
            ->paginate(15);

        $categories = SiteCategory::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('account.site-submissions', [
            'meta' => $seo->forDefault(),
            'submissions' => $submissions,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0'],
            'site_category_id' => ['nullable', 'integer', 'exists:site_categories,id'],
            'age' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        SiteSubmission::query()->create([
            'user_id' => $request->user()->id,
            'url' => $data['url'],
            'price' => $data['price'],
            'site_category_id' => $data['site_category_id'] ?? null,
            'age' => $data['age'] ?? null,
            'status' => SiteSubmissionStatus::Pending,
        ]);

        return redirect()
            ->route('account.site-submissions')
            ->with('status', 'Başvurunuz alındı.');
    }
}
