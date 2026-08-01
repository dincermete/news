@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1')

@php
    $activeCategory = $activeCategory ?? null;
    $activeTag = $activeTag ?? null;
    $heroTitle = $activeCategory?->name
        ?? ($activeTag ? '#'.$activeTag->name : 'Blog');
    $heroText = $activeCategory?->description
        ?? ($activeTag
            ? $activeTag->name.' etiketli yazılar'
            : 'SEO, backlink ve dijital görünürlük hakkında pratik rehberler.');
@endphp

@section('content')
    <section class="px-2 pt-2 sm:px-3">
        <div class="panel-light relative overflow-hidden rounded-3xl text-ink">
            <div class="relative mx-auto flex max-w-7xl flex-col items-center px-5 pb-10 pt-14 text-center sm:px-8 sm:pb-12 sm:pt-16" data-reveal-group>
                <p class="inline-flex items-center gap-x-2 rounded-full border border-ink/10 bg-white py-1 pe-3.5 ps-1 text-xs text-ink-2 shadow-soft" data-reveal>
                    <span class="rounded-full bg-brand-500 px-2.5 py-0.5 text-[10px] font-semibold text-white">Blog</span>
                    {{ site_setting('site_name') }}
                </p>
                <h1 class="mt-5 max-w-2xl font-display text-4xl font-medium leading-[1.12] sm:text-5xl" data-reveal>
                    {{ $heroTitle }}
                </h1>
                <p class="mt-4 max-w-xl text-lg font-medium leading-relaxed text-ink-2" data-reveal>
                    {{ $heroText }}
                </p>

                <form method="get" action="{{ route('blog.index') }}" class="mt-7 flex w-full max-w-xl items-center gap-2 rounded-full border border-ink/10 bg-white p-1.5 shadow-pop" role="search" data-reveal>
                    @if ($categorySlug)
                        <input type="hidden" name="kategori" value="{{ $categorySlug }}">
                    @endif
                    <svg class="ms-3 size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    <input type="search" name="q" value="{{ $q }}" placeholder="Yazı ara…" class="w-full border-0 bg-transparent p-0 py-2 text-sm text-ink placeholder:text-ink-3 focus:ring-0" aria-label="Blog ara">
                    <button type="submit" class="inline-flex shrink-0 items-center gap-x-1.5 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white transition hover:scale-[1.03] active:scale-[0.98]">
                        Ara
                    </button>
                </form>

                @if ($categories->isNotEmpty())
                    <div class="mt-6 flex max-w-3xl flex-wrap items-center justify-center gap-2" data-reveal>
                        <a href="{{ route('blog.index') }}" @class(['rounded-full px-3.5 py-1.5 text-xs font-medium transition', 'bg-white text-ink shadow-soft' => $categorySlug === null && $activeTag === null, 'border border-ink/10 bg-white text-ink-2 hover:text-ink' => ! ($categorySlug === null && $activeTag === null)])>Tümü</a>
                        @foreach ($categories as $category)
                            <a href="{{ route('blog.category', $category) }}" @class(['rounded-full px-3.5 py-1.5 text-xs font-medium transition', 'bg-white text-ink shadow-soft' => $categorySlug === $category->slug, 'border border-ink/10 bg-white text-ink-2 hover:text-ink' => $categorySlug !== $category->slug])>
                                {{ $category->name }}
                                @if ($category->posts_count > 0)
                                    <span class="text-ink-3">({{ $category->posts_count }})</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @if ($featured->isNotEmpty() && blank($q) && $categorySlug === null && $activeTag === null)
            <section class="mb-12" data-reveal-group>
                <div class="mb-5 flex items-end justify-between gap-3">
                    <h2 class="font-display text-2xl font-medium text-ink sm:text-3xl" data-reveal>Öne çıkanlar</h2>
                </div>
                <div class="grid gap-5 md:grid-cols-3">
                    @foreach ($featured as $item)
                        <a href="{{ $item->url() }}" class="group overflow-hidden rounded-[20px] border border-ink/10 bg-white transition hover:border-ink/20" data-reveal>
                            @if ($item->featuredImageUrl())
                                <div class="aspect-[16/10] overflow-hidden bg-paper">
                                    <img src="{{ $item->featuredImageUrl() }}" alt="{{ $item->title }}" class="size-full object-cover transition duration-500 group-hover:scale-[1.03]">
                                </div>
                            @else
                                <div class="flex aspect-[16/10] items-center justify-center bg-gradient-to-br from-accent-600 to-accent-900">
                                    <span class="font-display text-2xl font-medium text-white/90">Blog</span>
                                </div>
                            @endif
                            <div class="p-5">
                                @if ($item->category)
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-accent-600">{{ $item->category->name }}</p>
                                @endif
                                <h3 class="mt-2 font-display text-lg font-medium leading-snug text-ink transition group-hover:text-accent-700">{{ $item->title }}</h3>
                                @if ($item->excerpt)
                                    <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-ink-2">{{ $item->excerpt }}</p>
                                @endif
                                <p class="mt-3 text-xs text-ink-3">{{ $item->published_at?->format('d.m.Y') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($posts->isEmpty())
            <div class="rounded-[20px] border border-ink/10 bg-paper px-6 py-16 text-center">
                <p class="font-display text-lg font-semibold text-ink">Bu filtrelere uygun yazı bulunamadı.</p>
                <a href="{{ route('blog.index') }}" class="mt-5 inline-flex items-center gap-x-1.5 rounded-xl bg-gradient-to-b from-black to-[#363b3c] px-5 py-2.5 text-sm font-semibold text-white">Tüm yazılar</a>
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                @foreach ($posts as $post)
                    <article class="group flex flex-col overflow-hidden rounded-[20px] border border-ink/10 bg-white transition hover:border-ink/20" data-reveal>
                        <a href="{{ $post->url() }}" class="block overflow-hidden">
                            @if ($post->featuredImageUrl())
                                <div class="aspect-[16/10] overflow-hidden bg-paper">
                                    <img src="{{ $post->featuredImageUrl() }}" alt="{{ $post->title }}" class="size-full object-cover transition duration-500 group-hover:scale-[1.03]" loading="lazy">
                                </div>
                            @else
                                <div class="flex aspect-[16/10] items-center justify-center bg-paper-2">
                                    <span class="text-sm font-semibold text-ink-3">{{ $post->category?->name ?? 'Blog' }}</span>
                                </div>
                            @endif
                        </a>
                        <div class="flex flex-1 flex-col p-5">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] font-semibold uppercase tracking-wide text-ink-3">
                                @if ($post->category)
                                    <a href="{{ route('blog.category', $post->category) }}" class="text-accent-600 hover:text-accent-700">{{ $post->category->name }}</a>
                                    <span aria-hidden="true">·</span>
                                @endif
                                <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('d.m.Y') }}</time>
                            </div>
                            <h2 class="mt-2 font-display text-xl font-medium leading-snug text-ink">
                                <a href="{{ $post->url() }}" class="transition hover:text-accent-700">{{ $post->title }}</a>
                            </h2>
                            @if ($post->excerpt)
                                <p class="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed text-ink-2">{{ $post->excerpt }}</p>
                            @endif
                            <div class="mt-4 flex items-center justify-between gap-3">
                                <p class="truncate text-xs text-ink-3">{{ $post->author?->name }}</p>
                                <a href="{{ $post->url() }}" class="inline-flex items-center gap-1 text-sm font-semibold text-ink transition hover:text-accent-700">
                                    Oku
                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
@endsection
