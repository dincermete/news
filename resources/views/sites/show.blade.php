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
    $metricDefs = \App\Support\SiteSeoMetrics::definitions();

    $fmt = fn (int $n): string => number_format($n, 0, ',', '.');
    $money = function (float $amount, string $curr): string {
        $symbol = $curr === 'TRY' ? '₺' : '$';
        $formatted = fmod($amount, 1.0) > 0.009
            ? number_format($amount, 2, ',', '.')
            : number_format($amount, 0, ',', '.');

        return $formatted.$symbol;
    };

    $metricPalette = [
        ['#67a429', '#aee576'],
        ['#f045aa', '#eeaad2'],
        ['#674cd0', '#a8a8ff'],
        ['#1774de', '#9ccaff'],
        ['#fa8837', '#faac75'],
    ];
    $visibleMetrics = collect($metricDefs)->filter(fn ($label, $key) => $site->{"{$key}_value"} !== null);

    $chip = 'inline-flex items-center rounded-[10px] border border-ink/5 bg-white px-3.5 py-2 text-sm font-medium text-ink shadow-soft';
    $btnDark = 'group inline-flex items-center justify-center gap-x-3 rounded-2xl bg-gradient-to-b from-black to-[#363b3c] p-1 pe-5 text-sm font-medium text-white transition hover:scale-[1.02] active:scale-[0.98]';
    $btnChip = 'inline-flex size-9 items-center justify-center rounded-xl';
@endphp

@section('content')
    {{-- ================= HERO ================= --}}
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-dark relative overflow-hidden rounded-3xl text-white">
            <div class="relative mx-auto grid max-w-6xl gap-10 px-5 pb-10 pt-10 sm:px-8 sm:pb-14 sm:pt-12 lg:grid-cols-[1.15fr_0.85fr] lg:items-start" data-reveal-group>
                <div>
                    <nav class="flex items-center gap-x-1.5 text-xs text-white/50" aria-label="Konum" data-reveal>
                        <a href="{{ route('home') }}" class="transition hover:text-white">Anasayfa</a>
                        <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                        <a href="{{ route('sites.index') }}" class="transition hover:text-white">Siteler</a>
                        <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                        <span class="truncate text-white/80">{{ $site->domain }}</span>
                    </nav>

                    <div class="mt-5 flex items-center gap-x-4" data-reveal>
                        <x-site-favicon :domain="$site->domain" :size="52" class="shrink-0 rounded-2xl bg-white/10 p-2" />
                        <div class="min-w-0">
                            <h1 class="truncate font-display text-3xl font-medium leading-tight sm:text-4xl">{{ $site->domain }}</h1>
                            <p class="mt-1 text-sm text-white/60">{{ $site->category?->name ?? 'Kategorisiz' }}</p>
                        </div>
                    </div>

                    @if ($site->description)
                        <p class="mt-5 max-w-xl text-[15px] leading-relaxed text-white/65" data-reveal>{{ $site->description }}</p>
                    @endif

                    <div class="mt-5 flex flex-wrap items-center gap-2" data-reveal>
                        @if ($site->is_dofollow)
                            <span class="inline-flex items-center gap-x-1.5 rounded-full bg-emerald-500/15 px-3 py-1.5 text-xs font-semibold text-emerald-300">
                                <span class="size-1.5 rounded-full bg-emerald-400"></span> Dofollow
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-white/50">Nofollow</span>
                        @endif
                        @if ($site->is_news_approved)
                            <span class="inline-flex items-center gap-x-1.5 rounded-full bg-accent-500/20 px-3 py-1.5 text-xs font-semibold text-accent-300">
                                <span class="size-1.5 rounded-full bg-accent-400"></span> News Onaylı
                            </span>
                        @endif
                        @if ($site->age !== null)
                            <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-white/70">{{ $site->age }} yıllık</span>
                        @endif
                        @foreach ($site->labels as $label)
                            <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-white/70">{{ $label->name }}</span>
                        @endforeach
                    </div>

                    <div class="mt-8 flex flex-wrap items-center gap-x-8 gap-y-4" data-reveal>
                        <div>
                            <p class="flex items-center gap-x-1.5">
                                <svg class="size-4 text-amber-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd"/></svg>
                                <span class="font-display text-xl font-semibold tabular-nums">{{ $fmt($favoritesCount) }}</span>
                            </p>
                            <p class="mt-0.5 text-xs text-white/50">Favoriye eklendi</p>
                        </div>
                        <div>
                            <p class="flex items-center gap-x-1.5">
                                <svg class="size-4 text-accent-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                <span class="font-display text-xl font-semibold tabular-nums">{{ $fmt($viewsToday) }}</span>
                            </p>
                            <p class="mt-0.5 text-xs text-white/50">Bugün görüntülenme</p>
                        </div>
                        <div>
                            <p class="font-display text-xl font-semibold tabular-nums">{{ $fmt($viewsTotal) }}</p>
                            <p class="mt-0.5 text-xs text-white/50">Toplam görüntülenme</p>
                        </div>
                    </div>
                </div>

                {{-- Satın alma kartı --}}
                <div class="rounded-2xl bg-white p-6 shadow-pop" data-reveal>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-3">Fiyat</p>
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
                        <input type="hidden" name="site_id" value="{{ $site->id }}">
                        <button type="submit" class="{{ $btnDark }} w-full">
                            <span class="{{ $btnChip }} bg-white/15 text-white">
                                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            </span>
                            Sepete Ekle
                        </button>
                    </form>

                    <form method="post" action="{{ route('sites.favorite', $site) }}" class="mt-2.5">
                        @csrf
                        <button type="submit" class="flex w-full items-center justify-center gap-x-2 rounded-2xl border border-ink/10 bg-white px-4 py-3 text-sm font-medium text-ink transition hover:border-ink/25">
                            <svg class="size-4 {{ $isFavorited ? 'text-brand-500' : 'text-ink-3' }}" viewBox="0 0 24 24" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                            {{ $isFavorited ? 'Favoride' : 'Favorile' }}
                        </button>
                    </form>

                    <div class="mt-5 space-y-2.5 border-t border-ink/10 pt-5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-ink-2">Kategori</span>
                            <span class="font-semibold text-ink">{{ $site->category?->name ?? 'Kategorisiz' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-ink-2">Link tipi</span>
                            <span class="font-semibold text-ink">{{ $site->is_dofollow ? 'Dofollow' : 'Nofollow' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-ink-2">6 ay link garantisi</span>
                            <svg class="size-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 lg:px-8">
        {{-- ================= SEO METRİKLERİ ================= --}}
        @if ($visibleMetrics->isNotEmpty())
            <section data-reveal-group>
                <p><span class="{{ $chip }}">SEO Metrikleri</span></p>
                <h2 class="mt-4 font-display text-2xl font-medium text-ink sm:text-[28px]" data-reveal>Site otorite verileri</h2>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($visibleMetrics as $key => $label)
                        @php
                            $value = $site->{"{$key}_value"};
                            [$from, $to] = $metricPalette[$loop->index % count($metricPalette)];
                        @endphp
                        <div class="rounded-[20px] border border-ink/10 bg-paper p-5" data-reveal>
                            <span class="inline-flex size-10 items-center justify-center rounded-[10px] text-white" style="background-image: linear-gradient(144deg, {{ $from }}, {{ $to }})">
                                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                            </span>
                            <p class="mt-4 font-display text-2xl font-semibold text-ink tabular-nums">{{ number_format((float) $value, 0) }}</p>
                            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-ink-3">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ================= BENZER SİTELER ================= --}}
        @if ($relatedSites->isNotEmpty())
            <section class="mt-14" data-reveal-group>
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

        {{-- ================= SORULAR & SORU SOR ================= --}}
        <section class="mt-14 grid gap-6 lg:grid-cols-2" data-reveal-group>
            <div class="rounded-[20px] border border-ink/10 bg-white">
                <div class="border-b border-ink/10 px-6 py-5">
                    <h2 class="font-display text-lg font-semibold text-ink">Sorular &amp; yanıtlar</h2>
                </div>
                <div class="divide-y divide-ink/5">
                    @forelse ($questions as $item)
                        <div class="px-6 py-4">
                            <p class="text-sm font-semibold text-ink">{{ $item->question }}</p>
                            <p class="mt-1.5 text-sm leading-relaxed text-ink-2">{{ $item->answer }}</p>
                            @if ($item->answered_at)
                                <p class="mt-2 text-[11px] font-medium text-ink-3">{{ $item->answered_at->format('d.m.Y') }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-sm text-ink-2">Henüz yanıtlanmış soru yok.</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-[20px] border border-ink/10 bg-white">
                <div class="border-b border-ink/10 px-6 py-5">
                    <h2 class="font-display text-lg font-semibold text-ink">Soru sor</h2>
                    <p class="mt-1 text-sm text-ink-2">Yanıtlandıktan sonra herkese açık olarak yayınlanır.</p>
                </div>

                <form method="post" action="{{ route('sites.question', $site) }}" class="space-y-4 p-6">
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
        </section>
    </div>
@endsection
