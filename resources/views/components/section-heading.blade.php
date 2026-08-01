@props([
    'gradient' => 'from-[#674cd0] to-[#a8a8ff]',
    'icon',
    'centered' => false,
])
<h2 {{ $attributes->class([
    'mt-5 font-display text-3xl font-medium leading-[1.2] tracking-[-0.01em] text-ink sm:text-[36px]',
    'flex flex-col items-start gap-3' => ! (bool) $centered,
    'flex flex-row items-center justify-center gap-3 text-center' => (bool) $centered,
]) }}>
    <span @class([
        'inline-flex size-10 shrink-0 items-center justify-center rounded-[10px] bg-gradient-to-br text-white sm:size-11',
        $gradient,
    ])>
        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
        </svg>
    </span>
    <span class="min-w-0">{{ $slot }}</span>
</h2>
