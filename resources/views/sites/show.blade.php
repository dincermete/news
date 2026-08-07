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

    $listingName = $site->listing_name
        ?? $listing?->name
        ?? $site->domain;

    $num = fn (?float $value): ?string => $value !== null ? number_format($value, 0, ',', '.') : null;

    $siteAge = null;
    if ($site->opened_at !== null) {
        $ageInterval = $site->opened_at->diff(now());
        $siteAge = collect([
            $ageInterval->y > 0 ? $ageInterval->y.' Yıl' : null,
            $ageInterval->m > 0 ? $ageInterval->m.' Ay' : null,
        ])->filter()->implode(' ');
    } elseif ($site->age !== null) {
        $siteAge = $site->age.' Yıl';
    }

    $trustStrip = collect([
        [
            'label' => 'DA Değeri',
            'value' => $num($site->da_value !== null ? (float) $site->da_value : null),
            'tint' => 'from-[#eef3ff] to-white',
            'gradient' => 'from-[#2248ab] to-[#7aa2ff]',
            'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
        ],
        [
            'label' => 'Günlük Hit',
            'value' => $num($site->monthly_traffic_value !== null ? (float) $site->monthly_traffic_value : null),
            'tint' => 'from-[#fff8f3] to-white',
            'gradient' => 'from-[#fa8837] to-[#faac75]',
            'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
        ],
        [
            'label' => 'Site Yaşı',
            'value' => $siteAge,
            'tint' => 'from-[#f0fdfa] to-white',
            'gradient' => 'from-[#0d9488] to-[#5eead4]',
            'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
        ],
    ])->filter(fn (array $metric): bool => filled($metric['value']))->values();

    $metaFacts = collect([
        $site->is_news_approved ? 'News Onaylı' : null,
        $site->age !== null ? $site->age.' yıllık' : null,
    ])->filter()->merge($site->labels->pluck('name'))->values();

    $shortDescription = $site->short_description
        ?: ($site->description ? \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($site->description)) ?? ''), 140) : null);

    $productDeliveryDetails = filled($site->delivery_details)
        ? (string) $site->delivery_details
        : (filled($listing?->delivery_details) ? (string) $listing->delivery_details : null);

    if (filled($productDeliveryDetails) && trim(strip_tags($productDeliveryDetails)) === '') {
        $productDeliveryDetails = null;
    }

    $deliveryDetails = $productDeliveryDetails
        ?? site_setting()->defaultDeliveryDetails();

    $tabs = [
        ['key' => 'aciklama', 'label' => 'Açıklamalar'],
        ...($deliveryDetails !== null ? [['key' => 'teslimat', 'label' => 'Teslimat Detayları']] : []),
        ['key' => 'yorumlar', 'label' => 'Kullanıcı Yorumları'],
        ['key' => 'soru-cevap', 'label' => 'Kullanıcı Soruları & Yanıtları'],
    ];

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
            <a href="{{ route('sites.index') }}" class="transition hover:text-ink">Siteler</a>
            <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            <span class="truncate text-ink-2">{{ $listingName }}</span>
        </nav>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <div class="grid min-w-0 gap-5 sm:gap-6 lg:grid-cols-[1.75fr_.85fr] lg:items-start" data-reveal-group>
            <div class="contents lg:flex lg:min-w-0 lg:flex-col lg:gap-5">
                {{-- 1. Site kimliği --}}
                <div class="{{ $card }} p-5 sm:p-6" style="order: 1" data-reveal>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-start gap-3 sm:gap-x-4">
                            <x-site-logo :site="$site" :height="60" class="shrink-0 rounded-lg" />
                            <div class="min-w-0 flex-1">
                                <h1 class="break-words font-display text-xl font-medium leading-tight text-ink sm:truncate sm:text-2xl">{{ $listingName }}</h1>
                                <div class="mt-1 flex items-center gap-x-2">
                                    <p class="truncate text-sm text-ink-2">{{ $site->domain }}</p>
                                    <span class="text-ink/20">&middot;</span>
                                    @if ($site->is_dofollow)
                                        <span class="inline-flex items-center gap-x-1 text-xs font-semibold text-emerald-700">
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                            Dofollow
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-x-1 text-xs font-semibold text-ink-3">
                                            <span class="size-1.5 rounded-full bg-ink/20"></span>
                                            Nofollow
                                        </span>
                                    @endif
                                </div>
                            </div>
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

                {{-- 5. Site Verileri --}}
                <x-site-verileri
                    :site="$site"
                    :listing="$listing"
                    :reviews-count="$reviews->count()"
                    :views-total="(int) $viewsTotal"
                    style="order: 5"
                    data-reveal
                />

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
                            @if (filled($site->description))
                                <div class="prose prose-sm max-w-none prose-headings:text-ink prose-p:text-ink-2 prose-a:text-ink prose-strong:text-ink">
                                    {!! $site->description !!}
                                </div>
                            @else
                                <p class="py-6 text-center text-sm text-ink-3">Bu site için henüz açıklama eklenmedi.</p>
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

                                    <form method="post" action="{{ route('sites.review', $site) }}" class="space-y-4 p-5">
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

                                    <form method="post" action="{{ route('sites.question', $site) }}" class="space-y-4 p-5">
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
                    @if ($hasDiscount)
                        <span class="mb-3 inline-flex rounded-full bg-gradient-to-br from-[#fa8837] to-[#faac75] px-2.5 py-1 text-[11px] font-semibold text-white shadow-soft">%{{ $discountPercent }} indirim</span>
                    @endif

                    @if ($hasDiscount)
                        <p class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                            <span class="font-display text-2xl font-semibold text-ink">{{ $money((float) $site->discount_price, $currency) }}</span>
                            <span class="text-sm text-ink-3 line-through">{{ $money((float) $site->price, $currency) }}</span>
                        </p>
                    @else
                        <p class="font-display text-2xl font-semibold text-ink">{{ $money((float) $site->price, $currency) }}</p>
                    @endif

                    <div class="mt-5 space-y-2.5">
                        <form method="post" action="{{ route('cart.add') }}">
                            @csrf
                            <input type="hidden" name="product_type" value="site_article">
                            <input type="hidden" name="site_id" value="{{ $site->id }}">
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
                            <input type="hidden" name="product_type" value="site_article">
                            <input type="hidden" name="site_id" value="{{ $site->id }}">
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

                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <form method="post" action="{{ route('sites.favorite', $site) }}">
                            @csrf
                            <button type="submit" class="{{ $outlineBtn }}">
                                <svg class="size-4 shrink-0 {{ $isFavorited ? 'text-ink' : 'text-ink-3' }}" viewBox="0 0 24 24" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                                {{ $isFavorited ? 'Favoride' : 'Favorile' }}
                            </button>
                        </form>

                        <button
                            type="button"
                            class="{{ $outlineBtn }}"
                            data-share-url="{{ storefront_site_url($site) }}"
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
                            <span>Favoriye eklendi</span>
                            <span class="shrink-0 text-ink-3">{{ $fmt($favoritesCount) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Bugün görüntülenme</span>
                            <span class="shrink-0 text-ink-3">{{ $fmt($viewsToday) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Toplam görüntülenme</span>
                            <span class="shrink-0 text-ink-3">{{ $fmt($viewsTotal) }}</span>
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
        @if ($relatedSites->isNotEmpty() || $recommendedSites->isNotEmpty())
            <section class="space-y-10 grid gap-8 lg:grid-cols-2" data-reveal-group>
                @if ($relatedSites->isNotEmpty())
                    <div data-reveal>
                        <x-section-heading
                            size="sm"
                            gradient="from-[#674cd0] to-[#a8a8ff]"
                            icon="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"
                        >İlgili Ürünler</x-section-heading>
                        <div class="mt-5 overflow-hidden">
                            <x-site-table
                                :sites="$relatedSites"
                                :favoritedSiteIds="$favoritedSiteIds"
                                :columns="['domain', 'indexed', 'price', 'actions']"
                            />
                        </div>
                    </div>
                @endif

                @if ($recommendedSites->isNotEmpty())
                    <div data-reveal>
                        <x-section-heading
                            size="sm"
                            gradient="from-[#0d9488] to-[#5eead4]"
                            icon="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.785 16.5 21.75l-.394-.965a1.5 1.5 0 0 0-1.079-1.078l-.965-.394.965-.394a1.5 1.5 0 0 0 1.079-1.078l.394-.965.394.965a1.5 1.5 0 0 0 1.078 1.078l.965.394-.965.394a1.5 1.5 0 0 0-1.078 1.078Z"
                        >Tavsiye Edilen Ürünler</x-section-heading>
                        <div class="mt-5 overflow-hidden">
                            <x-site-table
                                :sites="$recommendedSites"
                                :favoritedSiteIds="$favoritedSiteIds"
                                :columns="['domain', 'indexed', 'price', 'actions']"
                            />
                        </div>
                    </div>
                @endif
            </section>
        @endif
    </div>
@endsection
