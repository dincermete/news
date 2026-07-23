@php
    $cartCount = (int) ($cartCount ?? 0);
    $navLink = 'rounded-full px-3.5 py-2 text-[13px] font-medium text-ink-2 transition hover:bg-ink/5 hover:text-ink';
    $navLinkActive = 'rounded-full bg-ink/5 px-3.5 py-2 text-[13px] font-medium text-ink';
@endphp

<header class="sticky top-0 z-40">
    @include('components.announcement-bar')

    <div class="border-b border-ink/5 bg-white/90 backdrop-blur-md">
        <nav class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8" aria-label="Ana menü">
            <a href="{{ route('home') }}" class="inline-flex shrink-0 items-center gap-x-2 focus:outline-hidden">
                <span class="inline-flex size-8 items-center justify-center rounded-xl bg-gradient-to-br from-accent-500 to-accent-700 text-white">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </span>
                <span class="font-display text-lg font-semibold tracking-tight text-ink">
                    NewsTanıtım
                </span>
            </a>

            <div class="hidden items-center gap-x-1 lg:flex" role="navigation">
                <a href="{{ route('home') }}" @class([request()->routeIs('home') ? $navLinkActive : $navLink])>Anasayfa</a>
                <a href="{{ route('sites.index') }}" @class([request()->routeIs('sites.*') ? $navLinkActive : $navLink])>Tüm Siteler</a>
                <a href="{{ url('/basin-bulteni') }}" @class([$navLink])>Basın Bülteni</a>
                <a href="{{ url('/backlink-paketleri') }}" @class([request()->is('backlink-paketleri') ? $navLinkActive : $navLink])>Tanıtım Paketleri</a>
                <a href="{{ url('/story-satis') }}" @class([$navLink])>Story Satış</a>
                <a href="{{ url('/footer-link') }}" @class([$navLink])>Footer Link</a>
            </div>

            <div class="flex items-center gap-x-1 sm:gap-x-1.5">
                <button type="button" class="inline-flex size-9 items-center justify-center rounded-full text-ink-2 transition hover:bg-ink/5 hover:text-ink focus:outline-hidden" aria-label="Ara">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                </button>

                @auth
                    @php
                        $bellItems = ($headerNotifications ?? collect())->map(fn ($n) => [
                            'id' => $n->id,
                            'title' => $n->title,
                            'body' => $n->body,
                            'read_at' => $n->read_at?->toIso8601String(),
                            'created_at' => $n->created_at?->format('d.m.Y H:i'),
                        ])->values();
                    @endphp
                    <div
                        class="relative"
                        x-data="notificationBell({
                            unread: {{ (int) ($headerUnreadCount ?? 0) }},
                            items: {{ \Illuminate\Support\Js::from($bellItems) }},
                            markUrlTemplate: @js(url('/bildirimler/__ID__/oku')),
                            csrf: @js(csrf_token()),
                        })"
                        @keydown.escape.window="close()"
                    >
                        <button
                            type="button"
                            class="relative inline-flex size-9 items-center justify-center rounded-full text-ink-2 transition hover:bg-ink/5 hover:text-ink focus:outline-hidden"
                            aria-label="Bildirimler"
                            @click="toggle()"
                            :aria-expanded="open.toString()"
                        >
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                            <span
                                x-show="unread > 0"
                                x-cloak
                                class="absolute -end-0.5 -top-0.5 inline-flex min-w-4 items-center justify-center rounded-full bg-brand-500 px-1 text-[10px] font-bold text-white"
                                x-text="unread > 9 ? '9+' : unread"
                            ></span>
                        </button>

                        <div
                            x-show="open"
                            x-cloak
                            x-transition
                            @click.outside="close()"
                            class="absolute end-0 z-50 mt-2 w-80 overflow-hidden rounded-2xl border border-ink/10 bg-white shadow-pop"
                        >
                            <div class="border-b border-ink/5 px-4 py-2.5 font-mono text-[10px] uppercase tracking-[0.12em] text-ink-2">
                                Bildirimler
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                <template x-if="items.length === 0">
                                    <p class="px-3 py-6 text-center text-sm text-ink-2">Bildirim yok</p>
                                </template>
                                <template x-for="item in items" :key="item.id">
                                    <button
                                        type="button"
                                        class="flex w-full flex-col gap-0.5 border-b border-ink/5 px-4 py-2.5 text-left transition hover:bg-paper"
                                        :class="{ 'bg-brand-50/60': !item.read_at }"
                                        @click="markRead(item)"
                                    >
                                        <span class="text-sm font-medium text-ink" x-text="item.title"></span>
                                        <span class="line-clamp-2 text-xs text-ink-2" x-text="item.body"></span>
                                        <span class="text-[11px] text-ink/40" x-text="item.created_at"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                @endauth

                <a href="{{ auth()->check() ? route('account.dashboard') : route('login') }}" class="inline-flex size-9 items-center justify-center rounded-full text-ink-2 transition hover:bg-ink/5 hover:text-ink focus:outline-hidden" aria-label="Hesabım">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                </a>

                <a
                    href="{{ route('cart.index') }}"
                    class="group relative ms-1 inline-flex items-center gap-x-2 rounded-2xl bg-ink px-5 py-2.5 text-xs font-medium text-white transition hover:bg-black hover:scale-[1.03] active:scale-[0.98] focus:outline-hidden"
                >
                    Sepet
                    <svg class="size-3.5 shrink-0 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5 19.5 4.5m0 0H8.25m11.25 0v11.25" /></svg>
                    <span class="absolute -end-1.5 -top-1.5 inline-flex size-4 items-center justify-center rounded-full bg-brand-500 text-[10px] font-bold text-white">
                        {{ $cartCount }}
                    </span>
                </a>

                <button
                    type="button"
                    class="hs-collapse-toggle inline-flex size-9 items-center justify-center rounded-full border border-ink/10 text-ink transition hover:bg-ink/5 lg:hidden"
                    id="hs-navbar-collapse"
                    aria-expanded="false"
                    aria-controls="hs-navbar"
                    aria-label="Menü"
                    data-hs-collapse="#hs-navbar"
                >
                    <svg class="hs-collapse-open:hidden size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <svg class="hs-collapse-open:block hidden size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </nav>

        <div
            id="hs-navbar"
            class="hs-collapse hidden overflow-hidden border-t border-ink/5 transition-[height] duration-200 lg:hidden"
            aria-labelledby="hs-navbar-collapse"
            role="region"
        >
            <div class="mx-auto flex max-w-6xl flex-col gap-0.5 px-4 py-2 sm:px-6">
                <a href="{{ route('home') }}" class="rounded-xl px-3 py-2 text-sm text-ink-2 transition hover:bg-ink/5 hover:text-ink">Anasayfa</a>
                <a href="{{ route('sites.index') }}" class="rounded-xl px-3 py-2 text-sm text-ink-2 transition hover:bg-ink/5 hover:text-ink">Tüm Siteler</a>
                <a href="{{ url('/basin-bulteni') }}" class="rounded-xl px-3 py-2 text-sm text-ink-2 transition hover:bg-ink/5 hover:text-ink">Basın Bülteni</a>
                <a href="{{ url('/backlink-paketleri') }}" class="rounded-xl px-3 py-2 text-sm text-ink-2 transition hover:bg-ink/5 hover:text-ink">Tanıtım Paketleri</a>
                <a href="{{ url('/story-satis') }}" class="rounded-xl px-3 py-2 text-sm text-ink-2 transition hover:bg-ink/5 hover:text-ink">Story Satış</a>
                <a href="{{ url('/footer-link') }}" class="rounded-xl px-3 py-2 text-sm text-ink-2 transition hover:bg-ink/5 hover:text-ink">Footer Link</a>
                <a href="tel:08503052241" class="rounded-xl px-3 py-2 text-sm font-medium text-ink transition hover:bg-ink/5">0850 305 22 41</a>
            </div>
        </div>
    </div>
</header>
