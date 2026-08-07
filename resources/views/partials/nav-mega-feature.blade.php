@php
    /**
     * Right-hand highlight column for a full-width header mega menu.
     *
     * @var array<string, mixed> $feature
     * @var string $onClick Alpine expression run on click, e.g. "open = false".
     */
    $type = $feature['type'] ?? null;
    $arrowPath = 'M17 8l4 4m0 0-4 4m4-4H3';
@endphp

@if ($type === 'promo')
    <div class="flex h-full flex-col justify-between rounded-2xl border border-ink/10 bg-gradient-to-br from-paper to-white p-5">
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-accent-600">{{ $feature['eyebrow'] }}</p>
            <p class="mt-2 font-display text-base font-semibold text-ink">{{ $feature['title'] }}</p>
            <p class="mt-1.5 text-[13px] leading-relaxed text-ink-2">{{ $feature['body'] }}</p>

            @if (! empty($feature['stats']))
                <div class="mt-4 grid grid-cols-2 gap-3">
                    @foreach ($feature['stats'] as $stat)
                        <div class="rounded-xl bg-white p-3 ring-1 ring-ink/5">
                            <p class="font-display text-lg font-bold text-ink">{{ $stat['value'] }}</p>
                            <p class="text-[11px] font-medium text-ink-3">{{ $stat['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @if (! empty($feature['cta']))
            <a
                href="{{ $feature['cta']['url'] }}"
                @click="{{ $onClick }}"
                class="mt-4 inline-flex items-center justify-center gap-x-1.5 rounded-xl bg-ink px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-black"
            >
                {{ $feature['cta']['label'] }}
                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $arrowPath }}"/></svg>
            </a>
        @endif
    </div>
@elseif ($type === 'article')
    @php $post = $feature['post'] ?? null; @endphp
    <div class="flex h-full flex-col rounded-2xl border border-ink/10 bg-paper p-2">
        <p class="px-2 pb-2 pt-1.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-ink-3">{{ $feature['eyebrow'] }}</p>

        @if ($post)
            <a
                href="{{ $post['url'] }}"
                @click="{{ $onClick }}"
                class="group block overflow-hidden rounded-xl bg-white ring-1 ring-ink/5 transition hover:ring-ink/15"
            >
                @if ($post['image'])
                    <div class="aspect-[16/9] overflow-hidden bg-ink/5">
                        <img src="{{ $post['image'] }}" alt="" class="size-full object-cover transition duration-300 group-hover:scale-105" loading="lazy">
                    </div>
                @endif
                <div class="p-3.5">
                    <p class="line-clamp-2 text-sm font-semibold text-ink">{{ $post['title'] }}</p>
                    @if ($post['excerpt'])
                        <p class="mt-1 line-clamp-2 text-[12px] leading-relaxed text-ink-3">{{ $post['excerpt'] }}</p>
                    @endif
                    <span class="mt-2 inline-flex items-center gap-x-1 text-[12px] font-semibold text-accent-600">
                        Devamını oku
                        <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $arrowPath }}"/></svg>
                    </span>
                </div>
            </a>
        @else
            <div class="flex flex-1 flex-col justify-between rounded-xl bg-white p-4 ring-1 ring-ink/5">
                <div>
                    <p class="text-sm font-semibold text-ink">{{ $feature['fallback']['title'] }}</p>
                    <p class="mt-1.5 text-[13px] leading-relaxed text-ink-2">{{ $feature['fallback']['body'] }}</p>
                </div>
                <a href="{{ $feature['fallback']['cta_url'] }}" @click="{{ $onClick }}" class="mt-4 inline-flex items-center gap-x-1.5 text-[12px] font-semibold text-accent-600">
                    {{ $feature['fallback']['cta_label'] }}
                    <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $arrowPath }}"/></svg>
                </a>
            </div>
        @endif
    </div>
@elseif ($type === 'tool')
    <div class="flex h-full flex-col justify-between rounded-2xl border border-ink/10 bg-paper p-5">
        <div>
            <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-accent-600">{{ $feature['eyebrow'] }}</p>
            <span class="mt-3 inline-flex size-10 items-center justify-center rounded-xl bg-ink text-white">
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    @foreach ((array) $feature['icon'] as $d)
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $d }}"/>
                    @endforeach
                </svg>
            </span>
            <p class="mt-3 font-display text-base font-semibold text-ink">{{ $feature['name'] }}</p>
            <p class="mt-1.5 text-[13px] leading-relaxed text-ink-2">{{ $feature['excerpt'] }}</p>
        </div>

        <a
            href="{{ $feature['url'] }}"
            @click="{{ $onClick }}"
            class="mt-4 inline-flex items-center justify-center gap-x-1.5 rounded-xl bg-ink px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-black"
        >
            Aracı Dene
            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $arrowPath }}"/></svg>
        </a>
    </div>
@endif
