<?php

namespace App\Http\Controllers;

use App\Enums\SiteStatus;
use App\Models\SeoPackage;
use App\Models\SiteQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SeoPackageQuestionController extends Controller
{
    public function __invoke(Request $request, SeoPackage $package): RedirectResponse
    {
        if ($package->status !== SiteStatus::Active) {
            throw new NotFoundHttpException;
        }

        $data = $request->validate([
            'question' => ['required', 'string', 'min:10', 'max:2000'],
            'guest_email' => [$request->user() ? 'nullable' : 'required', 'email', 'max:255'],
        ]);

        SiteQuestion::query()->create([
            'site_id' => null,
            'seo_package_id' => $package->id,
            'user_id' => $request->user()?->id,
            'guest_email' => $request->user() ? null : ($data['guest_email'] ?? null),
            'question' => $data['question'],
            'is_public' => true,
        ]);

        return back()->with('status', 'Sorunuz alındı. Yanıtlandığında burada yayınlanacak.');
    }
}
