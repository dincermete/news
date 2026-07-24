@php
    /** @var \App\Models\CartItem $item */
@endphp
<span class="inline-flex items-center rounded-full bg-accent-100 px-2 py-0.5 text-[11px] font-semibold text-accent-700">
    {{ $item->product_type?->getLabel() }}
    @if ($item->footerLinkDurationOption)
        · {{ $item->footerLinkDurationOption->name }}
    @elseif ($item->instagramStoryPrice)
        · {{ $item->instagramStoryPrice->format->getLabel() }}
    @elseif ($item->seoPackageDurationOption)
        · {{ $item->seoPackageDurationOption->name }}
    @endif
</span>
