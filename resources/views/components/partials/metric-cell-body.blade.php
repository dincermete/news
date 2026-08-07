<span @class([
    'flex shrink-0 items-center justify-center rounded-md',
    'size-7' => $compact,
    'size-9 rounded-lg' => ! $compact,
    'bg-gray-900/5 text-gray-900' => $featured,
    'bg-white text-gray-400' => ! $featured && ($surface ?? 'white') === 'gray',
    'bg-gray-50 text-gray-400' => ! $featured && ($surface ?? 'white') !== 'gray',
])>
    @if (filled($icon))
        <x-metric-icon :source="$icon" @class(['size-3.5' => $compact, 'size-4' => ! $compact]) />
    @elseif ($glyphPath !== null)
        <svg @class(['size-3.5' => $compact, 'size-4' => ! $compact]) xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $glyphPath }}" />
        </svg>
    @endif
</span>

<span class="min-w-0 flex-1">
    <span @class([
        'block truncate font-semibold text-gray-900',
        'text-sm' => $compact,
        'text-base' => ! $compact,
    ])>{{ $value ?? '—' }}</span>
    <span @class([
        'mt-0.5 block truncate text-gray-400',
        'text-[10px]' => $compact,
        'text-xs' => ! $compact,
    ])>{{ $label }}</span>
</span>

@if ($hasLightbox)
    <svg @class(['shrink-0 text-gray-300', 'size-3.5' => $compact, 'size-4' => ! $compact]) xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6"/>
    </svg>
@endif
