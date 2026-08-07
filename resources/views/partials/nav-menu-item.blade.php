@php
    /**
     * @var array{label: string, url: string, active: bool, icon?: string|array<int, string>, description?: string} $item
     * @var string $onClick Alpine expression run on click, e.g. "open = false".
     */
    $iconPaths = isset($item['icon']) ? (array) $item['icon'] : [];
@endphp

<a
    href="{{ $item['url'] }}"
    @click="{{ $onClick }}"
    @class([
        'group flex items-start gap-x-3 rounded-lg p-3 text-sm transition',
        $item['active'] ? 'bg-brand-50 text-brand-700' : 'text-ink-2 hover:bg-paper hover:text-ink',
    ])
>
    @if ($iconPaths !== [])
        <span @class([
            'mt-0.5 inline-flex size-9 shrink-0 items-center justify-center rounded-lg transition',
            $item['active'] ? 'bg-brand-500 text-white' : 'bg-paper text-ink group-hover:bg-ink group-hover:text-white',
        ])>
            <svg class="size-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                @foreach ($iconPaths as $d)
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $d }}"/>
                @endforeach
            </svg>
        </span>
    @endif

    <span class="min-w-0 flex-1">
        <span class="block truncate font-semibold">{{ $item['label'] }}</span>
        @if (! empty($item['description']))
            <span @class(['mt-0.5 block truncate text-[12px] leading-snug', $item['active'] ? 'text-brand-700/70' : 'text-ink-3'])>{{ $item['description'] }}</span>
        @endif
    </span>
</a>
