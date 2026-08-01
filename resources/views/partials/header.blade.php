@php
    $cartCount = (int) ($cartCount ?? 0);
    $navLink = 'rounded-full px-3.5 py-2 text-[13px] font-medium text-ink-2 transition hover:bg-ink/5 hover:text-ink';
    $navLinkActive = 'rounded-full bg-ink/5 px-3.5 py-2 text-[13px] font-medium text-ink';
    $drawerLink = 'flex items-center justify-between gap-3 rounded-2xl px-4 py-3.5 text-[15px] font-medium transition';
    $phone = $siteSettings->support_phone ?: '08503052241';
    $phoneDisplay = $siteSettings->support_phone_display ?: '0850 305 22 41';

    $currentKategori = request()->routeIs('sites.index') ? request()->query('kategori') : null;

    $categoryNav = \App\Models\SiteCategory::query()
        ->orderBy('name')
        ->get(['name', 'slug'])
        ->map(fn (\App\Models\SiteCategory $category): array => [
            'label' => $category->name,
            'url' => route('sites.index', ['kategori' => $category->slug]),
            'active' => $currentKategori === $category->slug,
        ])
        ->all();

    $allSitesNav = [
        ['label' => 'Tüm Siteler', 'url' => route('sites.index'), 'active' => request()->routeIs('sites.index') && $currentKategori === null],
        ['label' => 'Basın Bülteni', 'url' => route('press-release.index'), 'active' => request()->routeIs('press-release.*')],
        ['label' => 'Footer Link', 'url' => route('footer-links.index'), 'active' => request()->routeIs('footer-links.*')],
    ];

    $packagesNav = [
        ['label' => 'Tanıtım Paketleri', 'url' => route('bundles.index'), 'active' => request()->routeIs('bundles.*')],
        ['label' => 'SEO Paketleri', 'url' => route('seo-packages.index'), 'active' => request()->routeIs('seo-packages.*')],
        ['label' => 'Backlink Paketleri', 'url' => route('backlink-packages.index'), 'active' => request()->routeIs('backlink-packages.*')],
        ['label' => 'GEO', 'url' => route('geo.index'), 'active' => request()->routeIs('geo.*')],
    ];

    $navGroups = [
        ['key' => 'kategoriler', 'label' => 'Kategoriler', 'items' => $categoryNav],
        ['key' => 'tum-siteler', 'label' => 'Tüm Siteler', 'items' => $allSitesNav],
        ['key' => 'paketler', 'label' => 'Paketler', 'items' => $packagesNav],
    ];

    $companyNav = [
        ['label' => 'Hakkımızda', 'url' => route('about.show'), 'active' => request()->routeIs('about.*')],
        ['label' => 'Blog', 'url' => route('blog.index'), 'active' => request()->routeIs('blog.*')],
        ['label' => 'İletişim', 'url' => route('contact.show'), 'active' => request()->routeIs('contact.*')],
    ];
@endphp

<header
    class="sticky top-0 z-40"
    x-data="{ mobileOpen: false }"
    x-init="$watch('mobileOpen', (open) => document.documentElement.classList.toggle('overflow-hidden', open))"
    @keydown.escape.window="mobileOpen = false"
>
    <div class="border-b border-ink/5 bg-white/90 backdrop-blur-md">
        <nav class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8" aria-label="Ana menü">
            <a href="{{ route('home') }}" class="inline-flex shrink-0 items-center gap-x-2 focus:outline-hidden">
                @if ($siteSettings->logoUrl())
                    <img src="{{ $siteSettings->logoUrl() }}" alt="{{ $siteSettings->siteName() }}" class="h-8 w-auto">
                @else
                    <span class="inline-flex size-8 items-center justify-center rounded-xl bg-gradient-to-br from-accent-500 to-accent-700 text-white">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </span>
                    <span class="font-display text-lg font-semibold tracking-tight text-ink">
                        {{ $siteSettings->siteName() }}
                    </span>
                @endif
            </a>

            <div class="hidden min-w-0 flex-1 items-center justify-center gap-x-0.5 lg:flex xl:gap-x-1" role="navigation">
                <a href="{{ route('home') }}" @class([request()->routeIs('home') ? $navLinkActive : $navLink, 'shrink-0 whitespace-nowrap'])>Anasayfa</a>

                @foreach ($navGroups as $group)
                    @php $groupActive = collect($group['items'])->contains(fn (array $item): bool => $item['active']); @endphp
                    <div
                        class="relative shrink-0"
                        x-data="{ open: false }"
                        @keydown.escape.window="open = false"
                    >
                        <button
                            type="button"
                            @click="open = !open"
                            @class([
                                $groupActive ? $navLinkActive : $navLink,
                                'inline-flex shrink-0 items-center gap-x-1.5 whitespace-nowrap',
                            ])
                            :aria-expanded="open.toString()"
                            aria-haspopup="true"
                        >
                            {{ $group['label'] }}
                            <svg class="size-3.5 shrink-0 opacity-60 transition" :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                        </button>

                        <div
                            x-show="open"
                            x-cloak
                            x-transition
                            @click.outside="open = false"
                            class="absolute start-1/2 z-50 mt-3 max-h-[80vh] w-64 -translate-x-1/2 overflow-y-auto rounded-2xl border border-ink/10 bg-white p-2 shadow-pop"
                        >
                            @foreach ($group['items'] as $item)
                                <a
                                    href="{{ $item['url'] }}"
                                    @click="open = false"
                                    @class([
                                        'block rounded-xl px-4 py-2.5 text-sm font-medium whitespace-nowrap transition',
                                        $item['active'] ? 'bg-ink/5 text-ink' : 'text-ink-2 hover:bg-ink/5 hover:text-ink',
                                    ])
                                >{{ $item['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <a href="{{ route('account.site-submissions') }}" @class([request()->routeIs('account.site-submissions') ? $navLinkActive : $navLink, 'shrink-0 whitespace-nowrap'])>Siteni Ekle</a>
            </div>

            <div class="flex shrink-0 items-center gap-x-1 sm:gap-x-1.5">
                <div
                    class="relative hidden sm:block"
                    x-data="{ open: false }"
                    @keydown.escape.window="open = false"
                    x-init="$watch('open', (value) => { if (value) $nextTick(() => $refs.headerSearchInput?.focus()) })"
                >
                    <button
                        type="button"
                        class="inline-flex size-9 items-center justify-center rounded-full text-ink-2 transition hover:bg-ink/5 hover:text-ink focus:outline-hidden"
                        aria-label="Ara"
                        @click="open = !open"
                        :aria-expanded="open.toString()"
                    >
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        @click.outside="open = false"
                        class="absolute end-0 z-50 mt-2 w-72 rounded-2xl border border-ink/10 bg-white p-2 shadow-pop sm:w-80"
                    >
                        <form method="get" action="{{ route('sites.index') }}" class="flex items-center gap-1.5" role="search">
                            <svg class="ms-2 size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                            <input
                                type="search"
                                name="q"
                                x-ref="headerSearchInput"
                                placeholder="Site ara, ör. habergazetesi.com.tr"
                                class="w-full border-0 bg-transparent p-0 py-1.5 text-sm text-ink placeholder:text-ink-3 focus:ring-0"
                                aria-label="Site ara"
                            >
                            <button type="submit" class="inline-flex shrink-0 items-center justify-center rounded-full bg-ink px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-black">
                                Ara
                            </button>
                        </form>
                    </div>
                </div>

                <div
                    class="relative"
                    x-data="notificationBell({
                        unread: {{ (int) ($headerUnreadCount ?? 0) }},
                        items: {{ \Illuminate\Support\Js::from($headerBellItems ?? []) }},
                        markUrlTemplate: @js(auth()->check() ? url('/bildirimler/__ID__/oku') : ''),
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
                            class="absolute -end-0.5 -top-0.5 inline-flex min-w-4 items-center justify-center rounded-full bg-brand-500 px-1 text-[10px] font-bold leading-4 text-white ring-2 ring-white"
                            x-text="unread > 9 ? '9+' : unread"
                        ></span>
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="translate-y-1 scale-[0.98] opacity-0"
                        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click.outside="close()"
                        class="absolute end-0 z-50 mt-2 w-[min(100vw-2rem,22rem)] overflow-hidden rounded-2xl border border-ink/10 bg-white shadow-pop"
                    >
                        <div class="flex items-center justify-between gap-3 border-b border-ink/5 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-ink">Bildirimler</p>
                                <p class="mt-0.5 text-[11px] font-medium text-ink-3" x-show="unread > 0" x-cloak>
                                    <span x-text="unread"></span> okunmamış
                                </p>
                                <p class="mt-0.5 text-[11px] font-medium text-ink-3" x-show="unread === 0">Hepsi güncel</p>
                            </div>
                            <button
                                type="button"
                                class="rounded-full px-2.5 py-1 text-[11px] font-semibold text-accent-600 transition hover:bg-accent-50 disabled:pointer-events-none disabled:opacity-40"
                                x-show="unread > 0"
                                x-cloak
                                @click="markAllRead()"
                            >Tümünü oku</button>
                        </div>

                        <div class="max-h-[22rem] overflow-y-auto overscroll-contain p-2">
                            <template x-if="items.length === 0">
                                <div class="flex flex-col items-center px-4 py-10 text-center">
                                    <span class="inline-flex size-12 items-center justify-center rounded-2xl bg-paper text-ink-3">
                                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                                    </span>
                                    <p class="mt-3 text-sm font-semibold text-ink">Henüz bildirim yok</p>
                                    <p class="mt-1 text-xs text-ink-3">Yeni duyurular burada görünecek.</p>
                                </div>
                            </template>

                            <template x-for="item in items" :key="item.id">
                                <button
                                    type="button"
                                    class="group flex w-full items-start gap-3 rounded-xl px-2.5 py-2.5 text-left transition hover:bg-paper"
                                    :class="{ 'bg-accent-50/70': !item.read_at }"
                                    @click="markRead(item)"
                                >
                                    <span
                                        class="relative mt-0.5 inline-flex size-10 shrink-0 items-center justify-center rounded-xl text-white"
                                        :class="item.kind === 'announcement' ? 'bg-gradient-to-b from-accent-500 to-accent-700' : 'bg-gradient-to-b from-black to-[#363b3c]'"
                                        aria-hidden="true"
                                    >
                                        <template x-if="item.kind === 'announcement'">
                                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.172.10.0.1.2.172.16.0.2.10.0.1.2.0.1.05.418-.024.1-.017.17-.038.254-.064a49.003 49.003 0 0 0 4.928-2.405c.25-.137.4-.4.4-.684V8.288c0-.284-.15-.547-.4-.684a49.003 49.003 0 0 0-4.928-2.405c-.084-.026-.17-.047-.254-.064-.148-.021-.292-.007-.418.024a.75.75 0 0 0-.47.386c-.4.891-.732 1.821-.985 2.783m0 9.18v-9.18" /></svg>
                                        </template>
                                        <template x-if="item.kind !== 'announcement'">
                                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                                        </template>
                                        <span
                                            x-show="!item.read_at"
                                            class="absolute -end-0.5 -top-0.5 size-2.5 rounded-full bg-brand-500 ring-2 ring-white"
                                        ></span>
                                    </span>

                                    <span class="min-w-0 flex-1">
                                        <span class="flex items-start justify-between gap-2">
                                            <span class="truncate text-sm font-semibold text-ink" x-text="item.title"></span>
                                            <span
                                                class="shrink-0 rounded-full px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide"
                                                :class="item.kind === 'announcement' ? 'bg-accent-100 text-accent-700' : 'bg-ink/5 text-ink-2'"
                                                x-text="item.kind === 'announcement' ? 'Duyuru' : 'Hesap'"
                                            ></span>
                                        </span>
                                        <span class="mt-0.5 line-clamp-2 text-[12px] font-medium leading-relaxed text-ink-2" x-text="item.body"></span>
                                        <span class="mt-1.5 flex items-center gap-1.5 text-[11px] font-medium text-ink-3">
                                            <span x-text="item.created_at"></span>
                                            <template x-if="!item.read_at">
                                                <span class="inline-flex items-center gap-1 text-emerald-600">
                                                    <span class="size-1 rounded-full bg-emerald-500"></span>
                                                    Yeni
                                                </span>
                                            </template>
                                        </span>
                                    </span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <a href="{{ auth()->check() ? route('account.dashboard') : route('login') }}" class="inline-flex size-9 items-center justify-center rounded-full text-ink-2 transition hover:bg-ink/5 hover:text-ink focus:outline-hidden" aria-label="Hesabım">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                </a>

                <a
                    href="{{ route('cart.index') }}"
                    class="group relative ms-1 inline-flex items-center gap-x-2 rounded-2xl bg-ink px-3 py-2.5 text-xs font-medium text-white transition hover:bg-black hover:scale-[1.03] active:scale-[0.98] focus:outline-hidden sm:px-5"
                >
                    <span class="hidden sm:inline">Sepet</span>
                    <svg class="size-3.5 shrink-0 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5 19.5 4.5m0 0H8.25m11.25 0v11.25" /></svg>
                    <span class="absolute -end-1.5 -top-1.5 inline-flex size-4 items-center justify-center rounded-full bg-brand-500 text-[10px] font-bold text-white">
                        {{ $cartCount }}
                    </span>
                </a>

                <button
                    type="button"
                    class="inline-flex size-9 items-center justify-center rounded-full border border-ink/10 text-ink transition hover:bg-ink/5 lg:hidden"
                    @click="mobileOpen = true"
                    :aria-expanded="mobileOpen.toString()"
                    aria-controls="mobile-nav-drawer"
                    aria-label="Menüyü aç"
                >
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>
        </nav>
    </div>

    {{-- Mobile side drawer --}}
    <div
        class="lg:hidden"
        x-cloak
        x-show="mobileOpen"
        x-transition.opacity.duration.200ms
        role="dialog"
        aria-modal="true"
        aria-label="Mobil menü"
    >
        <div
            class="fixed inset-0 z-[60] bg-ink/40 backdrop-blur-[2px]"
            @click="mobileOpen = false"
            aria-hidden="true"
        ></div>

        <div
            id="mobile-nav-drawer"
            class="fixed inset-y-0 end-0 z-[70] flex w-[min(100vw-2.5rem,22rem)] flex-col bg-white shadow-pop"
            x-show="mobileOpen"
            x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            @click.stop
        >
            <div class="flex items-center justify-between gap-3 border-b border-ink/5 px-4 py-3.5">
                <div class="min-w-0">
                    <p class="truncate font-display text-base font-semibold text-ink">{{ $siteSettings->siteName() }}</p>
                    <p class="text-[11px] font-medium text-ink-3">Menü</p>
                </div>
                <button
                    type="button"
                    class="inline-flex size-9 shrink-0 items-center justify-center rounded-full border border-ink/10 text-ink transition hover:bg-ink/5"
                    @click="mobileOpen = false"
                    aria-label="Menüyü kapat"
                >
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto overscroll-contain px-3 py-4">
                <form method="get" action="{{ route('sites.index') }}" class="mb-5 flex items-center gap-2 rounded-2xl border border-ink/10 bg-paper px-3 py-2" role="search">
                    <svg class="size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <input
                        type="search"
                        name="q"
                        placeholder="Site ara…"
                        class="w-full border-0 bg-transparent p-0 text-sm text-ink placeholder:text-ink-3 focus:ring-0"
                        aria-label="Site ara"
                    >
                </form>

                <div class="mb-5 space-y-0.5">
                    <a
                        href="{{ route('home') }}"
                        @click="mobileOpen = false"
                        @class([
                            $drawerLink,
                            request()->routeIs('home') ? 'bg-ink text-white' : 'text-ink hover:bg-ink/5',
                        ])
                    >
                        <span>Anasayfa</span>
                    </a>
                </div>

                @foreach ($navGroups as $group)
                    <p class="px-4 pb-1.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-ink-3">{{ $group['label'] }}</p>
                    <div class="mb-5 space-y-0.5">
                        @foreach ($group['items'] as $item)
                            <a
                                href="{{ $item['url'] }}"
                                @click="mobileOpen = false"
                                @class([
                                    $drawerLink,
                                    $item['active'] ? 'bg-ink text-white' : 'text-ink hover:bg-ink/5',
                                ])
                            >
                                <span>{{ $item['label'] }}</span>
                                <svg class="size-3.5 opacity-40" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                            </a>
                        @endforeach
                    </div>
                @endforeach

                <p class="px-4 pb-1.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-ink-3">Kurumsal</p>
                <div class="space-y-0.5">
                    @foreach ($companyNav as $item)
                        <a
                            href="{{ $item['url'] }}"
                            @click="mobileOpen = false"
                            @class([
                                $drawerLink,
                                $item['active'] ? 'bg-ink text-white' : 'text-ink hover:bg-ink/5',
                            ])
                        >
                            <span>{{ $item['label'] }}</span>
                            <svg class="size-3.5 opacity-40" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="border-t border-ink/5 bg-paper p-4">
                <a
                    href="tel:{{ $phone }}"
                    class="mb-2 flex items-center justify-center gap-2 rounded-2xl border border-ink/10 bg-white px-4 py-3 text-sm font-semibold text-ink"
                >
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                    {{ $phoneDisplay }}
                </a>
                <a
                    href="{{ route('cart.index') }}"
                    @click="mobileOpen = false"
                    class="flex items-center justify-center gap-2 rounded-2xl bg-ink px-4 py-3 text-sm font-semibold text-white"
                >
                    Sepete git
                    @if ($cartCount > 0)
                        <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-brand-500 px-1.5 text-[11px]">{{ $cartCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>
</header>
