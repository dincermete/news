<?php

namespace App\Http\Controllers\Auth;

use App\Enums\CustomerStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        if (blank(config('services.google.client_id')) || blank(config('services.google.client_secret'))) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google ile giriş şu anda kullanılamıyor.',
            ]);
        }

        if ($request->filled('ref')) {
            $request->session()->put('registration_ref', $request->string('ref')->toString());
        }

        return Socialite::driver('google')
            ->redirectUrl(route('auth.google.callback'))
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(route('auth.google.callback'))
                ->user();
        } catch (InvalidStateException) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google ile giriş başarısız oldu. Lütfen tekrar deneyin.',
            ]);
        }

        $user = User::query()->where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $user = User::query()->where('email', $googleUser->getEmail())->first();
        }

        if ($user) {
            if (! $user->google_id) {
                $user->forceFill([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $user->avatar ?? $googleUser->getAvatar(),
                ])->save();
            }
        } else {
            $user = User::query()->create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Kullanıcı',
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => Hash::make(Str::random(32)),
                'role' => UserRole::Customer,
                'status' => CustomerStatus::Active,
                'email_verified_at' => now(),
                'referred_by_id' => $this->resolveReferrerId($request->session()->pull('registration_ref')),
            ]);

            event(new Registered($user));
        }

        if ($user->isSuspended()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Hesabınız askıya alınmış.',
            ]);
        }

        Auth::login($user, true);

        request()->session()->regenerate();

        return redirect()->intended(route('account.dashboard'));
    }

    protected function resolveReferrerId(?string $code): ?int
    {
        if ($code === null || trim($code) === '') {
            return null;
        }

        return User::query()->where('affiliate_code', $code)->value('id');
    }
}
