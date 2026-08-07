@props([
    'site',
    'listing' => null,
    'reviewsCount' => 0,
    'viewsTotal' => 0,
])

@php
    /** @var \App\Models\Site $site */
    /** @var \App\Models\PromotionalListing|null $listing */

    $fmt = fn (int $n): string => number_format($n, 0, ',', '.');
    $num = fn (?float $value): ?string => $value !== null ? number_format($value, 0, ',', '.') : null;

    $yesBadge = 'inline-flex items-center rounded-full bg-emerald-100 px-2 py-1 text-[11px] font-semibold text-emerald-700';
    $noBadge = 'inline-flex items-center rounded-full bg-ink/5 px-2 py-1 text-[11px] font-semibold text-ink-3';
    $softBadge = 'inline-flex items-center rounded-full bg-sky-100 px-2 py-1 text-[11px] font-semibold text-sky-800';
    $badge = fn (string $text, string $class): string => '<span class="'.$class.'">'.e($text).'</span>';
    $metricValue = fn (?string $text): string => '<span class="text-sm font-bold tabular-nums text-ink">'.e(filled($text) ? $text : '—').'</span>';

    // Mavi = teknik/otorite · Yeşil = durum/onay · Amber = zaman/işlem
    $tones = [
        'blue' => 'bg-blue-50 text-blue-600',
        'green' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600',
    ];

    $glyphs = [
        'news' => 'M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5',
        'chart' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
        'link' => 'M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244',
        'shield' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
        'arrow-out' => 'M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25',
        'globe' => 'M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418',
        'truck' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-9.026 0C3.672 5.568 3.25 6.048 3.25 6.615v9.017',
        'bag' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
        'chat' => 'M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z',
        'document' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
        'tag' => 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z',
        'eye' => 'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
    ];

    $analyticsUrls = $site->analyticsImageUrls();
    $referenceUrl = $site->reference_content_url ?? $listing?->reference_content_url;
    $referenceImages = $listing?->referenceContentImageUrls()
        ?? \App\Support\PublicImagePaths::urls($site->reference_content_image_paths ?? null);
    $referenceLabel = $site->reference_content_label
        ?? $listing?->reference_content_label
        ?? 'Tıklayın';

    $ordersCount = (int) ($site->orders_count ?? 0);
    $analyticsCount = count($analyticsUrls);
    $referenceImageCount = count($referenceImages);
    $deliveryText = $site->estimated_delivery ?? $listing?->estimated_delivery;
    $categoryName = $site->category?->name ?? 'Kategorisiz';

    /**
     * Üst katman — DA / Hit / Yaş güven şeridinde; burada yok.
     * 2×3 tam dolu.
     *
     * @var list<array{label: string, tone: string, glyph: string, html: string}> $featuredRows
     */
    $featuredRows = [
        [
            'label' => 'Google News',
            'tone' => 'green',
            'glyph' => 'news',
            'html' => $site->is_news_approved
                ? $badge('Kayıtlı', $yesBadge)
                : $badge('Kayıtlı Değil', $noBadge),
        ],
        [
            'label' => 'Google Index',
            'tone' => 'green',
            'glyph' => 'globe',
            'html' => $site->is_google_indexed
                ? $badge('Var', $yesBadge)
                : $badge('Yok', $noBadge),
        ],
        [
            'label' => 'Link Türü',
            'tone' => 'blue',
            'glyph' => 'link',
            'html' => $site->is_dofollow
                ? $badge('Dofollow', $yesBadge)
                : $badge('Nofollow', $noBadge),
        ],
        [
            'label' => 'PA Değeri',
            'tone' => 'blue',
            'glyph' => 'shield',
            'html' => $metricValue($num($site->pa_value !== null ? (float) $site->pa_value : null)),
        ],
        [
            'label' => 'Link Çıkışı',
            'tone' => 'blue',
            'glyph' => 'arrow-out',
            'html' => $metricValue($site->max_link_count !== null ? (string) $site->max_link_count : null),
        ],
        [
            'label' => 'Kategori',
            'tone' => 'blue',
            'glyph' => 'tag',
            'html' => $metricValue($categoryName),
        ],
    ];

    /**
     * Alt katman — 3×2 tam dolu.
     *
     * @var list<array{label: string, tone: string, glyph: string, html: string, images?: list<string>, href?: string|null}> $detailCards
     */
    $detailCards = [
        [
            'label' => 'Google Analytics',
            'tone' => 'green',
            'glyph' => 'chart',
            'html' => $analyticsCount > 0
                ? $badge($analyticsCount.' Görsel', $softBadge)
                : $badge('Görsel Yok', $noBadge),
            'images' => $analyticsUrls,
        ],
        [
            'label' => 'Tahmini Teslimat',
            'tone' => 'amber',
            'glyph' => 'truck',
            'html' => $metricValue($deliveryText),
        ],
        [
            'label' => 'Toplam Satış',
            'tone' => 'amber',
            'glyph' => 'bag',
            'html' => $ordersCount > 0
                ? $badge($fmt($ordersCount).' Adet', $softBadge)
                : $badge('Satış Yok', $noBadge),
        ],
        [
            'label' => 'Yorumlar',
            'tone' => 'amber',
            'glyph' => 'chat',
            'html' => $reviewsCount > 0
                ? $badge($fmt((int) $reviewsCount).' Yorum', $softBadge)
                : $badge('Yorum Yok', $noBadge),
        ],
        [
            'label' => 'Referans İçerik',
            'tone' => 'green',
            'glyph' => 'document',
            'html' => $referenceImageCount > 0
                ? $badge($referenceImageCount.' Görsel', $softBadge)
                : (filled($referenceUrl)
                    ? $badge($referenceLabel, $softBadge)
                    : $badge('Yok', $noBadge)),
            'images' => $referenceImages,
            'href' => $referenceUrl,
        ],
        [
            'label' => 'Görüntülenme',
            'tone' => 'amber',
            'glyph' => 'eye',
            'html' => $viewsTotal > 0
                ? $badge($fmt((int) $viewsTotal), $softBadge)
                : $badge('Yok', $noBadge),
        ],
    ];
@endphp

<div {{ $attributes->class(['min-w-0 overflow-hidden rounded-[20px] border border-ink/10 bg-white shadow-soft']) }} x-data="{ open: false }">
    <div class="border-b border-ink/10 px-5 py-4 sm:px-6">
        <x-section-heading
            size="sm"
            gradient="from-[#2248ab] to-[#7aa2ff]"
            icon="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"
        >Site Verileri</x-section-heading>
    </div>

    <div class="grid grid-cols-1 gap-2 p-4 sm:p-5 md:grid-cols-2">
        @foreach ($featuredRows as $row)
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

    <div class="border-t border-ink/10 px-4 py-4 sm:px-5">
        <button
            type="button"
            class="inline-flex items-center gap-1.5 text-sm font-semibold text-ink-2 transition hover:text-ink"
            @click="open = ! open"
            :aria-expanded="open.toString()"
        >
            <span x-text="open ? 'Daralt' : 'Devamı için tıklayın'">Devamı için tıklayın</span>
            <svg
                class="size-4 transition-transform duration-200"
                :class="{ 'rotate-180': open }"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition duration-200 ease-out"
            x-transition:enter-start="-translate-y-1 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition duration-150 ease-in"
            x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="-translate-y-1 opacity-0"
            class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3"
        >
            @foreach ($detailCards as $card)
                @php
                    $cardImages = array_values(array_filter((array) ($card['images'] ?? [])));
                    $hasLightbox = $cardImages !== [];
                    $hasLink = ! $hasLightbox && filled($card['href'] ?? null);
                    $cardClass = 'flex h-full flex-col gap-3 rounded-[16px] border border-ink/10 bg-white p-4 shadow-soft';
                @endphp

                @if ($hasLightbox)
                    <div x-data="{ lightboxOpen: false, index: 0, images: @js($cardImages) }" class="min-w-0">
                        <button
                            type="button"
                            class="{{ $cardClass }} w-full text-left transition hover:border-ink/20"
                            @click="lightboxOpen = true; index = 0"
                        >
                            <span class="inline-flex size-9 items-center justify-center rounded-full {{ $tones[$card['tone']] }}">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $glyphs[$card['glyph']] }}" />
                                </svg>
                            </span>
                            <span class="block text-xs font-medium text-ink-3">{{ $card['label'] }}</span>
                            <span class="block">{!! $card['html'] !!}</span>
                        </button>

                        <div
                            x-show="lightboxOpen"
                            x-cloak
                            @keydown.escape.window="lightboxOpen = false"
                            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
                            role="dialog"
                            aria-modal="true"
                            aria-label="{{ $card['label'] }}"
                        >
                            <div class="absolute inset-0" @click="lightboxOpen = false"></div>

                            <button type="button" @click="lightboxOpen = false" class="absolute end-4 top-4 z-10 rounded-lg bg-white/10 p-2 text-white transition hover:bg-white/20" aria-label="Kapat">
                                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            </button>

                            <template x-if="images.length > 1">
                                <button type="button" @click="index = (index - 1 + images.length) % images.length" class="absolute start-4 z-10 rounded-lg bg-white/10 p-2 text-white transition hover:bg-white/20" aria-label="Önceki">
                                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                                </button>
                            </template>

                            <template x-if="images.length > 1">
                                <button type="button" @click="index = (index + 1) % images.length" class="absolute end-4 bottom-4 z-10 rounded-lg bg-white/10 p-2 text-white transition hover:bg-white/20 sm:bottom-auto" aria-label="Sonraki">
                                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                </button>
                            </template>

                            <figure class="relative z-10 max-h-full w-full max-w-5xl">
                                <img :src="images[index]" alt="{{ $card['label'] }}" class="mx-auto max-h-[80vh] w-auto rounded-xl bg-white object-contain shadow-2xl">
                                <figcaption class="mt-3 text-center text-xs text-white/70">
                                    {{ $card['label'] }}
                                    <span x-show="images.length > 1" x-text="`(${index + 1}/${images.length})`"></span>
                                </figcaption>
                            </figure>
                        </div>
                    </div>
                @elseif ($hasLink)
                    <a
                        href="{{ $card['href'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="{{ $cardClass }} transition hover:border-ink/20"
                    >
                        <span class="inline-flex size-9 items-center justify-center rounded-full {{ $tones[$card['tone']] }}">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $glyphs[$card['glyph']] }}" />
                            </svg>
                        </span>
                        <span class="block text-xs font-medium text-ink-3">{{ $card['label'] }}</span>
                        <span class="block">{!! $card['html'] !!}</span>
                    </a>
                @else
                    <div class="{{ $cardClass }}">
                        <span class="inline-flex size-9 items-center justify-center rounded-full {{ $tones[$card['tone']] }}">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $glyphs[$card['glyph']] }}" />
                            </svg>
                        </span>
                        <span class="block text-xs font-medium text-ink-3">{{ $card['label'] }}</span>
                        <span class="block">{!! $card['html'] !!}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
