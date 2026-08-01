@props([
    'site',
    'label' => 'Siteye Git',
    'compact' => false,
])
@php
    $url = publisher_site_url($site);
@endphp
<a
    href="{{ $url }}"
    target="_blank"
    rel="noopener noreferrer"
    {{ $attributes->class([
        'inline-flex items-center justify-center gap-x-1.5 font-semibold transition',
        'size-8 rounded-full border border-ink/10 text-ink-2 hover:border-ink/25 hover:text-ink' => $compact,
        'rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 hover:border-gray-300 hover:bg-gray-50' => ! $compact,
    ]) }}
    @if ($compact) aria-label="{{ $label }}" title="{{ $label }}" @endif
>
    <svg class="{{ $compact ? 'size-4' : 'size-4 shrink-0 text-gray-400' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
    @unless ($compact)
        <span>{{ $label }}</span>
    @endunless
</a>
