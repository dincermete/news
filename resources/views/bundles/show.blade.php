@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /** @var \App\Models\SiteBundle $bundle */
    /** @var \Illuminate\Support\Collection<int, \App\Models\FaqEntry> $faqs */
    /** @var \Illuminate\Support\Collection<int, \App\Models\SiteBundle> $relatedBundles */

    $currency = $bundle->currency?->value ?? (string) $bundle->currency;
    $money = function (float $amount, string $curr): string {
        $symbol = $curr === 'TRY' ? '₺' : '$';
        $formatted = fmod($amount, 1.0) > 0.009
            ? number_format($amount, 2, ',', '.')
            : number_format($amount, 0, ',', '.');

        return $formatted.$symbol;
    };

    $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
    $btnDark = 'group inline-flex items-center justify-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 text-sm font-medium text-white transition hover:scale-[1.02] active:scale-[0.98]';
    $btnChip = 'inline-flex size-9 items-center justify-center rounded-xl';

    $num = fn (?float $value): string => $value !== null ? number_format($value, 0, ',', '.') : '—';

    $daValues = $bundle->sites->pluck('da_value')->filter(fn ($v) => $v !== null)->map(fn ($v) => (float) $v);
    $avgDa = $daValues->isNotEmpty() ? $daValues->avg() : null;
    $dofollowCount = $bundle->sites->where('is_dofollow', true)->count();
    $newsCount = $bundle->sites->where('is_news_approved', true)->count();

    $dataCells = [
        ['label' => 'Site Sayısı', 'value' => (string) $bundle->sites_count, 'type' => 'text'],
        ['label' => 'Ortalama DA', 'value' => $num($avgDa), 'type' => $avgDa !== null ? 'score' : 'muted', 'icon' => 'moz'],
        ['label' => 'Dofollow Site', 'value' => (string) $dofollowCount, 'type' => $dofollowCount > 0 ? 'score' : 'muted'],
        ['label' => 'News Onaylı', 'value' => (string) $newsCount, 'type' => $newsCount > 0 ? 'score' : 'muted', 'icon' => 'google'],
        ['label' => 'Para Birimi', 'value' => $currency, 'type' => 'text'],
        ['label' => 'Link Garantisi', 'value' => '6 ay', 'type' => 'score'],
    ];
    $dataCellRows = collect($dataCells)->chunk(3);

    $shortDescription = $bundle->description
        ? \Illuminate\Support\Str::limit($bundle->description, 140)
        : null;

    $tabs = collect([
        ['key' => 'aciklama', 'label' => 'Açıklamalar'],
        ['key' => 'siteler', 'label' => 'Pakete Dahil Siteler'],
        filled($faqs) && $faqs->isNotEmpty() ? ['key' => 'sss', 'label' => 'Sık Sorulan Sorular'] : null,
    ])->filter()->values();
@endphp

@section('content')
    {{-- ================= BAŞLIK: sadece breadcrumb + ürün adı ================= --}}
    <section class="mx-auto max-w-6xl px-4 pt-8 sm:px-6 lg:px-8" data-reveal-group>
        <nav class="flex items-center gap-x-1.5 text-xs text-ink-3" aria-label="Konum" data-reveal>
            <a href="{{ route('home') }}" class="transition hover:text-ink">Anasayfa</a>
            <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            <a href="{{ route('bundles.index') }}" class="transition hover:text-ink">Tanıtım Paketleri</a>
            <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            <span class="truncate text-ink-2">{{ $bundle->name }}</span>
        </nav>
    </section>

    {{-- ================= İÇERİK: kimlik/veriler/detaylar solda, satın alma sağda ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr] lg:items-start" data-reveal-group>
            <div class="space-y-6">
                {{-- Paket kimliği: ikon, ad, rozetler --}}
                <div class="rounded-[20px] border border-ink/10 bg-white p-5" data-reveal>
                    <div class="flex items-center gap-x-4">
                        <span class="inline-flex size-[52px] shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 text-white">
                            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h1 class="truncate font-display text-3xl font-medium leading-tight text-ink sm:text-4xl">{{ $bundle->name }}</h1>
                            <p class="mt-1 text-sm text-ink-2">Tanıtım Paketi</p>
                        </div>
                    </div>

                    @if ($shortDescription)
                        <p class="mt-4 max-w-2xl text-sm leading-relaxed text-ink-2">{{ $shortDescription }}</p>
                    @endif

                    <div class="mt-5 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-x-1.5 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                            <span class="size-1.5 rounded-full bg-emerald-500"></span> {{ $bundle->sites_count }} Site
                        </span>
                        @if ($dofollowCount > 0)
                            <span class="inline-flex items-center gap-x-1.5 rounded-full bg-accent-100 px-3 py-1.5 text-xs font-semibold text-accent-700">
                                <span class="size-1.5 rounded-full bg-accent-500"></span> {{ $dofollowCount }} Dofollow
                            </span>
                        @endif
                        <span class="inline-flex items-center rounded-full bg-ink/5 px-3 py-1.5 text-xs font-semibold text-ink-2">Tek işlemde toplu yayın</span>
                    </div>
                </div>

                {{-- Paket Verileri --}}
                <div class="rounded-[20px] border border-ink/10 bg-white" data-reveal>
                    <div class="px-5 py-4">
                        <p class="font-display text-base font-semibold text-ink">Paket Verileri</p>
                    </div>

                    <div class="divide-y divide-ink/10 border-t border-ink/10">
                        @foreach ($dataCellRows as $row)
                            @php $rowCols = min(3, $row->count()); @endphp
                            <div @class([
                                'grid divide-x divide-ink/10',
                                'grid-cols-1' => $rowCols === 1,
                                'grid-cols-2' => $rowCols === 2,
                                'grid-cols-2 sm:grid-cols-3' => $rowCols === 3,
                            ])>
                                @foreach ($row as $cell)
                                    <div class="px-5 py-4">
                                        <p class="flex items-center gap-1 text-[11px] font-semibold uppercase tracking-wide text-ink-3">
                                            @if (! empty($cell['icon']))
                                                <x-metric-icon :source="$cell['icon']" />
                                            @endif
                                            {{ $cell['label'] }}
                                        </p>
                                        <p @class([
                                            'mt-1 truncate text-base font-bold',
                                            'text-emerald-600' => $cell['type'] === 'score',
                                            'text-ink' => $cell['type'] === 'text',
                                            'text-ink-3' => $cell['type'] === 'muted',
                                        ])>{{ $cell['value'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Detay sekmeleri --}}
                <div class="rounded-[20px] border border-ink/10 bg-white" x-data="{ tab: '{{ $tabs->first()['key'] }}' }" data-reveal>
                    <div class="no-scrollbar flex gap-1 overflow-x-auto border-b border-ink/10 px-2 pt-2">
                        @foreach ($tabs as $t)
                            <button
                                type="button"
                                @click="tab = '{{ $t['key'] }}'"
                                :class="tab === '{{ $t['key'] }}' ? 'border-ink text-ink' : 'border-transparent text-ink-3 hover:text-ink-2'"
                                class="shrink-0 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition"
                            >{{ $t['label'] }}</button>
                        @endforeach
                    </div>

                    <div class="p-6">
                        {{-- Açıklamalar --}}
                        <div x-show="tab === 'aciklama'">
                            @if (filled($bundle->content))
                                <article class="prose prose-zinc max-w-none prose-sm">
                                    {!! $bundle->content !!}
                                </article>
                            @elseif ($bundle->description)
                                <p class="text-[15px] leading-relaxed text-ink-2">{{ $bundle->description }}</p>
                            @else
                                <p class="py-6 text-center text-sm text-ink-2">Bu paket için henüz açıklama eklenmedi.</p>
                            @endif
                        </div>

                        {{-- Pakete Dahil Siteler --}}
                        <div x-show="tab === 'siteler'" x-cloak>
                            @if ($bundle->sites->isNotEmpty())
                                <ul class="divide-y divide-ink/5 overflow-hidden rounded-2xl border border-ink/10">
                                    @foreach ($bundle->sites as $site)
                                        <li>
                                            <a href="{{ route('sites.show', $site->domain) }}" class="flex items-center gap-4 px-4 py-3.5 transition hover:bg-paper">
                                                <x-site-logo :site="$site" :height="36" class="shrink-0 rounded-lg" />
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-sm font-semibold text-ink">{{ $site->domain }}</p>
                                                    <p class="mt-0.5 truncate text-xs text-ink-3">{{ $site->category?->name ?? 'Kategorisiz' }}</p>
                                                </div>
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
                                <p class="py-6 text-center text-sm text-ink-2">Bu pakete henüz site eklenmedi.</p>
                            @endif
                        </div>

                        {{-- Sık Sorulan Sorular --}}
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

            <div class="space-y-6 lg:sticky lg:top-28">
                {{-- Satın alma kartı --}}
                <div class="rounded-2xl border border-ink/10 bg-white p-6" data-reveal>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-3">Fiyat</p>
                    <p class="mt-1.5 font-display text-3xl font-semibold text-ink">{{ $money((float) $bundle->price, $currency) }}</p>

                    <form method="post" action="{{ route('cart.add') }}" class="mt-5">
                        @csrf
                        <input type="hidden" name="product_type" value="bundle">
                        <input type="hidden" name="site_bundle_id" value="{{ $bundle->id }}">
                        @guest
                            <button type="button" class="{{ $btnDark }} w-full" onclick="window.dispatchEvent(new CustomEvent('open-login-modal'))">
                                <span class="{{ $btnChip }} bg-white/15 text-white">
                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                </span>
                                Sepete Ekle
                            </button>
                        @else
                            <button type="submit" class="{{ $btnDark }} w-full">
                                <span class="{{ $btnChip }} bg-white/15 text-white">
                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                </span>
                                Sepete Ekle
                            </button>
                        @endguest
                    </form>

                    <div class="mt-2.5">
                        <button
                            type="button"
                            class="flex w-full items-center justify-center gap-x-1.5 rounded-2xl border border-ink/10 bg-white px-3 py-3 text-sm font-medium text-ink transition hover:border-ink/25"
                            data-share-url="{{ route('bundles.show', $bundle->slug) }}"
                            onclick="const btn=this; const url=btn.dataset.shareUrl; if (navigator.share) { navigator.share({title: document.title, url}); } else { navigator.clipboard.writeText(url); const label = btn.querySelector('[data-share-label]'); const original = label.textContent; label.textContent = 'Kopyalandı'; setTimeout(() => { label.textContent = original; }, 1500); }"
                        >
                            <svg class="size-4 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z"/></svg>
                            <span data-share-label>Paylaş</span>
                        </button>
                    </div>

                    <div class="mt-5 space-y-2.5 border-t border-ink/10 pt-5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-ink-2">6 ay link garantisi</span>
                            <svg class="size-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-ink-2">Pakete dahil site</span>
                            <span class="font-semibold text-ink">{{ $bundle->sites_count }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-ink-2">Tek işlemle yayın</span>
                            <svg class="size-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Diğer Paketler --}}
                @if ($relatedBundles->isNotEmpty())
                    <div class="rounded-2xl border border-ink/10 bg-white p-6" data-reveal>
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-3">Diğer Paketler</p>
                            <a href="{{ route('bundles.index') }}" class="text-xs font-semibold text-ink-2 transition hover:text-ink">Tümü</a>
                        </div>
                        <ul class="mt-3 space-y-1">
                            @foreach ($relatedBundles as $related)
                                <li>
                                    <a href="{{ route('bundles.show', $related->slug) }}" class="flex items-center gap-x-2.5 rounded-xl px-1.5 py-2 transition hover:bg-paper">
                                        <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-brand-500 to-brand-600 text-white">
                                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                                        </span>
                                        <span class="min-w-0 flex-1 truncate text-sm font-medium text-ink">{{ $related->name }}</span>
                                        <span class="shrink-0 text-sm font-bold text-ink">{{ $money((float) $related->price, $related->currency?->value ?? 'TRY') }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 pb-14 sm:px-6 lg:px-8">
        {{-- ================= BENZER PAKETLER ================= --}}
        @if ($relatedBundles->isNotEmpty())
            <section data-reveal-group>
                <p><span class="{{ $chip }}">Benzer paketler</span></p>
                <h2 class="mt-4 font-display text-2xl font-medium text-ink sm:text-[28px]" data-reveal>İncelemek isteyebileceğiniz diğer paketler</h2>

                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
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
