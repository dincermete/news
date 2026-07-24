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
    <section class="px-2 pt-2 sm:px-3" x-data="{ cycle: '{{ (string) ($durationOptions->first()?->id) }}' }">
        <div class="panel-dark relative overflow-hidden rounded-3xl text-white">
            <div class="relative mx-auto flex max-w-3xl flex-col items-center px-5 pb-12 pt-16 text-center sm:px-8 lg:pb-16 lg:pt-20">
                <p class="inline-flex items-center gap-x-2 rounded-full border border-white/15 bg-white/5 py-1 pe-3.5 ps-1 text-xs text-white/80">
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">SEO · GEO · AEO</span>
                    Hepsi bir arada
                </p>

                <h1 class="mt-5 font-display text-4xl font-medium leading-[1.1] sm:text-5xl" data-reveal>
                    Google ve yapay zekâda tam görünürlük
                </h1>

                <p class="mt-5 max-w-xl text-lg font-medium leading-relaxed text-white/65">
                    Google'da ilk sayfa, ChatGPT · Gemini · Perplexity'de referans alınma. 8 başlık ve 99 kontrol noktasıyla SEO, GEO ve AEO tek pakette uygulanır.
                </p>

                <p class="mt-4 inline-flex items-center gap-x-2 text-sm text-white/70">
                    <span class="text-amber-400">★★★★★</span>
                    5,0 · 1.773 olumlu müşteri değerlendirmesi
                </p>

                {{-- Billing cycle toggle --}}
                <div class="mt-8 inline-flex flex-wrap items-center justify-center gap-1.5 rounded-2xl border border-white/15 bg-white/5 p-1.5">
                    @foreach ($durationOptions as $option)
                        <button
                            type="button"
                            class="relative rounded-xl px-4 py-2 text-sm font-medium transition"
                            :class="cycle === '{{ $option->id }}' ? 'bg-white text-ink' : 'text-white/70 hover:text-white'"
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
        </div>

        {{-- ================= PAKET KARTLARI ================= --}}
        <div class="mx-auto max-w-6xl px-2 py-10 sm:px-4">
            <div class="grid gap-5 lg:grid-cols-3">
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

    {{-- ================= BİLGİLENDİRME ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p><span class="{{ $chip }}">Profesyonel SEO Hizmetleri</span></p>
            <h2 class="mt-5 {{ $h2 }}">SEO Paketleri</h2>
            <p class="mt-4 {{ $sub }}">
                SEO paketi seçimi, paket adıyla değil sitenin ihtiyacıyla yapılır. Doğru yapı; teknik sorunların giderilmesi, hedef sayfaların güçlendirilmesi, içerik planının kurulması ve performansın düzenli izlenmesiyle sonuç verir.
            </p>
        </div>

        <div class="mt-10 grid gap-8 sm:grid-cols-3">
            @foreach ([
                ['title' => 'Hangi SEO paketi hangi site için uygundur?', 'text' => 'Doğru paket, sektör adına göre değil sitenin mevcut açığına göre belirlenir. Yeni açılmış kurumsal sitelerde temel teknik düzenleme öne çıkarken, rekabetçi projelerde içerik yoğunluğu ve teknik takip birlikte çalışmalıdır.'],
                ['title' => 'SEO paketi seçerken nelere dikkat edilmeli?', 'text' => 'İlk bakılması gereken şey fiyat değil kapsamdır. Teknik analiz yoksa ve raporlama yalnızca sıralama ekranından ibaretse paket güçlü görünse bile zayıftır.'],
                ['title' => 'SEO paketi kapsamında neler bulunmalı?', 'text' => 'Etkili bir SEO paketi; teknik tarama, anahtar kelime haritası, sayfa içi optimizasyon, içerik geliştirme ve düzenli raporlamayı birlikte içermelidir.'],
            ] as $info)
                <div>
                    <h3 class="font-display text-lg font-semibold text-ink">{{ $info['title'] }}</h3>
                    <p class="mt-2 text-sm font-medium leading-relaxed text-ink-2">{{ $info['text'] }}</p>
                </div>
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
