@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1 bg-gray-50')

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

    $num = fn (?float $value): ?string => $value !== null ? number_format($value, 0, ',', '.') : null;

    $dataCells = [
        ['label' => 'Kategori', 'value' => $site->category?->name ?? 'Kategorisiz', 'icon' => null, 'featured' => false],
        ['label' => 'Google Index', 'value' => $site->is_google_indexed ? 'Var' : 'Yok', 'icon' => 'google', 'featured' => false],
        ['label' => 'Google News', 'value' => $site->is_news_approved ? 'Var' : 'Yok', 'icon' => 'google', 'featured' => false],
        ['label' => 'DA', 'value' => $num($site->da_value !== null ? (float) $site->da_value : null), 'icon' => 'moz', 'featured' => true],
        ['label' => 'PA', 'value' => $num($site->pa_value !== null ? (float) $site->pa_value : null), 'icon' => 'moz', 'featured' => true],
        ['label' => 'Ahrefs DR', 'value' => $num($site->ahrefs_dr_value !== null ? (float) $site->ahrefs_dr_value : null), 'icon' => 'ahrefs', 'featured' => true],
        ['label' => 'Semrush AS', 'value' => $num($site->semrush_authority_score_value !== null ? (float) $site->semrush_authority_score_value : null), 'icon' => 'semrush', 'featured' => false],
        ['label' => 'Site Yaşı', 'value' => $site->age !== null ? $site->age.' yıl' : null, 'icon' => null, 'featured' => false],
        ['label' => 'Link Tipi', 'value' => $site->is_dofollow ? 'Dofollow' : 'Nofollow', 'icon' => null, 'featured' => false],
        ['label' => 'Aylık Trafik', 'value' => $num($site->monthly_traffic_value !== null ? (float) $site->monthly_traffic_value : null), 'icon' => 'google', 'featured' => false],
        ['label' => 'Moz Rank', 'value' => $num($site->moz_rank_value !== null ? (float) $site->moz_rank_value : null), 'icon' => 'moz', 'featured' => false],
        ['label' => 'Majestic CF', 'value' => $num($site->majestic_cf_value !== null ? (float) $site->majestic_cf_value : null), 'icon' => 'majestic', 'featured' => false],
        ['label' => 'Majestic TF', 'value' => $num($site->majestic_tf_value !== null ? (float) $site->majestic_tf_value : null), 'icon' => 'majestic', 'featured' => false],
        ['label' => 'Backlink', 'value' => $num($site->backlinks_value !== null ? (float) $site->backlinks_value : null), 'icon' => 'ahrefs', 'featured' => false],
        ['label' => 'Link Çıkışı', 'value' => $site->max_link_count !== null ? (string) $site->max_link_count : null, 'icon' => null, 'featured' => false],
        ['label' => 'Spam Score', 'value' => $num($site->spam_score_value !== null ? (float) $site->spam_score_value : null), 'icon' => 'moz', 'featured' => false],
        ['label' => 'Ahrefs Kelime', 'value' => $num($site->ahrefs_keywords_value !== null ? (float) $site->ahrefs_keywords_value : null), 'icon' => 'ahrefs', 'featured' => false],
    ];

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

    $ctaWhatsappColor = filled($site->cta_whatsapp_color) ? (string) $site->cta_whatsapp_color : '#25D366';

    $card = 'min-w-0 overflow-hidden rounded-xl border border-gray-200 bg-white';
    $ctaBase = 'inline-flex w-full items-center justify-center gap-x-2 rounded-xl px-4 py-3.5 text-sm font-semibold text-white transition hover:brightness-110 active:scale-[0.99]';
    $ctaCart = $ctaBase.' bg-black hover:bg-gray-900';
    $ctaBuy = $ctaBase.' bg-accent-600 hover:bg-accent-700';
    $outlineBtn = 'inline-flex w-full items-center justify-center gap-x-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-gray-900 transition hover:border-gray-300 hover:bg-gray-50';
    $linkAccent = 'text-black transition hover:text-gray-700';
@endphp

@section('content')
    <section class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8" data-reveal-group>
        <nav class="flex items-center gap-x-1.5 text-xs text-gray-400" aria-label="Konum" data-reveal>
            <a href="{{ route('home') }}" class="transition hover:text-gray-900">Anasayfa</a>
            <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            <a href="{{ route('sites.index') }}" class="transition hover:text-gray-900">Siteler</a>
            <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            <span class="truncate text-gray-500">{{ $site->domain }}</span>
        </nav>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        <div class="grid min-w-0 gap-5 sm:gap-6 lg:grid-cols-[1.75fr_.85fr] lg:items-start" data-reveal-group>
            <div class="contents lg:flex lg:min-w-0 lg:flex-col lg:gap-5">
                {{-- 1. Site kimliği --}}
                <div class="{{ $card }} p-5 sm:p-6" style="order: 1" data-reveal>
                    <div class="flex items-start gap-3 sm:items-center sm:gap-x-4">
                        <x-site-logo :site="$site" :height="40" class="shrink-0 rounded-lg" />
                        <div class="min-w-0 flex-1">
                            <h1 class="break-words font-display text-xl font-medium leading-tight text-gray-900 sm:truncate sm:text-3xl lg:text-4xl">{{ $site->domain }}</h1>
                            <p class="mt-1 text-sm text-gray-500">{{ $site->category?->name ?? 'Kategorisiz' }}</p>
                        </div>
                        <x-visit-site-button :site="$site" class="shrink-0" />
                    </div>

                    <div class="mt-4">
                        @if ($site->is_dofollow)
                            <span class="inline-flex items-center gap-x-1.5 rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700">
                                <span class="size-1.5 rounded-full bg-gray-500"></span>
                                Dofollow
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-semibold text-gray-500">Nofollow</span>
                        @endif
                    </div>

                    @if ($shortDescription)
                        <p class="mt-4 max-w-2xl text-sm leading-relaxed text-gray-500">{{ $shortDescription }}</p>
                    @endif

                    @if ($metaFacts->isNotEmpty())
                        <ul class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-xs text-gray-400">
                            @foreach ($metaFacts as $fact)
                                <li class="inline-flex items-center gap-x-1.5">
                                    <svg class="size-3.5 shrink-0 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    {{ $fact }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- 3. Site Verileri --}}
                <div class="{{ $card }}" style="order: 3" data-reveal>
                    <div class="border-b border-gray-200 px-5 py-4 sm:px-6">
                        <p class="font-display text-base font-semibold text-gray-900">Site Verileri</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 p-5 sm:grid-cols-3 sm:gap-3 sm:p-6">
                        @foreach ($dataCells as $cell)
                            @php
                                $isEmpty = $cell['value'] === null;
                                $isFeatured = ! empty($cell['featured']);
                            @endphp
                            <div @class([
                                'rounded-lg p-3',
                                'opacity-50' => $isEmpty,
                                'bg-gray-100' => $isFeatured,
                                'bg-gray-50' => ! $isFeatured,
                            ])>
                                <p @class([
                                    'truncate text-xl font-medium',
                                    'text-black' => $isFeatured,
                                    'text-gray-900' => ! $isFeatured,
                                ])>{{ $cell['value'] ?? '—' }}</p>
                                <p @class([
                                    'mt-1 flex items-center gap-1 text-xs',
                                    'text-black' => $isFeatured,
                                    'text-gray-400' => ! $isFeatured,
                                ])>
                                    @if (! empty($cell['icon']))
                                        <x-metric-icon :source="$cell['icon']" class="opacity-50" />
                                    @endif
                                    {{ $cell['label'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- 4. Detay sekmeleri --}}
                <div class="{{ $card }}" style="order: 4" x-data="{ tab: window.location.hash === '#yorumlar' ? 'yorumlar' : 'aciklama' }" data-reveal>
                    <div class="no-scrollbar flex gap-1 overflow-x-auto border-b border-gray-200 px-2 pt-2">
                        @foreach ($tabs as $t)
                            <button
                                type="button"
                                @click="tab = '{{ $t['key'] }}'"
                                :class="tab === '{{ $t['key'] }}' ? 'border-black text-gray-900' : 'border-transparent text-gray-400 hover:text-gray-500'"
                                class="shrink-0 whitespace-nowrap border-b-2 px-3 py-3 text-xs font-semibold transition sm:px-4 sm:text-sm"
                            >{{ $t['label'] }}</button>
                        @endforeach
                    </div>

                    <div class="p-5 sm:p-6">
                        <div x-show="tab === 'aciklama'">
                            @if (filled($site->description))
                                <div class="prose prose-sm max-w-none prose-headings:text-gray-900 prose-p:text-gray-500 prose-a:text-black prose-strong:text-gray-900">
                                    {!! $site->description !!}
                                </div>
                            @else
                                <p class="py-6 text-center text-sm text-gray-400">Bu site için henüz açıklama eklenmedi.</p>
                            @endif
                        </div>

                        @if ($deliveryDetails !== null)
                            <div x-show="tab === 'teslimat'" x-cloak>
                                <div class="prose prose-sm max-w-none prose-headings:text-gray-900 prose-p:text-gray-500 prose-a:text-black prose-strong:text-gray-900">
                                    {!! $deliveryDetails !!}
                                </div>
                            </div>
                        @endif

                        <div x-show="tab === 'yorumlar'" x-cloak id="yorumlar">
                            <div class="grid gap-5 lg:grid-cols-2 lg:gap-6">
                                <div class="overflow-hidden rounded-xl border border-gray-200">
                                    <div class="border-b border-gray-200 px-5 py-4">
                                        <h3 class="font-display text-base font-semibold text-gray-900">Kullanıcı yorumları</h3>
                                    </div>
                                    <div class="max-h-[420px] divide-y divide-gray-100 overflow-y-auto">
                                        @forelse ($reviews as $review)
                                            <div class="px-5 py-4">
                                                <p class="text-sm font-semibold text-gray-900">{{ $review->name }}</p>
                                                <p class="mt-1.5 text-sm leading-relaxed text-gray-500">{{ $review->message }}</p>
                                                @if ($review->approved_at)
                                                    <p class="mt-2 text-[11px] font-medium text-gray-400">{{ $review->approved_at->format('d.m.Y') }}</p>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="px-5 py-10 text-center text-sm text-gray-400">Henüz onaylanmış yorum yok.</div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="overflow-hidden rounded-xl border border-gray-200">
                                    <div class="border-b border-gray-200 px-5 py-4">
                                        <h3 class="font-display text-base font-semibold text-gray-900">Yorum yaz</h3>
                                        <p class="mt-1 text-sm text-gray-500">Üye olmadan gönderebilirsiniz. Onaylandıktan sonra yayınlanır.</p>
                                    </div>

                                    <form method="post" action="{{ route('sites.review', $site) }}" class="space-y-4 p-5">
                                        @csrf
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div class="sm:col-span-2">
                                                <label for="review_name" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-gray-400">Ad soyad</label>
                                                <input
                                                    id="review_name"
                                                    type="text"
                                                    name="name"
                                                    value="{{ old('name', auth()->user()?->name) }}"
                                                    required
                                                    maxlength="120"
                                                    class="block w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-black focus:ring-0"
                                                >
                                                @error('name')
                                                    <p class="mt-1.5 text-xs text-brand-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div>
                                                <label for="review_email" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-gray-400">E-posta</label>
                                                <input
                                                    id="review_email"
                                                    type="email"
                                                    name="email"
                                                    value="{{ old('email', auth()->user()?->email) }}"
                                                    required
                                                    class="block w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-black focus:ring-0"
                                                >
                                                @error('email')
                                                    <p class="mt-1.5 text-xs text-brand-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div>
                                                <label for="review_phone" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-gray-400">Telefon</label>
                                                <input
                                                    id="review_phone"
                                                    type="tel"
                                                    name="phone"
                                                    value="{{ old('phone') }}"
                                                    required
                                                    maxlength="40"
                                                    class="block w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-black focus:ring-0"
                                                >
                                                @error('phone')
                                                    <p class="mt-1.5 text-xs text-brand-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                        <div>
                                            <label for="review_message" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-gray-400">Mesaj</label>
                                            <textarea
                                                id="review_message"
                                                name="message"
                                                rows="4"
                                                required
                                                minlength="10"
                                                class="block w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-black focus:ring-0"
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
                                <div class="overflow-hidden rounded-xl border border-gray-200">
                                    <div class="border-b border-gray-200 px-5 py-4">
                                        <h3 class="font-display text-base font-semibold text-gray-900">Sorular &amp; yanıtlar</h3>
                                    </div>
                                    <div class="max-h-[420px] divide-y divide-gray-100 overflow-y-auto">
                                        @forelse ($questions as $item)
                                            <div class="px-5 py-4">
                                                <p class="text-sm font-semibold text-gray-900">{{ $item->question }}</p>
                                                <p class="mt-1.5 text-sm leading-relaxed text-gray-500">{{ $item->answer }}</p>
                                                @if ($item->answered_at)
                                                    <p class="mt-2 text-[11px] font-medium text-gray-400">{{ $item->answered_at->format('d.m.Y') }}</p>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="px-5 py-10 text-center text-sm text-gray-400">Henüz yanıtlanmış soru yok.</div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="overflow-hidden rounded-xl border border-gray-200">
                                    <div class="border-b border-gray-200 px-5 py-4">
                                        <h3 class="font-display text-base font-semibold text-gray-900">Soru sor</h3>
                                        <p class="mt-1 text-sm text-gray-500">Yanıtlandıktan sonra herkese açık olarak yayınlanır.</p>
                                    </div>

                                    <form method="post" action="{{ route('sites.question', $site) }}" class="space-y-4 p-5">
                                        @csrf
                                        @guest
                                            <div>
                                                <label for="guest_email" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-gray-400">E-posta</label>
                                                <input
                                                    id="guest_email"
                                                    type="email"
                                                    name="guest_email"
                                                    value="{{ old('guest_email') }}"
                                                    required
                                                    class="block w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-black focus:ring-0"
                                                >
                                                @error('guest_email')
                                                    <p class="mt-1.5 text-xs text-brand-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        @endguest
                                        <div>
                                            <label for="question" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-gray-400">Sorunuz</label>
                                            <textarea
                                                id="question"
                                                name="question"
                                                rows="4"
                                                required
                                                minlength="10"
                                                class="block w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-black focus:ring-0"
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
                <div class="{{ $card }} p-5 sm:p-6" style="order: 2" data-reveal>
                    @if ($hasDiscount)
                        <span class="mb-3 inline-flex rounded-lg bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-900">%{{ $discountPercent }} indirim</span>
                    @endif

                    @if ($hasDiscount)
                        <p class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                            <span class="text-2xl font-semibold text-gray-900">{{ $money((float) $site->discount_price, $currency) }}</span>
                            <span class="text-sm text-gray-400 line-through">{{ $money((float) $site->price, $currency) }}</span>
                        </p>
                    @else
                        <p class="text-2xl font-semibold text-gray-900">{{ $money((float) $site->price, $currency) }}</p>
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
                                class="{{ $ctaBase }}"
                                style="background-color: {{ $ctaWhatsappColor }}"
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
                                <svg class="size-4 shrink-0 {{ $isFavorited ? 'text-gray-900' : 'text-gray-400' }}" viewBox="0 0 24 24" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                                {{ $isFavorited ? 'Favoride' : 'Favorile' }}
                            </button>
                        </form>

                        <button
                            type="button"
                            class="{{ $outlineBtn }}"
                            data-share-url="{{ storefront_site_url($site) }}"
                            onclick="const btn=this; const url=btn.dataset.shareUrl; if (navigator.share) { navigator.share({title: document.title, url}); } else { navigator.clipboard.writeText(url); const label = btn.querySelector('[data-share-label]'); const original = label.textContent; label.textContent = 'Kopyalandı'; setTimeout(() => { label.textContent = original; }, 1500); }"
                        >
                            <svg class="size-4 shrink-0 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z"/></svg>
                            <span data-share-label>Paylaş</span>
                        </button>
                    </div>

                    <div class="mt-5 space-y-2 border-t border-gray-200 pt-5 text-sm text-gray-500">
                        <div class="flex items-center justify-between gap-3">
                            <span>6 ay link garantisi</span>
                            <svg class="size-4 shrink-0 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Favoriye eklendi</span>
                            <span class="shrink-0 text-gray-400">{{ $fmt($favoritesCount) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Bugün görüntülenme</span>
                            <span class="shrink-0 text-gray-400">{{ $fmt($viewsToday) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Toplam görüntülenme</span>
                            <span class="shrink-0 text-gray-400">{{ $fmt($viewsTotal) }}</span>
                        </div>
                    </div>
                </div>

                {{-- 5. En Çok Satanlar --}}
                @if ($bestSellers->isNotEmpty())
                    <div class="{{ $card }} p-5 sm:p-6" style="order: 5" data-reveal>
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">En Çok Satanlar</p>
                            <a href="{{ route('sites.index') }}" class="{{ $linkAccent }} shrink-0 text-xs font-semibold">Tümü</a>
                        </div>
                        <ul class="mt-3 space-y-1">
                            @foreach ($bestSellers as $bestSeller)
                                @php
                                    $bsCurrency = $bestSeller->currency?->value ?? (string) $bestSeller->currency;
                                    $bsHasDiscount = $bestSeller->discount_price !== null && (float) $bestSeller->discount_price < (float) $bestSeller->price;
                                @endphp
                                <li>
                                    <a href="{{ storefront_site_url($bestSeller) }}" class="flex min-w-0 items-center gap-x-2.5 rounded-lg px-1.5 py-2 transition hover:bg-gray-50">
                                        <x-site-logo :site="$bestSeller" :height="20" class="shrink-0 rounded" />
                                        <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-900">{{ $bestSeller->domain }}</span>
                                        <span class="shrink-0 text-sm font-medium tabular-nums text-gray-500">{{ $money((float) ($bsHasDiscount ? $bestSeller->discount_price : $bestSeller->price), $bsCurrency) }}</span>
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
            <section data-reveal-group>
                <div class="grid gap-8 lg:grid-cols-2">
                    @if ($relatedSites->isNotEmpty())
                        <div data-reveal>
                            <h2 class="font-display text-2xl font-medium text-gray-900 sm:text-[28px]">İlgili Ürünler</h2>
                            <div class="mt-5 rounded-[20px] border border-ink/10 bg-white p-2 sm:p-3">
                                <x-site-table-compact
                                    :sites="$relatedSites"
                                    :favoritedSiteIds="$favoritedSiteIds"
                                />
                            </div>
                        </div>
                    @endif

                    @if ($recommendedSites->isNotEmpty())
                        <div data-reveal>
                            <h2 class="font-display text-2xl font-medium text-gray-900 sm:text-[28px]">Tavsiye Edilen Ürünler</h2>
                            <div class="mt-5 rounded-[20px] border border-ink/10 bg-white p-2 sm:p-3">
                                <x-site-table-compact
                                    :sites="$recommendedSites"
                                    :favoritedSiteIds="$favoritedSiteIds"
                                />
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endif
    </div>
@endsection
