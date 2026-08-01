{{--
    Metrik kaynağı logosu. Yerel asset'ler public/images/metrics altında.
--}}
@props([
    'source',
])
@php
    $logos = [
        'google' => 'google.svg',
        'google-news' => 'google-news.svg',
        'moz' => 'moz.svg',
        'ahrefs' => 'ahrefs.svg',
        'semrush' => 'semrush.svg',
        'majestic' => 'majestic.png',
    ];

    $file = $logos[$source] ?? null;
    $wide = in_array($file, ['ahrefs.svg', 'moz.svg', 'majestic.png'], true);
@endphp
@if ($file)
    <img
        {{ $attributes->class([
            'inline-block shrink-0 object-contain object-left',
            'h-3.5 w-auto max-w-6' => $wide,
            'size-4' => ! $wide,
        ]) }}
        src="{{ asset('images/metrics/'.$file) }}"
        alt=""
        loading="lazy"
        decoding="async"
    >
@else
    <span {{ $attributes->class(['inline-flex shrink-0 items-center rounded-md bg-ink/5 px-1.5 py-0.5 text-[9px] font-bold lowercase leading-none tracking-tight text-ink-2']) }}>
        {{ $source }}
    </span>
@endif
