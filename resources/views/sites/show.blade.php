@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    /** @var \App\Models\Site $site */
    $currency = $site->currency?->value ?? (string) $site->currency;
    $hasDiscount = $site->discount_price !== null
        && (float) $site->discount_price < (float) $site->price;

    $fmt = fn (int $n): string => number_format($n, 0, ',', '.');
    $money = function (float $amount, string $curr): string {
        $symbol = $curr === 'TRY' ? '₺' : '$';
        $formatted = fmod($amount, 1.0) > 0.009
            ? number_format($amount, 2, ',', '.')
            : number_format($amount, 0, ',', '.');

        return $formatted.$symbol;
    };

    $discountPercent = $hasDiscount
        ? (int) round((1 - ((float) $site->discount_price / (float) $site->price)) * 100)
        : null;

    $chip ='inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
    $btnDark = 'group inline-flex items-center justify-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 text-sm font-medium text-white transition hover:scale-[1.02] active:scale-[0.98]';
    $btnChip = 'inline-flex size-9 items-center justify-center rounded-xl';

    // "Site Verileri" kartı: kısa özet + otorite verileri tek gridde birleşti.
    // Yalnızca gerçekten "otorite skoru" olan DA/PA/News yeşil vurgulanır;
    // diğerleri (Moz Rank, Majestic, trafik) normal ink tonunda kalır.
    $num = fn (?float $value): string => $value !== null ? number_format($value, 0, ',', '.') : '—';

    $dataCells = [
        ['label' => 'Kategori', 'value' => $site->category?->name ?? 'Kategorisiz', 'type' => 'text'],
        ['label' => 'Site Yaşı', 'value' => $site->age !== null ? $site->age.' yıl' : '—', 'type' => 'text'],
        ['label' => 'DA', 'value' => $num($site->da_value !== null ? (float) $site->da_value : null), 'type' => 'score', 'icon' => 'moz'],
        ['label' => 'PA', 'value' => $num($site->pa_value !== null ? (float) $site->pa_value : null), 'type' => 'score', 'icon' => 'moz'],
        ['label' => 'News Kaydı', 'value' => $site->is_news_approved ? 'Var' : 'Yok', 'type' => $site->is_news_approved ? 'score' : 'muted', 'icon' => 'google'],
        ['label' => 'Link Tipi', 'value' => null, 'type' => 'linktype'],
        ['label' => 'Moz Rank', 'value' => $num($site->moz_rank_value !== null ? (float) $site->moz_rank_value : null), 'type' => 'text', 'icon' => 'moz'],
        ['label' => 'Majestic CF', 'value' => $num($site->majestic_cf_value !== null ? (float) $site->majestic_cf_value : null), 'type' => 'text', 'icon' => 'majestic'],
        ['label' => 'Majestic TF', 'value' => $num($site->majestic_tf_value !== null ? (float) $site->majestic_tf_value : null), 'type' => 'text', 'icon' => 'majestic'],
        ['label' => 'Aylık Trafik', 'value' => $num($site->monthly_traffic_value !== null ? (float) $site->monthly_traffic_value : null), 'type' => 'text', 'icon' => 'google'],
    ];
    $dataCellRows = collect($dataCells)->chunk(3);

    $shortDescription = $site->description
        ? \Illuminate\Support\Str::limit($site->description, 140)
        : null;

    $tabs = [
        ['key' => 'aciklama', 'label' => 'Açıklamalar'],
        ['key' => 'teslimat', 'label' => 'Teslimat Detayları'],
        ['key' => 'yorumlar', 'label' => 'Kullanıcı Yorumları'],
        ['key' => 'soru-cevap', 'label' => 'Kullanıcı Soruları & Yanıtları'],
    ];
@endphp

@section('content')
    {{-- ================= BAŞLIK: sadece breadcrumb + ürün adı ================= --}}
    <section class="mx-auto max-w-6xl px-4 pt-8 sm:px-6 lg:px-8" data-reveal-group>
        <nav class="flex items-center gap-x-1.5 text-xs text-ink-3" aria-label="Konum" data-reveal>
            <a href="{{ route('home') }}" class="transition hover:text-ink">Anasayfa</a>
            <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            <a href="{{ route('sites.index') }}" class="transition hover:text-ink">Siteler</a>
            <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            <span class="truncate text-ink-2">{{ $site->domain }}</span>
        </nav>

    </section>

    {{-- ================= İÇERİK: kimlik/veriler/detaylar solda, satın alma sağda ================= --}}
    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr] lg:items-start" data-reveal-group>
            <div class="space-y-6">
                {{-- Site kimliği: logo, ad, kategori, rozetler --}}
                <div class="rounded-[20px] border border-ink/10 bg-white p-5" data-reveal>
                    <div class="flex items-center gap-x-4">
                        <x-site-logo :site="$site" :height="52" class="shrink-0 rounded-2xl" />
                        <div class="min-w-0">
                            <h1 class="truncate font-display text-3xl font-medium leading-tight text-ink sm:text-4xl">{{ $site->domain }}</h1>
                            <p class="mt-1 text-sm text-ink-2">{{ $site->category?->name ?? 'Kategorisiz' }}</p>
                        </div>
                    </div>

                    @if ($shortDescription)
                        <p class="mt-4 max-w-2xl text-sm leading-relaxed text-ink-2">{{ $shortDescription }}</p>
                    @endif

                    <div class="mt-5 flex flex-wrap items-center gap-2">
                        @if ($site->is_dofollow)
                            <span class="inline-flex items-center gap-x-1.5 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                                <span class="size-1.5 rounded-full bg-emerald-500"></span> Dofollow
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-ink/5 px-3 py-1.5 text-xs font-semibold text-ink-3">Nofollow</span>
                        @endif
                        @if ($site->is_news_approved)
                            <span class="inline-flex items-center gap-x-1.5 rounded-full bg-accent-100 px-3 py-1.5 text-xs font-semibold text-accent-700">
                                <span class="size-1.5 rounded-full bg-accent-500"></span> News Onaylı
                            </span>
                        @endif
                        @if ($site->age !== null)
                            <span class="inline-flex items-center rounded-full bg-ink/5 px-3 py-1.5 text-xs font-semibold text-ink-2">{{ $site->age }} yıllık</span>
                        @endif
                        @foreach ($site->labels as $label)
                            <span class="inline-flex items-center rounded-full bg-ink/5 px-3 py-1.5 text-xs font-semibold text-ink-2">{{ $label->name }}</span>
                        @endforeach
                    </div>
                </div>

                {{-- Site Verileri + Otorite Verileri --}}
                <div class="rounded-[20px] border border-ink/10 bg-white" data-reveal>
                    <div class="px-5 py-4">
                        <p class="font-display text-base font-semibold text-ink">Site Verileri</p>
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
                                        @if ($cell['type'] === 'linktype')
                                            <p class="mt-1.5">
                                                @if ($site->is_dofollow)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                                        <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                                        Dofollow
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-ink/5 px-2.5 py-1 text-xs font-semibold text-ink-3">Nofollow</span>
                                                @endif
                                            </p>
                                        @else
                                            <p @class([
                                                'mt-1 truncate text-base font-bold',
                                                'text-emerald-600' => $cell['type'] === 'score',
                                                'text-ink' => $cell['type'] === 'text',
                                                'text-ink-3' => $cell['type'] === 'muted',
                                            ])>{{ $cell['value'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Detay sekmeleri --}}
                <div class="rounded-[20px] border border-ink/10 bg-white" x-data="{ tab: 'aciklama' }" data-reveal>
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
                            @if ($site->description)
                                <p class="text-[15px] leading-relaxed text-ink-2">{{ $site->description }}</p>
                            @else
                                <p class="py-6 text-center text-sm text-ink-2">Bu site için henüz açıklama eklenmedi.</p>
                            @endif
                        </div>

                        {{-- Teslimat Detayları --}}
                        <div x-show="tab === 'teslimat'" x-cloak>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-5 sm:grid-cols-3">
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Max Link Çıkışı</p>
                                    <p class="mt-1 text-base font-bold text-ink">{{ $site->max_link_count !== null ? $site->max_link_count : '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Tahmini Teslimat</p>
                                    <p class="mt-1 text-base font-bold text-ink">{{ $site->estimated_delivery ?: '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Günlük Kapasite</p>
                                    <p class="mt-1 text-base font-bold text-ink">{{ $site->daily_capacity !== null ? $site->daily_capacity : '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Haftalık Kapasite</p>
                                    <p class="mt-1 text-base font-bold text-ink">{{ $site->weekly_capacity !== null ? $site->weekly_capacity : '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Link Garantisi</p>
                                    <p class="mt-1 flex items-center gap-1.5 text-base font-bold text-ink">
                                        6 ay
                                        <svg class="size-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Kullanıcı Yorumları --}}
                        <div x-show="tab === 'yorumlar'" x-cloak>
                            <p class="py-6 text-center text-sm text-ink-2">Bu site için henüz kullanıcı yorumu bulunmuyor.</p>
                        </div>

                        {{-- Kullanıcı Soruları & Yanıtları --}}
                        <div x-show="tab === 'soru-cevap'" x-cloak>
                            <div class="grid gap-6 lg:grid-cols-2">
                                <div class="rounded-[20px] border border-ink/10">
                                    <div class="border-b border-ink/10 px-5 py-4">
                                        <h3 class="font-display text-base font-semibold text-ink">Sorular &amp; yanıtlar</h3>
                                    </div>
                                    <div class="max-h-[420px] divide-y divide-ink/5 overflow-y-auto">
                                        @forelse ($questions as $item)
                                            <div class="px-5 py-4">
                                                <p class="text-sm font-semibold text-ink">{{ $item->question }}</p>
                                                <p class="mt-1.5 text-sm leading-relaxed text-ink-2">{{ $item->answer }}</p>
                                                @if ($item->answered_at)
                                                    <p class="mt-2 text-[11px] font-medium text-ink-3">{{ $item->answered_at->format('d.m.Y') }}</p>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="px-5 py-10 text-center text-sm text-ink-2">Henüz yanıtlanmış soru yok.</div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="rounded-[20px] border border-ink/10">
                                    <div class="border-b border-ink/10 px-5 py-4">
                                        <h3 class="font-display text-base font-semibold text-ink">Soru sor</h3>
                                        <p class="mt-1 text-sm text-ink-2">Yanıtlandıktan sonra herkese açık olarak yayınlanır.</p>
                                    </div>

                                    <form method="post" action="{{ route('sites.question', $site) }}" class="space-y-4 p-5">
                                        @csrf
                                        @guest
                                            <div>
                                                <label for="guest_email" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">E-posta</label>
                                                <input
                                                    id="guest_email"
                                                    type="email"
                                                    name="guest_email"
                                                    value="{{ old('guest_email') }}"
                                                    required
                                                    class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0"
                                                >
                                                @error('guest_email')
                                                    <p class="mt-1.5 text-xs text-brand-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        @endguest
                                        <div>
                                            <label for="question" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-ink-3">Sorunuz</label>
                                            <textarea
                                                id="question"
                                                name="question"
                                                rows="4"
                                                required
                                                minlength="10"
                                                class="block w-full rounded-xl border border-ink/10 bg-white px-3 py-2.5 text-sm text-ink focus:border-ink/30 focus:ring-0"
                                            >{{ old('question') }}</textarea>
                                            @error('question')
                                                <p class="mt-1.5 text-xs text-brand-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <button type="submit" class="{{ $btnDark }}">
                                            <span class="{{ $btnChip }} bg-white/15 text-white">
                                                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.126A59.768 59.768 0 0 1 21.485 12 59.77 59.77 0 0 1 3.27 20.876L5.999 12Zm0 0h7.5"/></svg>
                                            </span>
                                            Gönder
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="space-y-6 lg:sticky lg:top-28">
                {{-- Satın alma kartı --}}
                <div class="rounded-2xl border border-ink/10 bg-white p-6" data-reveal>
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-3">Fiyat</p>
                        @if ($hasDiscount)
                            <span class="rounded-full bg-brand-100 px-2.5 py-1 text-[11px] font-bold text-brand-700">%{{ $discountPercent }} İNDİRİM</span>
                        @endif
                    </div>
                    @if ($hasDiscount)
                        <p class="mt-1.5 flex items-baseline gap-x-2">
                            <span class="font-display text-3xl font-semibold text-accent-600">{{ $money((float) $site->discount_price, $currency) }}</span>
                            <span class="text-sm text-ink-3 line-through">{{ $money((float) $site->price, $currency) }}</span>
                        </p>
                    @else
                        <p class="mt-1.5 font-display text-3xl font-semibold text-ink">{{ $money((float) $site->price, $currency) }}</p>
                    @endif

                    <form method="post" action="{{ route('cart.add') }}" class="mt-5">
                        @csrf
                        <input type="hidden" name="product_type" value="site_article">
                        <input type="hidden" name="site_id" value="{{ $site->id }}">
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

                    <div class="mt-2.5 grid grid-cols-2 gap-2">
                        <form method="post" action="{{ route('sites.favorite', $site) }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center justify-center gap-x-1.5 rounded-2xl border border-ink/10 bg-white px-3 py-3 text-sm font-medium text-ink transition hover:border-ink/25">
                                <svg class="size-4 {{ $isFavorited ? 'text-brand-500' : 'text-ink-3' }}" viewBox="0 0 24 24" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                                {{ $isFavorited ? 'Favoride' : 'Favorile' }}
                            </button>
                        </form>

                        <button
                            type="button"
                            class="flex w-full items-center justify-center gap-x-1.5 rounded-2xl border border-ink/10 bg-white px-3 py-3 text-sm font-medium text-ink transition hover:border-ink/25"
                            data-share-url="{{ route('sites.show', $site->domain) }}"
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
                            <span class="text-ink-2">Favoriye eklendi</span>
                            <span class="font-semibold text-ink">{{ $fmt($favoritesCount) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-ink-2">Bugün görüntülenme</span>
                            <span class="font-semibold text-ink">{{ $fmt($viewsToday) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-ink-2">Toplam görüntülenme</span>
                            <span class="font-semibold text-ink">{{ $fmt($viewsTotal) }}</span>
                        </div>
                    </div>
                </div>

                {{-- En Çok Satanlar --}}
                @if ($bestSellers->isNotEmpty())
                    <div class="rounded-2xl border border-ink/10 bg-white p-6" data-reveal>
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink-3">En Çok Satanlar</p>
                            <a href="{{ route('sites.index') }}" class="text-xs font-semibold text-ink-2 transition hover:text-ink">Tümü</a>
                        </div>
                        <ul class="mt-3 space-y-1">
                            @foreach ($bestSellers as $bestSeller)
                                @php
                                    $bsCurrency = $bestSeller->currency?->value ?? (string) $bestSeller->currency;
                                    $bsHasDiscount = $bestSeller->discount_price !== null && (float) $bestSeller->discount_price < (float) $bestSeller->price;
                                @endphp
                                <li>
                                    <a href="{{ route('sites.show', $bestSeller->domain) }}" class="flex items-center gap-x-2.5 rounded-xl px-1.5 py-2 transition hover:bg-paper">
                                        <x-site-logo :site="$bestSeller" :height="20" class="shrink-0 rounded" />
                                        <span class="min-w-0 flex-1 truncate text-sm font-medium text-ink">{{ $bestSeller->domain }}</span>
                                        <span class="shrink-0 text-sm font-bold text-ink">{{ $money((float) ($bsHasDiscount ? $bestSeller->discount_price : $bestSeller->price), $bsCurrency) }}</span>
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
        {{-- ================= BENZER SİTELER ================= --}}
        @if ($relatedSites->isNotEmpty())
            <section data-reveal-group>
                <p><span class="{{ $chip }}">Benzer siteler</span></p>
                <h2 class="mt-4 font-display text-2xl font-medium text-ink sm:text-[28px]" data-reveal>Aynı kategoride diğer siteler</h2>

                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relatedSites as $related)
                        <div data-reveal>
                            <x-site-card :site="$related" />
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
