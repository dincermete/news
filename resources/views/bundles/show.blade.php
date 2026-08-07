@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /** @var \App\Models\SiteBundle $bundle */
    /** @var \Illuminate\Support\Collection<int, \App\Models\FaqEntry> $faqs */
    /** @var \Illuminate\Support\Collection<int, \App\Models\SiteBundle> $relatedBundles */
    /** @var \Illuminate\Support\Collection<int, \App\Models\BlogPost> $latestBlogPosts */
    /** @var \Illuminate\Support\Collection<int, \App\Models\SiteReview> $reviews */
    /** @var \Illuminate\Support\Collection<int, \App\Models\SiteQuestion> $questions */

    $currency = $bundle->currency?->value ?? (string) $bundle->currency;
    $money = function (float $amount, string $curr): string {
        $symbol = $curr === 'TRY' ? '₺' : '$';
        $formatted = fmod($amount, 1.0) > 0.009
            ? number_format($amount, 2, ',', '.')
            : number_format($amount, 0, ',', '.');

        return $formatted.$symbol;
    };

    $num = fn (?float $value): ?string => $value !== null ? number_format($value, 0, ',', '.') : null;
    $metricValue = fn (?string $text): string => '<span class="text-sm font-bold tabular-nums text-ink">'.e(filled($text) ? $text : '—').'</span>';
    $yesBadge = 'inline-flex items-center rounded-full bg-emerald-100 px-2 py-1 text-[11px] font-semibold text-emerald-700';
    $noBadge = 'inline-flex items-center rounded-full bg-ink/5 px-2 py-1 text-[11px] font-semibold text-ink-3';
    $softBadge = 'inline-flex items-center rounded-full bg-sky-100 px-2 py-1 text-[11px] font-semibold text-sky-800';
    $badge = fn (string $text, string $class): string => '<span class="'.$class.'">'.e($text).'</span>';

    $tones = [
        'blue' => 'bg-blue-50 text-blue-600',
        'green' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600',
    ];

    $glyphs = [
        'stack' => 'M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3A2.25 2.25 0 0 1 19.5 9v.878m0 0a2.246 2.246 0 0 0-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6c0-.98.626-1.813 1.5-2.122',
        'shield' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
        'link' => 'M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244',
        'news' => 'M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5',
        'calendar' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
        'currency' => 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
    ];

    $daValues = $bundle->sites->pluck('da_value')->filter(fn ($v) => $v !== null)->map(fn ($v) => (float) $v);
    $avgDa = $daValues->isNotEmpty() ? round($daValues->avg()) : null;
    $dofollowCount = $bundle->sites->where('is_dofollow', true)->count();
    $newsCount = $bundle->sites->where('is_news_approved', true)->count();
    $indexedCount = $bundle->sites->where('is_google_indexed', true)->count();

    $trustStrip = collect([
        [
            'label' => 'Site Sayısı',
            'value' => (string) $bundle->sites_count,
            'tint' => 'from-[#eef3ff] to-white',
            'gradient' => 'from-[#2248ab] to-[#7aa2ff]',
            'icon' => $glyphs['stack'],
        ],
        [
            'label' => 'Ortalama DA',
            'value' => $num($avgDa !== null ? (float) $avgDa : null),
            'tint' => 'from-[#fff8f3] to-white',
            'gradient' => 'from-[#fa8837] to-[#faac75]',
            'icon' => $glyphs['shield'],
        ],
        [
            'label' => 'News Onaylı',
            'value' => (string) $newsCount,
            'tint' => 'from-[#f0fdfa] to-white',
            'gradient' => 'from-[#0d9488] to-[#5eead4]',
            'icon' => $glyphs['news'],
        ],
    ])->filter(fn (array $metric): bool => filled($metric['value']))->values();

    /** @var list<array{label: string, tone: string, glyph: string, html: string}> $paketFactRows */
    $paketFactRows = [
        [
            'label' => 'Dofollow Site',
            'tone' => 'blue',
            'glyph' => 'link',
            'html' => $dofollowCount > 0
                ? $badge($dofollowCount.' Adet', $softBadge)
                : $badge('Yok', $noBadge),
        ],
        [
            'label' => 'Google Index',
            'tone' => 'green',
            'glyph' => 'news',
            'html' => $indexedCount > 0
                ? $badge($indexedCount.' Site', $yesBadge)
                : $badge('Yok', $noBadge),
        ],
        [
            'label' => 'Link Garantisi',
            'tone' => 'amber',
            'glyph' => 'calendar',
            'html' => $metricValue('6 Ay'),
        ],
        [
            'label' => 'Para Birimi',
            'tone' => 'amber',
            'glyph' => 'currency',
            'html' => $metricValue($currency),
        ],
        [
            'label' => 'Toplu Yayın',
            'tone' => 'green',
            'glyph' => 'stack',
            'html' => $badge('Var', $yesBadge),
        ],
        [
            'label' => 'Pakete Dahil',
            'tone' => 'blue',
            'glyph' => 'stack',
            'html' => $metricValue($bundle->sites_count.' Site'),
        ],
    ];

    $shortDescription = $bundle->description
        ? \Illuminate\Support\Str::limit($bundle->description, 140)
        : null;

    $metaFacts = collect([
        $bundle->sites_count.' Site',
        $dofollowCount > 0 ? $dofollowCount.' Dofollow' : null,
        'Tek işlemde toplu yayın',
    ])->filter()->values();

    $deliveryDetails = site_setting()->defaultDeliveryDetails();
    if (filled($deliveryDetails) && trim(strip_tags($deliveryDetails)) === '') {
        $deliveryDetails = null;
    }

    $tabs = collect([
        ['key' => 'aciklama', 'label' => 'Açıklamalar'],
        $deliveryDetails !== null ? ['key' => 'teslimat', 'label' => 'Teslimat Detayları'] : null,
        ['key' => 'yorumlar', 'label' => 'Kullanıcı Yorumları'],
        ['key' => 'soru-cevap', 'label' => 'Kullanıcı Soruları & Yanıtları'],
        filled($faqs) && $faqs->isNotEmpty() ? ['key' => 'sss', 'label' => 'Sık Sorulan Sorular'] : null,
    ])->filter()->values();

    $card = 'min-w-0 overflow-hidden rounded-[20px] border border-ink/10 bg-white shadow-soft';
    $ctaBase = 'inline-flex w-full items-center justify-center gap-x-2 rounded-xl px-4 py-3.5 text-sm font-semibold transition active:scale-[0.99]';
    $ctaCart = $ctaBase.' bg-gradient-to-b from-black to-[#363b3c] text-white hover:scale-[1.02]';
    $ctaBuy = $ctaBase.' border border-ink/15 bg-white text-ink hover:bg-paper';
    $ctaWhatsapp = $ctaBase.' border border-emerald-500/40 bg-white text-emerald-700 hover:bg-emerald-50';
    $outlineBtn = 'inline-flex w-full items-center justify-center gap-x-1.5 rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm font-medium text-ink transition hover:border-ink/20 hover:bg-paper';
    $linkAccent = 'text-ink transition hover:text-ink-2';
    $inputClass = 'block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink focus:ring-0';
    $labelClass = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3';
@endphp

@section('content')
    <section class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8" data-reveal-group>
        <nav class="flex items-center gap-x-1.5 text-xs text-ink-3" aria-label="Konum" data-reveal>
            <a href="{{ route('home') }}" class="transition hover:text-ink">Anasayfa</a>
            <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            <a href="{{ route('bundles.index') }}" class="transition hover:text-ink">Tanıtım Paketleri</a>
            <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            <span class="truncate text-ink-2">{{ $bundle->name }}</span>
        </nav>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <div class="grid min-w-0 gap-5 sm:gap-6 lg:grid-cols-[1.75fr_.85fr] lg:items-start" data-reveal-group>
            <div class="contents lg:flex lg:min-w-0 lg:flex-col lg:gap-5">
                {{-- 1. Paket kimliği --}}
                <div class="{{ $card }} p-5 sm:p-6" style="order: 1" data-reveal>
                    <div class="flex items-start gap-3 sm:gap-x-4">
                        <span
                            class="inline-flex size-[60px] shrink-0 items-center justify-center rounded-lg text-white shadow-soft"
                            style="{{ $bundle->iconBadgeStyle() }}"
                        >
                            {{ svg($bundle->resolvedIcon(), 'size-7') }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <h1 class="break-words font-display text-xl font-medium leading-tight text-ink sm:truncate sm:text-2xl">{{ $bundle->name }}</h1>
                            <p class="mt-1 text-sm text-ink-2">Tanıtım Paketi</p>
                        </div>
                    </div>

                    @if ($shortDescription)
                        <p class="mt-4 max-w-2xl text-sm leading-relaxed text-ink-2">{{ $shortDescription }}</p>
                    @endif

                    @if ($metaFacts->isNotEmpty())
                        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-ink/10 pt-4">
                            @foreach ($metaFacts as $fact)
                                <span class="inline-flex items-center rounded-full bg-paper px-2.5 py-1 text-xs font-medium text-ink-2">
                                    {{ $fact }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- 3. Güven şeridi --}}
                @if ($trustStrip->isNotEmpty())
                    <div
                        @class([
                            'grid gap-3',
                            'sm:grid-cols-1' => $trustStrip->count() === 1,
                            'sm:grid-cols-2' => $trustStrip->count() === 2,
                            'sm:grid-cols-3' => $trustStrip->count() >= 3,
                        ])
                        style="order: 3"
                        data-reveal
                    >
                        @foreach ($trustStrip as $metric)
                            <div class="rounded-[20px] border border-ink/10 bg-gradient-to-br {{ $metric['tint'] }} p-5 shadow-soft sm:p-6">
                                <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-[10px] bg-gradient-to-br text-white {{ $metric['gradient'] }}">
                                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $metric['icon'] }}" />
                                    </svg>
                                </span>
                                <p class="mt-3 font-display text-2xl font-semibold tabular-nums tracking-tight text-ink sm:text-3xl">
                                    {{ $metric['value'] }}
                                </p>
                                <p class="mt-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-3">
                                    {{ $metric['label'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- 5. Paket Verileri --}}
                <div class="{{ $card }}" style="order: 5" data-reveal>
                    <div class="border-b border-ink/10 px-5 py-4 sm:px-6">
                        <x-section-heading
                            size="sm"
                            gradient="from-[#2248ab] to-[#7aa2ff]"
                            icon="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"
                        >Paket Verileri</x-section-heading>
                    </div>

                    <div class="grid grid-cols-1 gap-2 p-4 sm:p-5 md:grid-cols-2">
                        @foreach ($paketFactRows as $row)
                            <div class="flex items-center justify-between gap-3 rounded-lg bg-paper px-4 py-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-full {{ $tones[$row['tone']] }}">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $glyphs[$row['glyph']] }}" />
                                        </svg>
                                    </span>
                                    <span class="truncate text-sm font-medium text-ink-2">{{ $row['label'] }}</span>
                                </div>
                                <div class="shrink-0 text-end">
                                    {!! $row['html'] !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- 4. Pakete dahil siteler (girişte görünür) --}}
                <div class="{{ $card }}" style="order: 4" data-reveal>
                    <div class="flex items-center justify-between gap-3 border-b border-ink/10 px-5 py-4 sm:px-6">
                        <x-section-heading
                            size="sm"
                            gradient="from-[#fa8837] to-[#faac75]"
                            icon="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c1.657 0 3-4.03 3-9s-1.343-9-3-9-3 4.03-3 9 1.343 9 3 9Zm-8.716-6.747h17.432M3.284 9.747h17.432"
                        >Pakete Dahil Siteler</x-section-heading>
                        <span class="shrink-0 rounded-full bg-paper px-2.5 py-1 text-[11px] font-semibold text-ink-2">{{ $bundle->sites_count }} site</span>
                    </div>

                    @if ($bundle->sites->isNotEmpty())
                        <ul class="divide-y divide-ink/10">
                            @foreach ($bundle->sites as $site)
                                <li>
                                    <a href="{{ storefront_site_url($site) }}" class="flex items-center gap-4 px-5 py-3.5 transition hover:bg-paper sm:px-6">
                                        <x-site-identity
                                            :site="$site"
                                            :height="36"
                                            :linked="false"
                                            class="min-w-0 flex-1 gap-x-4"
                                            domain-class="truncate text-sm font-semibold text-ink"
                                        />
                                        <div class="flex shrink-0 items-center gap-1.5">
                                            @if ($site->da_value !== null)
                                                <span class="inline-flex items-center rounded-full bg-paper px-2.5 py-1 text-[11px] font-bold text-ink">DA {{ number_format((float) $site->da_value, 0) }}</span>
                                            @endif
                                            @if ($site->is_dofollow)
                                                <span class="hidden items-center rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 sm:inline-flex">Dofollow</span>
                                            @endif
                                        </div>
                                        <svg class="size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="px-5 py-8 text-center text-sm text-ink-3 sm:px-6">Bu pakete henüz site eklenmedi.</p>
                    @endif
                </div>

                {{-- 6. Detay sekmeleri --}}
                <div class="{{ $card }}" style="order: 6" x-data="{ tab: window.location.hash === '#yorumlar' ? 'yorumlar' : 'aciklama' }" data-reveal>
                    <div class="no-scrollbar flex gap-1 overflow-x-auto border-b border-ink/10 px-2 pt-2">
                        @foreach ($tabs as $t)
                            <button
                                type="button"
                                @click="tab = '{{ $t['key'] }}'"
                                :class="tab === '{{ $t['key'] }}' ? 'border-ink text-ink' : 'border-transparent text-ink-3 hover:text-ink-2'"
                                class="shrink-0 whitespace-nowrap border-b-2 px-3 py-3 text-xs font-semibold transition sm:px-4 sm:text-sm"
                            >{{ $t['label'] }}</button>
                        @endforeach
                    </div>

                    <div class="p-5 sm:p-6">
                        <div x-show="tab === 'aciklama'">
                            @if (filled($bundle->content))
                                <div class="prose prose-sm max-w-none prose-headings:text-ink prose-p:text-ink-2 prose-a:text-ink prose-strong:text-ink">
                                    {!! $bundle->content !!}
                                </div>
                            @elseif ($bundle->description)
                                <p class="text-sm leading-relaxed text-ink-2">{{ $bundle->description }}</p>
                            @else
                                <p class="py-6 text-center text-sm text-ink-3">Bu paket için henüz açıklama eklenmedi.</p>
                            @endif
                        </div>

                        @if ($deliveryDetails !== null)
                            <div x-show="tab === 'teslimat'" x-cloak>
                                <div class="prose prose-sm max-w-none prose-headings:text-ink prose-p:text-ink-2 prose-a:text-ink prose-strong:text-ink">
                                    {!! $deliveryDetails !!}
                                </div>
                            </div>
                        @endif

                        <div x-show="tab === 'yorumlar'" x-cloak id="yorumlar">
                            <div class="grid gap-5 lg:grid-cols-2 lg:gap-6">
                                <div class="overflow-hidden rounded-[20px] border border-ink/10">
                                    <div class="border-b border-ink/10 px-5 py-4">
                                        <h3 class="font-display text-base font-semibold text-ink">Kullanıcı yorumları</h3>
                                    </div>
                                    <div class="max-h-[420px] divide-y divide-ink/10 overflow-y-auto">
                                        @forelse ($reviews as $review)
                                            <div class="px-5 py-4">
                                                <p class="text-sm font-semibold text-ink">{{ $review->name }}</p>
                                                <p class="mt-1.5 text-sm leading-relaxed text-ink-2">{{ $review->message }}</p>
                                                @if ($review->approved_at)
                                                    <p class="mt-2 text-[11px] font-medium text-ink-3">{{ $review->approved_at->format('d.m.Y') }}</p>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="px-5 py-10 text-center text-sm text-ink-3">Henüz onaylanmış yorum yok.</div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="overflow-hidden rounded-[20px] border border-ink/10">
                                    <div class="border-b border-ink/10 px-5 py-4">
                                        <h3 class="font-display text-base font-semibold text-ink">Yorum yaz</h3>
                                        <p class="mt-1 text-sm text-ink-2">Üye olmadan gönderebilirsiniz. Onaylandıktan sonra yayınlanır.</p>
                                    </div>

                                    <form method="post" action="{{ route('bundles.review', $bundle) }}" class="space-y-4 p-5">
                                        @csrf
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div class="sm:col-span-2">
                                                <label for="review_name" class="{{ $labelClass }}">Ad soyad</label>
                                                <input
                                                    id="review_name"
                                                    type="text"
                                                    name="name"
                                                    value="{{ old('name', auth()->user()?->name) }}"
                                                    required
                                                    maxlength="120"
                                                    class="{{ $inputClass }}"
                                                >
                                                @error('name')
                                                    <p class="mt-1.5 text-xs text-brand-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div>
                                                <label for="review_email" class="{{ $labelClass }}">E-posta</label>
                                                <input
                                                    id="review_email"
                                                    type="email"
                                                    name="email"
                                                    value="{{ old('email', auth()->user()?->email) }}"
                                                    required
                                                    class="{{ $inputClass }}"
                                                >
                                                @error('email')
                                                    <p class="mt-1.5 text-xs text-brand-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div>
                                                <label for="review_phone" class="{{ $labelClass }}">Telefon</label>
                                                <input
                                                    id="review_phone"
                                                    type="tel"
                                                    name="phone"
                                                    value="{{ old('phone') }}"
                                                    required
                                                    maxlength="40"
                                                    class="{{ $inputClass }}"
                                                >
                                                @error('phone')
                                                    <p class="mt-1.5 text-xs text-brand-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                        <div>
                                            <label for="review_message" class="{{ $labelClass }}">Mesaj</label>
                                            <textarea
                                                id="review_message"
                                                name="message"
                                                rows="4"
                                                required
                                                minlength="10"
                                                class="{{ $inputClass }}"
                                            >{{ old('message') }}</textarea>
                                            @error('message')
                                                <p class="mt-1.5 text-xs text-brand-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <button type="submit" class="{{ $outlineBtn }} sm:w-auto sm:px-6">
                                            Gönder
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div x-show="tab === 'soru-cevap'" x-cloak>
                            <div class="grid gap-5 lg:grid-cols-2 lg:gap-6">
                                <div class="overflow-hidden rounded-[20px] border border-ink/10">
                                    <div class="border-b border-ink/10 px-5 py-4">
                                        <h3 class="font-display text-base font-semibold text-ink">Sorular &amp; yanıtlar</h3>
                                    </div>
                                    <div class="max-h-[420px] divide-y divide-ink/10 overflow-y-auto">
                                        @forelse ($questions as $item)
                                            <div class="px-5 py-4">
                                                <p class="text-sm font-semibold text-ink">{{ $item->question }}</p>
                                                <p class="mt-1.5 text-sm leading-relaxed text-ink-2">{{ $item->answer }}</p>
                                                @if ($item->answered_at)
                                                    <p class="mt-2 text-[11px] font-medium text-ink-3">{{ $item->answered_at->format('d.m.Y') }}</p>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="px-5 py-10 text-center text-sm text-ink-3">Henüz yanıtlanmış soru yok.</div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="overflow-hidden rounded-[20px] border border-ink/10">
                                    <div class="border-b border-ink/10 px-5 py-4">
                                        <h3 class="font-display text-base font-semibold text-ink">Soru sor</h3>
                                        <p class="mt-1 text-sm text-ink-2">Yanıtlandıktan sonra herkese açık olarak yayınlanır.</p>
                                    </div>

                                    <form method="post" action="{{ route('bundles.question', $bundle) }}" class="space-y-4 p-5">
                                        @csrf
                                        @guest
                                            <div>
                                                <label for="guest_email" class="{{ $labelClass }}">E-posta</label>
                                                <input
                                                    id="guest_email"
                                                    type="email"
                                                    name="guest_email"
                                                    value="{{ old('guest_email') }}"
                                                    required
                                                    class="{{ $inputClass }}"
                                                >
                                                @error('guest_email')
                                                    <p class="mt-1.5 text-xs text-brand-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        @endguest
                                        <div>
                                            <label for="question" class="{{ $labelClass }}">Sorunuz</label>
                                            <textarea
                                                id="question"
                                                name="question"
                                                rows="4"
                                                required
                                                minlength="10"
                                                class="{{ $inputClass }}"
                                            >{{ old('question') }}</textarea>
                                            @error('question')
                                                <p class="mt-1.5 text-xs text-brand-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <button type="submit" class="{{ $outlineBtn }} sm:w-auto sm:px-6">
                                            Gönder
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        @if ($faqs->isNotEmpty())
                            <div x-show="tab === 'sss'" x-cloak class="space-y-3">
                                @foreach ($faqs as $index => $faq)
                                    <div x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }" class="rounded-2xl bg-paper">
                                        <button
                                            type="button"
                                            class="flex w-full items-center justify-between gap-4 px-5 py-4 text-start focus:outline-hidden"
                                            @click="open = !open"
                                            :aria-expanded="open.toString()"
                                        >
                                            <span class="text-sm font-medium text-ink">{{ $faq->question_topic }}</span>
                                            <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full border border-ink/10 bg-white text-ink transition-transform duration-300" :class="open ? 'rotate-45' : ''">
                                                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                            </span>
                                        </button>
                                        <div x-show="open" x-cloak class="px-5 pb-4 text-[13px] font-medium leading-relaxed text-ink-2">
                                            {{ $faq->answer }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sağ kolon --}}
            <div class="contents lg:sticky lg:top-28 lg:flex lg:min-w-0 lg:flex-col lg:gap-5">
                {{-- 2. Satın alma --}}
                <div
                    class="{{ $card }} p-5 sm:p-6"
                    style="order: 2; background: linear-gradient(160deg, #fff8f3 0%, #ffffff 55%, #f0f4fc 100%);"
                    data-reveal
                >
                    <p class="font-display text-2xl font-semibold text-ink">{{ $money((float) $bundle->price, $currency) }}</p>

                    <div class="mt-5 space-y-2.5">
                        <form method="post" action="{{ route('cart.add') }}">
                            @csrf
                            <input type="hidden" name="product_type" value="bundle">
                            <input type="hidden" name="site_bundle_id" value="{{ $bundle->id }}">
                            <input type="hidden" name="redirect" value="cart">
                            @guest
                                <button type="button" class="{{ $ctaCart }}" onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))">
                                    <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                    Sepete Ekle
                                </button>
                            @else
                                <button type="submit" class="{{ $ctaCart }}">
                                    <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                    Sepete Ekle
                                </button>
                            @endguest
                        </form>

                        <form method="post" action="{{ route('cart.add') }}">
                            @csrf
                            <input type="hidden" name="product_type" value="bundle">
                            <input type="hidden" name="site_bundle_id" value="{{ $bundle->id }}">
                            <input type="hidden" name="redirect" value="checkout">
                            @guest
                                <button type="button" class="{{ $ctaBuy }}" onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))">
                                    <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                    Hemen Satın Al
                                </button>
                            @else
                                <button type="submit" class="{{ $ctaBuy }}">
                                    <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                    Hemen Satın Al
                                </button>
                            @endguest
                        </form>

                        @if (filled($whatsappUrl))
                            <a
                                href="{{ $whatsappUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="{{ $ctaWhatsapp }}"
                            >
                                <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                                WhatsApp Sipariş Hattı
                            </a>
                        @endif
                    </div>

                    <div class="mt-3">
                        <button
                            type="button"
                            class="{{ $outlineBtn }}"
                            data-share-url="{{ $bundle->canonicalUrl() }}"
                            onclick="const btn=this; const url=btn.dataset.shareUrl; if (navigator.share) { navigator.share({title: document.title, url}); } else { navigator.clipboard.writeText(url); const label = btn.querySelector('[data-share-label]'); const original = label.textContent; label.textContent = 'Kopyalandı'; setTimeout(() => { label.textContent = original; }, 1500); }"
                        >
                            <svg class="size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z"/></svg>
                            <span data-share-label>Paylaş</span>
                        </button>
                    </div>

                    <div class="mt-5 space-y-2 border-t border-ink/10 pt-5 text-sm text-ink-2">
                        <div class="flex items-center justify-between gap-3">
                            <span>6 ay link garantisi</span>
                            <svg class="size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Pakete dahil site</span>
                            <span class="shrink-0 text-ink-3">{{ $bundle->sites_count }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Tek işlemle yayın</span>
                            <svg class="size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </div>
                    </div>
                </div>

                {{-- 7. Son blog yazıları --}}
                @if ($latestBlogPosts->isNotEmpty())
                    <div class="{{ $card }} p-5 sm:p-6" style="order: 7" data-reveal>
                        <div class="flex items-center justify-between gap-3">
                            <x-section-heading
                                size="sm"
                                gradient="from-[#674cd0] to-[#a8a8ff]"
                                icon="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5"
                            >Son Blog Yazıları</x-section-heading>
                            <a href="{{ route('blog.index') }}" class="{{ $linkAccent }} shrink-0 text-xs font-semibold">Tümü</a>
                        </div>
                        <ul class="mt-3 space-y-1">
                            @foreach ($latestBlogPosts as $post)
                                <li>
                                    <a href="{{ $post->url() }}" class="flex min-w-0 items-center gap-x-2.5 rounded-xl px-1.5 py-2 ps-0 transition hover:bg-paper">
                                        @if ($post->featuredImageUrl())
                                            <img
                                                src="{{ $post->featuredImageUrl() }}"
                                                alt=""
                                                class="size-10 shrink-0 rounded-lg object-cover"
                                                loading="lazy"
                                            >
                                        @else
                                            <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-lg bg-paper text-[10px] font-semibold uppercase tracking-wide text-ink-3">
                                                Blog
                                            </span>
                                        @endif
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-sm font-medium text-ink">{{ $post->title }}</span>
                                            <span class="mt-0.5 block truncate text-[11px] text-ink-3">
                                                @if ($post->category)
                                                    {{ $post->category->name }} ·
                                                @endif
                                                {{ $post->published_at?->format('d.m.Y') }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8">
        @if ($relatedBundles->isNotEmpty())
            <section data-reveal-group>
                <x-section-heading
                    size="sm"
                    gradient="from-[#fa8837] to-[#faac75]"
                    icon="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"
                    data-reveal
                >İlgili Ürünler</x-section-heading>

                <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relatedBundles as $related)
                        <div data-reveal>
                            <x-bundle-card :bundle="$related" />
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
