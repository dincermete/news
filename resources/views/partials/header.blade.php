@php
    $cartCount = (int) ($cartCount ?? 0);
    $navLink = 'rounded-md px-3.5 py-2 text-[13px] font-medium text-ink-2 transition hover:bg-ink/5 hover:text-ink';
    $navLinkActive = 'rounded-md bg-accent-50 px-3.5 py-2 text-[13px] font-semibold text-accent-700';
    $phone = $siteSettings->support_phone ?: '08503052241';
    $phoneDisplay = $siteSettings->support_phone_display ?: '0850 305 22 41';

    // Icon paths reused verbatim from elsewhere in this app (already verified to render correctly).
    $icons = [
        'phone' => ['M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z'],
        'home' => ['M2.25 21h19.5M3 21V9.75L12 3l9 6.75V21M9.75 21v-6a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v6'],
        'document' => ['M9 12h3.75M9 15h3.75M9 18h3.75m3-15H9.75A2.25 2.25 0 0 0 7.5 5.25v13.5A2.25 2.25 0 0 0 9.75 21h4.5a2.25 2.25 0 0 0 2.25-2.25V6.108c0-.318-.126-.622-.351-.847L14.643 3.75a1.5 1.5 0 0 0-1.06-.44H12'],
        'envelope' => ['M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75'],
        'eye' => ['M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z', 'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
        'bell' => ['M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0'],
        'link' => ['M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z'],
        'trending' => ['M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941'],
        'globe' => ['M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c1.657 0 3-4.03 3-9s-1.343-9-3-9-3 4.03-3 9 1.343 9 3 9Zm-8.716-6.747h17.432M3.284 9.747h17.432'],
        'briefcase' => ['M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-4.5A3.375 3.375 0 0 0 12.75 11h-1.5A3.375 3.375 0 0 0 8 14.25V18.75m9-12.75v-1.5a3 3 0 0 0-3-3h-3a3 3 0 0 0-3 3v1.5'],
        'sparkle' => ['M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z'],
    ];

    $sitesExploreNav = [
        ['label' => 'Tüm Siteler', 'url' => route('sites.index'), 'active' => request()->routeIs('sites.index'), 'icon' => $icons['eye'], 'description' => 'Kataloğun tamamını gözden geçirin'],
        ['label' => 'Basın Bülteni', 'url' => route('press-release.index'), 'active' => request()->routeIs('press-release.*'), 'icon' => $icons['bell'], 'description' => 'Geniş dağıtımlı basın bülteni hizmeti'],
        ['label' => 'Footer Link', 'url' => route('footer-links.index'), 'active' => request()->routeIs('footer-links.*'), 'icon' => $icons['link'], 'description' => 'Site altbilgisinde kalıcı backlink'],
    ];

    $packagesNav = [
        ['label' => 'Tanıtım Paketleri', 'url' => route('bundles.index'), 'active' => request()->routeIs('bundles.*'), 'icon' => $icons['document'], 'description' => 'Çoklu site tanıtım yazısı paketleri'],
        ['label' => 'SEO Paketleri', 'url' => route('seo-packages.index'), 'active' => request()->routeIs('seo-packages.*'), 'icon' => $icons['trending'], 'description' => 'Aylık, anahtar kelime bazlı SEO çalışması'],
        ['label' => 'Backlink Paketleri', 'url' => route('backlink-packages.index'), 'active' => request()->routeIs('backlink-packages.*'), 'icon' => $icons['link'], 'description' => 'Rekabet seviyesine göre backlink paketleri'],
        ['label' => 'GEO', 'url' => route('geo.index'), 'active' => request()->routeIs('geo.*'), 'icon' => $icons['globe'], 'description' => 'Yapay zekâ aramalarında görünürlük'],
    ];

    $currentServiceSlug = request()->routeIs('agency-services.show') ? request()->route('slug') : null;

    $servicesNav = collect(\App\Support\AgencyServicePages::all())
        ->map(fn (array $service): array => [
            'label' => $service['name'],
            'url' => route('agency-services.show', $service['slug']),
            'active' => $currentServiceSlug === $service['slug'],
            'icon' => [$service['icon']],
            'description' => $service['excerpt'],
        ])
        ->all();

    $currentToolSlug = request()->routeIs('tools.show') ? request()->route('slug') : null;

    $toolsSections = [];

    foreach (\App\Support\Tools::categories() as $categoryKey => $categoryLabel) {
        $toolsSections[] = [
            'label' => $categoryLabel,
            'items' => collect(\App\Support\Tools::grouped()[$categoryKey] ?? [])
                ->map(fn (array $tool): array => [
                    'label' => $tool['name'],
                    'url' => route('tools.show', $tool['slug']),
                    'active' => $currentToolSlug === $tool['slug'],
                    'icon' => [$tool['icon']],
                    'description' => $tool['excerpt'] ?? null,
                ])
                ->all(),
        ];
    }

    $navGroups = [
        [
            'key' => 'siteler',
            'label' => 'Siteler',
            'url' => route('sites.index'),
            'layout' => 'list',
            'sections' => [
                ['label' => null, 'variant' => 'icons', 'items' => $sitesExploreNav],
            ],
            'footer' => [
                ['label' => 'Tüm Siteleri Gör', 'url' => route('sites.index'), 'icon' => $icons['eye']],
                ['label' => 'Siteni Ücretsiz Ekle', 'url' => route('account.site-submissions'), 'icon' => $icons['sparkle']],
            ],
        ],
        [
            'key' => 'paketler',
            'label' => 'Paketler',
            'url' => route('bundles.index'),
            'layout' => 'grid2',
            'sections' => [
                ['label' => null, 'variant' => 'icons', 'items' => $packagesNav],
            ],
            'footer' => [
                ['label' => 'Tüm Paketleri Gör', 'url' => route('bundles.index'), 'icon' => $icons['document']],
                ['label' => 'Bize Ulaşın', 'url' => route('contact.show'), 'icon' => $icons['envelope']],
            ],
        ],
        [
            'key' => 'hizmetler',
            'label' => 'Hizmetler',
            'url' => route('agency-services.index'),
            'layout' => 'grid2',
            'sections' => [
                ['label' => null, 'variant' => 'icons', 'items' => $servicesNav],
            ],
            'footer' => [
                ['label' => 'Tüm Hizmetleri Gör', 'url' => route('agency-services.index'), 'icon' => $icons['briefcase']],
                ['label' => 'Bize Ulaşın', 'url' => route('contact.show'), 'icon' => $icons['envelope']],
            ],
        ],
        [
            'key' => 'araclar',
            'label' => 'Araçlar',
            'url' => route('tools.index'),
            'layout' => 'columns',
            'sections' => collect($toolsSections)
                ->map(fn (array $section): array => [...$section, 'variant' => 'icons'])
                ->all(),
            'footer' => [
                ['label' => 'Tüm Araçları Gör', 'url' => route('tools.index'), 'icon' => $icons['sparkle']],
                ['label' => 'Ücretsiz SEO Analizi', 'url' => route('free-analysis.show'), 'icon' => $icons['trending']],
            ],
        ],
    ];

    $companyNav = [
        ['label' => 'Hakkımızda', 'url' => route('about.show'), 'active' => request()->routeIs('about.*'), 'icon' => $icons['home']],
        ['label' => 'Blog', 'url' => route('blog.index'), 'active' => request()->routeIs('blog.*'), 'icon' => $icons['document']],
        ['label' => 'İletişim', 'url' => route('contact.show'), 'active' => request()->routeIs('contact.*'), 'icon' => $icons['envelope']],
    ];
@endphp

<header
    class="sticky top-0 z-[55]"
    x-data="{ mobileOpen: false }"
    x-init="$watch('mobileOpen', (open) => {
        document.documentElement.classList.toggle('overflow-hidden', open);
        document.documentElement.classList.toggle('mobile-menu-open', open);
    })"
    @keydown.escape.window="mobileOpen = false"
>
    {{-- Utility bar --}}
    <div class="hidden bg-ink text-white/70 sm:block">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-2 text-xs sm:px-6 lg:px-8">
            <div class="flex items-center gap-x-5">
                <a href="tel:{{ $phone }}" class="inline-flex items-center gap-x-1.5 transition hover:text-white">
                    <svg class="size-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['phone'][0] }}"/></svg>
                    {{ $phoneDisplay }}
                </a>
                <a href="{{ route('account.site-submissions') }}" class="hidden items-center gap-x-1.5 transition hover:text-white md:inline-flex">
                    <svg class="size-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['home'][0] }}"/></svg>
                    Siteni Ekle
                </a>
            </div>

            <div class="flex items-center gap-x-5">
                <a href="{{ route('about.show') }}" class="hidden transition hover:text-white md:inline">Hakkımızda</a>
                <a href="{{ route('blog.index') }}" class="hidden transition hover:text-white md:inline">Blog</a>
                <a href="{{ route('contact.show') }}" class="transition hover:text-white">İletişim</a>
                @guest
                    <span class="h-3 w-px bg-white/15" aria-hidden="true"></span>
                    <button type="button" @click="window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: { tab: 'login' } }))" class="transition hover:text-white">Giriş Yap</button>
                    <button type="button" @click="window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: { tab: 'register' } }))" class="font-semibold text-white transition hover:text-white/80">Kayıt Ol</button>
                @endguest
            </div>
        </div>
    </div>

    {{-- Main nav --}}
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

            <div
                class="relative hidden min-w-0 flex-1 items-center justify-center gap-x-0.5 xl:flex xl:gap-x-1"
                role="navigation"
                x-data="{ openGroup: null, closeTimer: null }"
                @keydown.escape.window="openGroup = null"
            >
                <a href="{{ route('home') }}" @class([request()->routeIs('home') ? $navLinkActive : $navLink, 'shrink-0 whitespace-nowrap'])>Anasayfa</a>

                @foreach ($navGroups as $group)
                    @php
                        $groupActive = collect($group['sections'])
                            ->flatMap(fn (array $section): array => $section['items'])
                            ->contains(fn (array $item): bool => $item['active']);
                        $sectionsGridClass = match ($group['layout']) {
                            'columns' => 'grid gap-x-8 gap-y-6 sm:grid-cols-'.min(count($group['sections']), 4),
                            default => '',
                        };
                        $panelWidthClass = match ($group['layout']) {
                            'columns' => 'w-[min(92vw,46rem)]',
                            'grid2' => 'w-[min(92vw,34rem)]',
                            default => 'w-[min(92vw,24rem)]',
                        };
                    @endphp
                    <div
                        class="relative shrink-0"
                        @mouseenter="clearTimeout(closeTimer); openGroup = '{{ $group['key'] }}'"
                        @mouseleave="closeTimer = setTimeout(() => { if (openGroup === '{{ $group['key'] }}') openGroup = null }, 100)"
                    >
                        <a
                            href="{{ $group['url'] }}"
                            @click="openGroup = null"
                            @focus="clearTimeout(closeTimer); openGroup = '{{ $group['key'] }}'"
                            @class([
                                $groupActive ? $navLinkActive : $navLink,
                                'inline-flex shrink-0 items-center gap-x-1.5 whitespace-nowrap',
                            ])
                            :aria-expanded="(openGroup === '{{ $group['key'] }}').toString()"
                            aria-haspopup="true"
                        >
                            {{ $group['label'] }}
                            <svg class="size-3.5 shrink-0 opacity-60 transition" :class="openGroup === '{{ $group['key'] }}' && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                        </a>

                        <div
                            x-show="openGroup === '{{ $group['key'] }}'"
                            x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-1"
                            @click.outside="if (openGroup === '{{ $group['key'] }}') openGroup = null"
                            @class([
                                'absolute start-1/2 top-full z-50 mt-3 -translate-x-1/2 overflow-hidden rounded-lg border border-ink/10 bg-white shadow-pop',
                                $panelWidthClass,
                            ])
                        >
                            <div @class([$sectionsGridClass, 'space-y-0.5' => $sectionsGridClass === '', 'max-h-[65vh] overflow-y-auto p-3'])>
                                @foreach ($group['sections'] as $section)
                                    @continue(empty($section['items']))
                                    <div>
                                        @if ($section['label'])
                                            <p class="px-3 pb-2 pt-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-ink-3">{{ $section['label'] }}</p>
                                        @endif

                                        <div @class(['space-y-0.5', 'grid grid-cols-1 gap-x-4 sm:grid-cols-2' => $group['layout'] === 'grid2'])>
                                            @foreach ($section['items'] as $item)
                                                @include('partials.nav-menu-item', ['item' => $item, 'onClick' => 'openGroup = null'])
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if (! empty($group['footer']))
                                <div class="grid grid-cols-2 divide-x divide-ink/10 border-t border-ink/10 bg-paper">
                                    @foreach ($group['footer'] as $footerItem)
                                        <a
                                            href="{{ $footerItem['url'] }}"
                                            @click="openGroup = null"
                                            class="flex items-center justify-center gap-x-2 p-3 text-[13px] font-semibold text-ink transition hover:bg-ink/5"
                                        >
                                            <svg class="size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                @foreach ((array) $footerItem['icon'] as $d)
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $d }}"/>
                                                @endforeach
                                            </svg>
                                            {{ $footerItem['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
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
                        class="inline-flex size-9 items-center justify-center rounded-md text-ink-2 transition hover:bg-ink/5 hover:text-ink focus:outline-hidden"
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
                        class="absolute end-0 z-50 mt-2 w-72 rounded-md border border-ink/10 bg-white p-2 shadow-pop sm:w-80"
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
                            <button type="submit" class="inline-flex shrink-0 items-center justify-center rounded-md bg-ink px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-black">
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
                        class="relative inline-flex size-9 items-center justify-center rounded-md text-ink-2 transition hover:bg-ink/5 hover:text-ink focus:outline-hidden"
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
                        class="absolute end-0 z-50 mt-2 w-[min(100vw-2rem,22rem)] overflow-hidden rounded-md border border-ink/10 bg-white shadow-pop"
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
                                class="rounded-md px-2.5 py-1 text-[11px] font-semibold text-accent-600 transition hover:bg-accent-50 disabled:pointer-events-none disabled:opacity-40"
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
                                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
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

                @auth
                    <a href="{{ route('account.dashboard') }}" class="hidden size-9 items-center justify-center rounded-md text-ink-2 transition hover:bg-ink/5 hover:text-ink focus:outline-hidden xl:inline-flex" aria-label="Hesabım">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                    </a>
                @else
                    <button
                        type="button"
                        @click="window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: { tab: 'login' } }))"
                        class="hidden size-9 items-center justify-center rounded-md text-ink-2 transition hover:bg-ink/5 hover:text-ink focus:outline-hidden xl:inline-flex"
                        aria-label="Hesabım"
                    >
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                    </button>
                @endauth

                <a
                    href="{{ route('cart.index') }}"
                    class="group relative ms-1 hidden items-center gap-x-2 rounded-md bg-ink px-3 py-2.5 text-xs font-medium text-white transition hover:bg-black hover:scale-[1.03] active:scale-[0.98] focus:outline-hidden xl:inline-flex"
                >
                    <span class="hidden sm:inline">Sepet</span>
                    <svg class="size-3.5 shrink-0 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5 19.5 4.5m0 0H8.25m11.25 0v11.25" /></svg>
                    <span class="absolute -end-1.5 -top-1.5 inline-flex size-4 items-center justify-center rounded-full bg-brand-500 text-[10px] font-bold text-white">
                        {{ $cartCount }}
                    </span>
                </a>

                <button
                    type="button"
                    class="inline-flex size-9 items-center justify-center rounded-md border border-ink/10 text-ink transition hover:bg-ink/5 xl:hidden"
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
        class="xl:hidden"
        role="dialog"
        aria-modal="true"
        aria-label="Mobil menü"
    >
        <div
            class="fixed inset-0 z-[60] bg-ink/40 backdrop-blur-[2px]"
            x-cloak
            x-show="mobileOpen"
            x-transition.opacity.duration.200ms
            @click="mobileOpen = false"
            aria-hidden="true"
        ></div>

        <div
            id="mobile-nav-drawer"
            class="fixed inset-x-4 top-4 z-[70] flex max-h-[calc(100vh-2rem)] flex-col overflow-hidden rounded-2xl bg-white shadow-pop sm:inset-x-auto sm:end-4 sm:w-[22rem]"
            x-cloak
            x-show="mobileOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.stop
        >
            <div class="flex shrink-0 items-center justify-between gap-3 border-b border-ink/5 px-4 py-3.5">
                <a href="{{ route('home') }}" @click="mobileOpen = false" class="inline-flex min-w-0 items-center gap-x-2">
                    @if ($siteSettings->logoUrl())
                        <img src="{{ $siteSettings->logoUrl() }}" alt="{{ $siteSettings->siteName() }}" class="h-7 w-auto">
                    @else
                        <span class="inline-flex size-7 items-center justify-center rounded-md bg-gradient-to-br from-accent-500 to-accent-700 text-white">
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </span>
                        <span class="truncate font-display text-base font-semibold text-ink">{{ $siteSettings->siteName() }}</span>
                    @endif
                </a>
                <button
                    type="button"
                    class="inline-flex size-8 shrink-0 items-center justify-center rounded-md text-ink-3 transition hover:bg-paper hover:text-ink"
                    @click="mobileOpen = false"
                    aria-label="Menüyü kapat"
                >
                    <svg class="size-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto overscroll-contain px-4 py-3">
                <form method="get" action="{{ route('sites.index') }}" class="mb-3 flex items-center gap-2 rounded-md border border-ink/10 bg-paper px-3 py-2" role="search">
                    <svg class="size-4 shrink-0 text-ink-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <input
                        type="search"
                        name="q"
                        placeholder="Site ara…"
                        class="w-full border-0 bg-transparent p-0 text-sm text-ink placeholder:text-ink-3 focus:ring-0"
                        aria-label="Site ara"
                    >
                </form>

                <a
                    href="{{ route('home') }}"
                    @click="mobileOpen = false"
                    @class(['block px-1 py-3 text-[15px]', request()->routeIs('home') ? 'font-semibold text-ink' : 'font-medium text-ink-2 hover:text-ink'])
                >
                    Anasayfa
                </a>

                @foreach ($navGroups as $group)
                    @php
                        $groupActive = collect($group['sections'])
                            ->flatMap(fn (array $section): array => $section['items'])
                            ->contains(fn (array $item): bool => $item['active']);
                    @endphp
                    <div x-data="{ open: false }">
                        <button
                            type="button"
                            @click="open = !open"
                            @class(['flex w-full items-center justify-between gap-2 px-1 py-3 text-[15px]', $groupActive ? 'font-semibold text-ink' : 'font-medium text-ink-2'])
                            :aria-expanded="open.toString()"
                        >
                            {{ $group['label'] }}
                            <svg class="size-4 shrink-0 text-ink-3 transition" :class="open && 'rotate-180'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                        </button>
                        <div x-show="open" x-cloak x-transition class="space-y-2 pb-3 ps-3">
                            @foreach ($group['sections'] as $section)
                                @continue(empty($section['items']))
                                <div>
                                    @if ($section['label'])
                                        <p class="pb-1 text-[10px] font-semibold uppercase tracking-[0.1em] text-ink-3/70">{{ $section['label'] }}</p>
                                    @endif
                                    @foreach ($section['items'] as $item)
                                        <a
                                            href="{{ $item['url'] }}"
                                            @click="mobileOpen = false"
                                            @class(['block py-1.5 text-[14px]', $item['active'] ? 'font-semibold text-brand-600' : 'text-ink-2'])
                                        >
                                            {{ $item['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="my-3 border-t border-ink/5"></div>

                <div class="grid grid-cols-2 gap-x-4 gap-y-2.5 pb-2">
                    @foreach ($companyNav as $item)
                        <a href="{{ $item['url'] }}" @click="mobileOpen = false" class="text-[13px] font-medium text-ink-2 hover:text-ink">{{ $item['label'] }}</a>
                    @endforeach
                    <a href="{{ route('account.site-submissions') }}" @click="mobileOpen = false" class="text-[13px] font-medium text-ink-2 hover:text-ink">Siteni Ekle</a>
                    @guest
                        <button type="button" @click="mobileOpen = false; window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: { tab: 'login' } }))" class="text-start text-[13px] font-medium text-ink-2 hover:text-ink">Giriş Yap</button>
                        <button type="button" @click="mobileOpen = false; window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: { tab: 'register' } }))" class="text-start text-[13px] font-medium text-ink-2 hover:text-ink">Kayıt Ol</button>
                    @endguest
                </div>
            </div>

            <div class="shrink-0 space-y-2 border-t border-ink/5 p-4">
                <a
                    href="{{ route('cart.index') }}"
                    @click="mobileOpen = false"
                    class="flex items-center justify-center gap-2 rounded-md bg-ink px-4 py-3 text-sm font-semibold text-white"
                >
                    Sepete git
                    @if ($cartCount > 0)
                        <span class="inline-flex min-w-5 items-center justify-center rounded-full bg-brand-500 px-1.5 text-[11px]">{{ $cartCount }}</span>
                    @endif
                </a>
                <a
                    href="tel:{{ $phone }}"
                    class="flex items-center justify-center gap-2 rounded-md border border-ink/10 bg-white px-4 py-3 text-sm font-semibold text-ink"
                >
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                    {{ $phoneDisplay }}
                </a>
            </div>
        </div>
    </div>
</header>
