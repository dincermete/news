@php
    /** @var \Illuminate\Contracts\Pagination\Paginator $paginator */
@endphp
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Sayfalar" class="flex items-center justify-between gap-3">
        {{-- Önceki --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex size-9 items-center justify-center rounded-xl border border-ink/10 text-ink-3">
                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex size-9 items-center justify-center rounded-xl border border-ink/10 bg-white text-ink transition hover:border-ink/25">
                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            </a>
        @endif

        {{-- Sayfa numaraları --}}
        <div class="flex flex-wrap items-center justify-center gap-1.5">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex size-9 items-center justify-center text-sm font-medium text-ink-3">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="inline-flex size-9 items-center justify-center rounded-xl bg-gradient-to-b from-black to-[#363b3c] text-sm font-semibold text-white" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex size-9 items-center justify-center rounded-xl text-sm font-medium text-ink-2 transition hover:bg-paper">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Sonraki --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex size-9 items-center justify-center rounded-xl border border-ink/10 bg-white text-ink transition hover:border-ink/25">
                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </a>
        @else
            <span class="inline-flex size-9 items-center justify-center rounded-xl border border-ink/10 text-ink-3">
                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </span>
        @endif
    </nav>
@endif
