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

    $howSteps = [
        ['no' => '01', 'title' => 'Hesabını oluştur', 'text' => 'Firma bilgilerinizi ekleyin, panelinizi açın ve mevcut sitelerinizi/markalarınızı tanımlayın.'],
        ['no' => '02', 'title' => 'Sitelerini seç', 'text' => 'Katalogdan kategori, fiyat ve DA/PA değerine göre filtreleyip markanıza uygun siteleri seçin.'],
        ['no' => '03', 'title' => 'Yayını ve bütçeyi izle', 'text' => 'İçeriği gönderin; yayın linkleri, raporlar ve harcamalar panelinize otomatik düşsün.'],
    ];

    $aiTabs = [
        ['key' => 'icerik', 'label' => 'Otomatik içerik', 'title' => 'İçeriği yapay zekâ yazsın', 'text' => 'Sepette "Makale Yazdır" seçeneğini işaretleyip birkaç cümlelik brief verin; AI, SEO uyumlu özgün tanıtım yazınızı hazırlasın — siz tek satır yazmadan.', 'benefits' => ['Brief’ten yayına hazır metin üretir', 'Anahtar kelimeleri doğal yerleştirir', 'Kelime paketine göre fiyatlanır']],
        ['key' => 'oneri', 'label' => 'Sohbet asistanı', 'title' => 'Bütçenize göre site önerisi', 'text' => 'Sağ alttaki sohbet asistanına bütçenizi ve hedef kelimenizi yazın; katalogdaki gerçek sitelerden en uygun seçkiyi anında önersin.', 'benefits' => ['Bütçeye göre gerçek site önerileri', 'Sık sorulan sorulara anında yanıt', 'Gerekirse canlı destek ekibine yönlendirir']],
    ];

    $counters = [
        ['value' => $stats['active_sites'], 'suffix' => '+', 'title' => 'Aktif haber sitesi', 'text' => 'Onaylı, DA/PA verisi güncel katalog.', 'chip' => 'from-[#67a429] to-[#aee576]'],
        ['value' => $stats['published_orders'], 'suffix' => '+', 'title' => 'Yayınlanmış yazı', 'text' => 'Tek panelden yayına alınan içerik.', 'chip' => 'from-[#f045aa] to-[#eeaad2]'],
        ['value' => $stats['customers'], 'suffix' => '+', 'title' => 'Memnun müşteri', 'text' => 'Ajanslar, markalar ve girişimler.', 'chip' => 'from-[#674cd0] to-[#a8a8ff]'],
    ];

    $roles = ['Ajanslar', 'SEO Uzmanları', 'E-Ticaret', 'Girişimler', 'Markalar', 'Freelancerlar', 'Daha fazlası+'];

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
        ['name' => 'Story Satış', 'text' => 'Instagram hesaplarında Post veya Story olarak markanızı duyurun.', 'price' => 'Hesaba özel fiyat', 'suffix' => '', 'url' => route('story.index'), 'icon' => 'M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169'],
        ['name' => 'Tanıtım Paketleri', 'text' => 'Birden fazla siteyi tek pakette, tek işlemle satın alın.', 'price' => $moneyShort($productPrices['bundle']), 'suffix' => '\'den başlayan', 'url' => route('bundles.index'), 'icon' => 'M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.653v-2.882a2.25 2.25 0 0 0-.596-1.6'],
        ['name' => 'SEO Paketleri', 'text' => 'SEO, GEO ve AEO\'yu tek pakette birleştiren aylık büyüme paketleri.', 'price' => $moneyShort($productPrices['seo_package']), 'suffix' => '/ay\'dan başlayan', 'url' => route('seo-packages.index'), 'icon' => 'M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25'],
        ['name' => 'Backlink Paketleri', 'text' => 'Yüksek otoriteli kaynaklardan doğal anchor dağılımıyla kalıcı backlink.', 'price' => $moneyShort($productPrices['backlink_package']), 'suffix' => '\'den başlayan', 'url' => route('backlink-packages.index'), 'icon' => 'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22'],
        ['name' => 'GEO', 'text' => 'ChatGPT, Gemini ve Perplexity gibi AI motorlarında kaynak gösterilin.', 'price' => 'Ücretsiz analiz', 'suffix' => '', 'url' => route('geo.index'), 'icon' => 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
    ];

    $testimonials = [
        ['quote' => 'İşlerin ve bütçenin nerede durduğunu artık her an net görebiliyorum.', 'name' => 'Murat K.', 'role' => 'E-Ticaret Yöneticisi, İstanbul'],
        ['quote' => 'Ekibimiz sürekli yazışmadan da hizalı kalıyor. Panel her gün net bir görünüm veriyor. Yeni başlayan arkadaşlar süreci günler içinde kavradı; ayrıca bir eğitim gerekmedi.', 'name' => 'Elif S.', 'role' => 'SEO Uzmanı, Ankara'],
        ['quote' => 'Üç ayrı aracı tek platformla değiştirdik. Sipariş takibi ve site görünürlüğü nihayet mantıklı.', 'name' => 'Baran T.', 'role' => 'Ajans Kurucusu, İzmir'],
        ['quote' => 'İlk kez yayınların ve harcamaların nerede durduğunu tek ekranda görebiliyorum.', 'name' => 'Zeynep A.', 'role' => 'Pazarlama Direktörü, Bursa'],
        ['quote' => 'Birden çok müşteriyi bağlam kaybetmeden yönetiyorum. Notlar, siparişler ve raporlar birbirine bağlı. Kaçan güncelleme neredeyse kalmadı; her şey tek yerde yaşıyor.', 'name' => 'Onur D.', 'role' => 'Müşteri Ekibi Lideri, Antalya'],
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
    $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
    $h2 = 'font-display text-3xl font-medium leading-[1.2] tracking-[-0.01em] text-ink sm:text-[44px] lg:text-[52px]';
    $sub = 'text-lg font-medium leading-relaxed text-ink-2';
    $btnWhite = 'group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-white to-[#c9c9c9] p-1 pe-4 text-sm font-medium text-ink transition hover:scale-[1.03] active:scale-[0.98]';
    $btnGhostDark = 'group inline-flex items-center gap-x-3 rounded-2xl border border-white/15 bg-white/5 p-1 pe-4 text-sm font-medium text-white transition hover:bg-white/10 hover:scale-[1.03] active:scale-[0.98]';
    $btnDark = 'group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-4 text-sm font-medium text-white transition hover:scale-[1.03] active:scale-[0.98]';
    $btnChip = 'inline-flex size-8 items-center justify-center rounded-xl';
    $arrowIcon = '<svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>';
@endphp

@section('content')
    {{-- ================= HERO ================= --}}
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-dark relative overflow-hidden rounded-3xl text-white">
            <div class="relative mx-auto max-w-6xl px-5 pb-14 pt-16 sm:px-8 lg:pb-20 lg:pt-24" data-reveal-group>
                <div class="lg:max-w-3xl">
                    <p class="inline-flex items-center gap-x-2 rounded-full border border-white/15 bg-white/5 py-1 pe-3.5 ps-1 text-xs text-white/80" data-reveal>
                        <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">Yeni</span>
                        Daha akıllı tanıtım yönetimi
                    </p>

                    <h1 class="mt-5 font-display text-4xl font-medium leading-[1.1] sm:text-5xl lg:text-[3.6rem]" data-reveal>
                        Markanızın tüm
                        <div class="relative inline-block rotor-wrapper ml-4">
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
                        </div>                        
                        <br>
                        tek panelde toplansın
                    </h1>

                    <p class="mt-5 max-w-md text-lg font-medium leading-relaxed text-white/65" data-reveal>
                        Site aramayı, pazarlığı ve link kovalamayı bırakın. Tanıtım yazısı, basın bülteni ve backlink tek yerde.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3" data-reveal>
                        <a href="{{ route('sites.index') }}" class="{{ $btnWhite }}">
                            <span class="{{ $btnChip }} bg-gradient-to-b from-black to-[#363b3c] text-white">{!! $arrowIcon !!}</span>
                            Hemen Başla
                        </a>
                        <a href="{{ url('/backlink-paketleri') }}" class="{{ $btnGhostDark }}">
                            <span class="{{ $btnChip }} bg-white/10 text-white">
                                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.14v14l11-7-11-7Z"/></svg>
                            </span>
                            Paketleri İncele
                        </a>
                    </div>
                    <p class="mt-4 text-xs text-white/45" data-reveal>Kayıt ücretsiz. Kart bilgisi gerekmez.</p>

                    <div class="mt-10 flex flex-wrap items-center gap-x-10 gap-y-5" data-reveal>
                        <div>
                            <p class="flex items-center gap-x-1.5">
                                <svg class="size-4 text-amber-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd"/></svg>
                                <span class="font-display text-2xl font-semibold">4.8</span>
                                <span class="text-sm text-white/50">/5.0</span>
                            </p>
                            <p class="mt-1 text-xs text-white/50">{{ $fmt($stats['customers']) }}+ müşteri değerlendirmesi</p>
                        </div>
                        <div>
                            <p class="flex items-center gap-x-1.5">
                                <svg class="size-4 text-accent-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c1.657 0 3-4.03 3-9s-1.343-9-3-9-3 4.03-3 9 1.343 9 3 9Zm-8.716-6.747h17.432M3.284 9.747h17.432"/></svg>
                                <span class="font-display text-2xl font-semibold tabular-nums" data-odometer="{{ $stats['active_sites'] }}" data-odometer-suffix="+">{{ $fmt($stats['active_sites']) }}+</span>
                            </p>
                            <p class="mt-1 text-xs text-white/50">Katalogda aktif haber sitesi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl rounded-b-[20px] bg-paper overflow-hidden">
            <div class="mx-auto flex max-w-6xl flex-col items-center gap-5 px-5 py-[30px] sm:px-8 lg:flex-row lg:gap-10">
                <p class="shrink-0 text-center font-display text-[22px] font-semibold leading-[1.25] text-ink lg:max-w-[190px] lg:text-start">Türkiye çapında ekipler güveniyor</p>
                @if ($marqueeDomains->isNotEmpty())
                    <div class="relative min-w-0 flex-1 overflow-hidden">
                        <div class="marquee-track items-center gap-10 pe-10" style="--marquee-duration: 36s">
                            @foreach ([0, 1] as $copy)
                                @foreach ($marqueeDomains as $domain)
                                    <a
                                        href="{{ route('sites.show', $domain) }}"
                                        @if ($copy === 1) aria-hidden="true" tabindex="-1" @endif
                                        class="inline-flex h-5 shrink-0 items-center gap-x-2 text-sm font-semibold text-ink-3 grayscale transition hover:text-ink hover:grayscale-0"
                                    >
                                        <x-site-favicon :domain="$domain" :size="16" class="shrink-0 opacity-60" />
                                        {{ $domain }}
                                    </a>
                                @endforeach
                            @endforeach
                        </div>
                        {{-- Kenar solmaları (şablondaki 120px degrade maskeler) --}}
                        <span class="pointer-events-none absolute inset-y-0 start-0 w-[120px] bg-gradient-to-r from-paper to-transparent" aria-hidden="true"></span>
                        <span class="pointer-events-none absolute inset-y-0 end-0 w-[120px] bg-gradient-to-l from-paper to-transparent" aria-hidden="true"></span>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="mx-auto max-w-2xl text-center">
            <p data-reveal>
                <span class="inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft">
                    Neler sunuyorsunuz
                </span>
            </p>

            <h2 class="mt-5 font-display text-3xl font-medium leading-[1.2] tracking-[-0.01em] text-ink sm:text-[44px] lg:text-[52px]" data-reveal>
                İhtiyacınız olan her şey tek yerde
            </h2>
        </div>

        <div class="mt-10 grid gap-5 md:grid-cols-3">
            {{-- Kart 1: üst üste binen sipariş satırları (Motion card stack) --}}
            <div class="relative overflow-hidden rounded-[20px] bg-paper p-8" data-reveal>
                <div class="relative mx-auto h-[260px] w-full max-w-[280px]" data-order-stack>
                    @foreach ([
                        ['Finans sitesinde tanıtım yazısı', 'Yayında', 'bg-emerald-100 text-emerald-700'],
                        ['Teknoloji bülteni dağıtımı', 'Editörde', 'bg-accent-100 text-accent-700'],
                        ['Story yayını — 3 hesap', 'Sırada', 'bg-amber-100 text-amber-700'],
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
                            <x-site-favicon domain="habergazetesi.com.tr" :size="24" class="shrink-0" />
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

    {{-- ================= NASIL ÇALIŞIR (koyu panel + akordeon) ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
            {{-- Sol: birleşik panel görseli (Worklane "No switching tools" kartı) --}}
            <div class="relative overflow-hidden rounded-[20px] bg-ink px-8 pb-10 pt-14 lg:min-h-[578px]" data-reveal>
                {{-- Üstte mor + mavi ışıma elipsleri --}}
                <div class="pointer-events-none absolute -top-8 start-1/2 h-20 w-[640px] -translate-x-1/2 rounded-[50%] bg-[#6b10e4]/60 blur-[80px]" aria-hidden="true"></div>
                <div class="pointer-events-none absolute top-16 start-1/2 h-20 w-[640px] -translate-x-1/2 rounded-[50%] bg-[#1774de]/45 blur-[90px]" aria-hidden="true"></div>

                {{-- Kesikli yörünge elipsleri --}}
                <div class="pointer-events-none absolute start-1/2 top-[10%] h-[243px] w-[640px] -translate-x-1/2 rounded-[50%] border border-dashed border-white/20" aria-hidden="true"></div>
                <div class="pointer-events-none absolute start-1/2 top-[24%] h-[243px] w-[640px] -translate-x-1/2 rounded-[50%] border border-dashed border-white/10" aria-hidden="true"></div>

                {{-- Yörüngelerdeki ikonlu daireler (şablon boyut/renkleri) --}}
                <div class="relative mx-auto h-[345px] max-w-[345px] sm:max-w-[420px]" aria-hidden="true">
                    {{-- 112px açık mavi: Basın Bülteni --}}
                    <span class="absolute start-[16%] top-[2%] inline-flex size-[112px] items-center justify-center rounded-full bg-[#9ccaff]">
                        <svg class="size-[56px] text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73"/></svg>
                    </span>
                    {{-- 51px pembe: Story --}}
                    <span class="absolute start-[2%] top-[42%] inline-flex size-[51px] items-center justify-center rounded-full bg-[#f045aa]">
                        <svg class="size-[26px] text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z"/></svg>
                    </span>
                    {{-- 46px beyaz: AI --}}
                    <span class="absolute start-[54%] top-0 inline-flex size-[46px] items-center justify-center rounded-full bg-white">
                        <svg class="size-[24px] text-ink" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                    </span>
                    {{-- 80px mavi: Tanıtım Yazısı --}}
                    <span class="absolute end-[6%] top-[12%] inline-flex size-[80px] items-center justify-center rounded-full bg-[#1774de]">
                        <svg class="size-[40px] text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5"/></svg>
                    </span>
                    {{-- 60px yeşil: GEO --}}
                    <span class="absolute end-0 top-[56%] inline-flex size-[60px] items-center justify-center rounded-full bg-[#67a429]">
                        <svg class="size-[30px] text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                    </span>
                    {{-- 40px turuncu: Rapor --}}
                    <span class="absolute start-[12%] top-[70%] inline-flex size-[40px] items-center justify-center rounded-full bg-[#fa8837]">
                        <svg class="size-[24px] text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                    </span>
                    {{-- 91px yeşil: Backlink --}}
                    <span class="absolute start-[36%] top-[58%] inline-flex size-[91px] items-center justify-center rounded-full bg-[#67a429]">
                        <svg class="size-[45px] text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
                    </span>

                    {{-- Merkez: 190px mor→mavi gradyan daire içinde uygulama logosu --}}
                    <span class="absolute start-1/2 top-[46%] flex size-[190px] -translate-x-1/2 flex-col items-center justify-center gap-2 rounded-full bg-gradient-to-br from-[#674cd0] to-[#1774de] shadow-[0_0_80px_rgba(103,76,208,0.5)]">
                        <svg class="size-[70px] text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <span class="font-display text-sm font-semibold text-white/90">NewsTanıtım</span>
                    </span>
                </div>

                <div class="relative mt-10 text-center">
                    <span class="inline-flex items-center rounded-[10px] bg-white/10 px-3.5 py-2 text-sm font-medium text-white">Araç değiştirmek yok</span>
                    <p class="mx-auto mt-3 max-w-sm text-lg font-medium text-white">Tüm yollar NewsTanıtım birleşik paneline çıkar</p>
                </div>
            </div>

            {{-- Sağ: başlık + adım akordeonu --}}
            <div x-data="{ step: 0 }">
                <div data-reveal-group>
                    <p data-reveal><span class="{{ $chip }}">Nasıl çalışır</span></p>
                    <h2 class="mt-5 {{ $h2 }}" data-reveal>Sizi dakikalar içinde yayına hazırlayan kurulum</h2>
                    <p class="mt-4 {{ $sub }}" data-reveal>Bir kez kurun, ekibinizi ekleyin ve her şeyi tek panelden yönetin.</p>
                </div>

                <div class="mt-8 space-y-6">
                    @foreach ($howSteps as $i => $step)
                        <div class="flex cursor-pointer items-start gap-4" @click="step = {{ $i }}" data-reveal>
                            <span
                                class="inline-flex size-10 shrink-0 items-center justify-center rounded-[10px] font-display text-lg font-semibold transition-colors duration-300"
                                :class="step === {{ $i }} ? 'bg-gradient-to-b from-black to-[#363b3c] text-white' : 'bg-white text-ink-2 shadow-soft'"
                            >{{ $step['no'] }}</span>
                            <div class="min-w-0">
                                <h3 class="font-display text-[22px] font-semibold transition-colors duration-300" :class="step === {{ $i }} ? 'text-ink' : 'text-ink-2'">{{ $step['title'] }}</h3>
                                <p x-show="step === {{ $i }}" x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="-translate-y-1 opacity-0" x-transition:enter-end="translate-y-0 opacity-100" @if ($i !== 0) x-cloak @endif class="mt-1.5 {{ $sub }}">{{ $step['text'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ================= ÜRÜN TURU (Product overview) ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between" data-reveal-group>
            <div class="max-w-xl">
                <p data-reveal><span class="{{ $chip }}">Ürün turu</span></p>
                <h2 class="mt-5 {{ $h2 }}" data-reveal>Günlük operasyonunuzun temeli</h2>
            </div>
            <a href="{{ route('sites.index') }}" class="{{ $btnDark }} shrink-0" data-reveal>
                <span class="{{ $btnChip }} bg-white/15 text-white">{!! $arrowIcon !!}</span>
                Hemen başlayın
            </a>
        </div>

        <div class="mt-10 grid gap-5 lg:grid-cols-[1.53fr_1fr]" data-reveal-group>
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
                        @foreach ([['Finans sitesinde tanıtım yazısı', 'S', 'bg-brand-100 text-brand-700', 'Bugün'], ['Teknoloji bülteni dağıtımı', 'E', 'bg-accent-100 text-accent-700', 'Yarın'], ['E-ticaret backlink paketi', 'M', 'bg-emerald-100 text-emerald-700', 'Çar'], ['Story yayını — 3 hesap', 'B', 'bg-amber-100 text-amber-700', 'Cum']] as [$is, $avatar, $tone, $gun])
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
                            <x-site-favicon domain="olaymedya.com" :size="24" class="shrink-0" />
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

        <div class="mt-5 grid gap-5 lg:grid-cols-[1fr_1.53fr]" data-reveal-group>
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
                            <x-site-favicon :domain="$domain" :size="32" class="shrink-0 rounded-full" />
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

    {{-- ================= ÖNE ÇIKANLAR (Feature highlights) ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="max-w-2xl">
            <p data-reveal><span class="{{ $chip }}">Öne çıkanlar</span></p>
            <h2 class="mt-5 {{ $h2 }}" data-reveal>Ekibinizi odakta tutmak için tasarlandı</h2>
        </div>

        {{-- Büyük panel mockup + telefon --}}
        <div class="relative mt-10" data-reveal>
            <div class="overflow-hidden rounded-[20px] border border-ink/10 bg-white shadow-pop">
                <div class="flex">
                    {{-- Kenar çubuğu --}}
                    <div class="hidden w-52 shrink-0 border-e border-ink/5 bg-paper p-4 lg:block">
                        <p class="flex items-center gap-x-2 text-[13px] font-bold text-ink">
                            <span class="inline-flex size-6 items-center justify-center rounded-lg bg-gradient-to-br from-accent-500 to-accent-700 text-[10px] text-white">N</span>
                            NewsTanıtım
                        </p>
                        <ul class="mt-5 space-y-1">
                            @foreach ([['Genel Bakış', true], ['Siparişler', false], ['Siteler', false], ['Favoriler', false], ['Faturalar', false], ['Destek', false]] as [$menu, $aktif])
                                <li @class(['rounded-lg px-3 py-2 text-[12px] font-medium', 'bg-white text-ink shadow-soft' => $aktif, 'text-ink-2' => ! $aktif])>{{ $menu }}</li>
                            @endforeach
                        </ul>
                    </div>
                    {{-- Ana içerik --}}
                    <div class="min-w-0 flex-1 p-5 lg:pe-64">
                        <div class="flex items-center justify-between">
                            <p class="text-[15px] font-bold text-ink">Günaydın, Metehan 👋</p>
                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-semibold text-emerald-700">3 yayın bugün canlıda</span>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach ([['Aktif sipariş', '14'], ['Yayında', '128'], ['Bekleyen onay', '3'], ['Bakiye', '8.450₺']] as [$k, $v])
                                <div class="rounded-xl border border-ink/5 bg-paper p-3">
                                    <p class="text-[10px] font-medium text-ink-2">{{ $k }}</p>
                                    <p class="mt-1 font-display text-lg font-semibold text-ink tabular-nums">{{ $v }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 rounded-xl border border-ink/5">
                            <div class="grid grid-cols-[1fr_auto_auto] gap-3 border-b border-ink/5 px-4 py-2.5 text-[10px] font-semibold uppercase tracking-wide text-ink-3 sm:grid-cols-[1.6fr_1fr_auto_auto]">
                                <span>Site</span><span class="hidden sm:block">Sipariş</span><span>Durum</span><span class="text-end">Tutar</span>
                            </div>
                            @foreach ([['habergazetesi.com.tr', 'Tanıtım yazısı', 'Yayında', 'bg-emerald-100 text-emerald-700', '500₺'], ['olaymedya.com', 'Basın bülteni', 'Editörde', 'bg-accent-100 text-accent-700', '1.000₺'], ['nesilhaber.com', 'Footer link', 'Onayda', 'bg-amber-100 text-amber-700', '300₺']] as [$domain, $tur, $durum, $tone, $tutar])
                                <div class="grid grid-cols-[1fr_auto_auto] items-center gap-3 border-b border-ink/5 px-4 py-2.5 last:border-0 sm:grid-cols-[1.6fr_1fr_auto_auto]">
                                    <p class="flex min-w-0 items-center gap-x-2 truncate text-[12px] font-semibold text-ink">
                                        <x-site-favicon :domain="$domain" :size="14" class="shrink-0" />
                                        {{ $domain }}
                                    </p>
                                    <span class="hidden text-[11px] font-medium text-ink-2 sm:block">{{ $tur }}</span>
                                    <span class="{{ $tone }} rounded-full px-2 py-0.5 text-[10px] font-semibold">{{ $durum }}</span>
                                    <span class="text-end text-[12px] font-bold text-ink tabular-nums">{{ $tutar }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Telefon mockup --}}
            <div class="absolute -bottom-8 end-6 hidden w-[230px] rounded-[28px] border-[6px] border-ink bg-ink shadow-pop lg:block">
                <div class="overflow-hidden rounded-[22px] bg-white">
                    <div class="flex items-center justify-between bg-paper px-4 py-3">
                        <p class="text-[11px] font-bold text-ink">Bildirimler</p>
                        <span class="inline-flex size-5 items-center justify-center rounded-full bg-brand-500 text-[9px] font-bold text-white">3</span>
                    </div>
                    <div class="space-y-2 p-3">
                        @foreach ([['Yazınız yayında 🎉', 'habergazetesi.com.tr'], ['Bakiye yüklendi', '500₺ hesabınıza tanımlandı'], ['Onay bekliyor', 'olaymedya.com taslağı']] as [$baslik, $alt])
                            <div class="rounded-xl border border-ink/5 bg-white p-2.5 shadow-soft">
                                <p class="text-[11px] font-semibold text-ink">{{ $baslik }}</p>
                                <p class="text-[10px] text-ink-2">{{ $alt }}</p>
                            </div>
                        @endforeach
                        <div class="rounded-xl bg-gradient-to-br from-accent-600 to-accent-700 p-2.5 text-white">
                            <p class="text-[11px] font-semibold">6 ay garanti aktif</p>
                            <p class="text-[10px] text-white/70">1.042 link korunuyor</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4 kolon --}}
        <div class="mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
            ['Sade günlük görünüm', 'Bugün ne önemli, kazmadan görün.', 'M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5'],
            ['Anlık durum takibi', 'Gecikme ve sorunları büyümeden yakalayın.', 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            ['Net yayın geçmişi', 'Tüm linkleri ve raporları tek bakışta izleyin.', 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'],
            ['İş + bütçe bir arada', 'Yayınlarla harcamaların bağlantısını görün.', 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
            ] as [$baslik, $metin, $ikon])
                <div class="flex items-start gap-3" data-reveal>
                    <svg class="mt-0.5 size-5 shrink-0 text-ink" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}"/></svg>
                    <div>
                        <h3 class="font-display text-lg font-semibold text-ink">{{ $baslik }}</h3>
                        <p class="mt-1 text-base font-medium leading-snug text-ink-2">{{ $metin }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= AI ASİSTAN (Beta) ================= --}}
    <section class="mx-auto px-4 py-16 sm:px-6 lg:px-8" x-data="{ tab: 'icerik' }">
        <div class="panel-dark relative overflow-hidden rounded-[20px] p-6 text-white sm:p-12" data-reveal-group>
            {{-- Devre hatları hissi --}}
            <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/25 to-transparent" aria-hidden="true"></div>

            {{-- Küp aksesuar --}}
            <div class="mx-auto flex size-[120px] items-center justify-center rounded-[20px] bg-[#141416] shadow-[inset_-3px_-3px_8px_3px_rgba(0,0,0,0.5)]" data-reveal>
                <div class="flex size-[96px] items-center justify-center rounded-2xl bg-gradient-to-br from-[#2d1b46] via-[#4a0b43] to-accent-900 p-px">
                    <div class="flex size-full items-center justify-center rounded-2xl bg-black">
                        <svg class="size-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/></svg>
                    </div>
                </div>
            </div>

            <div class="mx-auto mt-8 max-w-2xl text-center">
                <p class="inline-flex items-center gap-x-2 rounded-[10px] bg-gradient-to-r from-accent-600/60 via-black to-brand-600/40 py-1.5 pe-4 ps-1.5 text-sm font-medium text-white" data-reveal>
                    <span class="rounded bg-white px-2 py-0.5 text-[11px] font-bold text-ink shadow">Beta</span>
                    AI Asistan
                </p>
                <h2 class="mt-5 font-display text-3xl font-medium leading-[1.2] sm:text-[44px] lg:text-[52px]" data-reveal>
                    Angarya işleri kaldıran yapay zekâ
                </h2>
                <p class="mx-auto mt-4 max-w-lg text-xl font-medium text-[#a4a4a4]" data-reveal>
                    Angaryayı atlayın. İçerik, öneri ve raporlar kendiliğinden hazırlansın.
                </p>
            </div>

            <div class="mt-8 flex flex-wrap justify-center gap-2.5" data-reveal>
                @foreach ($aiTabs as $tabItem)
                    <button
                        type="button"
                        class="border-glass rounded-[20px] px-5 py-3 text-sm font-medium transition"
                        :class="tab === '{{ $tabItem['key'] }}' ? 'text-white ring-1 ring-white/40' : 'text-white/60 hover:text-white'"
                        @click="tab = '{{ $tabItem['key'] }}'"
                    >{{ $tabItem['label'] }}</button>
                @endforeach
            </div>

            @foreach ($aiTabs as $tabItem)
                <div x-show="tab === '{{ $tabItem['key'] }}'" x-transition.opacity.duration.200ms @if (! $loop->first) x-cloak @endif class="border-glass mx-auto mt-6 max-w-6xl rounded-[20px] p-6 sm:p-8">
                    <div class="grid gap-8 lg:grid-cols-2 lg:items-center">
                        <div>
                            <h3 class="font-display text-[22px] font-semibold">{{ $tabItem['title'] }}</h3>
                            <p class="mt-2 text-base font-medium leading-relaxed text-white/60">{{ $tabItem['text'] }}</p>
                            <p class="mt-6 text-[11px] font-semibold uppercase tracking-wide text-white/40">Faydalar</p>
                            <ul class="mt-3 space-y-3">
                                @foreach ($tabItem['benefits'] as $benefit)
                                    <li class="flex items-start gap-x-2.5 text-[14px] font-medium text-white/85">
                                        <svg class="mt-0.5 size-4 shrink-0 text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        {{ $benefit }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="rounded-2xl bg-white/[0.04] p-4">
                            <div class="flex items-center gap-x-2 border-b border-white/10 pb-3">
                                <span class="inline-flex size-7 items-center justify-center rounded-lg bg-gradient-to-br from-accent-500 to-brand-500 text-[10px] font-bold text-white">AI</span>
                                <p class="text-[12px] font-semibold text-white/80">Asistan çalışıyor…</p>
                            </div>
                            <div class="mt-3 space-y-2">
                                <div class="h-2 w-11/12 rounded-full bg-white/10"></div>
                                <div class="h-2 w-4/5 rounded-full bg-white/10"></div>
                                <div class="h-2 w-2/3 rounded-full bg-white/10"></div>
                            </div>
                            <div class="mt-4 rounded-xl bg-white/5 p-3">
                                <p class="text-[11px] font-semibold text-emerald-300">✓ {{ $tabItem['label'] }} hazır</p>
                                <p class="mt-1 text-[11px] text-white/50">Panelinize gönderildi · az önce</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="mt-8 text-center" data-reveal>
                <a href="{{ auth()->check() ? route('account.dashboard') : route('register') }}" class="{{ $btnWhite }}">
                    <span class="{{ $btnChip }} bg-gradient-to-b from-black to-[#363b3c] text-white">{!! $arrowIcon !!}</span>
                    Ücretsiz Dene
                </a>
            </div>
        </div>
    </section>

    {{-- ================= SAYAÇLAR (What teams achieve) ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="flex flex-col gap-10 lg:flex-row lg:items-center lg:justify-between">
            {{-- Sol: 28px başlık + gradyan çipli CTA --}}
            <div class="shrink-0 lg:max-w-[320px]" data-reveal>
                <h2 class="font-display text-[28px] font-medium leading-[1.2] text-ink">Ekiplerin platformumuzla ulaştığı sonuçlar</h2>
                <a href="{{ route('register') }}" class="group mt-4 inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 text-lg font-semibold text-white transition hover:scale-[1.03] active:scale-[0.98]">
                    <span class="inline-flex size-11 items-center justify-center rounded-xl bg-gradient-to-b from-white to-[#c9c9c9] text-ink">
                        <svg class="size-4 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </span>
                    Ücretsiz başlayın
                </a>
            </div>

            {{-- Sağ: 3 kolon — 50px gradyan ikon karesi + rakam yan yana --}}
            <div class="grid flex-1 gap-8 sm:grid-cols-3 lg:max-w-[800px]">
                @foreach ($counters as $counter)
                    <div class="max-w-[247px]" data-reveal>
                        <div class="flex items-center gap-x-4">
                            <span class="inline-flex size-[50px] shrink-0 items-center justify-center rounded-[10px] bg-gradient-to-br {{ $counter['chip'] }} text-white">
                                <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                            </span>
                            <p class="font-display text-[32px] font-medium leading-none text-ink tabular-nums" data-odometer="{{ $counter['value'] }}" data-odometer-suffix="{{ $counter['suffix'] }}">{{ $fmt($counter['value']) }}{{ $counter['suffix'] }}</p>
                        </div>
                        <h3 class="mt-4 font-display text-lg font-semibold text-ink">{{ $counter['title'] }}</h3>
                        <p class="mt-1 text-sm font-medium leading-snug text-ink-2">{{ $counter['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= HER ROL İÇİN (Built for teams) ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
            <div>
                <p data-reveal><span class="{{ $chip }}">Ekipler için</span></p>
                <h2 class="mt-5 {{ $h2 }}" data-reveal>Birçok role uyan tek sistem</h2>
                <p class="mt-4 max-w-md {{ $sub }}" data-reveal>
                    Farklı ekipler platformu farklı şekillerde kullanır; hedef aynı kalır: yayınlar, siteler ve bütçe tek basit yerde.
                </p>
                <div class="mt-7 flex max-w-md flex-wrap gap-2.5" data-reveal>
                    @foreach ($roles as $i => $role)
                        <span @class([
                            'rounded-[10px] px-4 py-1.5 text-[13px] font-medium',
                            'bg-ink text-white' => $i === count($roles) - 1,
                            'bg-paper text-ink-2' => $i !== count($roles) - 1,
                        ])>{{ $role }}</span>
                    @endforeach
                </div>
            </div>

            {{-- Lavanta panel + binen kartlar --}}
            <div class="relative overflow-hidden rounded-[20px] bg-[#f5f3ff] p-8 lg:min-h-[483px]" data-reveal>
                <div class="relative mx-auto h-[300px] max-w-[420px]">
                    <div class="absolute start-0 top-2 w-[240px] rounded-[20px] bg-white p-4 shadow-[0_6px_20px_rgba(10,11,11,0.1)]">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Sipariş görünümü</p>
                        <div class="mt-3 space-y-2">
                            @foreach ([['Site Yazısı', '12 yayın', 'bg-brand-100 text-brand-700'], ['Basın Bülteni', '8 yayın', 'bg-accent-100 text-accent-700'], ['Backlink Paketi', '3 sipariş', 'bg-emerald-100 text-emerald-700']] as [$urun, $adet, $tone])
                                <div class="flex items-center gap-2.5 rounded-xl bg-paper px-3 py-2.5">
                                    <span class="{{ $tone }} inline-flex size-6 items-center justify-center rounded-full text-[10px] font-bold">{{ mb_substr($urun, 0, 1) }}</span>
                                    <p class="min-w-0 flex-1 truncate text-[12px] font-semibold text-ink">{{ $urun }}</p>
                                    <span class="text-[10px] font-medium text-ink-2">{{ $adet }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="absolute -end-2 bottom-0 w-[200px] rounded-[20px] bg-white p-4 shadow-[0_6px_20px_rgba(10,11,11,0.1)]">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">SEO görünümü</p>
                        <p class="mt-2 font-display text-[26px] font-semibold text-ink tabular-nums">DA 45+</p>
                        <p class="text-[11px] font-medium text-ink-2">ortalama site otoritesi</p>
                        <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-ink/10"><div class="h-full w-4/5 rounded-full bg-gradient-to-r from-accent-500 to-brand-500"></div></div>
                    </div>
                </div>
                <div class="mt-6 flex items-center justify-center gap-5 text-[11px] font-semibold uppercase tracking-wide text-ink-3">
                    <span>Tanıtım</span><span>·</span><span>Bülten</span><span>·</span><span>Backlink</span><span>·</span><span>GEO</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= ÜRÜNLERİMİZ ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center" data-reveal-group>
            <p data-reveal><span class="{{ $chip }}">Ürünlerimiz</span></p>
            <h2 class="mt-5 {{ $h2 }}" data-reveal>İhtiyacınıza göre tek tek ya da paket halinde satın alın</h2>
            <p class="mt-4 {{ $sub }}" data-reveal>Gizli ücret yok; her ürünün fiyatı katalog sayfasında net olarak görünür.</p>
        </div>

        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group>
            @foreach ($products as $product)
                <a href="{{ $product['url'] }}" class="group flex flex-col rounded-[20px] bg-paper p-6 transition hover:-translate-y-0.5 hover:shadow-pop" data-reveal>
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
            @endforeach
        </div>
    </section>

    {{-- ================= YORUMLAR (What teams say) ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        {{-- Başlık satırı: solda rozet + başlık, sağda puan bloğu --}}
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <p data-reveal><span class="{{ $chip }}">Ekipler ne diyor</span></p>
                <h2 class="mt-5 {{ $h2 }}" data-reveal>Gerçek iş yapan ekiplerin güvendiği platform</h2>
            </div>
            <div class="flex shrink-0 items-center gap-x-4" data-reveal>
                <span class="inline-flex size-[60px] items-center justify-center rounded-[10px] bg-white shadow-soft">
                    <span class="font-display text-[28px] font-medium text-ink">4.8</span>
                </span>
                <span>
                    <span class="flex gap-x-0.5 text-amber-400" aria-label="5 üzerinden 5 yıldız">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd"/></svg>
                        @endfor
                    </span>
                    <span class="mt-1.5 block text-sm font-medium text-ink-2">{{ $fmt($stats['customers']) }}+ kullanıcının değerlendirmesi</span>
                </span>
            </div>
        </div>

        {{-- 370x370 kare kartlardan yatay şerit (1. ve 4. kart koyu) --}}
        <div data-reveal>
            <ul class="no-scrollbar -mx-4 mt-10 flex snap-x snap-mandatory gap-5 overflow-x-auto px-4 pb-2 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                @foreach ($testimonials as $i => $testimonial)
                    @php $koyu = in_array($i, [0, 3], true); @endphp
                    <li class="w-[85vw] max-w-[370px] shrink-0 snap-start sm:w-[370px]">
                        <figure @class([
                            'relative flex h-[370px] flex-col justify-between overflow-hidden rounded-[20px] p-[30px]',
                            'bg-ink text-white' => $koyu,
                            'bg-paper' => ! $koyu,
                        ])>
                            @if ($koyu)
                                {{-- Fotoğraf kartının koyu zemin dokusu --}}
                                <span class="pointer-events-none absolute -end-16 -top-16 size-56 rounded-full bg-accent-600/40 blur-[70px]" aria-hidden="true"></span>
                                <span class="pointer-events-none absolute -bottom-20 -start-16 size-56 rounded-full bg-brand-600/30 blur-[70px]" aria-hidden="true"></span>
                                <span class="pointer-events-none absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-black/60 to-transparent" aria-hidden="true"></span>
                            @endif

                            <blockquote @class(['relative text-lg font-semibold leading-relaxed', 'text-white' => $koyu, 'text-ink' => ! $koyu])>
                                &ldquo;{{ $testimonial['quote'] }}&rdquo;
                            </blockquote>

                            <figcaption class="relative flex items-center gap-x-4">
                                @unless ($koyu)
                                    <span class="inline-flex size-[50px] shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-accent-100 to-brand-100 font-display text-lg font-semibold text-accent-700">
                                        {{ mb_substr($testimonial['name'], 0, 1) }}
                                    </span>
                                @endunless
                                <span class="min-w-0">
                                    <span @class(['block text-lg font-medium', 'text-white' => $koyu, 'text-ink' => ! $koyu])>{{ $testimonial['name'] }}</span>
                                    <span @class(['block text-sm font-medium', 'text-white/80' => $koyu, 'text-ink-2' => ! $koyu])>{{ $testimonial['role'] }}</span>
                                </span>
                            </figcaption>
                        </figure>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ================= SSS ================= --}}
    <section class="mx-auto max-w-6xl px-4 pb-20 pt-16 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
            <div class="lg:sticky lg:top-28 lg:self-start" data-reveal-group>
                <p data-reveal><span class="{{ $chip }}">SSS</span></p>
                <h2 class="mt-5 max-w-xs {{ $h2 }}" data-reveal>Sık sorulan soruların cevapları</h2>
                <p class="mt-4 {{ $sub }}" data-reveal>Cevabınızı bulamadınız mı?</p>
                <a href="tel:08503052241" class="{{ $btnDark }} mt-4" data-reveal>
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
