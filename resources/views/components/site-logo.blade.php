{{--
    Site logosu: panelden yüklenen gerçek marka logosu, yoksa favicon
    fallback. Plaka genişliği her zaman 80px; yükseklik çağıran taraftan gelir.
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
@endphp
<span
    {{ $attributes->class([
        'inline-flex w-[80px] shrink-0 items-center justify-center overflow-hidden rounded-md border border-ink/10 bg-white',
    ]) }}
    style="height: {{ $height }}px;"
>
    @if ($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $domain }}" loading="lazy" decoding="async" class="h-full w-full object-contain p-1">
    @else
        <img src="{{ $faviconUrl }}" alt="{{ $domain }} favicon" loading="lazy" decoding="async" class="h-[60%] w-[60%] object-contain">
    @endif
</span>
