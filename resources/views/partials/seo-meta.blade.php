@php
    /** @var array{title: string, description: string, keywords?: string|null, og_image: string|null, og_url?: string|null} $meta */
@endphp
<title>{{ $meta['title'] }}</title>
<meta name="description" content="{{ $meta['description'] }}">
@if (! empty($meta['keywords']))
    <meta name="keywords" content="{{ $meta['keywords'] }}">
@endif
<meta property="og:title" content="{{ $meta['title'] }}">
<meta property="og:description" content="{{ $meta['description'] }}">
<meta property="og:type" content="website">
@if (! empty($meta['og_url']))
    <meta property="og:url" content="{{ $meta['og_url'] }}">
@endif
@if (! empty($meta['og_image']))
    <meta property="og:image" content="{{ $meta['og_image'] }}">
@endif
<meta name="twitter:card" content="{{ ! empty($meta['og_image']) ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $meta['title'] }}">
<meta name="twitter:description" content="{{ $meta['description'] }}">
@if (! empty($meta['og_image']))
    <meta name="twitter:image" content="{{ $meta['og_image'] }}">
@endif
