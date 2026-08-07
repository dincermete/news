@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
    @foreach ($jsonLd as $schema)
        <script type="application/ld+json">
            {!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}
        </script>
    @endforeach
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /** @var \App\Models\Province $province */
    /** @var \Illuminate\Support\Collection<int, \App\Models\Site> $sites */
    /** @var array<string, mixed> $stats */
    $sitesCount = (int) ($stats['sites_count'] ?? $sites->count());
    $topCategories = $stats['top_categories'] ?? [];
    $fmtInt = fn (int $n): string => number_format($n, 0, ',', '.');
    $fmt = fn (?float $n): string => $n !== null ? number_format($n, 0, ',', '.') : '—';
@endphp

@section('content')
    {{-- ================= HERO (siteler katalogu ile aynı dil) ================= --}}
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-light relative overflow-hidden rounded-3xl text-ink">
            <div class="relative mx-auto flex max-w-7xl flex-col items-center px-5 pb-10 pt-14 text-center sm:px-8 sm:pb-12 sm:pt-16" data-reveal-group>
                <nav aria-label="Konum" class="mb-4 text-xs text-ink-3" data-reveal>
                    <ol class="flex flex-wrap items-center justify-center gap-1.5">
                        <li><a href="{{ route('home') }}" class="hover:text-ink">Ana Sayfa</a></li>
                        <li aria-hidden="true">/</li>
                        <li><a href="{{ route('sites.index') }}" class="hover:text-ink">Tüm Siteler</a></li>
                        <li aria-hidden="true">/</li>
                        <li class="font-medium text-ink">{{ $province->name }}</li>
                    </ol>
                </nav>

                <p class="inline-flex items-center gap-x-2 rounded-full border border-ink/10 bg-white py-1 pe-3.5 ps-1 text-xs text-ink-2 shadow-soft" data-reveal>
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">{{ $province->plate_code }}</span>
                    {{ $province->name }} · {{ $fmtInt($sitesCount) }} site
                </p>

                <h1 class="mt-5 max-w-2xl font-display text-4xl font-medium leading-[1.12] sm:text-5xl" data-reveal>
                    {{ $province->name }} Tanıtım Yazısı Siteleri
                </h1>

                <p class="mt-4 max-w-xl text-lg font-medium leading-relaxed text-ink-2" data-reveal>
                    {{ $stats['summary'] }}
                </p>

                <div class="mt-7 flex flex-wrap items-center justify-center gap-2" data-reveal>
                    <a
                        href="{{ route('sites.index') }}"
                        class="inline-flex items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.03] active:scale-[0.98]"
                    >
                        Tüm siteleri gör
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>

                @if ($topCategories !== [])
                    <div class="mt-6 flex max-w-2xl flex-wrap items-center justify-center gap-2" data-reveal>
                        @foreach ($topCategories as $category)
                            <span class="rounded-full border border-ink/10 bg-white px-3.5 py-1.5 text-xs font-medium text-ink-2">
                                {{ $category['name'] }}
                                <span class="ms-1 font-semibold text-ink">{{ $category['count'] }}</span>
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        {{-- ================= ÖZET BAR (filtre barı yerine il metrikleri) ================= --}}
        <div class="rounded-[20px] border border-ink/10 bg-paper p-4 sm:p-5" data-reveal>
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[110px] flex-1">
                    <p class="mb-1.5 text-[11px] font-semibold uppercase tracking-wide text-ink-3">Site sayısı</p>
                    <p class="rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm font-semibold text-ink">{{ $fmtInt($sitesCount) }}</p>
                </div>
                <div class="min-w-[110px] flex-1">
                    <p class="mb-1.5 text-[11px] font-semibold uppercase tracking-wide text-ink-3">DA aralığı</p>
                    <p class="rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm font-semibold text-ink">
                        @if ($stats['da_min'] !== null && $stats['da_max'] !== null)
                            {{ $fmt($stats['da_min']) }}–{{ $fmt($stats['da_max']) }}
                        @else
                            —
                        @endif
                    </p>
                </div>
                <div class="min-w-[110px] flex-1">
                    <p class="mb-1.5 text-[11px] font-semibold uppercase tracking-wide text-ink-3">Trafik aralığı</p>
                    <p class="rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm font-semibold text-ink">
                        @if ($stats['traffic_min'] !== null && $stats['traffic_max'] !== null)
                            {{ $fmt($stats['traffic_min']) }}–{{ $fmt($stats['traffic_max']) }}
                        @else
                            —
                        @endif
                    </p>
                </div>
                <div class="min-w-[150px] flex-1">
                    <p class="mb-1.5 text-[11px] font-semibold uppercase tracking-wide text-ink-3">Plaka</p>
                    <p class="rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm font-semibold text-ink">{{ $province->plate_code }} · {{ $province->name }}</p>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-ink/10 pt-4">
                <span class="inline-flex items-center gap-x-1.5 rounded-full border border-ink/10 bg-white px-3.5 py-1.5 text-xs font-medium text-ink-2">
                    {{ $province->name_locative }} yayın siteleri
                </span>
                <span class="ms-auto flex items-center gap-3 text-sm font-medium text-ink-2">
                    <strong class="font-display text-ink">{{ $fmtInt($sites->count()) }}</strong> site listeleniyor
                    <a
                        href="{{ route('sites.index') }}"
                        class="inline-flex items-center gap-x-1.5 rounded-xl border border-ink/10 bg-white px-3.5 py-2 text-xs font-semibold text-ink-2 transition hover:border-ink/25 hover:text-ink"
                    >
                        Tüm katalog
                    </a>
                </span>
            </div>
        </div>

        {{-- ================= TABLO ================= --}}
        <div class="mt-6" data-reveal>
            @if ($sites->isEmpty())
                <div class="rounded-[20px] border border-ink/10 bg-paper px-6 py-16 text-center">
                    <p class="font-display text-lg font-semibold text-ink">{{ $province->name }} için yayın siteleri çok yakında</p>
                    <p class="mt-1.5 text-sm text-ink-2">Bu arada benzer illerdeki siteleri aşağıda inceleyebilir veya tüm katalogu görüntüleyebilirsiniz.</p>
                    <a href="{{ route('sites.index') }}" class="mt-5 inline-flex items-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white">
                        Tüm Siteleri Gör
                    </a>
                </div>
            @else
                <x-site-table :sites="$sites" :favoritedSiteIds="$favoritedSiteIds" />
            @endif
        </div>

        {{-- ================= İNCE İÇERİK (SEO) ================= --}}
        @if ($sitesCount < 3 && ! empty($stats['related_sites']))
            <div class="mt-12" data-reveal>
                <h2 class="font-display text-xl font-semibold text-ink">Benzer illerden yayın siteleri</h2>
                <p class="mt-1 text-sm text-ink-2">{{ $province->name }} ile benzer kategori yoğunluğuna sahip illerden seçilmiş siteler.</p>
                <div class="mt-5">
                    <x-site-table :sites="collect($stats['related_sites'])" :favoritedSiteIds="$favoritedSiteIds" />
                </div>
            </div>
        @endif

        @if ($sitesCount < 3)
            <div class="mt-8 rounded-[20px] border border-ink/10 bg-paper px-6 py-8" data-reveal>
                <h2 class="font-display text-lg font-semibold text-ink">Bu ilde sitenizi eklemek ister misiniz?</h2>
                <p class="mt-2 text-sm text-ink-2">
                    {{ $province->name_locative }} tanıtım yazısı veya backlink veren bir yayın sitesi işletiyorsanız kataloğumuza başvurabilirsiniz.
                </p>
                <a
                    href="{{ auth()->check() ? route('account.site-submissions') : route('login') }}"
                    class="mt-5 inline-flex items-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white"
                >
                    Sitenizi ekleyin
                </a>
            </div>
        @endif

        @if (! empty($stats['similar_provinces']))
            <div class="mt-12" data-reveal>
                <h2 class="font-display text-xl font-semibold text-ink">Diğer illerdeki tanıtım yazısı siteleri</h2>
                <div class="mt-5 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($stats['similar_provinces'] as $related)
                        <a
                            href="{{ route('provinces.sites', $related['slug']) }}"
                            class="flex items-center justify-between gap-3 rounded-[20px] border border-ink/10 bg-white px-4 py-3 text-sm transition hover:shadow-soft"
                        >
                            <span class="flex min-w-0 items-center gap-3">
                                <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-xl bg-paper font-display text-xs font-bold text-ink">
                                    {{ $related['plate_code'] }}
                                </span>
                                <span class="truncate font-semibold text-ink">{{ $related['name'] }}</span>
                            </span>
                            @if (($related['sites_count'] ?? 0) > 0)
                                <span class="shrink-0 rounded-full bg-paper px-2.5 py-0.5 text-[11px] font-semibold text-ink">
                                    {{ $related['sites_count'] }} site
                                </span>
                            @else
                                <span class="shrink-0 rounded-full bg-paper-2 px-2.5 py-0.5 text-[11px] font-semibold text-ink-3">yakında</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if (! empty($stats['faqs']))
            <div class="mt-12" data-reveal>
                <h2 class="font-display text-xl font-semibold text-ink">Sık sorulan sorular</h2>
                <dl class="mt-5 space-y-3">
                    @foreach ($stats['faqs'] as $faq)
                        <div class="rounded-[20px] border border-ink/10 bg-white px-5 py-4">
                            <dt class="font-semibold text-ink">{{ $faq['question'] }}</dt>
                            <dd class="mt-2 text-sm leading-relaxed text-ink-2">{{ $faq['answer'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
    </div>
@endsection
