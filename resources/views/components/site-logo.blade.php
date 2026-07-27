{{--
    Site logosu: panelden yüklenen gerçek marka logosu (40:9 en-boy), yoksa
    favicon otomatik fallback olarak çekilir. Her iki durumda da aynı
    boyuttaki beyaz "plaka" içinde render edilir, böylece liste/tablo
    hizası her zaman tutarlı kalır.
--}}
@props([
    'site' => null,
    'domain' => null,
    'height' => 32,
])
@php
    /** @var \App\Models\Site|null $site */
    $domain ??= $site?->domain;
    $logoUrl = $site?->logoUrl();
    $faviconUrl = app(\App\Services\SeoMetaService::class)->faviconUrl($domain);
    $width = (int) round($height * 40 / 9);
@endphp
<span
    {{ $attributes->class(['inline-flex shrink-0 items-center justify-center overflow-hidden rounded-md border border-ink/10 bg-white']) }}
    style="width: {{ $width }}px; height: {{ $height }}px;"
>
    @if ($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $domain }}" loading="lazy" decoding="async" class="h-full w-full object-contain p-1">
    @else
        <img src="{{ $faviconUrl }}" alt="{{ $domain }} favicon" loading="lazy" decoding="async" class="h-[60%] w-[60%] object-contain">
    @endif
</span>
