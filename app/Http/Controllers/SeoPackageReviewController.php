<?php

namespace App\Http\Controllers;

use App\Enums\SiteStatus;
use App\Models\SeoPackage;
use App\Models\SiteReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SeoPackageReviewController extends Controller
{
    public function __invoke(Request $request, SeoPackage $package): RedirectResponse
    {
        if ($package->status !== SiteStatus::Active) {
            throw new NotFoundHttpException;
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'min:7', 'max:40'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        SiteReview::query()->create([
            'site_id' => null,
            'seo_package_id' => $package->id,
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
