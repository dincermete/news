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
    $btnChip = 'inline-flex size-8 items-center justify-center rounded-xl';
    $arrowIcon = '<svg class="size-3.5 transition group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>';
    $fmt = fn (int $n): string => number_format($n, 0, ',', '.');

    $impact = [
        ['value' => $stats['active_sites'], 'suffix' => '+', 'label' => 'Aktif haber sitesi'],
        ['value' => $stats['published_orders'], 'suffix' => '+', 'label' => 'Yayınlanmış içerik'],
        ['value' => $stats['customers'], 'suffix' => '+', 'label' => 'Kayıtlı müşteri'],
        ['value' => 8, 'suffix' => '', 'label' => 'Farklı hizmet hattı'],
    ];

    $painPoints = [
        'Onlarca site sahibiyle tek tek yazışıp fiyat pazarlığı yapmak',
        'Yayın durumunu öğrenmek için sürekli mesaj atmak',
        'Dağınık dekontlar ve takipsiz bütçe',
        'Hangi sitenin DA/PA değeri güncel, hangisi değil bilmemek',
        'Basın bülteni, backlink ve SEO için ayrı ayrı tedarikçi aramak',
    ];

    $focusPoints = [
        ['title' => 'Şeffaf fiyatlandırma', 'text' => 'Her sitenin ve paketin fiyatı katalogda görünür; pazarlık ya da "teklife özel" belirsizliği yok.', 'icon' => 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ['title' => 'Gerçek envanter', 'text' => 'Site, fiyat ve DA/PA verisi uydurulmaz; asistanımız ve katalog her zaman gerçek stoktan beslenir.', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ['title' => 'Hızlı yayın', 'text' => 'Hazır içeriklerde onaydan sonra 1–2 gün, editör desteğiyle hazırlanan siparişlerde 7 gün içinde yayındasınız.', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ['title' => 'Güvenli altyapı', 'text' => 'Ödemeler PayTR güvencesinde, tüm trafik SSL ile şifreli; yayınlar en az 6 ay link garantilidir.', 'icon' => 'M9 12.75 11.25 15 15 9.75m6-3v8.25a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V9.75m15-3h.375a2.25 2.25 0 0 1 2.25 2.25v.75'],
    ];

    $roles = ['Ajanslar', 'SEO Uzmanları', 'E-Ticaret', 'Girişimler', 'Markalar', 'Freelancerlar', 'Kurumsal Ekipler'];

    $teamFunctions = [
        ['title' => 'Editör Ekibi', 'text' => 'Talep ettiğinizde tanıtım yazınızı SEO uyumlu, özgün içerik olarak sizin için hazırlar.', 'icon' => 'M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5'],
        ['title' => 'SEO & GEO Uzmanları', 'text' => 'SEO, GEO ve AEO paketlerinizi kurar; teknik denetim ve raporlamayı yürütür.', 'icon' => 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
        ['title' => 'Destek Ekibi', 'text' => 'Sohbet asistanı, WhatsApp ve destek talepleri üzerinden sorularınızı yanıtlar.', 'icon' => 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z'],
        ['title' => 'Operasyon', 'text' => 'Yayın takibini, ödeme onaylarını ve fatura süreçlerini panelinizde güncel tutar.', 'icon' => 'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22'],
    ];
@endphp

@section('content')
    {{-- ================= HERO ================= --}}
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-dark relative overflow-hidden rounded-3xl text-white">
            <div class="relative mx-auto flex max-w-3xl flex-col items-center px-5 pb-14 pt-16 text-center sm:px-8 lg:pb-20 lg:pt-24" data-reveal-group>
                <p class="inline-flex items-center gap-x-2 rounded-full border border-white/15 bg-white/5 py-1 pe-3.5 ps-1 text-xs text-white/80" data-reveal>
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">Hakkımızda</span>
                    NewsTanıtım
                </p>

                <h1 class="mt-5 font-display text-4xl font-medium leading-[1.1] sm:text-5xl" data-reveal>
                    Tanıtımı tek panelde basitleştiriyoruz
                </h1>

                <p class="mt-5 max-w-xl text-lg font-medium leading-relaxed text-white/65" data-reveal>
                    Site yazısı, basın bülteni, backlink, story satış ve SEO/GEO hizmetlerini aramak, pazarlık etmek ve takip etmek için onlarca kişiyle yazışmanıza gerek yok. Katalogdan seçin, sepete ekleyin, yayını panelinizden izleyin.
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3" data-reveal>
                    <a href="{{ route('sites.index') }}" class="{{ $btnWhite }}">
                        <span class="{{ $btnChip }} bg-gradient-to-b from-black to-[#363b3c] text-white">{!! $arrowIcon !!}</span>
                        Katalogu İncele
                    </a>
                    <a href="{{ route('contact.show') }}" class="{{ $btnGhostDark }}">
                        <span class="{{ $btnChip }} bg-white/10 text-white">
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                        </span>
                        Bize Ulaşın
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= ETKİMİZ ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="mx-auto max-w-2xl text-center">
            <p data-reveal><span class="{{ $chip }}">Rakamlarla</span></p>
            <h2 class="mt-5 {{ $h2 }}" data-reveal>Bugüne kadar oluşturduğumuz etki</h2>
        </div>

        <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($impact as $item)
                <div class="text-center" data-reveal>
                    <p class="font-display text-[40px] font-semibold text-ink tabular-nums" data-odometer="{{ $item['value'] }}" data-odometer-suffix="{{ $item['suffix'] }}">{{ $fmt($item['value']) }}{{ $item['suffix'] }}</p>
                    <p class="mt-1.5 text-sm font-medium text-ink-2">{{ $item['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= NEDEN ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
            <div data-reveal-group>
                <p data-reveal><span class="{{ $chip }}">Neden NewsTanıtım</span></p>
                <h2 class="mt-5 {{ $h2 }}" data-reveal>Bu platformu neden kurduk</h2>
                <p class="mt-4 {{ $sub }}" data-reveal>
                    Dijital tanıtım yapan herkes aynı sorunlarla karşılaşıyor: doğru siteyi bulmak, güvenilir fiyat almak ve yayını takip etmek zaman alıyor.
                </p>
            </div>

            <div class="rounded-[20px] bg-paper p-8" data-reveal>
                <ul class="space-y-3.5">
                    @foreach ($painPoints as $point)
                        <li class="flex items-start gap-x-2.5 text-sm font-medium text-ink-2">
                            <svg class="mt-0.5 size-4 shrink-0 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            {{ $point }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- ================= ODAĞIMIZ ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="mx-auto max-w-2xl text-center">
            <p data-reveal><span class="{{ $chip }}">Odağımız</span></p>
            <h2 class="mt-5 {{ $h2 }}" data-reveal>Her şeyi bu ilkelere göre kuruyoruz</h2>
        </div>

        <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($focusPoints as $point)
                <div data-reveal>
                    <svg class="size-6 text-ink" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $point['icon'] }}"/></svg>
                    <h3 class="mt-4 font-display text-lg font-semibold text-ink">{{ $point['title'] }}</h3>
                    <p class="mt-1.5 text-sm font-medium leading-relaxed text-ink-2">{{ $point['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= KİMLER KULLANIYOR ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="mx-auto max-w-2xl text-center">
            <p data-reveal><span class="{{ $chip }}">Kimler kullanıyor</span></p>
            <h2 class="mt-5 {{ $h2 }}" data-reveal>Farklı ekipler, tek platform</h2>
        </div>

        <div class="mt-10 flex flex-wrap items-center justify-center gap-2.5" data-reveal>
            @foreach ($roles as $role)
                <span class="rounded-[10px] bg-paper px-4 py-2 text-sm font-medium text-ink-2">{{ $role }}</span>
            @endforeach
        </div>
    </section>

    {{-- ================= EKİBİMİZ ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8" data-reveal-group>
        <div class="mx-auto max-w-2xl text-center">
            <p data-reveal><span class="{{ $chip }}">Ekibimiz</span></p>
            <h2 class="mt-5 {{ $h2 }}" data-reveal>Ürünün arkasındaki ekipler</h2>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($teamFunctions as $team)
                <div class="rounded-[20px] bg-paper p-6" data-reveal>
                    <span class="inline-flex size-10 items-center justify-center rounded-[10px] bg-ink text-white">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $team['icon'] }}"/></svg>
                    </span>
                    <h3 class="mt-4 font-display text-base font-semibold text-ink">{{ $team['title'] }}</h3>
                    <p class="mt-1.5 text-[13px] font-medium leading-relaxed text-ink-2">{{ $team['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= KAPANIŞ CTA ================= --}}
    <section class="px-2 pb-2 sm:px-3">
        <div class="panel-dark relative overflow-hidden rounded-3xl text-white">
            <div class="relative mx-auto flex max-w-2xl flex-col items-center px-5 py-16 text-center sm:px-8">
                <h2 class="font-display text-3xl font-medium leading-[1.2] sm:text-[40px]">
                    Sitenizi tanıtmaya bugün başlayın
                </h2>
                <p class="mt-4 max-w-md text-lg font-medium leading-relaxed text-white/65">
                    Kayıt ücretsiz, kart bilgisi gerekmez. Ödemeyi yalnızca sipariş verdiğinizde yaparsınız.
                </p>
                <div class="mt-7 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ auth()->check() ? route('account.dashboard') : route('register') }}" class="{{ $btnWhite }}">
                        <span class="{{ $btnChip }} bg-gradient-to-b from-black to-[#363b3c] text-white">{!! $arrowIcon !!}</span>
                        Ücretsiz Başla
                    </a>
                    <a href="tel:08503052241" class="{{ $btnGhostDark }}">
                        <span class="{{ $btnChip }} bg-white/10 text-white">
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                        </span>
                        0850 305 22 41
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
