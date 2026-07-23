<?php

namespace App\Http\Controllers\Auth;

use App\Enums\CustomerStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SeoMetaService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(Request $request, SeoMetaService $seo): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('account.dashboard');
        }

        return view('auth.register', [
            'meta' => $seo->forDefault(),
            'ref' => $request->query('ref'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:32'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'ref' => ['nullable', 'string', 'max:32'],
        ]);

        $referredById = $this->resolveReferrerId($validated['ref'] ?? null);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => UserRole::Customer,
            'status' => CustomerStatus::Active,
            'referred_by_id' => $referredById,
        ]);

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('account.dashboard');
    }

    protected function resolveReferrerId(?string $code): ?int
    {
        if ($code === null || trim($code) === '') {
            return null;
        }

        $referrer = User::query()
            ->where('affiliate_code', $code)
            ->first();

        return $referrer?->id;
    }
}
