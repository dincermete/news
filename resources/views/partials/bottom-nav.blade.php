@php
    $cartCount = (int) ($cartCount ?? 0);
    $bottomNavActive = 'flex flex-col items-center justify-center gap-0.5 text-ink font-semibold';
    $bottomNavInactive = 'flex flex-col items-center justify-center gap-0.5 text-ink-3 transition hover:text-ink';

    $bottomNavItems = [
        [
            'label' => 'Anasayfa',
            'url' => route('home'),
            'active' => request()->routeIs('home'),
            'icon' => 'M2.25 21h19.5M3 21V9.75L12 3l9 6.75V21M9.75 21v-6a1.5 1.5 0 0 1 1.5-1.5h1.5a1.5 1.5 0 0 1 1.5 1.5v6',
        ],
        [
            'label' => 'Siteler',
            'url' => route('sites.index'),
            'active' => request()->routeIs('sites.*'),
            'icon' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z',
        ],
        [
            'label' => 'Sepet',
            'url' => route('cart.index'),
            'active' => request()->routeIs('cart.*'),
            'icon' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
            'badge' => $cartCount,
        ],
        [
            'label' => 'Favoriler',
            'url' => route('account.favorites'),
            'active' => request()->routeIs('account.favorites'),
            'icon' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z',
        ],
        [
            'label' => 'Hesabım',
            'url' => auth()->check() ? route('account.dashboard') : route('login'),
            'active' => request()->routeIs('account.*') && ! request()->routeIs('account.favorites') || request()->routeIs('login') || request()->routeIs('register'),
            'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
        ],
    ];
@endphp

<nav
    class="mobile-floating-widget fixed inset-x-3 bottom-[calc(0.75rem+env(safe-area-inset-bottom))] z-40 rounded-md border border-ink/10 bg-white/95 backdrop-blur-md shadow-pop xl:hidden"
    aria-label="Alt menü"
>
    <div class="grid grid-cols-5 rounded-md overflow-hidden">
        @foreach ($bottomNavItems as $item)
            <a href="{{ $item['url'] }}" @class([$item['active'] ? $bottomNavActive : $bottomNavInactive, 'relative py-2'])>
                <svg class="size-5.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="{{ $item['active'] ? 2 : 1.5 }}" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                </svg>
                <span @class(['text-[10px]', 'font-semibold' => $item['active'], 'font-medium' => ! $item['active']])>{{ $item['label'] }}</span>

                @if (! empty($item['badge']))
                    <span class="absolute end-[calc(50%-1.25rem)] top-1 inline-flex size-4 items-center justify-center rounded-full bg-brand-500 text-[9px] font-bold text-white">
                        {{ $item['badge'] > 9 ? '9+' : $item['badge'] }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>
</nav>
