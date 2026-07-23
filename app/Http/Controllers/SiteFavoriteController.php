<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteFavoriteController extends Controller
{
    public function __invoke(Request $request, Site $site): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()
                ->guest(route('login'))
                ->with('status', 'Favoriye eklemek için giriş yapın.');
        }

        $existing = Favorite::query()
            ->where('user_id', $user->id)
            ->where('site_id', $site->id)
            ->first();

        if ($existing !== null) {
            $existing->delete();

            return back()->with('status', 'Favorilerden kaldırıldı.');
        }

        Favorite::query()->firstOrCreate([
            'user_id' => $user->id,
            'site_id' => $site->id,
        ]);

        return back()->with('status', 'Favorilere eklendi.');
    }
}
