@props([
    'gradient' => 'from-[#674cd0] to-[#a8a8ff]',
    'icon',
    'centered' => false,
    'size' => 'md',
])
@php
    $isSm = $size === 'sm';
@endphp
<h2 {{ $attributes->class([
    'font-display font-medium leading-[1.2] tracking-[-0.01em] text-ink',
    'mt-5 text-3xl sm:text-[36px]' => ! $isSm,
    'text-lg sm:text-xl' => $isSm,
    'flex flex-col items-start gap-3' => ! (bool) $centered && ! $isSm,
    'flex flex-row items-center gap-2.5' => $isSm || (bool) $centered,
    'justify-center text-center' => (bool) $centered,
]) }}>
    <span @class([
        'inline-flex shrink-0 items-center justify-center rounded-[10px] bg-gradient-to-br text-white',
        'size-10 sm:size-11' => ! $isSm,
        'size-8' => $isSm,
        $gradient,
    ])>
        <svg @class(['size-5' => ! $isSm, 'size-4' => $isSm]) xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
        </svg>
    </span>
    <span class="min-w-0 capitalize">{{ $slot }}</span>
</h2>
