{{-- Site favicon/logo: lazy by default; no extra image package. --}}
@props([
    'domain',
    'alt' => null,
    'size' => 32,
])
@php
    $src = app(\App\Services\SeoMetaService::class)->faviconUrl($domain);
    $alt ??= $domain.' favicon';
@endphp
<img
    {{ $attributes->merge([
        'src' => $src,
        'alt' => $alt,
        'width' => $size,
        'height' => $size,
        'loading' => 'lazy',
        'decoding' => 'async',
        'class' => 'inline-block rounded-sm bg-zinc-100',
    ]) }}
>
