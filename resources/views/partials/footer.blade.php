@php
    $productLinks = [
        ['label' => 'Siteler', 'url' => route('sites.index')],
        ['label' => 'Basın Bülteni', 'url' => route('press-release.index')],
        ['label' => 'Tanıtım Paketleri', 'url' => route('bundles.index')],
        ['label' => 'Story Satış', 'url' => route('story.index')],
        ['label' => 'Footer Link', 'url' => route('footer-links.index')],
    ];

    $serviceLinks = [
        ['label' => 'SEO Paketleri', 'url' => route('seo-packages.index')],
        ['label' => 'Backlink Paketleri', 'url' => route('backlink-packages.index')],
        ['label' => 'GEO', 'url' => route('geo.index')],
        ['label' => 'Yapay Zeka Görünürlük', 'url' => url('/yapay-zeka-gorunurluk')],
        ['label' => 'Ücretsiz Analiz', 'url' => route('free-analysis.show')],
    ];

    $companyLinks = [
        ['label' => 'Hakkımızda', 'url' => route('about.show')],
        ['label' => 'İletişim', 'url' => route('contact.show')],
        ['label' => 'Destek', 'url' => auth()->check() ? route('account.support-tickets') : route('login')],
    ];

    $accountLinks = [
        ['label' => 'Hesabım', 'url' => auth()->check() ? route('account.dashboard') : route('login')],
        ['label' => 'Kayıt Ol', 'url' => route('register')],
        ['label' => 'Giriş Yap', 'url' => route('login')],
    ];

    $legalLinks = [
        ['label' => 'Gizlilik Politikası', 'url' => route('pages.show', 'gizlilik')],
        ['label' => 'Mesafeli Satış Sözleşmesi', 'url' => route('pages.show', 'mesafeli-satis-sozlesmesi')],
        ['label' => 'Ön Bilgilendirme Formu', 'url' => route('pages.show', 'on-bilgilendirme-formu')],
        ['label' => 'KVKK Aydınlatma', 'url' => route('pages.show', 'kvkk')],
        ['label' => 'Çerez Politikası', 'url' => route('pages.show', 'cerez-politikasi')],
        ['label' => 'Üyelik Sözleşmesi', 'url' => route('pages.show', 'uyelik-sozlesmesi')],
    ];

    try {
        $fromDb = \App\Models\Page::query()
            ->legal()
            ->active()
            ->orderBy('title')
            ->get(['title', 'slug']);

        if ($fromDb->isNotEmpty()) {
            $legalLinks = $fromDb
                ->map(fn (\App\Models\Page $page): array => [
                    'label' => $page->title,
                    'url' => route('pages.show', $page->slug),
                ])
                ->all();
        }
    } catch (\Throwable) {
        // Migration öncesi fallback sabit linkler.
    }

    $social = collect([
        ['label' => 'Instagram', 'url' => $siteSettings->social_instagram, 'path' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z'],
        ['label' => 'X', 'url' => $siteSettings->social_x, 'path' => 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.727-8.835L1.254 2.25H8.08l4.251 5.699L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z'],
        ['label' => 'YouTube', 'url' => $siteSettings->social_youtube, 'path' => 'M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z'],
        ['label' => 'LinkedIn', 'url' => $siteSettings->social_linkedin, 'path' => 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z'],
    ])->filter(fn (array $item): bool => filled($item['url']))->values()->all();

    $phone = $siteSettings->support_phone ?: '08503052241';
    $phoneDisplay = $siteSettings->support_phone_display ?: '0850 305 22 41';
    $payments = ['Visa', 'Mastercard', 'Troy', 'Amex', 'PayTR'];
@endphp

<style>
    .ty-footer-cols {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        column-gap: 1.5rem;
        row-gap: 2.5rem;
    }

    @media (min-width: 768px) {
        .ty-footer-cols {
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 1.5rem;
        }
    }
</style>

<footer class="mt-auto px-2 pb-2 sm:px-3 sm:pb-3">
    <div class="panel-dark overflow-hidden rounded-3xl text-white">
        <div class="mx-auto w-full max-w-6xl px-5 pt-14 sm:px-8">
            {{-- Üst CTA --}}
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="font-display text-3xl font-semibold leading-tight sm:text-4xl">
                    Yayınlarınızı, sitelerinizi ve bütçenizi bir araya getirin
                </h2>
                <div class="mt-7 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('register') }}" class="group inline-flex items-center gap-x-3 rounded-2xl bg-white p-1 pe-4 text-sm font-medium text-ink transition hover:scale-[1.03] active:scale-[0.98]">
                        <span class="inline-flex size-8 items-center justify-center rounded-xl bg-ink text-white">
                            <svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </span>
                        Hemen Başla
                    </a>
                    <a href="tel:{{ $phone }}" class="group inline-flex items-center gap-x-3 rounded-2xl border border-white/15 bg-white/5 p-1 pe-4 text-sm font-medium text-white transition hover:bg-white/10 hover:scale-[1.03] active:scale-[0.98]">
                        <span class="inline-flex size-8 items-center justify-center rounded-xl bg-white/10 text-white">
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                        </span>
                        {{ $phoneDisplay }}
                    </a>
                </div>
                <p class="mt-4 text-xs text-white/45">Kayıt ücretsiz. Kart bilgisi gerekmez.</p>
            </div>

            {{-- Link kolonları: her zaman 5 kolon (mobilde 2) --}}
            <div class="ty-footer-cols mt-14 border-t border-white/10 pt-10">
                <div>
                    <h4 class="text-[13px] font-semibold text-white">Ürünler</h4>
                    <ul class="mt-4 space-y-2.5 text-[13px]">
                        @foreach ($productLinks as $item)
                            <li><a href="{{ $item['url'] }}" class="text-white/55 transition hover:text-white">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h4 class="text-[13px] font-semibold text-white">Hizmetler</h4>
                    <ul class="mt-4 space-y-2.5 text-[13px]">
                        @foreach ($serviceLinks as $item)
                            <li><a href="{{ $item['url'] }}" class="text-white/55 transition hover:text-white">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h4 class="text-[13px] font-semibold text-white">Kurumsal</h4>
                    <ul class="mt-4 space-y-2.5 text-[13px]">
                        @foreach ($companyLinks as $item)
                            <li><a href="{{ $item['url'] }}" class="text-white/55 transition hover:text-white">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h4 class="text-[13px] font-semibold text-white">Hesap</h4>
                    <ul class="mt-4 space-y-2.5 text-[13px]">
                        @foreach ($accountLinks as $item)
                            <li><a href="{{ $item['url'] }}" class="text-white/55 transition hover:text-white">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h4 class="text-[13px] font-semibold text-white">Yasal</h4>
                    <ul class="mt-4 space-y-2.5 text-[13px]">
                        @foreach ($legalLinks as $item)
                            <li><a href="{{ $item['url'] }}" class="text-white/55 transition hover:text-white">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Güvenlik şeridi --}}
            <div class="mt-12 flex flex-col gap-5 rounded-2xl border border-white/10 bg-white/5 p-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h4 class="text-[13px] font-semibold text-white">Güvenebileceğiniz altyapı</h4>
                    <p class="mt-1 max-w-md text-[12px] leading-relaxed text-white/55">
                        Ödemeler PayTR güvencesinde, tüm trafik SSL ile şifreli; verileriniz her seviyede korunur.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-1.5">
                    @foreach ($payments as $payment)
                        <span class="inline-flex items-center rounded-full border border-white/15 bg-white/5 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wide text-white/70">
                            {{ $payment }}
                        </span>
                    @endforeach
                </div>
            </div>

            {{-- Alt bar --}}
            <div class="mt-8 flex flex-col gap-4 border-t border-white/10 py-6 text-xs text-white/45 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} {{ $siteSettings->siteName() }}. Her hakkı saklıdır.</p>
                <div class="flex items-center gap-2">
                    @foreach ($social as $item)
                        <a
                            href="{{ $item['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex size-8 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white/60 transition hover:bg-white/10 hover:text-white"
                            aria-label="{{ $item['label'] }}"
                        >
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="{{ $item['path'] }}" />
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</footer>
