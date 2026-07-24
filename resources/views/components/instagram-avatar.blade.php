@props([
    'account',
    'size' => 32,
])
@php
    $initial = mb_strtoupper(mb_substr(ltrim($account->handle, '@'), 0, 1));
@endphp
@if ($account->avatar_url)
    <img
        {{ $attributes->merge([
            'src' => \Illuminate\Support\Facades\Storage::disk('public')->url($account->avatar_url),
            'alt' => $account->handle,
            'width' => $size,
            'height' => $size,
            'loading' => 'lazy',
            'decoding' => 'async',
            'class' => 'inline-block rounded-full bg-zinc-100 object-cover',
        ]) }}
    >
@else
    <span
        {{ $attributes->merge([
            'class' => 'inline-flex shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-fuchsia-500 to-amber-400 font-semibold text-white',
        ]) }}
        style="width: {{ $size }}px; height: {{ $size }}px; font-size: {{ max(10, (int) ($size / 2.4)) }}px"
    >
        {{ $initial }}
    </span>
@endif
