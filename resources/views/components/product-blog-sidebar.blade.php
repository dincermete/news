@props([
    'posts',
])

@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\BlogPost> $posts */
@endphp

@if ($posts->isNotEmpty())
    <div {{ $attributes->class('min-w-0 overflow-hidden rounded-[20px] border border-ink/10 bg-white p-5 shadow-soft sm:p-6') }}>
        <div class="flex items-center justify-between gap-3">
            <x-section-heading
                size="sm"
                gradient="from-[#674cd0] to-[#a8a8ff]"
                icon="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5"
            >Son Blog Yazıları</x-section-heading>
            <a href="{{ route('blog.index') }}" class="shrink-0 text-xs font-semibold text-ink transition hover:text-ink-2">Tümü</a>
        </div>
        <ul class="mt-3 space-y-1">
            @foreach ($posts as $post)
                <li>
                    <a href="{{ $post->url() }}" class="flex min-w-0 items-center gap-x-2.5 rounded-xl px-1.5 py-2 ps-0 transition hover:bg-paper">
                        @if ($post->featuredImageUrl())
                            <img
                                src="{{ $post->featuredImageUrl() }}"
                                alt=""
                                class="size-10 shrink-0 rounded-lg object-cover"
                                loading="lazy"
                            >
                        @else
                            <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-lg bg-paper text-[10px] font-semibold uppercase tracking-wide text-ink-3">
                                Blog
                            </span>
                        @endif
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium text-ink">{{ $post->title }}</span>
                            <span class="mt-0.5 block truncate text-[11px] text-ink-3">
                                @if ($post->category)
                                    {{ $post->category->name }} ·
                                @endif
                                {{ $post->published_at?->format('d.m.Y') }}
                            </span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
