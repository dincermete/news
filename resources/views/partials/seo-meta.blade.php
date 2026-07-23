@php
    /** @var array{title: string, description: string, og_image: string|null} $meta */
@endphp
<title>{{ $meta['title'] }}</title>
<meta name="description" content="{{ $meta['description'] }}">
<meta property="og:title" content="{{ $meta['title'] }}">
<meta property="og:description" content="{{ $meta['description'] }}">
<meta property="og:type" content="website">
@if (! empty($meta['og_image']))
    <meta property="og:image" content="{{ $meta['og_image'] }}">
@endif
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ $meta['title'] }}">
<meta name="twitter:description" content="{{ $meta['description'] }}">
