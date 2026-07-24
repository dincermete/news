@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\BacklinkPackage> $packages */

    $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
    $h2 = 'font-display text-3xl font-medium leading-[1.2] tracking-[-0.01em] text-ink sm:text-[44px] lg:text-[52px]';
    $sub = 'text-lg font-medium leading-relaxed text-ink-2';

    $money = fn (float $amount): string => number_format($amount, 0, ',', '.');

    $metricBadges = ['Moz DA', 'Ahrefs DR', 'Majestic TF', 'Semrush AS', 'Reddit', 'Medium', 'LinkedIn', 'Tumblr', 'ChatGPT', 'Gemini', 'Claude', 'Perplexity'];

    $renewal = [
        'old' => [
            'title' => 'Eski Paketler',
            'items' => [
                'Klasik backlink ve içerik üretimi',
                'Yalnızca Google odaklı optimizasyon',
                'Tek kanal yayın stratejisi',
            ],
        ],
        'new' => [
            'title' => 'Yeni Paketler',
            'items' => [
                'AEO — İçerikler ChatGPT ve Gemini\'nin alıntılayacağı yapıda',
                'GEO — AI asistanların markanızı güvenilir kaynak tanıması',
                'SEO — Çok katmanlı backlink, doğal anchor, sektörel otorite',
            ],
        ],
    ];

    $faqItems = [
        ['q' => 'Backlink paketi seçerken ilk neye bakılmalı?', 'a' => 'İlk bakılması gereken şey link sayısı değil, kaynakların kalitesi ve konuyla alakasıdır. Otoritesi düşük, alakasız veya spam riski taşıyan kaynaklardan verilen çok sayıda link, az sayıda kaliteli linkten daha az değer üretir, hatta zarar verebilir.'],
        ['q' => 'Hangi backlink paketi hangi site için uygundur?', 'a' => 'Doğru paket; sitenin mevcut otoritesine (DR/DA), hedef sayfasına ve rekabet düzeyine göre belirlenir. Yeni sitelerde temel güven sinyali, rekabetçi sayfalarda ise daha güçlü ve hedefli kaynaklar öne çıkar.'],
        ['q' => 'Backlink kalitesi neye göre belirlenir?', 'a' => 'Kaynağın otoritesi (DR/DA), gerçek organik trafiği, konuyla alaka düzeyi, link tipi (dofollow/nofollow) ve sitenin link profilinin doğallığı belirleyicidir. Tek bir metrik değil, bu faktörlerin bütünü kaliteyi tanımlar.'],
        ['q' => 'Backlink almak SEO\'ya zarar verir mi?', 'a' => 'Doğru yapılan, alakalı ve kaliteli kaynaklardan alınan backlink zarar vermez, otoriteyi güçlendirir. Zarar; spam ağlardan, alakasız ve yapay görünen toplu linklerden gelir. Bu yüzden kaynak seçimi link sayısından önemlidir.'],
        ['q' => 'Backlink paketleri tek seferlik midir?', 'a' => 'Paketler tek seferlik kurgulanabilir; ancak otorite kalıcı bir varlıktır ve rekabet sürdükçe düzenli link kazanımı avantaj sağlar. İhtiyaca göre tek seferlik kampanya ya da sürekli çalışma tercih edilebilir.'],
        ['q' => 'Backlinklerin etkisi ne zaman görülür?', 'a' => 'Etki; kaynağın indexlenme hızına, sayfanın mevcut durumuna ve rekabete bağlıdır. İlk sinyaller birkaç hafta içinde görülebilir, anlamlı otorite ve sıralama değişimi genellikle birkaç ay içinde belirginleşir.'],
        ['q' => 'Anchor (çapa) metni nasıl olmalı?', 'a' => 'Anchor dağılımı doğal olmalıdır: ağırlıklı marka ve URL anchorları, sınırlı ve dengeli kelime anchorları. Aynı anahtar kelimeyle çok sayıda link, yapay görünerek risk oluşturur.'],
        ['q' => 'Dofollow ve nofollow link farkı nedir?', 'a' => 'Dofollow linkler otorite aktarır ve sıralamaya doğrudan katkı sağlar; nofollow linkler ise genellikle otorite aktarmaz ama doğal bir link profili için değer taşır. Sağlıklı bir profilde ikisi birlikte bulunur.'],
        ['q' => 'Backlink çalışmasında garanti verilir mi?', 'a' => 'Net sıra garantisi verilmez; sonuç rekabet, sayfa kalitesi ve mevcut otorite gibi birçok etkene bağlıdır. Sağlam hizmet, garanti cümlesi yerine şeffaf kaynak listesi ve ölçülebilir raporlama sunar.'],
    ];
@endphp

@section('content')
    {{-- ================= HERO ================= --}}
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-dark relative overflow-hidden rounded-3xl text-white">
            <div class="relative mx-auto flex max-w-3xl flex-col items-center px-5 pb-12 pt-16 text-center sm:px-8 lg:pb-16 lg:pt-20">
                <p class="inline-flex items-center gap-x-2 rounded-full border border-white/15 bg-white/5 py-1 pe-3.5 ps-1 text-xs text-white/80">
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">Off-Page SEO & Otorite</span>
                    6 Ay Garanti
                </p>

                <h1 class="mt-5 font-display text-4xl font-medium leading-[1.1] sm:text-5xl" data-reveal>
                    Otoriteyi büyüten backlink paketleri
                </h1>

                <p class="mt-5 max-w-xl text-lg font-medium leading-relaxed text-white/65">
                    Konuyla alakalı, yüksek otoriteli ve gerçek trafiği olan kaynaklardan; doğal anchor dağılımıyla kalıcı backlink paketleri. DR/DA değerinizi yükseltin, hedef sayfalarınızı güçlendirin, sıralamada öne geçin.
                </p>

                <p class="mt-4 inline-flex items-center gap-x-2 text-sm text-white/70">
                    <span class="text-amber-400">★★★★★</span>
                    5,0 · 1.773 olumlu müşteri değerlendirmesi
                </p>

                <div class="mt-6 flex max-w-2xl flex-wrap items-center justify-center gap-2">
                    @foreach ($metricBadges as $badge)
                        <span class="rounded-full border border-white/15 bg-white/5 px-3.5 py-1.5 text-xs font-medium text-white/80">{{ $badge }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ================= ÜÇ EKSEN, TEK PAKET ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p><span class="{{ $chip }}">Üç Eksen, Tek Paket</span></p>
            <h2 class="mt-5 {{ $h2 }}">Neden paketlerimizi yeniledik?</h2>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2">
            <div class="rounded-[20px] border border-ink/10 bg-paper p-6 sm:p-8">
                <h3 class="font-display text-lg font-semibold text-ink">{{ $renewal['old']['title'] }}</h3>
                <ul class="mt-4 space-y-3">
                    @foreach ($renewal['old']['items'] as $item)
                        <li class="flex items-start gap-x-2.5 text-sm font-medium text-ink-2">
                            <svg class="mt-0.5 size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="rounded-[20px] border border-ink/10 bg-white p-6 shadow-pop sm:p-8">
                <h3 class="font-display text-lg font-semibold text-ink">{{ $renewal['new']['title'] }}</h3>
                <ul class="mt-4 space-y-3">
                    @foreach ($renewal['new']['items'] as $item)
                        <li class="flex items-start gap-x-2.5 text-sm font-medium text-ink-2">
                            <svg class="mt-0.5 size-4 shrink-0 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- ================= PAKET KARTLARI ================= --}}
    <section class="mx-auto max-w-6xl px-4 pb-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p><span class="{{ $chip }}">Off-Page SEO & AEO Paketi</span></p>
            <h2 class="mt-5 {{ $h2 }}">Bütçenize uygun otorite paketi seçin</h2>
        </div>

        <div class="mt-10 grid gap-5 lg:grid-cols-3">
            @foreach ($packages as $package)
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
                            <span class="font-display text-4xl font-semibold text-ink">{{ $money((float) $package->price) }}</span>
                            <span class="text-sm font-medium text-ink-2">{{ $package->currency?->value ?? 'TRY' }}</span>
                        </p>
                        <p class="mt-1 text-xs text-ink-3">Tek seferlik ödeme (+ KDV)</p>
                    </div>

                    @if ($package->competition_label)
                        <span class="mt-4 inline-flex w-fit items-center rounded-full bg-accent-100 px-2.5 py-1 text-[11px] font-semibold text-accent-700">{{ $package->competition_label }}</span>
                    @endif

                    <form method="post" action="{{ route('cart.add') }}" class="mt-5">
                        @csrf
                        <input type="hidden" name="product_type" value="backlink_package">
                        <input type="hidden" name="backlink_package_id" value="{{ $package->id }}">
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
                        <a href="{{ route('free-analysis.show') }}" class="mt-2 inline-flex w-full items-center justify-center rounded-xl border border-ink/10 px-4 py-2.5 text-xs font-semibold text-ink-2 transition hover:border-ink/25 hover:text-ink">Örnek Rapor</a>
                    @else
                        <button type="button" class="mt-2 inline-flex w-full items-center justify-center rounded-xl border border-ink/10 px-4 py-2.5 text-xs font-semibold text-ink-2 transition hover:border-ink/25 hover:text-ink" onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))">Örnek Rapor (Giriş Gerekli)</button>
                    @endauth

                    @if (! empty($package->features))
                        <ul class="mt-6 space-y-2 border-t border-ink/5 pt-6">
                            @foreach ($package->features as $feature)
                                <li class="flex items-start gap-x-2.5 text-[13px] font-medium text-ink-2">
                                    <svg class="mt-0.5 size-4 shrink-0 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-x-8 gap-y-2 text-sm font-medium text-ink-2">
            <span>Ücretsiz strateji kurulumu</span>
            <span>Taahhüt zorunluluğu yok</span>
            <span>24 saatte başlangıç</span>
        </div>
    </section>

    {{-- ================= BİLGİLENDİRME ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p><span class="{{ $chip }}">Profesyonel Backlink Hizmetleri</span></p>
            <h2 class="mt-5 {{ $h2 }}">Backlink Paketleri</h2>
            <p class="mt-4 {{ $sub }}">
                Backlink paketi seçimi, link sayısıyla değil link kalitesiyle yapılır. Doğru çalışma; konuyla alakalı, otoritesi yüksek ve gerçek trafiği olan kaynaklardan, doğal bir anchor dağılımıyla kalıcı bağlantılar kurar.
            </p>
        </div>

        <div class="mt-10 grid gap-8 sm:grid-cols-3">
            @foreach ([
                ['title' => 'Hangi backlink paketi hangi site için uygundur?', 'text' => 'Doğru paket, link sayısına göre değil sitenin otorite açığına ve hedef sayfasına göre belirlenir. Yeni sitelerde güven kazandıran temel otorite öne çıkarken, belirli sayfaları yükseltmek isteyen projelerde daha güçlü ve hedefli kaynaklar gerekir.'],
                ['title' => 'Backlink paketi seçerken nelere dikkat edilmeli?', 'text' => 'İlk bakılması gereken şey link adedi değil kaynak kalitesidir. Kaynağın konuyla alaka düzeyi, gerçek trafiği, dofollow/nofollow dengesi ve anchor metin dağılımı belirsizse paket güçlü görünse bile risk taşır.'],
                ['title' => 'Backlink paketi kapsamında neler bulunmalı?', 'text' => 'Etkili bir backlink paketi; konuyla alakalı yüksek otoriteli kaynaklar, doğal anchor dağılımı, hedef sayfa planı ve şeffaf raporlamayı birlikte içermelidir. Linkin değeri sayısında değil, bağlamındadır.'],
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
                <p class="mt-4 {{ $sub }}">Backlink paketleri, kaynak kalitesi ve otorite çalışması hakkında en çok sorulan sorulara net cevaplar.</p>
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

    {{-- ================= KAPANIŞ CTA ================= --}}
    <section class="px-2 pb-2 sm:px-3">
        <div class="panel-dark relative overflow-hidden rounded-3xl text-white">
            <div class="relative mx-auto flex max-w-2xl flex-col items-center px-5 py-16 text-center sm:px-8">
                <p class="inline-flex items-center gap-x-2 rounded-full border border-white/15 bg-white/5 py-1 pe-3.5 ps-1 text-xs text-white/80">
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">AI Visibility</span>
                    SEO Strateji Ajansı
                </p>
                <h2 class="mt-5 font-display text-3xl font-medium leading-[1.2] sm:text-[40px]">
                    Hangi paket size uygun? Önce ücretsiz analiz alalım
                </h2>
                <p class="mt-4 max-w-md text-lg font-medium leading-relaxed text-white/65">
                    Sitenizi 4 AI motorunda ve Google'da analiz edelim; rekabet düzeyinize en uygun paketi 24 saat içinde önerelim.
                </p>
                <div class="mt-7 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('free-analysis.show') }}" class="group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-white to-[#c9c9c9] p-1 pe-5 text-sm font-medium text-ink transition hover:scale-[1.03] active:scale-[0.98]">
                        <span class="inline-flex size-8 items-center justify-center rounded-xl bg-gradient-to-b from-black to-[#363b3c] text-white">
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </span>
                        Ücretsiz Analiz İste
                    </a>
                    <a href="tel:08503052241" class="group inline-flex items-center gap-x-3 rounded-2xl border border-white/15 bg-white/5 p-1 pe-5 text-sm font-medium text-white transition hover:bg-white/10 hover:scale-[1.03] active:scale-[0.98]">
                        <span class="inline-flex size-8 items-center justify-center rounded-xl bg-white/10 text-white">
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                        </span>
                        0850 305 22 41
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
