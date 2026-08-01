@props([
    'logo',
    'brand',
    'metric',
])
@php
    $wide = in_array($logo, ['ahrefs.svg', 'moz.svg', 'majestic.png'], true);
@endphp
<span {{ $attributes->class(['inline-flex items-center gap-1.5']) }}>
    <img
        src="{{ asset('images/metrics/'.$logo) }}"
        alt=""
        @class([
            'shrink-0 object-contain object-left',
            'h-4 w-auto max-w-7' => $wide,
            'size-5' => ! $wide,
        ])
        loading="lazy"
        decoding="async"
    >
    <span class="inline-flex flex-col items-start gap-0.5 leading-none normal-case tracking-normal">
        <span class="text-[9px] font-medium text-ink-3">{{ $brand }}</span>
        <span class="text-[11px] font-bold uppercase tracking-wide text-ink">{{ $metric }}</span>
    </span>
</span>
