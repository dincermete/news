@extends('layouts.app')

@section('meta')
    @include('partials.seo-meta', ['meta' => $meta])
@endsection

@section('mainClass', 'w-full flex-1 bg-gray-50')

@php
    $sidebarCard = 'overflow-hidden rounded-[20px] border border-ink/10 bg-white p-5';
@endphp

@section('content')
    <article>
        <section class="px-2 pt-2 sm:px-3">
            <div class="panel-light relative overflow-hidden rounded-3xl text-ink">
                <div class="relative mx-auto max-w-7xl px-5 pb-12 pt-10 text-start sm:px-8 sm:pb-14 sm:pt-14" data-reveal-group>
                    <nav class="flex flex-wrap items-center gap-x-1.5 text-xs text-ink-3" aria-label="Konum" data-reveal>
                        <a href="{{ route('home') }}" class="transition hover:text-ink">Anasayfa</a>
                        <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                        <a href="{{ route('blog.index') }}" class="transition hover:text-ink">Blog</a>
                        @if ($post->category)
                            <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                            <a href="{{ route('blog.category', $post->category) }}" class="transition hover:text-ink">{{ $post->category->name }}</a>
                        @endif
                    </nav>

                    <div class="mt-6 max-w-4xl">
                        <h1 class="blog-title" data-reveal>
                            {{ $post->title }}
                        </h1>

                        <div class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-ink-3" data-reveal>
                            @if ($post->author)
                                <span class="font-medium text-ink-2">{{ $post->author->name }}</span>
                            @endif
                            @if ($post->published_at)
                                <time datetime="{{ $post->published_at->toAtomString() }}">{{ $post->published_at->format('d.m.Y') }}</time>
                            @endif
                            <span>{{ number_format($post->views_count, 0, ',', '.') }} görüntülenme</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="mx-auto max-w-7xl px-4 py-10 text-start sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-12 lg:items-start lg:gap-10">
                <div class="min-w-0 lg:col-span-8">
                    @if ($post->featuredImageUrl())
                        <figure class="mb-8 overflow-hidden rounded-[20px] border border-ink/10 bg-white" data-reveal>
                            <img src="{{ $post->featuredImageUrl() }}" alt="{{ $post->title }}" class="w-full object-cover">
                        </figure>
                    @endif

                    @if (count($tocItems) > 0)
                        <nav class="mb-8 rounded-[20px] border border-ink/10 bg-white p-5 sm:p-6" aria-label="İçindekiler" data-reveal>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">İçindekiler</p>
                            <ol class="mt-3 space-y-2">
                                @foreach ($tocItems as $item)
                                    <li @class(['ps-4' => $item['level'] === 3])>
                                        <a
                                            href="#{{ $item['id'] }}"
                                            @class([
                                                'block text-sm leading-snug text-ink-2 transition hover:text-accent-700',
                                                'font-semibold text-ink' => $item['level'] === 2,
                                            ])
                                        >{{ $item['text'] }}</a>
                                    </li>
                                @endforeach
                            </ol>
                        </nav>
                    @endif

                    <div class="blog-prose max-w-none" data-reveal>
                        {!! $contentHtml !!}
                    </div>

                    @if ($post->tags->isNotEmpty())
                        <div class="mt-10 flex flex-wrap gap-2">
                            @foreach ($post->tags as $tag)
                                <a href="{{ route('blog.tag', $tag) }}" class="rounded-full border border-ink/10 bg-white px-3.5 py-1.5 text-xs font-medium text-ink-2 transition hover:border-ink/25 hover:text-ink">
                                    #{{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <hr class="my-10 border-ink/10">

                    <nav class="grid gap-3 sm:grid-cols-2" aria-label="Yazı gezintisi">
                        @if ($previous)
                            <a href="{{ $previous->url() }}" class="group rounded-[20px] border border-ink/10 bg-white p-4 transition hover:border-ink/20">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Önceki yazı</p>
                                <p class="mt-1.5 font-display text-base font-medium text-ink transition group-hover:text-accent-700">{{ $previous->title }}</p>
                            </a>
                        @else
                            <div class="hidden sm:block"></div>
                        @endif

                        @if ($next)
                            <a href="{{ $next->url() }}" class="group rounded-[20px] border border-ink/10 bg-white p-4 text-end transition hover:border-ink/20 sm:ms-auto">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Sonraki yazı</p>
                                <p class="mt-1.5 font-display text-base font-medium text-ink transition group-hover:text-accent-700">{{ $next->title }}</p>
                            </a>
                        @endif
                    </nav>
                </div>

                <aside class="space-y-5 lg:col-span-4 lg:sticky lg:top-28">
                    <div class="{{ $sidebarCard }}">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Ara</p>
                        <form method="get" action="{{ route('blog.index') }}" class="mt-3 flex items-center gap-2 rounded-full border border-ink/10 bg-paper p-1.5" role="search">
                            <svg class="ms-2.5 size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                            <input type="search" name="q" placeholder="Yazı ara…" class="w-full border-0 bg-transparent p-0 py-1.5 text-sm text-ink placeholder:text-ink-3 focus:ring-0" aria-label="Blog ara">
                            <button type="submit" class="inline-flex shrink-0 rounded-full bg-gradient-to-b from-black to-[#363b3c] px-3.5 py-2 text-xs font-semibold text-white">Ara</button>
                        </form>
                    </div>

                    @if ($categories->isNotEmpty())
                        <div class="{{ $sidebarCard }}">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Kategoriler</p>
                            <ul class="mt-3 space-y-1">
                                @foreach ($categories as $category)
                                    <li>
                                        <a
                                            href="{{ route('blog.category', $category) }}"
                                            @class([
                                                'flex items-center justify-between gap-3 rounded-xl px-2.5 py-2 text-sm transition',
                                                'bg-paper font-semibold text-ink' => $post->blog_category_id === $category->id,
                                                'text-ink-2 hover:bg-paper hover:text-ink' => $post->blog_category_id !== $category->id,
                                            ])
                                        >
                                            <span>{{ $category->name }}</span>
                                            <span class="text-xs text-ink-3">{{ $category->posts_count }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($popularTags->isNotEmpty())
                        <div class="{{ $sidebarCard }}">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-3">Popüler etiketler</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($popularTags as $tag)
                                    <a href="{{ route('blog.tag', $tag) }}" class="rounded-full border border-ink/10 bg-paper px-3 py-1.5 text-xs font-medium text-ink-2 transition hover:border-ink/25 hover:text-ink">
                                        #{{ $tag->name }}
                                        <span class="text-ink-3">({{ $tag->posts_count }})</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </aside>
            </div>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="border-t border-ink/10 bg-white">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8" data-reveal-group>
                <h2 class="font-display text-2xl font-medium text-ink sm:text-3xl" data-reveal>Bu yazıları da beğenebilirsiniz</h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <a href="{{ $item->url() }}" class="group overflow-hidden rounded-[20px] border border-ink/10 bg-paper transition hover:border-ink/20" data-reveal>
                            @if ($item->featuredImageUrl())
                                <div class="aspect-[16/10] overflow-hidden">
                                    <img src="{{ $item->featuredImageUrl() }}" alt="{{ $item->title }}" class="size-full object-cover transition duration-500 group-hover:scale-[1.03]" loading="lazy">
                                </div>
                            @endif
                            <div class="p-5">
                                @if ($item->category)
                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-accent-600">{{ $item->category->name }}</p>
                                @endif
                                <h3 class="mt-1.5 font-display text-lg font-medium text-ink transition group-hover:text-accent-700">{{ $item->title }}</h3>
                                <p class="mt-2 text-xs text-ink-3">{{ $item->published_at?->format('d.m.Y') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
