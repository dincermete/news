<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @hasSection('meta')
        @yield('meta')
    @else
        @include('partials.seo-meta', ['meta' => $meta ?? app(\App\Services\SeoMetaService::class)->forDefault()])
    @endif

    <script>document.documentElement.classList.add('js');</script>

    <link rel="preload" href="{{ asset('fonts/stack-sans-headline-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/InterDisplay-Medium.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">

    @vite(['resources/css/app.css', 'resources/js/public.js'])
</head>
<body class="storefront flex min-h-screen flex-col bg-white font-sans text-ink antialiased">
    @include('partials.header')

    @if (auth()->check() && ! auth()->user()->hasVerifiedEmail())
        <div class="border-b border-amber-200 bg-amber-50">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-2 px-4 py-2.5 text-sm text-amber-900 sm:px-6 lg:px-8">
                <p>E-posta adresiniz doğrulanmadı. Lütfen gelen kutunuzu kontrol edin.</p>
                <form method="post" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="font-semibold text-amber-800 underline underline-offset-2 hover:text-amber-950">
                        Doğrulama bağlantısını tekrar gönder
                    </button>
                </form>
            </div>
        </div>
    @endif

    <div class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-4">
            <aside class="lg:col-span-1">
                <div class="rounded-[20px] border border-ink/10 bg-paper p-5">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-accent-500 to-accent-700 text-sm font-semibold text-white">
                            {{ $accountUser->initials() }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-ink">{{ $accountUser->name }}</p>
                            <p class="truncate text-xs text-ink-3">{{ $accountUser->email }}</p>
                        </div>
                    </div>

                    <dl class="mt-4 space-y-2 border-t border-ink/10 pt-4 text-sm">
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-2">Toplam Bakiye</dt>
                            <dd class="font-display font-bold text-ink">{{ number_format($accountTotalBalance, 2, ',', '.') }} ₺</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-ink-2">Ana Bakiye</dt>
                            <dd class="font-semibold text-ink-2">{{ number_format($accountMainBalance, 2, ',', '.') }} ₺</dd>
                        </div>
                    </dl>

                    <a
                        href="{{ route('account.payment-notification') }}"
                        class="group mt-4 flex w-full items-center justify-center gap-x-2 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]"
                    >
                        Bakiye Yükle
                        <svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>

                @php
                    $nav = [
                        ['route' => 'account.dashboard', 'label' => 'Dashboard', 'count' => null],
                        ['route' => 'account.profile', 'label' => 'Profilim', 'count' => null],
                        ['route' => 'account.orders', 'label' => 'Siparişlerim', 'count' => $accountNavCounts['orders']],
                        ['route' => 'account.invoices', 'label' => 'Faturalarım', 'count' => $accountNavCounts['invoices']],
                        ['route' => 'account.favorites', 'label' => 'Favori Ürünlerim', 'count' => $accountNavCounts['favorites']],
                        ['route' => 'account.support-tickets', 'label' => 'Destek Taleplerim', 'count' => $accountNavCounts['support']],
                        ['route' => 'account.payment-notification', 'label' => 'Ödeme Bildirimi', 'count' => $accountNavCounts['payments']],
                        ['route' => 'account.site-submissions', 'label' => 'Site Başvurularım', 'count' => null],
                        ['route' => 'account.spin-wheel', 'label' => 'Çarkıfelek', 'count' => null],
                        ['route' => 'account.affiliate', 'label' => 'Satış Ortaklığı', 'count' => null],
                    ];
                @endphp

                <nav class="mt-3 space-y-0.5 rounded-[20px] border border-ink/10 bg-white p-2" aria-label="Hesap menüsü">
                    @foreach ($nav as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            @class([
                                'flex items-center justify-between gap-2 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                                'bg-ink text-white' => request()->routeIs($item['route']),
                                'text-ink-2 hover:bg-paper hover:text-ink' => ! request()->routeIs($item['route']),
                            ])
                        >
                            <span>{{ $item['label'] }}</span>
                            @if ($item['count'] !== null && $item['count'] > 0)
                                <span @class([
                                    'inline-flex min-w-5 items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-bold',
                                    'bg-white/15 text-white' => request()->routeIs($item['route']),
                                    'bg-brand-500 text-white' => ! request()->routeIs($item['route']),
                                ])>
                                    {{ $item['count'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                <form method="post" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full rounded-2xl border border-ink/10 bg-white px-3 py-2.5 text-sm font-medium text-ink-2 transition hover:border-ink/25 hover:text-ink">
                        Çıkış yap
                    </button>
                </form>
            </aside>

            <main class="min-w-0 lg:col-span-3">
                @if (session('status'))
                    <div class="mb-5 rounded-[20px] border border-emerald-200 bg-emerald-50 px-5 py-3.5 text-sm font-medium text-emerald-800" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 rounded-[20px] border border-brand-200 bg-brand-50 px-5 py-3.5 text-sm text-brand-800" role="alert">
                        <ul class="list-disc space-y-0.5 ps-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @include('partials.footer')

    @include('components.fake-order-toast')
    @include('components.chatbot-widget')

    @vite(['resources/js/chatbot.js'])
    @stack('scripts')
</body>
</html>
