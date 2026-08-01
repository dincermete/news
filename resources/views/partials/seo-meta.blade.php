@php
    /** @var array{title: string, description: string, keywords?: string|null, og_image: string|null, og_url?: string|null, og_type?: string, published_time?: string|null, modified_time?: string|null, author?: string|null} $meta */
    $ogType = $meta['og_type'] ?? 'website';
@endphp
<title>{{ $meta['title'] }}</title>
<meta name="description" content="{{ $meta['description'] }}">
@if (! empty($meta['keywords']))
    <meta name="keywords" content="{{ $meta['keywords'] }}">
@endif
@if (! empty($meta['og_url']))
    <link rel="canonical" href="{{ $meta['og_url'] }}">
@endif
<meta property="og:title" content="{{ $meta['title'] }}">
<meta property="og:description" content="{{ $meta['description'] }}">
<meta property="og:type" content="{{ $ogType }}">
@if (! empty($meta['og_url']))
    <meta property="og:url" content="{{ $meta['og_url'] }}">
@endif
@if (! empty($meta['og_image']))
    <meta property="og:image" content="{{ $meta['og_image'] }}">
@endif
@if ($ogType === 'article')
    @if (! empty($meta['published_time']))
        <meta property="article:published_time" content="{{ $meta['published_time'] }}">
    @endif
    @if (! empty($meta['modified_time']))
        <meta property="article:modified_time" content="{{ $meta['modified_time'] }}">
    @endif
    @if (! empty($meta['author']))
        <meta property="article:author" content="{{ $meta['author'] }}">
    @endif
@endif
<meta name="twitter:card" content="{{ ! empty($meta['og_image']) ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $meta['title'] }}">
<meta name="twitter:description" content="{{ $meta['description'] }}">
@if (! empty($meta['og_image']))
    <meta name="twitter:image" content="{{ $meta['og_image'] }}">
@endif
@if ($ogType === 'article' && ! empty($meta['og_url']))
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $meta['title'],
            'description' => $meta['description'],
            'url' => $meta['og_url'],
            'image' => $meta['og_image'] ?? null,
            'datePublished' => $meta['published_time'] ?? null,
            'dateModified' => $meta['modified_time'] ?? null,
            'author' => ! empty($meta['author']) ? [
                '@type' => 'Person',
                'name' => $meta['author'],
            ] : null,
            'mainEntityOfPage' => $meta['og_url'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}
    </script>
@endif
