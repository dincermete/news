<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SiteReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteReviewController extends Controller
{
    public function __invoke(Request $request, Site $site): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'min:7', 'max:40'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        SiteReview::query()->create([
            'site_id' => $site->id,
            'user_id' => $request->user()?->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'message' => $data['message'],
            'is_approved' => false,
        ]);

        return back()
            ->with('status', 'Yorumunuz alındı. Onaylandıktan sonra burada yayınlanacak.')
            ->withFragment('yorumlar');
    }
}
