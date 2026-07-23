@props([
    'site',
])
@php
    /** @var \App\Models\Site $site */
    $currency = $site->currency?->value ?? (string) $site->currency;
    $hasDiscount = $site->discount_price !== null
        && (float) $site->discount_price < (float) $site->price;
@endphp
<div class="flex h-full flex-col rounded-[20px] border border-ink/10 bg-white transition hover:-translate-y-0.5 hover:shadow-pop">
    <div class="flex flex-auto flex-col p-5">
        <div class="flex items-start gap-3">
            <x-site-favicon :domain="$site->domain" :size="32" class="mt-0.5 shrink-0 rounded-lg" />
            <div class="min-w-0 flex-1">
                <a
                    href="{{ route('sites.show', $site->domain) }}"
                    class="block truncate text-sm font-semibold text-ink transition hover:text-accent-700 focus:outline-hidden"
                >
                    {{ $site->domain }}
                </a>
                <p class="mt-0.5 truncate text-xs text-ink-3">
                    {{ $site->category?->name ?? 'Kategorisiz' }}
                </p>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap gap-1.5">
            @if ($site->da_value !== null)
                <span class="inline-flex items-center rounded-full bg-paper px-2 py-0.5 text-[11px] font-bold text-ink">
                    DA {{ number_format((float) $site->da_value, 0) }}
                </span>
            @endif
            @if ($site->pa_value !== null)
                <span class="inline-flex items-center rounded-full bg-paper px-2 py-0.5 text-[11px] font-bold text-ink">
                    PA {{ number_format((float) $site->pa_value, 0) }}
                </span>
            @endif
            @if ($site->is_dofollow)
                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                    Dofollow
                </span>
            @endif
            @if ($site->is_news_approved)
                <span class="inline-flex items-center rounded-full bg-accent-100 px-2 py-0.5 text-[11px] font-semibold text-accent-700">
                    News
                </span>
            @endif
            @foreach ($site->labels as $label)
                <span
                    class="inline-flex items-center rounded-full bg-paper px-2 py-0.5 text-[11px] font-medium text-ink-2"
                    @if ($label->color) style="color: {{ $label->color }}" @endif
                >
                    {{ $label->name }}
                </span>
            @endforeach
        </div>
    </div>

    <div class="mt-auto flex items-center justify-between gap-3 border-t border-ink/5 px-5 py-4">
        <div class="text-sm">
            @if ($hasDiscount)
                <span class="mr-1 text-xs text-ink-3 line-through">
                    {{ number_format((float) $site->price, 2) }} {{ $currency }}
                </span>
                <span class="font-display font-bold text-accent-600">
                    {{ number_format((float) $site->discount_price, 2) }} {{ $currency }}
                </span>
            @else
                <span class="font-display font-bold text-ink">
                    {{ number_format((float) $site->price, 2) }} {{ $currency }}
                </span>
            @endif
        </div>

        <form method="post" action="{{ route('cart.add') }}">
            @csrf
            <input type="hidden" name="site_id" value="{{ $site->id }}">
            <button
                type="submit"
                class="inline-flex items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-3.5 py-1.5 text-xs font-semibold text-white transition hover:scale-[1.04] active:scale-[0.98]"
            >
                Sepete Ekle
            </button>
        </form>
    </div>
</div>
