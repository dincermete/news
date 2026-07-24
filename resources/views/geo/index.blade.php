@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
    $h2 = 'font-display text-3xl font-medium leading-[1.2] tracking-[-0.01em] text-ink sm:text-[44px] lg:text-[52px]';
    $sub = 'text-lg font-medium leading-relaxed text-ink-2';
    $btnWhite = 'group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-white to-[#c9c9c9] p-1 pe-4 text-sm font-medium text-ink transition hover:scale-[1.03] active:scale-[0.98]';
    $btnGhostDark = 'group inline-flex items-center gap-x-3 rounded-2xl border border-white/15 bg-white/5 p-1 pe-4 text-sm font-medium text-white transition hover:bg-white/10 hover:scale-[1.03] active:scale-[0.98]';
    $btnDark = 'group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-4 text-sm font-medium text-white transition hover:scale-[1.03] active:scale-[0.98]';
    $btnChip = 'inline-flex size-8 items-center justify-center rounded-xl';
    $arrowIcon = '<svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>';

    $aiEngines = ['ChatGPT', 'Gemini', 'Claude', 'Perplexity', 'Copilot', 'Google AI Overview', 'Grok', 'DeepSeek'];

    $comparison = [
        'classic' => [
            'title' => 'Klasik SEO Yaklaşımı',
            'items' => [
                'Yalnızca Google sıralaması ve mavi linkler',
                'Anahtar kelime + backlink merkezli',
                'Sıfır tıklama sonuçlarında görünmüyor',
                'AI yanıtlarında alıntılanmıyor',
            ],
        ],
        'geo' => [
            'title' => 'GEO Yaklaşımı',
            'items' => [
                '5 AI motorunda kaynak ve alıntı olarak görünürlük',
                'Entity & varlık netliği + yapılandırılmış içerik',
                'E-E-A-T sinyalleri ve marka otoritesi inşası',
                'Klasik SEO temelleri üzerine inşa edilir, yerini almaz',
            ],
        ],
    ];

    $engineCards = [
        ['name' => 'ChatGPT', 'desc' => 'OpenAI · 200M+ haftalık kullanıcı'],
        ['name' => 'Gemini', 'desc' => 'Google · AI Overview kaynağı'],
        ['name' => 'Perplexity', 'desc' => 'Citation tabanlı yanıt motoru'],
        ['name' => 'Claude', 'desc' => 'Anthropic · uzun bağlam analizi'],
        ['name' => 'Copilot', 'desc' => 'Microsoft · Bing AI yanıtları'],
        ['name' => 'Google AI Overview', 'desc' => 'Google arama içi AI özetleri'],
    ];

    $services = [
        [
            'no' => '01',
            'title' => 'Yapay Zeka Arama Görünürlüğü Denetimi',
            'text' => 'AI platformlarının markanızı nasıl konumlandırdığını derinlemesine analiz ediyoruz; rakiplerinizin önerilme oranı ve referans alınan kaynakları inceliyoruz.',
            'items' => ['Marka & rakip prompt taraması', 'AI Share of Voice ölçümü', 'Citation kaynak haritası', 'Veri odaklı yol haritası'],
        ],
        [
            'no' => '02',
            'title' => 'Yapay Zeka Destekli İçerik Stratejisi',
            'text' => 'AI sistemlerinin kolayca çözümleyeceği, güven duyacağı ve referans gösterebileceği içerikler üretiyor ve optimize ediyoruz.',
            'items' => ['Topic cluster mimarisi', 'AEO Quick Answer blokları', 'Sektörel uzmanlık içerikleri', 'AI alıntılanabilir formatlar'],
        ],
        [
            'no' => '03',
            'title' => 'AI Tarama Botları için Teknik Optimizasyon',
            'text' => 'LLM\'lerin sitenizi tarama, okuma ve yorumlama kabiliyetini artırıyoruz; yapılandırılmış veri ve entity hiyerarşisi inşa ediyoruz.',
            'items' => ['llms.txt + robots.txt yönetimi', 'Tam schema kapsamı', 'Entity & Knowledge Graph', 'Crawl bütçesi optimizasyonu'],
        ],
        [
            'no' => '04',
            'title' => 'Otorite ve Güven Oluşturma (E-E-A-T)',
            'text' => 'Yapay zeka önerilerinin temelinde kaynak güvenilirliği yatar. Markanızı E-E-A-T kriterlerine uygun şekilde güçlendiriyoruz.',
            'items' => ['Otoriteli dijital PR', 'Çok kanallı marka tutarlılığı', 'Uzman görüş & yazar profilleri', 'Saygın yayın yerleşimleri'],
        ],
    ];

    $processSteps = [
        ['no' => '1', 'title' => 'Denetim & Analiz', 'text' => '5 büyük AI motorunda markanızın ve rakiplerinizin görünürlüğünü ölçer, kritik prompt\'ları haritalandırırız.', 'duration' => '1-2 HAFTA'],
        ['no' => '2', 'title' => 'Strateji & Yol Haritası', 'text' => 'Entity haritası, içerik mimarisi ve teknik öncelikler için 90 günlük uygulama planı hazırlarız.', 'duration' => '1 HAFTA'],
        ['no' => '3', 'title' => 'Üretim & Uygulama', 'text' => 'AEO uyumlu içerikler, schema, llms.txt, otorite tanıtım yazıları ve dijital PR çalışmalarını eş zamanlı yürütürüz.', 'duration' => 'SÜREKLİ'],
        ['no' => '4', 'title' => 'Ölçüm & İyileştirme', 'text' => 'AI Share of Voice, citation sayısı ve organik etki haftalık panelle takip edilir, strateji aylık güncellenir.', 'duration' => 'HAFTALIK + AYLIK'],
    ];

    $stats = [
        ['value' => '%12,3', 'title' => 'Ortalama dönüşüm', 'text' => 'AI sohbet kullanıcılarının ortalama dönüşüm oranı.', 'chip' => 'from-[#674cd0] to-[#a8a8ff]'],
        ['value' => '4×', 'title' => 'Satın alma sıklığı', 'text' => 'Klasik kullanıcıya göre satın alma sıklığı.', 'chip' => 'from-[#f045aa] to-[#eeaad2]'],
        ['value' => '5+', 'title' => 'Büyük dil modeli', 'text' => 'Takip edilen büyük dil modeli sayısı.', 'chip' => 'from-[#67a429] to-[#aee576]'],
        ['value' => '99', 'title' => 'Kontrol noktası', 'text' => 'GEO + SEO + AEO kontrol noktası.', 'chip' => 'from-[#fa8837] to-[#faac75]'],
    ];

    $tools = ['Google Analytics', 'Search Console', 'Tag Manager', 'Google Trends', 'Google İşletme Profili', 'Bing Webmaster', 'Yandex Webmaster', 'Semrush', 'Ahrefs', 'Screaming Frog', 'SEOmonitor', 'Majestic', 'Moz', 'Ubersuggest', 'KWFinder', 'Microsoft Clarity', 'Hotjar', 'GTmetrix', 'PageSpeed Insights', 'SimilarWeb', 'Looker Studio', 'Keyword Tool'];
    $aiTools = ['ChatGPT', 'Gemini', 'Perplexity', 'Claude', 'Grok', 'Copilot', 'DeepSeek', 'Google AI Overviews', 'Meta AI', 'Qwen', 'Kimi'];

    $faqItems = [
        ['q' => 'Yapay zeka aramaları için SEO (GEO) nedir?', 'a' => 'GEO, markanızın ChatGPT, Gemini, Claude, Perplexity ve Copilot gibi yapay zeka asistanları tarafından anlaşılabilir, güvenilir ve tavsiye edilebilir olmasını sağlamaya odaklanır. Google sıralamalarının ötesine geçerek AI araçlarının yanıt oluştururken içerik, varlık, uzmanlık ve marka otoritesini nasıl yorumladığı üzerinde çalışır.'],
        ['q' => 'GEO\'nun SEO\'dan farkı nedir?', 'a' => 'Geleneksel SEO sıralama, anahtar kelime ve backlink merkezlidir. GEO bu temelleri içerir; ek olarak entity netliği, yapılandırılmış içerik, kaynak güvenilirliği, E-E-A-T sinyalleri ve AI yanıtlarında alıntılanan kaynaklarda marka varlığını inceler.'],
        ['q' => 'Hangi yapay zeka araçları için geçerli?', 'a' => 'ChatGPT, Google AI Overview, Gemini, Claude, Perplexity, Copilot ve Grok dahil olmak üzere büyük AI asistanları ve AI destekli arama deneyimleri için tasarlanmıştır. Tek platform taktiği yerine ortak ilkelere odaklanıyoruz.'],
        ['q' => 'GEO çalışmaları kapsamında neler yapılır?', 'a' => 'Kaliteli ve yapılandırılmış içerik üretimi, semantik zenginlik, otorite artırma, veri güvenilirliği ve AI dostu içerik formatlarının oluşturulması yer alır. Amaç içeriğin AI araçları tarafından daha kolay anlaşılması ve önerilmesidir.'],
        ['q' => 'GEO neden önemlidir?', 'a' => 'Yapay zeka tabanlı arama sistemlerinin yaygınlaşmasıyla kullanıcılar artık doğrudan cevaplara ulaşıyor. GEO sayesinde markanız bu cevapların içinde yer alarak rekabet avantajı elde eder ve dijital görünürlüğünü geleceğe hazır hale getirir.'],
        ['q' => 'Geleneksel SEO yöntemlerini bırakmalı mıyım?', 'a' => 'Hayır. GEO, klasik SEO\'nun yerini almak yerine onun üzerine inşa edilir. Güçlü teknik temeller, temiz mimari ve kaliteli içerik hâlâ çok önemlidir. Sadece AI sistemlerinin bilgiyi nasıl işlediğine ve alıntıladığına uyarlanmak gerekir.'],
        ['q' => 'Yapay zeka tarafından üretilen içerik kullanıyor musunuz?', 'a' => 'Yapay zekayı uzmanlığın yerine değil destekleyici bir araç olarak kullanıyoruz. Tüm içerikler güvenilirlik ve doğruluğu sağlamak için gerçek deneyim, uzman görüşü ve marka bilgisi etrafında oluşturulur.'],
        ['q' => 'Sonuçları ne kadar sürede görürüm?', 'a' => 'İlk teknik kazanımlar 30-45 günde görülmeye başlar. AI motorlarında anlamlı citation ve Share of Voice artışı tipik olarak 90-120 günde, sürdürülebilir bir konumlanma 6-9 ayda elde edilir.'],
    ];
@endphp

@section('content')
    {{-- ================= HERO ================= --}}
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-dark relative overflow-hidden rounded-3xl text-white">
            <div class="relative mx-auto flex max-w-4xl flex-col items-center px-5 pb-14 pt-16 text-center sm:px-8 lg:pb-20 lg:pt-24" data-reveal-group>
                <p class="inline-flex items-center gap-x-2 rounded-full border border-white/15 bg-white/5 py-1 pe-3.5 ps-1 text-xs text-white/80" data-reveal>
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">GEO</span>
                    Generative Engine Optimization
                </p>

                <h1 class="mt-5 font-display text-4xl font-medium leading-[1.1] sm:text-5xl lg:text-[3.6rem]" data-reveal>
                    Yapay zeka cevaplarında kaynak olun
                </h1>

                <p class="mt-5 max-w-xl text-lg font-medium leading-relaxed text-white/65" data-reveal>
                    ChatGPT, Gemini, Claude, Perplexity ve Copilot artık müşterinizin ilk durağı. Google AI Overview ve AI Mode'da alıntılanan, önerilen ve güvenilen marka olun; geleneksel SEO'nun üzerine inşa edilmiş bütünleşik GEO stratejisiyle.
                </p>

                <div class="mt-6 flex max-w-2xl flex-wrap items-center justify-center gap-2" data-reveal>
                    @foreach ($aiEngines as $engine)
                        <span class="rounded-full border border-white/15 bg-white/5 px-3.5 py-1.5 text-xs font-medium text-white/80">{{ $engine }}</span>
                    @endforeach
                </div>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3" data-reveal>
                    <a href="tel:08503052241" class="{{ $btnWhite }}">
                        <span class="{{ $btnChip }} bg-gradient-to-b from-black to-[#363b3c] text-white">{!! $arrowIcon !!}</span>
                        Ücretsiz GEO Analizi
                    </a>
                    <a href="{{ route('bundles.index') }}" class="{{ $btnGhostDark }}">
                        <span class="{{ $btnChip }} bg-white/10 text-white">
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.14v14l11-7-11-7Z"/></svg>
                        </span>
                        SEO + GEO Paketleri
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= SEO VS GEO ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="mx-auto max-w-2xl text-center">
            <p data-reveal><span class="{{ $chip }}">SEO VS GEO</span></p>
            <h2 class="mt-5 {{ $h2 }}" data-reveal>Yapay zeka çağında oyunun kuralları değişti</h2>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2">
            <div class="rounded-[20px] border border-ink/10 bg-paper p-6 sm:p-8" data-reveal>
                <h3 class="font-display text-lg font-semibold text-ink">{{ $comparison['classic']['title'] }}</h3>
                <ul class="mt-4 space-y-3">
                    @foreach ($comparison['classic']['items'] as $item)
                        <li class="flex items-start gap-x-2.5 text-sm font-medium text-ink-2">
                            <svg class="mt-0.5 size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="rounded-[20px] border border-ink/10 bg-white p-6 shadow-pop sm:p-8" data-reveal>
                <h3 class="font-display text-lg font-semibold text-ink">{{ $comparison['geo']['title'] }}</h3>
                <ul class="mt-4 space-y-3">
                    @foreach ($comparison['geo']['items'] as $item)
                        <li class="flex items-start gap-x-2.5 text-sm font-medium text-ink-2">
                            <svg class="mt-0.5 size-4 shrink-0 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- ================= GEO NEDİR ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
            <div>
                <p data-reveal><span class="{{ $chip }}">GEO Nedir?</span></p>
                <h2 class="mt-5 {{ $h2 }}" data-reveal>Markanız yapay zeka cevaplarının içinde olsun</h2>
                <p class="mt-4 max-w-md {{ $sub }}" data-reveal>
                    İnsanlar artık ürün araştırırken, hizmet karşılaştırırken ve marka seçerken farklı LLM modellerini kullanıyor. Şirketiniz bu yanıtlarda yoksa trafik ve dönüşümleri rakiplerinize bırakıyorsunuz. GEO, markanızın yapay zeka asistanları tarafından bahsedilmesini, tavsiye edilmesini ve güven duyulmasını sağlar.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2" data-reveal>
                @foreach ($engineCards as $engine)
                    <div class="rounded-2xl bg-paper p-5">
                        <h3 class="font-display text-base font-semibold text-ink">{{ $engine['name'] }}</h3>
                        <p class="mt-1 text-xs font-medium text-ink-2">{{ $engine['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= HİZMET KAPSAMIMIZ ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="mx-auto max-w-2xl text-center">
            <p data-reveal><span class="{{ $chip }}">Hizmet Kapsamımız</span></p>
            <h2 class="mt-5 {{ $h2 }}" data-reveal>AI araçları için bütünleşik GEO yaklaşımı</h2>
            <p class="mt-4 {{ $sub }}" data-reveal>
                İçerik stratejisi, teknik SEO ve otorite inşasını bir araya getiren dört eksenli metodolojiyle markanızı yapay zeka destekli aramalarda güvenilir ve uzman bir kaynak olarak konumlandırıyoruz.
            </p>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2">
            @foreach ($services as $service)
                <div class="rounded-[20px] border border-ink/10 bg-white p-6 transition hover:-translate-y-0.5 hover:shadow-pop sm:p-8" data-reveal>
                    <span class="inline-flex size-11 items-center justify-center rounded-[10px] bg-gradient-to-b from-black to-[#363b3c] font-display text-lg font-semibold text-white">{{ $service['no'] }}</span>
                    <h3 class="mt-4 font-display text-[22px] font-semibold text-ink">{{ $service['title'] }}</h3>
                    <p class="mt-2 {{ $sub }} text-base">{{ $service['text'] }}</p>
                    <ul class="mt-4 space-y-2">
                        @foreach ($service['items'] as $item)
                            <li class="flex items-start gap-x-2.5 text-sm font-medium text-ink-2">
                                <svg class="mt-0.5 size-4 shrink-0 text-accent-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= ÇALIŞMA YÖNTEMİMİZ ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="mx-auto max-w-2xl text-center">
            <p data-reveal><span class="{{ $chip }}">Çalışma Yöntemimiz</span></p>
            <h2 class="mt-5 {{ $h2 }}" data-reveal>4 adımda yapay zeka görünürlüğü</h2>
            <p class="mt-4 {{ $sub }}" data-reveal>İlk denetimden ölçülebilir sonuca: GEO çalışmamızın yol haritası.</p>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($processSteps as $step)
                <div class="rounded-[20px] bg-paper p-6" data-reveal>
                    <span class="inline-flex size-10 items-center justify-center rounded-[10px] bg-white font-display text-lg font-semibold text-ink shadow-soft">{{ $step['no'] }}</span>
                    <h3 class="mt-4 font-display text-lg font-semibold text-ink">{{ $step['title'] }}</h3>
                    <p class="mt-1.5 text-sm font-medium leading-relaxed text-ink-2">{{ $step['text'] }}</p>
                    <span class="mt-4 inline-flex items-center rounded-full bg-ink/5 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-ink-3">{{ $step['duration'] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= RAKAMLARLA GEO ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="mx-auto max-w-2xl text-center">
            <p data-reveal><span class="{{ $chip }}">Rakamlarla GEO</span></p>
            <h2 class="mt-5 {{ $h2 }}" data-reveal>Yapay zeka aramaları artık satışa dönüşüyor</h2>
        </div>

        <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($stats as $stat)
                <div data-reveal>
                    <span class="inline-flex size-[50px] items-center justify-center rounded-[10px] bg-gradient-to-br {{ $stat['chip'] }} text-white">
                        <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                    </span>
                    <p class="mt-4 font-display text-[32px] font-medium leading-none text-ink">{{ $stat['value'] }}</p>
                    <h3 class="mt-3 font-display text-lg font-semibold text-ink">{{ $stat['title'] }}</h3>
                    <p class="mt-1 text-sm font-medium leading-snug text-ink-2">{{ $stat['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= ARAÇ SETİMİZ ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="mx-auto max-w-2xl text-center">
            <p data-reveal><span class="{{ $chip }}">Araç Setimiz</span></p>
            <h2 class="mt-5 {{ $h2 }}" data-reveal>GEO performansını ölçen profesyonel araçlar</h2>
            <p class="mt-4 {{ $sub }}" data-reveal>AI Share of Voice, citation takibi ve klasik SEO metriklerini tek panelde birleştiriyoruz.</p>
        </div>

        <div class="mt-10 space-y-4" data-reveal>
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

    {{-- ================= SSS ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
            <div class="lg:sticky lg:top-28 lg:self-start" data-reveal-group>
                <p data-reveal><span class="{{ $chip }}">SSS</span></p>
                <h2 class="mt-5 max-w-xs {{ $h2 }}" data-reveal>GEO hakkında merak edilenler</h2>
                <p class="mt-4 {{ $sub }}" data-reveal>Yapay zeka aramaları, GEO süreci ve klasik SEO ile farkı hakkında en çok sorulan sorular.</p>
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
            <div class="relative mx-auto flex max-w-2xl flex-col items-center px-5 py-16 text-center sm:px-8" data-reveal-group>
                <p class="inline-flex items-center gap-x-2 rounded-full border border-white/15 bg-white/5 py-1 pe-3.5 ps-1 text-xs text-white/80" data-reveal>
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">AI Visibility</span>
                    SEO Strateji Ajansı
                </p>
                <h2 class="mt-5 font-display text-3xl font-medium leading-[1.2] sm:text-[40px]" data-reveal>
                    Yapay zekada nerede görünüyorsunuz?
                </h2>
                <p class="mt-4 max-w-md text-lg font-medium leading-relaxed text-white/65" data-reveal>
                    Markanızı 5 AI motorunda ve Google'da analiz edelim; GEO yol haritanızı 24 saat içinde önerelim.
                </p>
                <div class="mt-7 flex flex-wrap items-center justify-center gap-3" data-reveal>
                    <a href="tel:08503052241" class="{{ $btnWhite }}">
                        <span class="{{ $btnChip }} bg-gradient-to-b from-black to-[#363b3c] text-white">{!! $arrowIcon !!}</span>
                        Ücretsiz GEO Analizi
                    </a>
                    <a href="tel:08503052241" class="{{ $btnGhostDark }}">
                        <span class="{{ $btnChip }} bg-white/10 text-white">
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                        </span>
                        0850 840 95 39
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
