<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Services\SeoMetaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountFavoriteController extends Controller
{
    public function index(Request $request, SeoMetaService $seo): View
    {
        $favorites = $request->user()
            ->favorites()
            ->with(['site.category', 'site.labels'])
            ->latest('id')
            ->paginate(24);

        return view('account.favorites', [
            'meta' => $seo->forDefault(),
            'favorites' => $favorites,
        ]);
    }

    public function destroy(Request $request, Favorite $favorite): RedirectResponse
    {
        abort_unless((int) $favorite->user_id === (int) $request->user()->id, 403);

        $favorite->delete();

        return redirect()
            ->route('account.favorites')
            ->with('status', 'Favorilerden kaldırıldı.');
    }
}
