@props([
    'label',
    'value' => null,
    'icon' => null,
    'glyph' => null,
    'featured' => false,
    'href' => null,
    'images' => [],
    'surface' => 'gray',
    'compact' => false,
])

@php
    $images = array_values(array_filter((array) $images));
    $isEmpty = $value === null;
    $hasLightbox = $images !== [] && ! $isEmpty;
    $isLink = ! $hasLightbox && filled($href) && ! $isEmpty;
    $surfaceClass = $surface === 'gray' ? 'border border-gray-200 bg-gray-50' : 'bg-white';
    $hoverClass = $surface === 'gray' ? 'hover:bg-gray-100' : 'hover:bg-gray-50';
    $paddingClass = $compact ? 'p-2.5' : 'p-3';
    $gapClass = $compact ? 'gap-2' : 'gap-3';
    $radiusClass = $compact ? 'rounded-lg' : 'rounded-xl';

    $glyphs = [
        'truck' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-9.026 0C3.672 5.568 3.25 6.048 3.25 6.615v9.017',
        'bag' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
        'chat' => 'M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z',
        'document' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
        'tag' => 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z',
        'chart' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
        'link' => 'M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244',
        'arrow-out' => 'M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25',
        'calendar' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
    ];

    $glyphPath = $glyph !== null ? ($glyphs[$glyph] ?? null) : null;
    $cellClasses = "flex w-full items-center {$gapClass} {$radiusClass} {$surfaceClass} {$paddingClass} text-left";
@endphp

@if ($hasLightbox)
    <div x-data="{ open: false, index: 0, images: @js($images) }" class="min-w-0">
        <button type="button" @click="open = true; index = 0" class="{{ $cellClasses }} transition {{ $hoverClass }}">
            @include('components.partials.metric-cell-body')
        </button>

        <div
            x-show="open"
            x-cloak
            @keydown.escape.window="open = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
            role="dialog"
            aria-modal="true"
            aria-label="{{ $label }}"
        >
            <div class="absolute inset-0" @click="open = false"></div>

            <button type="button" @click="open = false" class="absolute end-4 top-4 z-10 rounded-lg bg-white/10 p-2 text-white transition hover:bg-white/20" aria-label="Kapat">
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
                <img :src="images[index]" alt="{{ $label }}" class="mx-auto max-h-[80vh] w-auto rounded-xl bg-white object-contain shadow-2xl">
                <figcaption class="mt-3 text-center text-xs text-white/70">
                    {{ $label }}
                    <span x-show="images.length > 1" x-text="`(${index + 1}/${images.length})`"></span>
                </figcaption>
            </figure>
        </div>
    </div>
@elseif ($isLink)
    <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" class="{{ $cellClasses }} min-w-0 transition {{ $hoverClass }}">
        @include('components.partials.metric-cell-body')
    </a>
@else
    <div class="{{ $cellClasses }} min-w-0">
        @include('components.partials.metric-cell-body')
    </div>
@endif
