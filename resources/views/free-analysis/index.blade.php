@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
    $h2 = 'font-display text-3xl font-medium leading-[1.2] tracking-[-0.01em] text-ink sm:text-[44px] lg:text-[52px]';
    $sub = 'text-lg font-medium leading-relaxed text-ink-2';
    $btnWhite = 'group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-4 text-sm font-medium text-white transition hover:scale-[1.03] active:scale-[0.98]';
    $btnGhostDark = 'group inline-flex items-center gap-x-3 rounded-2xl border border-ink/10 bg-white p-1 pe-4 text-sm font-medium text-ink transition hover:bg-paper-2 hover:scale-[1.03] active:scale-[0.98]';
    $btnDark = 'group inline-flex items-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-4 text-sm font-medium text-white transition hover:scale-[1.03] active:scale-[0.98]';
    $btnChip = 'inline-flex size-8 items-center justify-center rounded-xl';
    $arrowIcon = '<svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>';

    $highlights = [
        ['title' => '24 saatte dönüş', 'text' => 'Talebiniz alındıktan sonra analiz özetini panelinizde görürsünüz.', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ['title' => 'Ücretsiz & bağlayıcı değil', 'text' => 'Ödeme yok; yalnızca site ve hedef hizmet bilgisi yeterli.', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ['title' => 'SEO + GEO + backlink', 'text' => 'İhtiyacınıza göre doğru hizmet hattını öneriyoruz.', 'icon' => 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z'],
        ['title' => 'Panelden takip', 'text' => 'Sonuçlar hesabım > Analizlerim bölümünde kalır.', 'icon' => 'M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25'],
    ];

    $processSteps = [
        ['no' => '1', 'title' => 'Formu doldurun', 'text' => 'Site URL’nizi, hizmet türünü ve varsa kısa brief’inizi gönderin.'],
        ['no' => '2', 'title' => 'Hızlı tarama', 'text' => 'Ekibimiz sitenizi ve hedeflerinizi hızlıca gözden geçirir.'],
        ['no' => '3', 'title' => 'Yol haritası', 'text' => 'Öncelikli aksiyonları ve uygun ürün/paket önerisini hazırlarız.'],
        ['no' => '4', 'title' => 'Panelde sonuç', 'text' => '24 saat içinde Analizlerim sayfasından sonucu görürsünüz.'],
    ];

    $includes = [
        'Mevcut görünürlük ve teknik durum özeti',
        'Hizmet tipine göre öncelikli fırsatlar',
        'Katalog ürünleriyle uyumlu öneri (SEO, GEO, backlink…)',
        'Sonraki adım için net aksiyon listesi',
    ];

    $faqItems = [
        ['q' => 'Analiz gerçekten ücretsiz mi?', 'a' => 'Evet. Formu doldurmanız yeterlidir; kart bilgisi veya peşin ödeme istenmez.'],
        ['q' => 'Sonucu nereden görürüm?', 'a' => 'Giriş yaptıktan sonra Hesabım > Analizlerim bölümünden talebinizi ve sonucu takip edebilirsiniz.'],
        ['q' => 'Hangi hizmetler için analiz alabilirim?', 'a' => 'SEO, GEO, backlink, web tasarım, Google Ads, sosyal medya veya henüz emin değilseniz “Bilmiyorum” seçeneğini kullanabilirsiniz.'],
        ['q' => 'Ne kadar sürer?', 'a' => 'Standart dönüş süremiz 24 saattir. Yoğun dönemlerde kısa gecikmeler olabilir; panel üzerinden durumu izlersiniz.'],
    ];
@endphp

@section('content')
    {{-- ================= HERO / BANNER ================= --}}
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-light relative overflow-hidden rounded-3xl text-ink">
            <div class="relative mx-auto flex max-w-3xl flex-col items-center px-5 pb-14 pt-16 text-center sm:px-8 lg:pb-20 lg:pt-24" data-reveal-group>
                <p class="inline-flex items-center gap-x-2 rounded-full border border-ink/10 bg-white py-1 pe-3.5 ps-1 text-xs text-ink-2 shadow-soft" data-reveal>
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">Ücretsiz Analiz</span>
                    24 saatte dönüş
                </p>

                <h1 class="mt-5 font-display text-4xl font-medium leading-[1.1] sm:text-5xl lg:text-[3.4rem]" data-reveal>
                    Projenize özel ücretsiz görünürlük analizi
                </h1>

                <p class="mt-5 max-w-xl text-lg font-medium leading-relaxed text-ink-2" data-reveal>
                    Sitenizi inceleyelim; SEO, GEO, backlink veya diğer hedeflerinize göre size en uygun yol haritasını panelinizde paylaşalım. Bağlayıcı teklif yok — yalnızca net sonraki adımlar.
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3" data-reveal>
                    <a href="#analiz-formu" class="{{ $btnWhite }}">
                        <span class="{{ $btnChip }} bg-white/15 text-white">{!! $arrowIcon !!}</span>
                        Analiz Formuna Git
                    </a>
                    <a href="{{ route('geo.index') }}" class="{{ $btnGhostDark }}">
                        <span class="{{ $btnChip }} bg-ink/5 text-ink">
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                        </span>
                        GEO hakkında bilgi
                    </a>
                </div>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-ink-2" data-reveal>
                    <span>Ücretsiz talep</span>
                    <span>·</span>
                    <span>Panelden takip</span>
                    <span>·</span>
                    <span>24 saat içinde özet</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= NEDEN ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="mx-auto max-w-2xl text-center">
            <p data-reveal><span class="{{ $chip }}">Neden ücretsiz analiz?</span></p>
            <h2 class="mt-5 {{ $h2 }}" data-reveal>Doğru hizmeti tahmin etmek yerine ölçüyoruz</h2>
            <p class="mt-4 {{ $sub }}" data-reveal>
                Her site aynı pakete ihtiyaç duymaz. Kısa bir analizle önceliklerinizi netleştirir, katalogdaki ürünlerle uyumlu öneri sunarız.
            </p>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($highlights as $item)
                <div class="rounded-[20px] border border-ink/10 bg-white p-6" data-reveal>
                    <span class="inline-flex size-10 items-center justify-center rounded-[10px] bg-ink text-white">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                    </span>
                    <h3 class="mt-4 font-display text-base font-semibold text-ink">{{ $item['title'] }}</h3>
                    <p class="mt-1.5 text-sm font-medium leading-relaxed text-ink-2">{{ $item['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= SÜREÇ ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="mx-auto max-w-2xl text-center">
            <p data-reveal><span class="{{ $chip }}">Süreç</span></p>
            <h2 class="mt-5 {{ $h2 }}" data-reveal>4 adımda ücretsiz analiz</h2>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($processSteps as $step)
                <div class="rounded-[20px] bg-paper p-6" data-reveal>
                    <span class="inline-flex size-10 items-center justify-center rounded-[10px] bg-white font-display text-lg font-semibold text-ink shadow-soft">{{ $step['no'] }}</span>
                    <h3 class="mt-4 font-display text-lg font-semibold text-ink">{{ $step['title'] }}</h3>
                    <p class="mt-1.5 text-sm font-medium leading-relaxed text-ink-2">{{ $step['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= ANALİZDE NELER VAR ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
            <div>
                <p data-reveal><span class="{{ $chip }}">Kapsam</span></p>
                <h2 class="mt-5 {{ $h2 }}" data-reveal>Analizde neler bulacaksınız?</h2>
                <p class="mt-4 {{ $sub }}" data-reveal>
                    Bu bir satış sunumu değil; sitenizin durumuna ve seçtiğiniz hizmete göre kısa, uygulanabilir bir özet.
                </p>
            </div>
            <div class="rounded-[20px] border border-ink/10 bg-white p-6 sm:p-8" data-reveal>
                <ul class="space-y-3.5">
                    @foreach ($includes as $item)
                        <li class="flex items-start gap-x-2.5 text-sm font-medium text-ink-2">
                            <svg class="mt-0.5 size-4 shrink-0 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- ================= FORM ================= --}}
    <section id="analiz-formu" class="mx-auto max-w-3xl scroll-mt-28 px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center" data-reveal-group>
            <p data-reveal><span class="{{ $chip }}">Talep formu</span></p>
            <h2 class="mt-5 {{ $h2 }}" data-reveal>Ücretsiz analiz talebi gönderin</h2>
            <p class="mt-4 {{ $sub }}" data-reveal>Formu doldurun; sonucu hesabım panelinizden takip edin.</p>
        </div>

        <div class="mt-10 rounded-[20px] border border-ink/10 bg-paper p-6 sm:p-8" data-reveal>
            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-disc space-y-1 ps-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @guest
                <div class="flex flex-col items-center gap-y-4 py-8 text-center">
                    <span class="inline-flex size-12 items-center justify-center rounded-full bg-ink/5 text-ink">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                    </span>
                    <p class="font-display text-lg font-semibold text-ink">Analiz talebi göndermek için giriş yapın</p>
                    <p class="max-w-sm text-sm font-medium text-ink-2">Sonucu hesabınızdan takip edebilmeniz için önce giriş yapmanız gerekiyor.</p>
                    <button
                        type="button"
                        class="{{ $btnDark }}"
                        onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))"
                    >
                        <span class="{{ $btnChip }} bg-white/15 text-white">{!! $arrowIcon !!}</span>
                        Giriş yap
                    </button>
                </div>
            @else
                <form method="post" action="{{ route('free-analysis.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Site adresiniz *</label>
                        <input type="url" name="site_url" required value="{{ old('site_url') }}" placeholder="https://siteniz.com" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink placeholder:text-ink-3 focus:border-ink/30 focus:ring-0">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Hangi hizmet için görüşüyoruz? *</label>
                        <select name="service_type" required class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0">
                            @foreach ($serviceTypes as $type)
                                <option value="{{ $type->value }}" @selected(old('service_type') === $type->value)>{{ $type->getLabel() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Kısaca anlatmak istediğiniz</label>
                        <textarea name="brief" rows="4" class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink placeholder:text-ink-3 focus:border-ink/30 focus:ring-0" placeholder="Hedefleriniz, rakipleriniz veya bilmemizi istediğiniz detaylar…">{{ old('brief') }}</textarea>
                    </div>
                    <button type="submit" class="{{ $btnDark }} w-full justify-center">
                        <span class="{{ $btnChip }} bg-white/15 text-white">{!! $arrowIcon !!}</span>
                        Analiz Talebi Gönder
                    </button>
                    <p class="text-center text-[11px] font-medium text-ink-3">Talep sonrası 24 saat içinde dönüş yapılır; sonucu hesabım panelinizden takip edebilirsiniz.</p>
                </form>
            @endguest
        </div>
    </section>

    {{-- ================= SSS ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
            <div class="lg:sticky lg:top-28 lg:self-start" data-reveal-group>
                <p data-reveal><span class="{{ $chip }}">SSS</span></p>
                <h2 class="mt-5 max-w-xs {{ $h2 }}" data-reveal>Merak edilenler</h2>
                <p class="mt-4 {{ $sub }}" data-reveal>Ücretsiz analiz süreci hakkında sık sorulan sorular.</p>
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
        <div class="panel-light relative overflow-hidden rounded-3xl text-ink">
            <div class="relative mx-auto flex max-w-2xl flex-col items-center px-5 py-16 text-center sm:px-8" data-reveal-group>
                <p class="inline-flex items-center gap-x-2 rounded-full border border-ink/10 bg-white py-1 pe-3.5 ps-1 text-xs text-ink-2 shadow-soft" data-reveal>
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">Hazır mısınız?</span>
                    Ücretsiz Analiz
                </p>
                <h2 class="mt-5 font-display text-3xl font-medium leading-[1.2] sm:text-[40px]" data-reveal>
                    Sitenizin yol haritasını birlikte netleştirelim
                </h2>
                <p class="mt-4 max-w-md text-lg font-medium leading-relaxed text-ink-2" data-reveal>
                    Formu doldurun; 24 saat içinde panelinizde özet analizi görün.
                </p>
                <div class="mt-7" data-reveal>
                    <a href="#analiz-formu" class="{{ $btnWhite }}">
                        <span class="{{ $btnChip }} bg-white/15 text-white">{!! $arrowIcon !!}</span>
                        Forma dön
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
