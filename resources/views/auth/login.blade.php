@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    $brand = isset($siteSettings) ? $siteSettings->siteName() : config('app.name');
    $label = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
    $input = 'block w-full rounded-xl border border-ink/10 bg-white px-3.5 py-3 text-sm text-ink placeholder:text-ink-3 focus:border-ink/30 focus:ring-0';
    $benefits = [
        [
            'icon' => 'M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73',
            'text' => 'Kampanya ve fırsatlardan öncelikli olarak haberdar olabilirsiniz.',
        ],
        [
            'icon' => 'M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5',
            'text' => 'Sipariş, yayın linki ve raporlarınızı tek panelden takip edebilirsiniz.',
        ],
        [
            'icon' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z',
            'text' => 'Favori sitelerinizi kaydedip sepet ve harcama geçmişinizi yönetebilirsiniz.',
        ],
    ];
@endphp

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
        <div class="grid gap-5 lg:grid-cols-2">
            {{-- Giriş --}}
            <div class="rounded-[20px] border border-ink/10 bg-white p-6 sm:p-8" x-data="{ showPassword: false }">
                <h1 class="font-display text-2xl font-semibold tracking-tight text-ink">Giriş yap</h1>
                <div class="mt-4 h-px w-full bg-ink/10" aria-hidden="true"></div>

                @if ($errors->any())
                    <div class="mt-5 rounded-xl border border-brand-200 bg-brand-50 px-3.5 py-2.5 text-sm text-brand-800">
                        {{ $errors->first() }}
                    </div>
                @endif

                <a href="{{ route('auth.google.redirect') }}" class="mt-6 flex w-full items-center justify-center gap-x-2.5 rounded-xl border border-ink/10 bg-paper px-4 py-3 text-sm font-semibold text-ink transition hover:bg-ink/5 active:scale-[0.98]">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                        <path fill="#FF3D00" d="m6.306 14.691 6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
                        <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/>
                        <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
                    </svg>
                    Google ile giriş yap
                </a>

                <div class="mt-6 flex items-center gap-x-3 text-xs font-medium uppercase tracking-wide text-ink-3">
                    <span class="h-px flex-1 bg-ink/10"></span>
                    veya
                    <span class="h-px flex-1 bg-ink/10"></span>
                </div>

                <form method="post" action="{{ route('login.store') }}" class="mt-6 space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="{{ $label }}">E-posta adresiniz *</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="Lütfen e-posta adresinizi yazınız"
                            class="{{ $input }}"
                        >
                    </div>
                    <div>
                        <label for="password" class="{{ $label }}">Şifreniz *</label>
                        <div class="relative">
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Lütfen şifrenizi yazınız"
                                class="{{ $input }} pr-11"
                            >
                            <button
                                type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-3 transition hover:text-ink"
                                @click="showPassword = !showPassword"
                                :aria-label="showPassword ? 'Şifreyi gizle' : 'Şifreyi göster'"
                            >
                                <svg x-show="!showPassword" class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                <svg x-show="showPassword" x-cloak class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                            </button>
                        </div>
                    </div>

                    <label class="flex cursor-pointer items-center gap-x-2.5 text-sm text-ink-2">
                        <input type="checkbox" name="remember" value="1" class="size-4 rounded border-ink/20 text-ink focus:ring-0">
                        Beni hatırla
                    </label>

                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-3.5 text-sm font-semibold text-white transition hover:scale-[1.01] active:scale-[0.98]">
                        Giriş yap
                    </button>
                </form>
            </div>

            {{-- Üye ol CTA --}}
            <div class="flex flex-col rounded-[20px] border border-ink/10 bg-white p-6 sm:p-8">
                <h2 class="font-display text-2xl font-semibold tracking-tight text-ink">Üye ol</h2>
                <div class="mt-4 h-px w-full bg-ink/10" aria-hidden="true"></div>

                <p class="mt-6 text-sm font-medium leading-relaxed text-ink-2">
                    {{ $brand }}’a üye olarak birçok avantajdan faydalanabilirsiniz. Öne çıkan avantajlar;
                </p>

                <ul class="mt-8 flex-1 space-y-6">
                    @foreach ($benefits as $benefit)
                        <li class="flex items-start gap-x-4">
                            <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-[10px] bg-paper text-ink">
                                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $benefit['icon'] }}"/></svg>
                            </span>
                            <p class="pt-2 text-sm font-medium leading-relaxed text-ink">{{ $benefit['text'] }}</p>
                        </li>
                    @endforeach
                </ul>

                <a href="{{ route('register') }}" class="mt-10 inline-flex w-full items-center justify-center rounded-xl border border-ink bg-white px-4 py-3.5 text-sm font-semibold text-ink transition hover:bg-ink/5 active:scale-[0.98]">
                    Üye ol
                </a>
            </div>
        </div>
    </section>
@endsection
