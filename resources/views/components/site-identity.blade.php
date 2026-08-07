{{--
    Site kimliği: logo + ürün adı (listing name) veya domain.
--}}
@props([
    'site',
    'height' => 28,
    'linked' => true,
    'stopPropagation' => false,
    'logoClass' => 'shrink-0 rounded-lg',
    'domainClass' => 'block truncate text-sm font-semibold text-ink transition group-hover:text-accent-700',
    'label' => null,
])
@php
    /** @var \App\Models\Site $site */
    $displayName = $label
        ?? $site->listing_name
        ?? $site->activeListing?->name
        ?? $site->domain;
@endphp
@if ($linked)
    <a
        href="{{ storefront_site_url($site) }}"
        {{ $attributes->class(['flex min-w-0 items-center gap-x-3']) }}
        @if ($stopPropagation) @click.stop @endif
    >
        <x-site-logo :site="$site" :height="$height" class="{{ $logoClass }}" />
        <span class="min-w-0">
            <span class="{{ $domainClass }}">{{ $displayName }}</span>
        </span>
    </a>
@else
    <span {{ $attributes->class(['flex min-w-0 items-center gap-x-3']) }}>
        <x-site-logo :site="$site" :height="$height" class="{{ $logoClass }}" />
        <span class="min-w-0">
            <span class="{{ $domainClass }}">{{ $displayName }}</span>
        </span>
    </span>
@endif
