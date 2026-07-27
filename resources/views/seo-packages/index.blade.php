@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\SeoPackage> $packages */
    /** @var \Illuminate\Support\Collection<int, \App\Models\SeoPackageDurationOption> $durationOptions */

    $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
    $h2 = 'font-display text-3xl font-medium leading-[1.2] tracking-[-0.01em] text-ink sm:text-[44px] lg:text-[52px]';
    $sub = 'text-lg font-medium leading-relaxed text-ink-2';
    $btnWhite = 'group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-white to-[#c9c9c9] p-1 pe-4 text-sm font-medium text-ink transition hover:scale-[1.03] active:scale-[0.98]';
    $btnGhostDark = 'group inline-flex items-center gap-x-3 rounded-2xl border border-ink/10 bg-white p-1 pe-4 text-sm font-medium text-ink transition hover:bg-paper-2 hover:scale-[1.03] active:scale-[0.98]';
    $btnChip = 'inline-flex size-8 items-center justify-center rounded-xl';
    $arrowIcon = '<svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>';

    $money = fn (float $amount): string => number_format($amount, 0, ',', '.');

    $packagePrices = $packages->mapWithKeys(function ($package) use ($durationOptions) {
        return [
            $package->id => $durationOptions->mapWithKeys(fn ($option) => [
                (string) $option->id => $option->resolvePrice($package->monthly_price),
            ]),
        ];
    });

    $comparisonGroups = [
        [
            'title' => 'Anahtar Kelime & Rekabet',
            'rows' => [
                ['label' => 'Anahtar kelime sayısı', 'values' => ['12', '20', '40']],
                ['label' => 'Arama niyeti & sektörel trend analizi', 'values' => [true, true, true]],
                ['label' => 'Rakip görünürlük & gap analizi', 'values' => [false, true, true]],
                ['label' => 'Topic cluster mimarisi', 'values' => [false, false, true]],
            ],
        ],
        [
            'title' => 'İçerik Stratejisi & Üretimi',
            'rows' => [
                ['label' => 'Aylık içerik adedi', 'values' => ['4', '8', '16']],
                ['label' => 'GEO uyumlu (AI alıntılanabilir) içerik', 'values' => [true, true, true]],
                ['label' => 'Meta, H etiket & iç bağlantı optimizasyonu', 'values' => [true, true, true]],
                ['label' => 'AEO Quick Answer & FAQ blokları', 'values' => [false, false, true]],
                ['label' => 'AI içerik kalite & intihal taraması', 'values' => [true, true, true]],
            ],
        ],
        [
            'title' => 'Otorite & Backlink İnşası',
            'rows' => [
                ['label' => 'Otorite tanıtım yazısı (haber & blog)', 'values' => ['8-12', '15-20', '20-25']],
                ['label' => 'Basın bülteni yayını', 'values' => ['10-20', '25-50', '50-100']],
                ['label' => 'Sektörel bağlantı çalışması', 'values' => ['1-2', '2-3', '3-5']],
                ['label' => 'Çok Katmanlı Sinyal Ağı™', 'values' => ['25-40', '50-75', '75-100']],
                ['label' => 'Link profili izleme & disavow', 'values' => [false, true, true]],
            ],
        ],
        [
            'title' => 'Teknik SEO & Altyapı',
            'rows' => [
                ['label' => 'Teknik denetim & Core Web Vitals', 'values' => [true, true, true]],
                ['label' => 'Site hızı & mobil uyumluluk', 'values' => [true, true, true]],
                ['label' => 'XML sitemap · robots.txt · canonical', 'values' => [true, true, true]],
                ['label' => 'Crawl budget & log analizi', 'values' => [false, false, true]],
                ['label' => '301 yönlendirme & duplicate content yönetimi', 'values' => [true, true, true]],
            ],
        ],
        [
            'title' => 'AI Erişilebilirlik & Yapısal Veri',
            'rows' => [
                ['label' => 'robots.txt + llms.txt AI bot yönetimi', 'values' => [true, true, true]],
                ['label' => 'Schema kapsamı', 'values' => ['Temel', 'Temel', 'Tam']],
                ['label' => 'Open Graph & Twitter Card etiketleri', 'values' => [true, true, true]],
                ['label' => 'Entity & Knowledge Graph optimizasyonu', 'values' => [false, true, true]],
            ],
        ],
        [
            'title' => 'Yerel SEO & Harita',
            'rows' => [
                ['label' => 'Google İşletme Profili & Maps', 'values' => [true, true, true]],
                ['label' => 'NAP tutarlılık & yorum yönetimi', 'values' => [true, true, true]],
            ],
        ],
        [
            'title' => 'AI Görünürlük Takibi & Raporlama',
            'rows' => [
                ['label' => 'Prompt takibi', 'values' => ['3 motor', '3 motor', '5 motor']],
                ['label' => 'Google AI Overview & featured snippet takibi', 'values' => [true, true, true]],
                ['label' => 'AI Share of Voice & citation takibi', 'values' => [false, false, true]],
                ['label' => 'GA4 & Search Console entegrasyonu', 'values' => [true, true, true]],
                ['label' => 'Raporlama', 'values' => ['Aylık', 'Aylık', 'Haftalık + Aylık']],
            ],
        ],
    ];

    $tools = ['Google Analytics', 'Search Console', 'Tag Manager', 'Google Trends', 'Google İşletme Profili', 'Bing Webmaster', 'Yandex Webmaster', 'Semrush', 'Ahrefs', 'Screaming Frog', 'SEOmonitor', 'Majestic', 'Moz', 'Ubersuggest', 'KWFinder', 'Microsoft Clarity', 'Hotjar', 'GTmetrix', 'PageSpeed Insights', 'SimilarWeb', 'Looker Studio', 'Keyword Tool'];
    $aiTools = ['ChatGPT', 'Gemini', 'Perplexity', 'Claude', 'Grok', 'Copilot', 'DeepSeek', 'Google AI Overviews', 'Meta AI', 'Qwen', 'Kimi'];

    $trustBadges = [
        ['value' => '500+', 'label' => 'Tamamlanan proje'],
        ['value' => '200', 'label' => 'Maddelik manuel analiz'],
        ['value' => '%100', 'label' => 'Manuel inceleme, otomasyon yok'],
    ];

    $platforms = ['Google', 'Bing', 'Yandex', 'ChatGPT', 'Perplexity', 'Gemini'];

    $methodSteps = [
        ['no' => '01', 'title' => 'Analiz', 'text' => 'Sitenizi 200 maddelik teknik ve içerik kontrol listesiyle uçtan uca tarar, rakip görünürlüğünü ölçeriz.'],
        ['no' => '02', 'title' => 'Planlama', 'text' => 'Bulguları önceliklendirir, anahtar kelime ve içerik takvimini paketinize göre netleştiririz.'],
        ['no' => '03', 'title' => 'Uygulama', 'text' => 'Teknik düzeltmeler, içerik üretimi ve otorite çalışmalarını paralel yürütürüz.'],
        ['no' => '04', 'title' => 'Raporlama', 'text' => 'Sıralama, trafik ve AI görünürlük verilerini düzenli aralıklarla panelinize taşırız.'],
    ];

    $crossSell = [
        ['name' => 'Backlink Paketleri', 'text' => 'Yüksek otoriteli kaynaklardan doğal anchor dağılımıyla kalıcı backlink.', 'url' => route('backlink-packages.index'), 'icon' => 'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22'],
        ['name' => 'GEO', 'text' => 'ChatGPT, Gemini ve Perplexity gibi AI motorlarında kaynak gösterilin.', 'url' => route('geo.index'), 'icon' => 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
        ['name' => 'Basın Bülteni', 'text' => 'Haber sitelerinde basın bülteninizi yayınlayın, geniş kitlelere ulaşın.', 'url' => route('press-release.index'), 'icon' => 'M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783'],
    ];

    $faqItems = [
        ['q' => 'SEO paketi seçerken ilk neye bakılmalı?', 'a' => 'SEO paketi seçerken ilk bakılması gereken şey fiyat değil, paketin hangi sorunu çözdüğüdür. Teknik analizi olmayan, işlem görecek sayfaları net tanımlamayan ve yalnızca genel vaat sunan paketler sağlıklı bir yapı kurmaz.'],
        ['q' => 'Hangi SEO paketi hangi site için uygundur?', 'a' => 'Doğru paket, sitenin yaşına, rekabet düzeyine, sayfa sayısına ve hedeflediği görünürlük alanına göre belirlenir. Yeni sitelerde temel yapı öne çıkarken, rekabetli projelerde içerik, teknik takip ve düzenli optimizasyon birlikte gerekir.'],
        ['q' => 'SEO paketi kapsamında hangi hizmetler bulunmalı?', 'a' => 'Etkili bir SEO paketi teknik tarama, anahtar kelime planlaması, sayfa içi optimizasyon, hedef sayfa geliştirme ve raporlamayı birlikte içermelidir. Tek bir alana sıkışan çalışmalar büyüme üretmez.'],
        ['q' => 'Yeni açılan siteye SEO paketi alınır mı?', 'a' => 'Yeni açılan siteye SEO paketi alınabilir. Ancak önce temel sayfa yapısının, teknik kurulumun ve hedef içeriklerin hazırlanmış olması gerekir.'],
        ['q' => 'SEO paketleri tek seferlik midir?', 'a' => 'SEO çoğu projede tek seferlik değil, takip gerektiren bir süreçtir. Teknik sorunlar yeniden oluşabilir, yeni sayfalar devreye girebilir ve rekabet değişebilir.'],
        ['q' => 'SEO paketinin etkisi ne zaman görülür?', 'a' => 'SEO paketinin etkisi yapılan işlemin kapsamına, sektörün rekabetine ve sitenin mevcut durumuna göre değişir. İlk sinyaller erken görülebilir; kalıcı sıralama artışı genellikle birkaç ayı bulur.'],
    ];
@endphp

@section('content')
    {{-- ================= HERO ================= --}}
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-light relative overflow-hidden rounded-3xl text-ink">
            <div class="relative mx-auto flex max-w-3xl flex-col items-center px-5 pb-12 pt-16 text-center sm:px-8 lg:pb-16 lg:pt-20">
                <p class="inline-flex items-center gap-x-2 rounded-full border border-ink/10 bg-white py-1 pe-3.5 ps-1 text-xs text-ink-2 shadow-soft">
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">SEO · GEO · AEO</span>
                    Hepsi bir arada
                </p>

                <h1 class="mt-5 font-display text-4xl font-medium leading-[1.1] sm:text-5xl" data-reveal>
                    Google ve yapay zekâda tam görünürlük
                </h1>

                <p class="mt-5 max-w-xl text-lg font-medium leading-relaxed text-ink-2">
                    Google'da ilk sayfa, ChatGPT · Gemini · Perplexity'de referans alınma. 8 başlık ve 99 kontrol noktasıyla SEO, GEO ve AEO tek pakette uygulanır.
                </p>

                <p class="mt-4 inline-flex items-center gap-x-2 text-sm text-ink-2">
                    <span class="text-amber-400">★★★★★</span>
                    5,0 · 1.773 olumlu müşteri değerlendirmesi
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <a href="#paketler" class="{{ $btnWhite }}">
                        <span class="{{ $btnChip }} bg-white/15 text-white">{!! $arrowIcon !!}</span>
                        Paketleri İncele
                    </a>
                    <a href="tel:{{ $siteSettings->support_phone ?: '08503052241' }}" class="{{ $btnGhostDark }}">
                        <span class="{{ $btnChip }} bg-ink/5 text-ink">
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                        </span>
                        {{ $siteSettings->support_phone_display ?: '0850 305 22 41' }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= GÜVEN ŞERİDİ ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8" data-reveal-group>
        <div class="grid gap-6 rounded-[20px] bg-paper p-6 sm:grid-cols-3 sm:p-8" data-reveal>
            @foreach ($trustBadges as $badge)
                <div class="text-center sm:text-start">
                    <p class="font-display text-3xl font-semibold text-ink">{{ $badge['value'] }}</p>
                    <p class="mt-1 text-sm font-medium text-ink-2">{{ $badge['label'] }}</p>
                </div>
            @endforeach
        </div>
        <div class="mt-6 flex flex-wrap items-center justify-center gap-2" data-reveal>
            @foreach ($platforms as $platform)
                <span class="rounded-full border border-ink/10 bg-white px-3.5 py-1.5 text-xs font-medium text-ink-2">{{ $platform }}</span>
            @endforeach
        </div>
    </section>

    {{-- ================= YÖNTEMİMİZ ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="mx-auto max-w-2xl text-center">
            <p data-reveal><span class="{{ $chip }}">Yöntemimiz</span></p>
            <h2 class="mt-5 {{ $h2 }}" data-reveal>4 adımda ölçülebilir büyüme</h2>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($methodSteps as $step)
                <div class="rounded-[20px] bg-paper p-6" data-reveal>
                    <span class="inline-flex size-10 items-center justify-center rounded-[10px] bg-white font-display text-lg font-semibold text-ink shadow-soft">{{ $step['no'] }}</span>
                    <h3 class="mt-4 font-display text-lg font-semibold text-ink">{{ $step['title'] }}</h3>
                    <p class="mt-1.5 text-sm font-medium leading-relaxed text-ink-2">{{ $step['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= PAKET KARTLARI ================= --}}
    <section id="paketler" class="px-2 sm:px-3" x-data="{ cycle: '{{ (string) ($durationOptions->first()?->id) }}' }">
        <div class="mx-auto max-w-6xl px-2 py-10 sm:px-4">
            <div class="mx-auto max-w-2xl text-center">
                <p><span class="{{ $chip }}">Paketler</span></p>
                <h2 class="mt-5 {{ $h2 }}">İhtiyacınıza uygun paketi seçin</h2>
            </div>

            {{-- Billing cycle toggle --}}
            <div class="mt-8 flex flex-wrap items-center justify-center gap-1.5">
                <div class="inline-flex flex-wrap items-center justify-center gap-1.5 rounded-2xl border border-ink/10 bg-white p-1.5 shadow-soft">
                    @foreach ($durationOptions as $option)
                        <button
                            type="button"
                            class="relative rounded-xl px-4 py-2 text-sm font-medium transition"
                            :class="cycle === '{{ $option->id }}' ? 'bg-ink text-white' : 'text-ink-2 hover:text-ink'"
                            @click="cycle = '{{ $option->id }}'"
                        >
                            {{ $option->name }}
                            @if ($option->bonus_label)
                                <span class="ms-1 rounded-full bg-brand-500 px-1.5 py-0.5 text-[9px] font-bold text-white">{{ $option->bonus_label }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="mt-10 grid gap-5 lg:grid-cols-3">
                @foreach ($packages as $package)
                    @php
                        $prices = $packagePrices[$package->id]->mapWithKeys(fn ($value, $key) => [$key => $money((float) $value)]);
                        $perMonth = $durationOptions->mapWithKeys(fn ($option) => [(string) $option->id => $money((float) $package->monthly_price * (float) $option->price_multiplier)]);
                    @endphp
                    <div
                        @class([
                            'relative flex h-full flex-col rounded-[20px] border bg-white p-6 sm:p-8',
                            'border-ink shadow-pop' => $package->is_featured,
                            'border-ink/10' => ! $package->is_featured,
                        ])
                    >
                        @if ($package->is_featured)
                            <span class="absolute -top-3 start-1/2 -translate-x-1/2 rounded-full bg-ink px-3.5 py-1 text-[11px] font-bold text-white">★ EN ÇOK TERCİH EDİLEN</span>
                        @endif

                        <h3 class="font-display text-xl font-semibold text-ink">{{ $package->name }}</h3>
                        <p class="mt-2 text-sm font-medium leading-relaxed text-ink-2">{{ $package->description }}</p>

                        <div class="mt-5">
                            <p class="flex items-baseline gap-x-1.5">
                                <span class="font-display text-4xl font-semibold text-ink" x-text="{{ \Illuminate\Support\Js::from($perMonth) }}[cycle]"></span>
                                <span class="text-sm font-medium text-ink-2">TL/ay</span>
                            </p>
                            <p class="mt-1 text-xs text-ink-3">Aylık ödeme (+ KDV) · toplam <span x-text="{{ \Illuminate\Support\Js::from($prices) }}[cycle]"></span>₺</p>
                        </div>

                        <span class="mt-4 inline-flex w-fit items-center rounded-full bg-accent-100 px-2.5 py-1 text-[11px] font-semibold text-accent-700">{{ $package->keyword_count }} anahtar kelime</span>

                        <form method="post" action="{{ route('cart.add') }}" class="mt-5">
                            @csrf
                            <input type="hidden" name="product_type" value="seo_package">
                            <input type="hidden" name="seo_package_id" value="{{ $package->id }}">
                            <input type="hidden" name="seo_package_duration_option_id" :value="cycle">
                            @guest
                                <button type="button" class="inline-flex w-full items-center justify-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-3 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]" onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))">
                                    Sepete Ekle
                                </button>
                            @else
                                <button type="submit" class="inline-flex w-full items-center justify-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-4 py-3 text-sm font-semibold text-white transition hover:scale-[1.02] active:scale-[0.98]">
                                    Sepete Ekle
                                </button>
                            @endguest
                        </form>

                        @auth
                            <a href="#karsilastir" class="mt-2 inline-flex w-full items-center justify-center rounded-xl border border-ink/10 px-4 py-2.5 text-xs font-semibold text-ink-2 transition hover:border-ink/25 hover:text-ink">Örnek Rapor</a>
                        @else
                            <button type="button" class="mt-2 inline-flex w-full items-center justify-center rounded-xl border border-ink/10 px-4 py-2.5 text-xs font-semibold text-ink-2 transition hover:border-ink/25 hover:text-ink" onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))">Örnek Rapor (Giriş Gerekli)</button>
                        @endauth

                        @if (! empty($package->features))
                            <ul class="mt-6 space-y-2.5 border-t border-ink/5 pt-6">
                                @foreach ($package->features as $feature)
                                    <li class="flex items-start gap-x-2.5 text-sm font-medium text-ink-2">
                                        <svg class="mt-0.5 size-4 shrink-0 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>

            <p class="mt-6 text-center text-sm font-medium text-ink-2">Ücretsiz ön analiz · Gizli ücret, sürpriz yok</p>
        </div>
    </section>

    {{-- ================= KARŞILAŞTIRMA TABLOSU ================= --}}
    <section id="karsilastir" class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p><span class="{{ $chip }}">Paketleri karşılaştır</span></p>
            <h2 class="mt-5 {{ $h2 }}">Her pakette neyin dahil olduğunu tek bakışta gör</h2>
        </div>

        <div class="mt-10 overflow-hidden rounded-[20px] border border-ink/10 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] border-collapse text-start">
                    <thead>
                        <tr class="border-b border-ink/10 bg-paper">
                            <th class="sticky start-0 z-10 bg-paper px-5 py-3.5 text-start text-[11px] font-semibold uppercase tracking-wide text-ink-3">Özellik</th>
                            @foreach ($packages as $package)
                                <th class="px-4 py-3.5 text-center text-[11px] font-semibold uppercase tracking-wide text-ink-3">{{ $package->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink/5">
                        @foreach ($comparisonGroups as $group)
                            <tr class="bg-paper/60">
                                <td colspan="{{ $packages->count() + 1 }}" class="px-5 py-2.5 text-[11px] font-bold uppercase tracking-wide text-ink-3">{{ $group['title'] }}</td>
                            </tr>
                            @foreach ($group['rows'] as $row)
                                <tr>
                                    <td class="sticky start-0 z-10 bg-white px-5 py-3 text-sm font-medium text-ink">{{ $row['label'] }}</td>
                                    @foreach ($row['values'] as $value)
                                        <td class="px-4 py-3 text-center">
                                            @if (is_bool($value))
                                                @if ($value)
                                                    <svg class="mx-auto size-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                                @else
                                                    <span class="text-ink-3">—</span>
                                                @endif
                                            @else
                                                <span class="text-sm font-semibold text-ink">{{ $value }}</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- ================= ARAÇ SETİMİZ ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p><span class="{{ $chip }}">Araç Setimiz</span></p>
            <h2 class="mt-5 {{ $h2 }}">İşin mutfağında profesyonel araçlar</h2>
            <p class="mt-4 {{ $sub }}">SEO & GEO performansınızı ölçeklendirmek ve güncel raporlamalar sunmak için en uygun araç setini kullanırız.</p>
        </div>

        <div class="mt-10 space-y-4">
            <div class="relative overflow-hidden rounded-[20px] bg-paper py-5">
                <div class="marquee-track items-center gap-8 pe-8" style="--marquee-duration: 40s">
                    @foreach ([0, 1] as $copy)
                        @foreach ($tools as $tool)
                            <span @if ($copy === 1) aria-hidden="true" @endif class="inline-flex shrink-0 items-center rounded-full border border-ink/10 bg-white px-4 py-2 text-xs font-semibold text-ink-2">{{ $tool }}</span>
                        @endforeach
                    @endforeach
                </div>
                <span class="pointer-events-none absolute inset-y-0 start-0 w-[80px] bg-gradient-to-r from-paper to-transparent" aria-hidden="true"></span>
                <span class="pointer-events-none absolute inset-y-0 end-0 w-[80px] bg-gradient-to-l from-paper to-transparent" aria-hidden="true"></span>
            </div>

            <div class="relative overflow-hidden rounded-[20px] bg-ink py-5">
                <div class="marquee-track items-center gap-8 pe-8" style="--marquee-duration: 32s">
                    @foreach ([0, 1] as $copy)
                        @foreach ($aiTools as $tool)
                            <span @if ($copy === 1) aria-hidden="true" @endif class="inline-flex shrink-0 items-center rounded-full border border-white/15 bg-white/5 px-4 py-2 text-xs font-semibold text-white/80">{{ $tool }}</span>
                        @endforeach
                    @endforeach
                </div>
                <span class="pointer-events-none absolute inset-y-0 start-0 w-[80px] bg-gradient-to-r from-ink to-transparent" aria-hidden="true"></span>
                <span class="pointer-events-none absolute inset-y-0 end-0 w-[80px] bg-gradient-to-l from-ink to-transparent" aria-hidden="true"></span>
            </div>
        </div>
    </section>

    {{-- ================= DİĞER HİZMETLER ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p><span class="{{ $chip }}">Diğer Hizmetler</span></p>
            <h2 class="mt-5 {{ $h2 }}">SEO paketinizi tamamlayan hizmetler</h2>
            <p class="mt-4 {{ $sub }}">Görünürlüğü tek kanala sıkıştırmayın; backlink, GEO ve basın bülteniyle etkiyi büyütün.</p>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-3">
            @foreach ($crossSell as $service)
                <a href="{{ $service['url'] }}" class="group flex flex-col rounded-[20px] bg-paper p-6 transition hover:-translate-y-0.5 hover:shadow-pop">
                    <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-[10px] bg-ink text-white">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $service['icon'] }}"/></svg>
                    </span>
                    <h3 class="mt-4 font-display text-lg font-semibold text-ink">{{ $service['name'] }}</h3>
                    <p class="mt-1.5 text-sm font-medium leading-relaxed text-ink-2">{{ $service['text'] }}</p>
                    <span class="mt-auto flex items-center gap-x-1.5 pt-4 text-xs font-semibold text-ink-2 transition group-hover:text-ink">
                        İncele
                        <svg class="size-3 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ================= SSS ================= --}}
    <section class="mx-auto max-w-6xl px-4 pb-16 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
            <div class="lg:sticky lg:top-28 lg:self-start">
                <p><span class="{{ $chip }}">Sıkça Sorulan Sorular</span></p>
                <h2 class="mt-5 max-w-xs {{ $h2 }}">Merak Edilenler</h2>
                <p class="mt-4 {{ $sub }}">SEO paketleri hakkında en çok sorulan sorulara net cevaplar.</p>
            </div>

            <div class="space-y-3">
                @foreach ($faqItems as $index => $faq)
                    <div x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }" class="rounded-2xl bg-paper">
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
                        <div x-show="open" x-cloak class="px-6 pb-5 text-[13px] font-medium leading-relaxed text-ink-2">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
