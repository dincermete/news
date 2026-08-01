@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /**
     * @var array{active_sites: int, published_orders: int, customers: int} $stats
     * @var array{newest: list<array<string, mixed>>, discounted: list<array<string, mixed>>, best_sellers: list<array<string, mixed>>} $sections
     * @var \Illuminate\Support\Collection<int, \App\Models\SiteCategory> $categories
     * @var \Illuminate\Support\Collection<int, \App\Models\FaqEntry> $faqs
     */
    $fmt = fn (int $n): string => number_format($n, 0, ',', '.');

    $marqueeDomains = collect($sections['best_sellers'])
        ->concat($sections['newest'])
        ->concat($sections['discounted'])
        ->pluck('domain')
        ->unique()
        ->take(12)
        ->values();

    $rotorWords = [
        ['text' => 'Tanıtımları', 'gradient' => 'linear-gradient(90deg, rgb(103, 76, 208), rgb(168, 168, 255))'],
        ['text' => 'Backlinkleri', 'gradient' => 'linear-gradient(90deg, rgb(240, 69, 170), rgb(238, 170, 210))'],
        ['text' => 'Bültenleri', 'gradient' => 'linear-gradient(90deg, rgb(103, 164, 41), rgb(174, 229, 118))'],
        ['text' => 'Yayınları', 'gradient' => 'linear-gradient(90deg, rgb(250, 136, 55), rgb(250, 172, 117))'],
    ];

    // Hero: banner ortasından sağa açılan yarım-ay — radyal olarak dönen, seyrek favicon halkaları.
    // Halka sayısı marqueeDomains ile birebir eşleşir (12), her favicon yörüngede yalnızca bir kez görünür.
    // Halkalar zıt yönlerde döner; içindeki favicon rozetleri döndükçe dik kalması için ters yönde
    // "counter" animasyonuyla dengelenir (klasik orbit CSS tekniği).
    $orbitRings = [
        ['count' => 5, 'radius' => 258, 'size' => 40, 'duration' => 46, 'ring' => 'orbit-ring', 'counter' => 'orbit-counter', 'offset' => 8],
        ['count' => 4, 'radius' => 172, 'size' => 50, 'duration' => 34, 'ring' => 'orbit-ring-reverse', 'counter' => 'orbit-counter-reverse', 'offset' => 42],
        ['count' => 3, 'radius' => 94, 'size' => 58, 'duration' => 24, 'ring' => 'orbit-ring', 'counter' => 'orbit-counter', 'offset' => 0],
    ];

    $money = fn (float $amount, string $currency): string => number_format($amount, 2, ',', '.').($currency === 'TRY' ? '₺' : '$');

    $moneyShort = function (?float $amount, string $suffix = ''): string {
        if ($amount === null || $amount <= 0.0) {
            return 'Katalog fiyatı';
        }

        return number_format($amount, 0, ',', '.').'₺'.$suffix;
    };

    $products = [
        ['name' => 'Site Yazısı', 'text' => 'Katalogdan kategori, fiyat ve DA/PA\'ya göre site seçip tanıtım yazınızı yayınlayın.', 'price' => $moneyShort($productPrices['site_article']), 'suffix' => '\'den başlayan', 'url' => route('sites.index'), 'icon' => 'M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5'],
        ['name' => 'Basın Bülteni', 'text' => 'Haber sitelerinde basın bülteninizi yayınlayın, geniş kitlelere ulaşın.', 'price' => $moneyShort($productPrices['press_release']), 'suffix' => '\'den başlayan', 'url' => route('press-release.index'), 'icon' => 'M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783'],
        ['name' => 'Footer Link', 'text' => 'Seçtiğiniz sitenin footer\'ında kalıcı veya süreli link yerleşimi alın.', 'price' => 'Katalog fiyatı', 'suffix' => '', 'url' => route('footer-links.index'), 'icon' => 'M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244'],
        ['name' => 'Tanıtım Paketleri', 'text' => 'Birden fazla siteyi tek pakette, tek işlemle satın alın.', 'price' => $moneyShort($productPrices['bundle']), 'suffix' => '\'den başlayan', 'url' => route('bundles.index'), 'icon' => 'M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9'],
        ['name' => 'SEO Paketleri', 'text' => 'SEO, GEO ve AEO\'yu tek pakette birleştiren aylık büyüme paketleri.', 'price' => $moneyShort($productPrices['seo_package']), 'suffix' => '/ay\'dan başlayan', 'url' => route('seo-packages.index'), 'icon' => 'M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25'],
        ['name' => 'Backlink Paketleri', 'text' => 'Yüksek otoriteli kaynaklardan doğal anchor dağılımıyla kalıcı backlink.', 'price' => $moneyShort($productPrices['backlink_package']), 'suffix' => '\'den başlayan', 'url' => route('backlink-packages.index'), 'icon' => 'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22'],
        ['name' => 'GEO', 'text' => 'ChatGPT, Gemini ve Perplexity gibi AI motorlarında kaynak gösterilin.', 'price' => 'Ücretsiz analiz', 'suffix' => '', 'url' => route('geo.index'), 'icon' => 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
    ];

    $fallbackFaqs = [
        ['q' => 'Başlamak için ödeme bilgisi gerekiyor mu?', 'a' => 'Hayır. Sadece e-postanızla ücretsiz kaydolursunuz; ödemeyi yalnızca sipariş verdiğinizde yaparsınız.'],
        ['q' => 'Makaleyi siz mi yazıyorsunuz?', 'a' => 'İsterseniz hazır içeriğinizi yükleyin, isterseniz editör ekibimiz SEO uyumlu özgün metni sizin için hazırlasın.'],
        ['q' => 'Yayın ne kadar sürede gerçekleşir?', 'a' => 'Hazır içeriklerde onaydan sonra 1–2 gün, içeriği bizim hazırladığımız siparişlerde 7 gün içinde yayındasınız.'],
        ['q' => 'Linkler kalıcı mı?', 'a' => 'Tüm yayınlar en az 6 ay link garantilidir; kaldırılan link ücretsiz yeniden yayınlanır ya da ücret iade edilir.'],
        ['q' => 'Verilerim güvende mi?', 'a' => 'Ödemeler PayTR güvencesiyle alınır, tüm trafik SSL ile şifrelenir ve fatura bilgileriniz yalnızca yasal zorunluluklar için saklanır.'],
    ];

    $faqItems = $faqs->isNotEmpty()
        ? $faqs->map(fn ($f) => ['q' => $f->question_topic, 'a' => $f->answer])->all()
        : $fallbackFaqs;

    // Worklane bileşen stilleri
    $h2 = 'font-display text-3xl font-medium leading-[1.2] tracking-[-0.01em] text-ink sm:text-[36px]';
    $sub = 'text-lg font-medium leading-relaxed text-ink-2';
    $btnWhite = 'group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-white to-[#c9c9c9] p-1 pe-4 text-sm font-medium text-ink transition hover:scale-[1.03] active:scale-[0.98]';
    $btnGhostDark = 'group inline-flex items-center gap-x-3 rounded-2xl border border-ink/10 bg-white p-1 pe-4 text-sm font-medium text-ink transition hover:bg-paper-2 hover:scale-[1.03] active:scale-[0.98]';
    $btnDark = 'group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-4 text-sm font-medium text-white transition hover:scale-[1.03] active:scale-[0.98]';
    $btnChip = 'inline-flex size-8 items-center justify-center rounded-xl';
    $arrowIcon = '<svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>';
@endphp

@section('content')
    {{-- ================= HERO ================= --}}
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-light relative overflow-hidden rounded-3xl text-ink">
            <div class="relative mx-auto max-w-7xl px-5 pt-16 sm:px-8  lg:pt-20" data-reveal-group>
                <div class="grid gap-10 lg:grid-cols-[1fr_auto] lg:items-end">
                    <div class="lg:max-w-md xl:max-w-xl pb-14 lg:pb-20">
                    <p class="inline-flex items-center gap-x-2 rounded-full border border-ink/10 bg-white py-1 pe-3.5 ps-1 text-xs text-ink-2 shadow-soft" data-reveal>
                        <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">Yeni</span>
                        Daha akıllı tanıtım yönetimi
                    </p>

                    <h1 class="mt-5 font-display text-[2rem] font-medium leading-[1.2] tracking-[-0.02em] sm:text-5xl" data-reveal>
                        Tanıtım Yazınız
                        <span
                            data-rough-underline
                            class="font-semibold text-brand-500"
                        >Haber </span>
                        Sitelerinde Yerini Alsın
                    </h1>

                    <p class="mt-5 max-w-md text-lg font-medium leading-relaxed text-ink-2" data-reveal>
                        Site aramayı, pazarlığı ve link kovalamayı bırakın. Tanıtım yazısı, basın bülteni ve backlink tek yerde.
                    </p>

                    <form method="get" action="{{ route('sites.index') }}" class="mt-7 flex w-full max-w-md items-center gap-2 rounded-full border border-ink/10 bg-white p-1.5 shadow-pop" role="search" data-reveal>
                        <svg class="ms-3 size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        <input
                            type="search"
                            name="q"
                            placeholder=""
                            class="w-full border-0 bg-transparent p-0 py-2.5 text-sm text-ink placeholder:text-ink-3 focus:ring-0"
                            aria-label="Site ara"
                            data-typewriter='["mynet.com","milliyet.com.tr","hurriyet.com.tr","ntv.com.tr","sabah.com.tr"]'
                            data-typewriter-prefix="Örn. "
                            autocomplete="off"
                        >
                        <button type="submit" class="inline-flex shrink-0 items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.03] active:scale-[0.98]">
                            Ara
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </button>
                    </form>
      
                    </div>

                    <div class="relative mx-auto hidden shrink-0 lg:block lg:h-[380px] lg:w-[280px] xl:h-[460px] xl:w-[360px]" data-reveal>
                        <div class="pointer-events-none absolute -top-6 start-1/2 h-48 w-48 -translate-x-1/2 rounded-full bg-accent-500/25 blur-3xl" aria-hidden="true"></div>
                        <div class="pointer-events-none absolute bottom-0 start-1/2 h-24 w-56 -translate-x-1/2 rounded-full bg-brand-500/20 blur-3xl" aria-hidden="true"></div>

                        <span class="absolute -start-4 top-2 z-20 rotate-[-6deg]">
                            <span class="float-badge flex rounded-2xl border border-ink/5 bg-white p-3 shadow-pop" style="animation-delay:.2s">
                                <img src="{{ asset('images/hero/logo-hurriyet.png') }}" alt="Hürriyet" class="h-8 w-auto">
                            </span>
                        </span>
                        <span class="absolute end-2 top-14 z-20 rotate-[5deg]">
                            <span class="float-badge flex rounded-2xl border border-ink/5 bg-white p-3 shadow-pop" style="animation-delay:1s">
                                <img src="{{ asset('images/hero/logo-ntv.png') }}" alt="NTV" class="h-8 w-auto">
                            </span>
                        </span>
                        <span class="absolute -start-6 top-[44%] z-20 -rotate-3">
                            <span class="float-badge flex rounded-2xl border border-ink/5 bg-white p-3 shadow-pop" style="animation-delay:1.6s">
                                <img src="{{ asset('images/hero/logo-mynet.png') }}" alt="Mynet" class="h-8 w-auto">
                            </span>
                        </span>
                        <span class="absolute end-[-10px] top-[56%] z-20 rotate-6">
                            <span class="float-badge flex rounded-2xl border border-ink/5 bg-white p-3 shadow-pop" style="animation-delay:.6s">
                                <img src="{{ asset('images/hero/logo-milliyet.png') }}" alt="Milliyet" class="h-8 w-auto">
                            </span>
                        </span>
                        <span class="absolute start-[20%] bottom-4 z-20 rotate-3">
                            <span class="float-badge flex rounded-2xl border border-ink/5 bg-white p-3 shadow-pop" style="animation-delay:1.3s">
                                <img src="{{ asset('images/hero/logo-sabah.png') }}" alt="Sabah" class="h-8 w-auto">
                            </span>
                        </span>

                        <img src="{{ asset('images/hero/person.png') }}" alt="" aria-hidden="true" class="absolute inset-x-0 bottom-0 z-10 mx-auto h-full w-auto object-contain object-bottom">
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-4xl rounded-[20px] mt-2 bg-paper overflow-hidden">
            <div class="relative min-w-0 flex-1 overflow-hidden px-4 py-4 sm:px-8">
            <h2 class="mx-auto flex max-w-full flex-nowrap items-baseline justify-center gap-x-2 text-center font-display text-base font-medium leading-[1.15] tracking-[-0.02em] text-zinc-700 sm:gap-x-3 sm:text-xl lg:text-2xl" data-reveal>
                    <span class="shrink-0 whitespace-nowrap">Markanızın tüm</span>
                    <span class="relative inline-block shrink-0 rotor-wrapper align-baseline">
                        <span class="rotor-measure"></span>

                        <span class="word-rotor" data-word-rotor>
                            @foreach ($rotorWords as $word)
                                <span style="background-image: {{ $word['gradient'] }}">{{ $word['text'] }}</span>
                            @endforeach
                        </span>

                        <span class="rotor-border tl"></span>
                        <span class="rotor-border tr"></span>
                        <span class="rotor-border bl"></span>
                        <span class="rotor-border br"></span>
                    </span>
                    <span class="shrink-0 whitespace-nowrap">tek panelde toplansın</span>
                </h2>
                {{-- Kenar solmaları --}}
                <span class="pointer-events-none absolute inset-y-0 start-0 w-[80px] bg-gradient-to-r from-paper to-transparent sm:w-[120px]" aria-hidden="true"></span>
                <span class="pointer-events-none absolute inset-y-0 end-0 w-[80px] bg-gradient-to-l from-paper to-transparent sm:w-[120px]" aria-hidden="true"></span>
            </div>
        </div>
    </section>

    {{-- ================= SİTE SEKMELERİ ================= --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group x-data="{ siteTab: 'popular' }">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-xl">
                <x-section-heading
                    data-reveal
                    gradient="from-[#2248ab] to-[#7aa2ff]"
                    icon="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c1.657 0 3-4.03 3-9s-1.343-9-3-9-3 4.03-3 9 1.343 9 3 9Zm-8.716-6.747h17.432M3.284 9.747h17.432"
                >Siteler</x-section-heading>
            </div>
            <div class="no-scrollbar -mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0" data-reveal>
                <div class="inline-flex w-max flex-nowrap items-center gap-1 rounded-full bg-paper p-1">
                    <button
                        type="button"
                        @click="siteTab = 'popular'"
                        :class="siteTab === 'popular' ? 'bg-white text-ink shadow-soft' : 'text-ink-2'"
                        class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold transition"
                    >Popüler Siteler</button>
                    <button
                        type="button"
                        @click="siteTab = 'newest'"
                        :class="siteTab === 'newest' ? 'bg-white text-ink shadow-soft' : 'text-ink-2'"
                        class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold transition"
                    >Yeni Siteler</button>
                    <button
                        type="button"
                        @click="siteTab = 'press'"
                        :class="siteTab === 'press' ? 'bg-white text-ink shadow-soft' : 'text-ink-2'"
                        class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold transition"
                    >Basın Bülteni</button>
                    <button
                        type="button"
                        @click="siteTab = 'best'"
                        :class="siteTab === 'best' ? 'bg-white text-ink shadow-soft' : 'text-ink-2'"
                        class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold transition"
                    >Çok Satanlar</button>
                </div>
            </div>
        </div>

        <div class="mt-10" data-reveal>
            <div x-show="siteTab === 'popular'">
                <x-site-table :sites="$popularSites" :favoritedSiteIds="$favoritedSiteIds" />
            </div>
            <div x-show="siteTab === 'newest'" x-cloak>
                <x-site-table :sites="$newestSites" :favoritedSiteIds="$favoritedSiteIds" />
            </div>
            <div x-show="siteTab === 'press'" x-cloak>
                <x-site-table
                    :sites="$pressReleaseSites"
                    :favoritedSiteIds="$favoritedSiteIds"
                    product-type="press_release"
                    :price="fn ($site) => (float) $site->price"
                />
            </div>
            <div x-show="siteTab === 'best'" x-cloak>
                <x-site-table :sites="$bestSellerSites" :favoritedSiteIds="$favoritedSiteIds" />
            </div>
        </div>

        <div class="mt-6 text-center" data-reveal>
            <a href="{{ route('sites.index') }}" class="{{ $btnDark }}">
                <span class="{{ $btnChip }} bg-white/15 text-white">{!! $arrowIcon !!}</span>
                Tüm siteleri gör
            </a>
        </div>
    </section>

    {{-- ================= POPÜLER PAKETLER (Tanıtım Paketleri) ================= --}}
    @if ($featuredBundles->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group x-data="autoSlider()">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-xl">
                    <x-section-heading
                        data-reveal
                        gradient="from-[#674cd0] to-[#a8a8ff]"
                        icon="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"
                    >Birden fazla siteyi tek pakette alın</x-section-heading>
                </div>
                <div class="flex shrink-0 items-center gap-2" data-reveal>
                    <button type="button" @click="prev()" class="inline-flex size-10 items-center justify-center rounded-full border border-ink/10 bg-white text-ink-2 shadow-soft transition hover:text-ink" aria-label="Önceki paketler">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                    </button>
                    <button type="button" @click="next()" class="inline-flex size-10 items-center justify-center rounded-full border border-ink/10 bg-white text-ink-2 shadow-soft transition hover:text-ink" aria-label="Sonraki paketler">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </button>
                    <a href="{{ route('bundles.index') }}" class="{{ $btnDark }}">
                        <span class="{{ $btnChip }} bg-white/15 text-white">{!! $arrowIcon !!}</span>
                        Tüm Paketler
                    </a>
                </div>
            </div>

            <ul x-ref="track" class="no-scrollbar mt-10 flex snap-x snap-mandatory gap-5 overflow-x-auto pb-2 pt-3" data-reveal>
                @foreach ($featuredBundles as $bundle)
                    <li class="w-full shrink-0 snap-start sm:w-[calc((100%-20px)/2)] lg:w-[calc((100%-40px)/3)]">
                        <x-bundle-card :bundle="$bundle" />
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- ================= BACKLINK PAKETLERİ ================= --}}
    @if ($featuredBacklinkPackages->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group x-data="autoSlider()">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-xl">
                    <x-section-heading
                        data-reveal
                        gradient="from-[#fa8837] to-[#faac75]"
                        icon="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"
                    >Otoriteyi büyüten backlink paketleri</x-section-heading>
                </div>
                <div class="flex shrink-0 items-center gap-2" data-reveal>
                    <button type="button" @click="prev()" class="inline-flex size-10 items-center justify-center rounded-full border border-ink/10 bg-white text-ink-2 shadow-soft transition hover:text-ink" aria-label="Önceki paketler">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                    </button>
                    <button type="button" @click="next()" class="inline-flex size-10 items-center justify-center rounded-full border border-ink/10 bg-white text-ink-2 shadow-soft transition hover:text-ink" aria-label="Sonraki paketler">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </button>
                    <a href="{{ route('backlink-packages.index') }}" class="{{ $btnDark }}">
                        <span class="{{ $btnChip }} bg-white/15 text-white">{!! $arrowIcon !!}</span>
                        Tüm Paketler
                    </a>
                </div>
            </div>

            <ul x-ref="track" class="no-scrollbar mt-10 flex snap-x snap-mandatory gap-5 overflow-x-auto pb-2 pt-5" data-reveal>
                @foreach ($featuredBacklinkPackages as $package)
                    <li class="w-full shrink-0 snap-start sm:w-[calc((100%-20px)/2)] lg:w-[calc((100%-40px)/3)]">
                        <x-backlink-package-card :package="$package" :feature-limit="5" />
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- ================= ÜRÜNLERİMİZ ================= --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group x-data="autoSlider({ auto: true, delay: 3500 })">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-xl">
                <x-section-heading
                    data-reveal
                    gradient="from-[#f045aa] to-[#eeaad2]"
                    icon="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"
                >İhtiyacınıza göre tek tek ya da paket halinde satın alın</x-section-heading>
                <p class="mt-4 {{ $sub }}" data-reveal>Gizli ücret yok; her ürünün fiyatı katalog sayfasında net olarak görünür.</p>
            </div>
            <div class="flex shrink-0 items-center gap-2" data-reveal>
                <button type="button" @click="prev()" class="inline-flex size-10 items-center justify-center rounded-full border border-ink/10 bg-white text-ink-2 shadow-soft transition hover:text-ink" aria-label="Önceki ürünler">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                </button>
                <button type="button" @click="next()" class="inline-flex size-10 items-center justify-center rounded-full border border-ink/10 bg-white text-ink-2 shadow-soft transition hover:text-ink" aria-label="Sonraki ürünler">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </button>
            </div>
        </div>

        <ul x-ref="track" class="no-scrollbar mt-10 flex snap-x snap-mandatory gap-5 overflow-x-auto pb-2 pt-3" data-reveal>
            @foreach ($products as $product)
                <li class="w-full shrink-0 snap-start sm:w-[calc((100%-20px)/2)] lg:w-[calc((100%-60px)/4)]">
                    <a href="{{ $product['url'] }}" class="group flex h-full flex-col rounded-[20px] bg-paper p-6 transition hover:-translate-y-0.5 hover:shadow-pop">
                        <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-[10px] bg-ink text-white">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $product['icon'] }}"/></svg>
                        </span>
                        <h3 class="mt-4 font-display text-lg font-semibold text-ink">{{ $product['name'] }}</h3>
                        <p class="mt-1.5 text-sm font-medium leading-relaxed text-ink-2">{{ $product['text'] }}</p>
                        <p class="mt-4 text-sm font-semibold text-ink">{{ $product['price'] }}{{ $product['suffix'] }}</p>
                        <span class="mt-auto flex items-center gap-x-1.5 pt-4 text-xs font-semibold text-ink-2 transition group-hover:text-ink">
                            İncele
                            <svg class="size-3 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="mx-auto max-w-2xl text-center">
            <x-section-heading
                data-reveal
                :centered="true"
                gradient="from-[#0d9488] to-[#5eead4]"
                icon="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.785 16.5 21.75l-.394-.965a1.5 1.5 0 0 0-1.079-1.078l-.965-.394.965-.394a1.5 1.5 0 0 0 1.079-1.078l.394-.965.394.965a1.5 1.5 0 0 0 1.078 1.078l.965.394-.965.394a1.5 1.5 0 0 0-1.078 1.078Z"
            >İhtiyacınız olan her şey tek yerde</x-section-heading>
        </div>

        <div class="mt-10 grid gap-5 md:grid-cols-3">
            {{-- Kart 1: üst üste binen sipariş satırları (Motion card stack) --}}
            <div class="relative overflow-hidden rounded-[20px] bg-paper p-8" data-reveal>
                <div class="relative mx-auto h-[260px] w-full max-w-[280px]" data-order-stack>
                    @foreach ([
                        ['Finans sitesinde tanıtım yazısı', 'Yayında', 'bg-emerald-100 text-emerald-700'],
                        ['Teknoloji bülteni dağıtımı', 'Editörde', 'bg-accent-100 text-accent-700'],
                        ['Footer link yerleşimi', 'Sırada', 'bg-amber-100 text-amber-700'],
                        ['SEO backlink paketi', 'Hazırlanıyor', 'bg-sky-100 text-sky-700'],
                        ['Basın bülteni gönderimi', 'Onayda', 'bg-violet-100 text-violet-700'],
                    ] as [$is, $durum, $tone])
                        <div
                            data-order-card
                            class="absolute inset-x-0 top-0 rounded-[10px] bg-white p-3.5 shadow-[0_5px_20px_rgba(10,11,11,0.1)]"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <p class="flex min-w-0 items-center gap-x-2 truncate text-[12px] font-semibold text-ink">
                                    <span class="inline-flex size-4 shrink-0 items-center justify-center rounded border border-ink/15 bg-paper">
                                        <svg class="size-2.5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    </span>
                                    {{ $is }}
                                </p>
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $tone }}">{{ $durum }}</span>
                            </div>
                            <div class="mt-2.5 flex items-center gap-1.5">
                                <span class="h-1.5 w-16 rounded-full bg-ink/10"></span>
                                <span class="h-1.5 w-8 rounded-full bg-ink/5"></span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <h3 class="mt-6 font-display text-[22px] font-semibold text-ink">Siparişlerin kontrolü sizde</h3>
                <p class="mt-2 {{ $sub }}">Siparişleri, teslim tarihlerini ve durumları araç değiştirmeden izleyin.</p>
            </div>

            {{-- Kart 2: site avatar dizisi + site kartı --}}
            <div class="relative overflow-hidden rounded-[20px] bg-paper p-8" data-reveal>
                <div class="flex h-[200px] flex-col justify-between">
                    <div class="flex items-center justify-center gap-2.5">
                        @foreach ([['H', 'bg-brand-100 text-brand-700', 'size-7'], ['O', 'bg-amber-100 text-amber-700', 'size-7'], ['N', 'bg-emerald-100 text-emerald-700', 'size-7'], ['E', 'bg-pink-100 text-pink-700', 'size-7'], ['★', 'bg-accent-600 text-white', 'size-10'], ['K', 'bg-accent-100 text-accent-700', 'size-7'], ['G', 'bg-purple-100 text-purple-700', 'size-7'], ['M', 'bg-teal-100 text-teal-700', 'size-7'], ['B', 'bg-orange-100 text-orange-700', 'size-7']] as [$harf, $tone, $boyut])
                            <span class="{{ $boyut }} {{ $tone }} inline-flex shrink-0 items-center justify-center rounded-full text-[11px] font-bold">{{ $harf }}</span>
                        @endforeach
                    </div>
                    <div class="mx-auto w-[86%] rounded-[11px] bg-white p-4 shadow-[0_5px_20px_rgba(10,11,11,0.1)]">
                        <div class="flex items-center gap-x-2.5">
                            <x-site-logo domain="habergazetesi.com.tr" :height="24" class="shrink-0" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[13px] font-semibold text-ink">habergazetesi.com.tr</p>
                                <p class="text-[11px] text-ink-2">Haber · Dofollow</p>
                            </div>
                            <span class="rounded-full bg-accent-100 px-2 py-0.5 text-[10px] font-semibold text-accent-700">DA 45</span>
                        </div>
                        <div class="mt-3 flex items-center gap-1.5">
                            <span class="h-1.5 w-20 rounded-full bg-ink/10"></span>
                            <span class="h-1.5 w-10 rounded-full bg-ink/5"></span>
                        </div>
                    </div>
                </div>
                <h3 class="mt-6 font-display text-[22px] font-semibold text-ink">Siteleriniz düzenli kalsın</h3>
                <p class="mt-2 {{ $sub }}">Notlar, favoriler ve yayın geçmişi tek yerde; ekibiniz hizalı kalır.</p>
            </div>

            {{-- Kart 3: gelir kartı + dairesel gösterge --}}
            <div class="relative overflow-hidden rounded-[20px] bg-paper p-8" data-reveal>
                <div class="flex h-[200px] items-center justify-between gap-4 rounded-[20px] bg-white p-5 shadow-[0_5px_20px_rgba(10,11,11,0.1)]">
                    <div class="min-w-0">
                        <p class="text-base font-bold text-ink">Bu ayki yayınlar</p>
                        <p class="mt-2 font-display text-[22px] font-semibold text-ink tabular-nums" data-countup="{{ max($stats['published_orders'], 1250) }}">{{ $fmt(max($stats['published_orders'], 1250)) }}</p>
                        <p class="mt-0.5 text-xs font-medium text-ink-2">Toplam yayın</p>
                    </div>
                    <div class="relative flex size-[130px] shrink-0 items-center justify-center rounded-full bg-emerald-200/50">
                        <svg class="absolute inset-0 size-full -rotate-90" viewBox="0 0 130 130" aria-hidden="true">
                            <circle cx="65" cy="65" r="58" fill="none" stroke="rgb(255 255 255 / 0.8)" stroke-width="7" />
                            <circle cx="65" cy="65" r="58" fill="none" stroke="#10b981" stroke-width="7" stroke-linecap="round" stroke-dasharray="364" stroke-dashoffset="91" />
                        </svg>
                        <span class="text-[22px] font-medium text-ink">75<span class="text-sm">%</span></span>
                    </div>
                </div>
                <h3 class="mt-6 font-display text-[22px] font-semibold text-ink">Rakamlarınızı anlayın</h3>
                <p class="mt-2 {{ $sub }}">Yayın adedi, harcama, fatura ve bakiye; hepsi görünür.</p>
            </div>
        </div>
    </section>

    {{-- ================= ÜRÜN TURU (Product overview) ================= --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between" data-reveal-group>
            <div class="max-w-xl">
                <x-section-heading
                    data-reveal
                    gradient="from-[#674cd0] to-[#a8a8ff]"
                    icon="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                >Günlük operasyonunuzun temeli</x-section-heading>
            </div>
            <a href="{{ route('sites.index') }}" class="{{ $btnDark }} shrink-0" data-reveal>
                <span class="{{ $btnChip }} bg-white/15 text-white">{!! $arrowIcon !!}</span>
                Hemen başlayın
            </a>
        </div>

        <div class="mt-10 grid gap-5 lg:grid-cols-2" data-reveal-group>
            {{-- Sipariş Takibi (geniş) --}}
            <div class="overflow-hidden rounded-[20px] bg-paper p-8 pb-0" data-reveal>
                <div class="flex items-center gap-x-3">
                    <span class="inline-flex size-10 items-center justify-center rounded-[10px] bg-gradient-to-br from-[#674cd0] to-[#a8a8ff] text-white">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </span>
                    <h3 class="font-display text-[22px] font-semibold text-ink">Sipariş Takibi</h3>
                </div>
                <p class="mt-3 {{ $sub }}">İşleri planlayın, sahiplerini atayın ve teslim tarihlerini uygulama değiştirmeden takip edin.</p>

                <div class="mt-6 rounded-t-2xl border border-b-0 border-ink/10 bg-white p-4">
                    <div class="flex items-center gap-2 border-b border-ink/5 pb-3">
                        @foreach (['Tümü', 'Yayında', 'Editörde', 'Onayda'] as $t => $tabAd)
                            <span @class(['rounded-lg px-3 py-1.5 text-[11px] font-semibold', 'bg-ink text-white' => $t === 0, 'bg-paper text-ink-2' => $t !== 0])>{{ $tabAd }}</span>
                        @endforeach
                    </div>
                    <ul class="divide-y divide-ink/5">
                        @foreach ([['Finans sitesinde tanıtım yazısı', 'S', 'bg-brand-100 text-brand-700', 'Bugün'], ['Teknoloji bülteni dağıtımı', 'E', 'bg-accent-100 text-accent-700', 'Yarın'], ['E-ticaret backlink paketi', 'M', 'bg-emerald-100 text-emerald-700', 'Çar'], ['Footer link yerleşimi', 'B', 'bg-amber-100 text-amber-700', 'Cum']] as [$is, $avatar, $tone, $gun])
                            <li class="flex items-center gap-3 py-3">
                                <span class="inline-flex size-4 items-center justify-center rounded border border-ink/15">
                                    <svg class="size-2.5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                </span>
                                <p class="min-w-0 flex-1 truncate text-[13px] font-medium text-ink">{{ $is }}</p>
                                <span class="hidden rounded-md bg-ink/5 px-2 py-1 text-[10px] font-semibold text-ink-2 sm:inline">{{ $gun }}</span>
                                <span class="{{ $tone }} inline-flex size-6 items-center justify-center rounded-full text-[10px] font-bold">{{ $avatar }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Site Yönetimi (dar) --}}
            <div class="relative overflow-hidden rounded-[20px] bg-paper p-8" data-reveal>
                <div class="flex items-center gap-x-3">
                    <span class="inline-flex size-10 items-center justify-center rounded-[10px] bg-gradient-to-br from-[#f045aa] to-[#eeaad2] text-white">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c1.657 0 3-4.03 3-9s-1.343-9-3-9-3 4.03-3 9 1.343 9 3 9Zm-8.716-6.747h17.432M3.284 9.747h17.432"/></svg>
                    </span>
                    <h3 class="font-display text-[22px] font-semibold text-ink">Site Yönetimi</h3>
                </div>
                <p class="mt-3 {{ $sub }}">Her sitenin notu, DA/PA verisi ve yayın durumu düzenli kalsın.</p>

                <div class="relative mt-6 h-[240px]">
                    {{-- Eş merkezli halkalar --}}
                    <div class="absolute start-1/2 top-1/2 size-[320px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-gradient-to-b from-[#dee6eb] to-transparent" aria-hidden="true"></div>
                    <div class="absolute start-1/2 top-1/2 size-[230px] -translate-x-1/2 -translate-y-1/2 rounded-full border border-ink/10 bg-white/40" aria-hidden="true"></div>
                    {{-- Yörünge avatarları --}}
                    @foreach ([['H', 'bg-brand-500', 'start-[8%] top-[16%] size-8'], ['E', 'bg-accent-500', 'end-[10%] top-[10%] size-9'], ['G', 'bg-emerald-500', 'end-[4%] bottom-[22%] size-7'], ['M', 'bg-amber-500', 'start-[4%] bottom-[14%] size-8']] as [$harf, $tone, $konum])
                        <span class="{{ $tone }} {{ $konum }} absolute inline-flex items-center justify-center rounded-full text-[10px] font-bold text-white shadow-soft" aria-hidden="true">{{ $harf }}</span>
                    @endforeach
                    {{-- Merkez site kartı --}}
                    <div class="absolute start-1/2 top-1/2 w-[230px] -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-4 shadow-[0_5px_20px_rgba(10,11,11,0.1)]">
                        <div class="flex items-center gap-x-2.5">
                            <x-site-logo domain="olaymedya.com" :height="24" class="shrink-0" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[13px] font-semibold text-ink">olaymedya.com</p>
                                <p class="text-[11px] text-ink-2">12 yayın · 2 not</p>
                            </div>
                        </div>
                        <div class="mt-3 flex gap-1.5">
                            <span class="rounded-full bg-accent-100 px-2 py-0.5 text-[10px] font-semibold text-accent-700">DA 38</span>
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Dofollow</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 grid gap-5 lg:grid-cols-2" data-reveal-group>
            {{-- Favori Sitelerim (dar) --}}
            <div class="rounded-[20px] bg-paper p-8" data-reveal>
                <div class="flex items-center gap-x-3">
                    <span class="inline-flex size-10 items-center justify-center rounded-[10px] bg-gradient-to-br from-[#67a429] to-[#aee576] text-white">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                    </span>
                    <h3 class="font-display text-[22px] font-semibold text-ink">Favori Sitelerim</h3>
                </div>
                <p class="mt-3 {{ $sub }}">Beğendiğiniz siteleri favorileyin, tek tıkla tekrar sipariş verin.</p>

                <div class="mt-6 space-y-2.5">
                    @foreach ([['habergazetesi.com.tr', 'DA 45', 'bg-brand-100 text-brand-700'], ['olaymedya.com', 'DA 38', 'bg-accent-100 text-accent-700'], ['nesilhaber.com', 'DA 41', 'bg-amber-100 text-amber-700']] as [$domain, $da, $tone])
                        <div class="flex items-center gap-3 rounded-full bg-white p-2 pe-3 shadow-soft">
                            <x-site-logo :domain="$domain" :height="32" class="shrink-0 rounded-full" />
                            <p class="min-w-0 flex-1 truncate text-[13px] font-semibold text-ink">{{ $domain }}</p>
                            <span class="{{ $tone }} shrink-0 rounded-md px-2 py-1 text-[11px] font-bold tabular-nums">{{ $da }}</span>
                            <svg class="size-4 shrink-0 text-brand-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-6.5-4.35-9.192-8.51C1.02 9.72 1.9 6.5 4.6 5.24c2.1-.98 4.2-.2 5.4 1.36C11.2 5.04 13.3 4.26 15.4 5.24c2.7 1.26 3.58 4.48 1.79 7.25C18.5 16.65 12 21 12 21Z"/></svg>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Harcama Takibi (geniş) --}}
            <div class="overflow-hidden rounded-[20px] bg-paper p-8 pb-0" data-reveal>
                <div class="flex items-center gap-x-3">
                    <span class="inline-flex size-10 items-center justify-center rounded-[10px] bg-gradient-to-br from-[#fa8837] to-[#faac75] text-white">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                    </span>
                    <h3 class="font-display text-[22px] font-semibold text-ink">Harcama Takibi</h3>
                </div>
                <p class="mt-3 {{ $sub }}">Faturaları, ödemeleri ve aylık toplamları tek temiz panelde görün.</p>

                <div class="mt-6 flex items-end gap-4">
                    <div class="hidden h-[220px] w-[90px] shrink-0 rounded-t-2xl bg-ink/5 sm:block" aria-hidden="true"></div>
                    <div class="min-w-0 flex-1 rounded-t-2xl bg-white p-5 shadow-[0_5px_20px_rgba(10,11,11,0.1)]">
                        <div class="flex items-center justify-between">
                            <p class="font-display text-lg font-semibold text-ink">Aylık Toplam</p>
                            <p class="font-display text-[22px] font-semibold text-ink tabular-nums">42.590₺</p>
                        </div>
                        <svg class="mt-4 h-[120px] w-full" viewBox="0 0 400 120" preserveAspectRatio="none" aria-hidden="true">
                            <defs>
                                <linearGradient id="alanDolgu" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#2248ab" stop-opacity="0.25" />
                                    <stop offset="100%" stop-color="#2248ab" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            <path d="M0,95 C40,88 60,70 95,74 C130,78 150,52 190,56 C230,60 250,38 290,34 C330,30 360,20 400,14 L400,120 L0,120 Z" fill="url(#alanDolgu)" />
                            <path d="M0,95 C40,88 60,70 95,74 C130,78 150,52 190,56 C230,60 250,38 290,34 C330,30 360,20 400,14" fill="none" stroke="#2248ab" stroke-width="2.5" />
                            <circle cx="290" cy="34" r="4" fill="#2248ab" stroke="#fff" stroke-width="2" />
                        </svg>
                        <div class="mt-2 flex justify-between text-[10px] font-medium text-ink-3">
                            @foreach (['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'] as $gun)
                                <span>{{ $gun }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= TANITIM YAZISI ================= --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
            <div data-reveal>
                <x-section-heading
                    data-reveal
                    gradient="from-[#67a429] to-[#aee576]"
                    icon="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5"
                >Tanıtım Yazısı</x-section-heading>

                <p class="mt-4 text-base font-medium leading-relaxed text-ink-2">
                    <strong class="font-semibold text-ink">Tanıtım yazısı;</strong> herhangi bir sitenin, hedeflenmiş anahtar kelimeler kullanılarak, Google ve diğer arama motorlarında, sitenin yükselmesini sağlayan, en önemli backlink metotlarından biridir.
                </p>
                <p class="mt-4 text-base font-medium leading-relaxed text-ink-2">
                    <strong class="font-semibold text-ink">Profesyonel tanıtım yazıları</strong> sayesinde, web sitesinde bulunan tüm ürün ve hizmetler kolayca tanıtılmış olur. Tanıtım yazılarının ikna edici özelliği, ürün ve hizmetlerin, hedef kitleye daha kolay ulaşmasına olanak tanır.
                </p>

                <h3 class="mt-7 font-display text-xl font-semibold text-ink">Tanıtım Yazısının Faydası Nedir?</h3>
                <p class="mt-3 text-base font-medium leading-relaxed text-ink-2">
                    Herhangi bir site için, anahtar kelimelerle oluşturulan tanıtım yazıları, SEO açısından önemlidir. Bunun yanında;
                </p>

                <ul class="mt-4 space-y-2.5">
                    @foreach ([
                        'Google ve diğer arama motorlarında bilinirlik sağlar.',
                        'Sitenizdeki ürün ve hizmetlerin tanıtımını yapmanın kolay yoludur.',
                        'Firma tanıtımı, reklam çalışmalarına gerek kalmadan yapılır.',
                        'Site hızı artmaya başlar.',
                    ] as $benefit)
                        <li class="flex items-start gap-x-2.5 text-sm font-medium text-ink-2">
                            <svg class="mt-0.5 size-4 shrink-0 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ $benefit }}
                        </li>
                    @endforeach
                </ul>

                <a href="#sss" class="mt-7 inline-flex items-center gap-x-2 rounded-full bg-brand-500 px-5 py-3 text-sm font-semibold text-white transition hover:scale-[1.03] active:scale-[0.98]">
                    Sık Sorulan Sorular
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 9.879a3 3 0 1 1 3.414 4.821c-.735.51-1.293 1.235-1.293 2.1M12 17.25h.008v.008H12v-.008Z"/></svg>
                </a>
            </div>

            <div class="relative mx-auto hidden max-w-md py-6 lg:block" data-reveal>
                <img src="{{ asset('images/tanitim/siteler-gorsel.png') }}" alt="Haber sitelerinde yayınlanmış tanıtım yazıları" class="mx-auto w-[85%]">

                <span class="absolute start-0 top-0 z-20 -rotate-6">
                    <span class="float-badge flex rounded-2xl border border-ink/5 bg-white p-3 shadow-pop" style="animation-delay:.2s">
                        <img src="{{ asset('images/tanitim/logo-posta.png') }}" alt="Posta" class="h-6 w-auto">
                    </span>
                </span>
                <span class="absolute end-0 top-6 z-20 rotate-3">
                    <span class="float-badge flex rounded-2xl border border-ink/5 bg-white p-3 shadow-pop" style="animation-delay:1s">
                        <img src="{{ asset('images/tanitim/logo-iha.png') }}" alt="İHA" class="h-6 w-auto">
                    </span>
                </span>
                <span class="absolute start-0 top-1/2 z-20 -translate-y-1/2 rotate-3">
                    <span class="float-badge flex rounded-2xl border border-ink/5 bg-white p-3 shadow-pop" style="animation-delay:1.6s">
                        <img src="{{ asset('images/tanitim/logo-aksam.png') }}" alt="Akşam" class="h-6 w-auto">
                    </span>
                </span>
                <span class="absolute end-0 bottom-16 z-20 -rotate-3">
                    <span class="float-badge flex rounded-2xl border border-ink/5 bg-white p-3 shadow-pop" style="animation-delay:.6s">
                        <img src="{{ asset('images/tanitim/logo-dha.png') }}" alt="DHA" class="h-6 w-auto">
                    </span>
                </span>
                <span class="absolute start-1/2 bottom-0 z-20 -translate-x-1/2 rotate-2">
                    <span class="float-badge flex rounded-2xl border border-ink/5 bg-white p-3 shadow-pop" style="animation-delay:1.3s">
                        <img src="{{ asset('images/tanitim/logo-onedio.png') }}" alt="onedio" class="h-6 w-auto">
                    </span>
                </span>
            </div>
        </div>
    </section>

    {{-- ================= SSS ================= --}}
    <section id="sss" class="mx-auto max-w-7xl px-4 pb-20 pt-16 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
            <div class="lg:sticky lg:top-28 lg:self-start" data-reveal-group>
                <x-section-heading
                    class="max-w-md"
                    data-reveal
                    gradient="from-[#ca8a04] to-[#facc15]"
                    icon="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"
                >Sık sorulan soruların cevapları</x-section-heading>
                <p class="mt-4 {{ $sub }}" data-reveal>Cevabınızı bulamadınız mı?</p>
                <a href="tel:{{ $siteSettings->support_phone ?: '08503052241' }}" class="{{ $btnDark }} mt-4" data-reveal>
                    <span class="{{ $btnChip }} bg-white/15 text-white">
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                    </span>
                    Bizimle konuşun
                </a>
            </div>

            <div class="space-y-3" data-reveal-group>
                @foreach ($faqItems as $index => $faq)
                    <div x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }" class="rounded-2xl bg-paper" data-reveal>
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-4 px-6 py-5 text-start focus:outline-hidden"
                            @click="open = !open"
                            :aria-expanded="open.toString()"
                        >
                            <span class="text-sm font-medium text-ink">{{ $faq['q'] }}</span>
                            <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full border border-ink/10 bg-white text-ink transition-transform duration-300" :class="open ? 'rotate-45' : ''">
                                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            </span>
                        </button>
                        <div x-show="open" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="-translate-y-1 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" @if ($index !== 0) x-cloak @endif class="px-6 pb-5 text-[13px] font-medium leading-relaxed text-ink-2">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
