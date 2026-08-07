import Alpine from 'alpinejs';
import { HSStaticMethods } from 'preline/non-auto';
import { animate, inView, stagger } from 'motion';
import { annotate } from 'rough-notation';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('announcementBar', (items = []) => ({
        items,
        dismissed: [],
        init() {
            try {
                this.dismissed = JSON.parse(localStorage.getItem('nt_dismissed_announcements') || '[]');
            } catch {
                this.dismissed = [];
            }
        },
        get visible() {
            return this.items.filter((item) => !this.dismissed.includes(item.id));
        },
        dismiss(id) {
            if (!this.dismissed.includes(id)) {
                this.dismissed.push(id);
                try {
                    localStorage.setItem('nt_dismissed_announcements', JSON.stringify(this.dismissed));
                } catch {
                    // ignore quota / private mode
                }
            }
        },
    }));

    Alpine.data('notificationBell', (config = {}) => ({
        open: false,
        unread: Number(config.unread || 0),
        items: config.items || [],
        markUrlTemplate: config.markUrlTemplate || '',
        csrf: config.csrf || '',
        dismissedAnnouncementIds: [],

        init() {
            try {
                this.dismissedAnnouncementIds = JSON.parse(
                    localStorage.getItem('nt_dismissed_announcements') || '[]',
                );
            } catch {
                this.dismissedAnnouncementIds = [];
            }

            this.items = this.items.map((item) => {
                if (item.kind !== 'announcement') {
                    return item;
                }

                if (this.dismissedAnnouncementIds.includes(item.source_id)) {
                    return { ...item, read_at: item.read_at || new Date().toISOString() };
                }

                return item;
            });

            this.recalcUnread();
        },

        recalcUnread() {
            this.unread = this.items.filter((item) => !item.read_at).length;
        },

        toggle() {
            this.open = !this.open;
        },

        close() {
            this.open = false;
        },

        async markRead(item) {
            if (item.read_at) {
                return;
            }

            if (item.kind === 'announcement') {
                this.dismissAnnouncement(item);

                return;
            }

            if (!this.markUrlTemplate) {
                return;
            }

            const url = this.markUrlTemplate.replace('__ID__', String(item.source_id ?? item.id));

            try {
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    return;
                }

                item.read_at = new Date().toISOString();
                this.recalcUnread();
            } catch {
                // ignore network errors in UI
            }
        },

        dismissAnnouncement(item) {
            item.read_at = new Date().toISOString();

            if (!this.dismissedAnnouncementIds.includes(item.source_id)) {
                this.dismissedAnnouncementIds.push(item.source_id);

                try {
                    localStorage.setItem(
                        'nt_dismissed_announcements',
                        JSON.stringify(this.dismissedAnnouncementIds),
                    );
                } catch {
                    // ignore quota / private mode
                }
            }

            this.recalcUnread();
        },

        async markAllRead() {
            const unread = this.items.filter((item) => !item.read_at);

            for (const item of unread) {
                await this.markRead(item);
            }
        },
    }));

    // Sitedeki tüm "giriş gerekli" tetikleyicileri (sepete ekle, favorile, rapor
    // gör…) hâlâ bu tek olayı fırlatıyor; artık ayrı bir onay adımı göstermeden
    // doğrudan tabli auth modal'ı login sekmesinde açıyoruz.
    window.addEventListener('open-login-modal', () => {
        window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: { tab: 'login' } }));
    });

    Alpine.data('authModal', (options = {}) => ({
        open: options.initialOpen ?? false,
        tab: options.initialTab === 'register' ? 'register' : 'login',
        showPassword: false,
        showPasswordConfirm: false,
        init() {
            // Header dışından (JS event) açılış.
            window.addEventListener('open-auth-modal', (event) => {
                this.tab = event.detail?.tab === 'register' ? 'register' : 'login';
                this.open = true;
            });

            // /giris veya /kayitol'dan yönlendirilen ?auth=login|register — sunucu
            // yerine burada, Alpine tarafında okunup modal buna göre açılıyor.
            const authParam = new URLSearchParams(window.location.search).get('auth');
            if (authParam === 'login' || authParam === 'register') {
                this.tab = authParam;
                this.open = true;

                const url = new URL(window.location.href);
                url.searchParams.delete('auth');
                window.history.replaceState({}, '', url);
            }
        },
        close() {
            this.open = false;
        },
    }));

    Alpine.data('siteTable', () => ({
        openRows: {},
        sortKey: null,
        sortDir: 'asc',
        orderedIds: [],
        sorting: false,

        init() {
            this.syncOrder();
        },

        syncOrder() {
            this.orderedIds = [...this.$refs.table.querySelectorAll('[data-sort-group]')].map(
                (el) => Number(el.dataset.id),
            );
        },

        isFirst(id) {
            return this.orderedIds[0] === id;
        },

        isLast(id) {
            return this.orderedIds[this.orderedIds.length - 1] === id;
        },

        sortBy(key) {
            if (this.sortKey === key) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortKey = key;
                this.sortDir = key === 'domain' ? 'asc' : 'desc';
            }

            this.applySort();
        },

        applySort() {
            const table = this.$refs.table;
            const groups = [...table.querySelectorAll('[data-sort-group]')];
            const key = this.sortKey;
            const dir = this.sortDir === 'asc' ? 1 : -1;

            groups.sort((a, b) => this.compareGroups(a, b, key, dir));

            this.sorting = true;

            requestAnimationFrame(() => {
                groups.forEach((group) => table.appendChild(group));
                this.syncOrder();

                requestAnimationFrame(() => {
                    this.sorting = false;
                });
            });
        },

        compareGroups(a, b, key, dir) {
            const av = a.dataset[key] ?? '';
            const bv = b.dataset[key] ?? '';
            const aEmpty = av === '';
            const bEmpty = bv === '';

            if (aEmpty && bEmpty) {
                return 0;
            }

            if (aEmpty) {
                return 1;
            }

            if (bEmpty) {
                return -1;
            }

            if (key === 'domain') {
                return av.localeCompare(bv, 'tr', { sensitivity: 'base' }) * dir;
            }

            return (Number(av) - Number(bv)) * dir;
        },

        sortIconClass(key) {
            if (this.sortKey !== key) {
                return 'opacity-25';
            }

            return this.sortDir === 'desc' ? 'rotate-180 opacity-100' : 'opacity-100';
        },
    }));

    Alpine.data('autoSlider', (options = {}) => ({
        auto: options.auto ?? false,
        delay: options.delay ?? 4500,
        timer: null,
        init() {
            if (!this.auto || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            this.play();
            this.$refs.track.addEventListener('mouseenter', () => this.pause());
            this.$refs.track.addEventListener('mouseleave', () => this.play());
            this.$refs.track.addEventListener('touchstart', () => this.pause(), { passive: true });
        },
        step() {
            const track = this.$refs.track;
            const first = track.firstElementChild;
            if (!first) {
                return 0;
            }
            const gap = parseFloat(getComputedStyle(track).columnGap || '0');

            return first.getBoundingClientRect().width + gap;
        },
        next() {
            const track = this.$refs.track;
            const maxScroll = track.scrollWidth - track.clientWidth;
            track.scrollTo({
                left: track.scrollLeft >= maxScroll - 4 ? 0 : track.scrollLeft + this.step(),
                behavior: 'smooth',
            });
        },
        prev() {
            const track = this.$refs.track;
            const maxScroll = track.scrollWidth - track.clientWidth;
            track.scrollTo({
                left: track.scrollLeft <= 4 ? maxScroll : track.scrollLeft - this.step(),
                behavior: 'smooth',
            });
        },
        play() {
            this.pause();
            this.timer = setInterval(() => this.next(), this.delay);
        },
        pause() {
            clearInterval(this.timer);
            this.timer = null;
        },
    }));

    Alpine.data('fakeOrderToast', (endpoint) => ({
        endpoint,
        visible: false,
        message: '',
        name: '',
        city: '',
        product: '',
        initials: '',
        intervalSeconds: 30,
        hideTimer: null,
        loopTimer: null,
        init() {
            this.schedule(2500);
        },
        destroy() {
            clearTimeout(this.hideTimer);
            clearTimeout(this.loopTimer);
        },
        schedule(delayMs) {
            clearTimeout(this.loopTimer);
            this.loopTimer = setTimeout(() => this.fetchAndShow(), delayMs);
        },
        async fetchAndShow() {
            try {
                const response = await fetch(this.endpoint, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    this.schedule(60000);

                    return;
                }

                const data = await response.json();
                if (!data.message) {
                    this.schedule(60000);

                    return;
                }

                this.message = data.message;
                this.name = data.name || '';
                this.city = data.city || '';
                this.product = data.product || data.message;
                this.initials = this.name ? Array.from(this.name)[0].toLocaleUpperCase('tr-TR') : '•';
                this.intervalSeconds = Math.max(5, Number(data.display_interval_seconds || 30));
                this.visible = true;

                clearTimeout(this.hideTimer);
                this.hideTimer = setTimeout(() => {
                    this.visible = false;
                    this.schedule(this.intervalSeconds * 1000);
                }, 6000);
            } catch {
                this.schedule(60000);
            }
        },
        dismiss() {
            this.visible = false;
            clearTimeout(this.hideTimer);
            this.schedule(this.intervalSeconds * 1000);
        },
    }));

    Alpine.data('turkeyMap', (config = {}) => ({
        provinces: config.provinces || [],
        svgUrl: config.svgUrl || '',
        lazy: config.lazy !== false,
        loaded: false,
        loading: false,
        query: '',
        sort: 'name',
        tooltip: { show: false, text: '', x: 0, y: 0 },
        visited: [],
        observer: null,

        get bySlug() {
            return Object.fromEntries(this.provinces.map((p) => [p.slug, p]));
        },

        get filteredProvinces() {
            const q = this.query.trim().toLocaleLowerCase('tr-TR');
            let list = [...this.provinces];

            if (q !== '') {
                list = list.filter((p) =>
                    p.name.toLocaleLowerCase('tr-TR').includes(q)
                    || p.plate_code.includes(q)
                    || p.slug.includes(q),
                );
            }

            if (this.sort === 'sites') {
                list.sort((a, b) => b.sites_count - a.sites_count || a.name.localeCompare(b.name, 'tr'));
            } else {
                list.sort((a, b) => a.name.localeCompare(b.name, 'tr'));
            }

            return list;
        },

        init() {
            try {
                this.visited = JSON.parse(localStorage.getItem('visited-provinces') || '[]');
            } catch {
                this.visited = [];
            }

            this.$nextTick(() => {
                if (! this.lazy) {
                    this.load();

                    return;
                }

                this.observer = new IntersectionObserver((entries) => {
                    if (entries.some((entry) => entry.isIntersecting)) {
                        this.load();
                        this.observer?.disconnect();
                    }
                }, { rootMargin: '200px' });

                this.observer.observe(this.$el);
            });
        },

        destroy() {
            this.observer?.disconnect();
        },

        async load() {
            const canvas = this.$refs.mapCanvas;
            if (this.loaded || this.loading || ! this.svgUrl || ! canvas) {
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(this.svgUrl, {
                    headers: { Accept: 'image/svg+xml,text/plain,*/*' },
                    credentials: 'same-origin',
                });

                if (! response.ok) {
                    canvas.innerHTML = '<p class="py-16 text-center text-sm text-ink-3">Harita yüklenemedi.</p>';

                    return;
                }

                canvas.innerHTML = await response.text();
                this.enhance(canvas);
                this.loaded = true;
            } catch {
                canvas.innerHTML = '<p class="py-16 text-center text-sm text-ink-3">Harita yüklenemedi.</p>';
            } finally {
                this.loading = false;
            }
        },

        enhance(root) {
            const svg = root.querySelector('svg');
            if (! svg) {
                return;
            }

            svg.setAttribute('role', 'img');
            svg.setAttribute('aria-label', 'Türkiye illeri haritası');
            svg.classList.add('turkey-map-svg');
            svg.removeAttribute('width');
            svg.removeAttribute('height');
            svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
            svg.style.width = '100%';
            svg.style.height = 'auto';
            svg.style.maxWidth = '100%';
            svg.style.display = 'block';

            root.querySelectorAll('g[data-slug][data-plakakodu]').forEach((group) => {
                const slug = group.getAttribute('data-slug');
                const province = this.bySlug[slug];
                if (! province) {
                    group.style.display = 'none';

                    return;
                }

                const bucket = province.bucket ?? 0;

                group.classList.add('turkey-province', `bucket-${bucket}`);
                if (this.visited.includes(slug)) {
                    group.classList.add('is-visited');
                }

                group.style.cursor = 'pointer';
                group.style.pointerEvents = 'auto';
                group.setAttribute('role', 'link');
                group.setAttribute('tabindex', '0');
                group.setAttribute(
                    'aria-label',
                    province.sites_count > 0
                        ? `${province.name} — ${province.sites_count} site`
                        : `${province.name} — yakında`,
                );
                group.dataset.url = province.url;

                // Clear inline fills so CSS :hover / .is-hover can win
                group.querySelectorAll('path, polygon, polyline').forEach((shape) => {
                    shape.removeAttribute('fill');
                    shape.style.removeProperty('fill');
                    shape.style.pointerEvents = 'auto';
                    shape.style.cursor = 'pointer';
                });

                const showTip = () => {
                    root.querySelectorAll('.turkey-province.is-hover').forEach((el) => {
                        el.classList.remove('is-hover');
                    });
                    group.classList.add('is-hover');
                    // Hover edilen ili üstte çiz (scale komşuların altında kalmasın)
                    group.parentNode?.appendChild(group);
                    this.showTooltip(group, province);
                };
                const hideTip = () => {
                    group.classList.remove('is-hover');
                    this.tooltip.show = false;
                };
                const onFocus = () => {
                    root.querySelectorAll('.turkey-province.is-hover, .turkey-province.is-focus').forEach((el) => {
                        el.classList.remove('is-hover', 'is-focus');
                    });
                    group.classList.add('is-focus', 'is-hover');
                    this.showTooltip(group, province);
                };
                const onBlur = () => {
                    group.classList.remove('is-focus', 'is-hover');
                    this.tooltip.show = false;
                };
                const go = (event) => {
                    event?.preventDefault?.();
                    this.navigate(slug, province.url);
                };

                group.addEventListener('mouseenter', showTip);
                group.addEventListener('mouseleave', hideTip);
                group.addEventListener('focus', onFocus);
                group.addEventListener('blur', onBlur);
                group.addEventListener('click', go);
                group.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        go(event);
                    }
                });
            });
        },

        showTooltip(group, province) {
            const svg = group.ownerSVGElement;
            const host = this.$refs.mapHost;
            if (! svg || ! host) {
                return;
            }

            let bbox;
            try {
                bbox = group.getBBox();
            } catch {
                return;
            }

            const ctm = group.getScreenCTM();
            const hostRect = host.getBoundingClientRect();
            if (! ctm) {
                return;
            }

            const pt = svg.createSVGPoint();
            pt.x = bbox.x + bbox.width / 2;
            pt.y = bbox.y + bbox.height / 2;
            const screen = pt.matrixTransform(ctm);

            this.tooltip = {
                show: true,
                text: province.sites_count > 0
                    ? `${province.name} · ${province.sites_count} site →`
                    : `${province.name} · yakında →`,
                x: screen.x - hostRect.left,
                y: screen.y - hostRect.top,
            };
        },

        navigate(slug, url) {
            try {
                const next = Array.from(new Set([...this.visited, slug]));
                this.visited = next;
                localStorage.setItem('visited-provinces', JSON.stringify(next));
            } catch {
                // ignore
            }

            window.location.href = url;
        },
    }));
});

Alpine.start();

window.addEventListener('load', () => {
    HSStaticMethods.autoInit(['collapse', 'accordion']);
});

/*
 | Motion (motion.dev) — Framer Motion'ın vanilla JS motoru.
 | [data-reveal]        : görünüme girince fade + slide-up (CSS'te gizli başlar)
 | [data-reveal-group]  : çocuk [data-reveal]'ları sırayla (stagger) oynatır
 | [data-countup]       : sayıyı 0'dan hedefe sayar (data-countup="2623")
 | [data-typewriter]    : input placeholder typewriter (JSON dizi)
 | [data-step-card]     : süreç kartı görünüme girince "aktif" (koyu) stile döner
 | [data-order-stack]   : sipariş kartlarını slot'lar arasında layout-style döndürür
 | [data-rough-underline]: Rough Notation ile elle çizilmiş alt çizgi
 */
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function initRoughUnderlines() {
    const color =
        getComputedStyle(document.documentElement).getPropertyValue('--color-accent-600').trim() ||
        '#2248ab';
    const isMobile = window.matchMedia('(max-width: 639px)').matches;

    document.querySelectorAll('[data-rough-underline]').forEach((el) => {
        const annotation = annotate(el, {
            type: 'underline',
            color,
            strokeWidth: isMobile ? 2.5 : 3,
            padding: isMobile ? 1 : 2,
            iterations: 2,
            multiline: true,
            animationDuration: prefersReducedMotion ? 1 : 900,
        });

        const show = () => {
            if (!annotation.isShowing()) {
                annotation.show();
            }
        };

        if (prefersReducedMotion) {
            show();

            return;
        }

        inView(
            el,
            () => {
                const delay = el.closest('[data-reveal], [data-reveal-group]') ? 550 : 80;
                const timer = setTimeout(show, delay);

                return () => clearTimeout(timer);
            },
            { amount: 0.45 },
        );
    });
}

initRoughUnderlines();

function initTypewriterPlaceholders() {
    document.querySelectorAll('[data-typewriter]').forEach((input) => {
        let words = [];

        try {
            words = JSON.parse(input.getAttribute('data-typewriter') || '[]');
        } catch {
            words = [];
        }

        if (!Array.isArray(words) || words.length === 0) {
            return;
        }

        const prefix = input.getAttribute('data-typewriter-prefix') || '';
        let wordIndex = 0;
        let charIndex = 0;
        let deleting = false;
        let paused = false;
        let timer = null;

        const stop = () => {
            paused = true;
            if (timer) {
                clearTimeout(timer);
                timer = null;
            }
        };

        const tick = () => {
            if (paused || document.activeElement === input || input.value.trim() !== '') {
                return;
            }

            const word = words[wordIndex];

            if (!deleting) {
                charIndex += 1;
                input.placeholder = prefix + word.slice(0, charIndex);

                if (charIndex >= word.length) {
                    timer = setTimeout(() => {
                        deleting = true;
                        tick();
                    }, 1600);

                    return;
                }

                timer = setTimeout(tick, 70);

                return;
            }

            charIndex -= 1;
            input.placeholder = prefix + word.slice(0, Math.max(charIndex, 0));

            if (charIndex <= 0) {
                deleting = false;
                wordIndex = (wordIndex + 1) % words.length;
                timer = setTimeout(tick, 320);

                return;
            }

            timer = setTimeout(tick, 40);
        };

        const start = () => {
            if (document.activeElement === input || input.value.trim() !== '') {
                return;
            }

            paused = false;
            if (!timer) {
                tick();
            }
        };

        input.addEventListener('focus', () => {
            stop();
            input.placeholder = '';
        });

        input.addEventListener('blur', () => {
            if (input.value.trim() === '') {
                start();
            }
        });

        input.addEventListener('input', () => {
            if (input.value.trim() !== '') {
                stop();
                input.placeholder = '';
            } else if (document.activeElement !== input) {
                start();
            }
        });

        if (prefersReducedMotion) {
            input.placeholder = prefix + words[0];

            return;
        }

        input.placeholder = prefix;
        start();
    });
}

initTypewriterPlaceholders();

if (!prefersReducedMotion) {
    const springEase = [0.22, 0.61, 0.36, 1];

    document.querySelectorAll('[data-reveal-group]').forEach((group) => {
        const items = group.querySelectorAll(':scope [data-reveal]');
        if (items.length === 0) {
            return;
        }

        inView(
            group,
            () => {
                animate(
                    items,
                    { opacity: 1, transform: 'translateY(0px)' },
                    { duration: 0.7, easing: springEase, delay: stagger(0.09) },
                );
            },
            { amount: 0.18 },
        );
    });

    document.querySelectorAll('[data-reveal]').forEach((el) => {
        if (el.closest('[data-reveal-group]')) {
            return;
        }

        inView(
            el,
            () => {
                animate(
                    el,
                    { opacity: 1, transform: 'translateY(0px)' },
                    { duration: 0.7, easing: springEase },
                );
            },
            { amount: 0.25 },
        );
    });

    document.querySelectorAll('[data-countup]').forEach((el) => {
        const target = Number(el.dataset.countup || '0');
        const suffix = el.dataset.countupSuffix ?? '';
        const prefix = el.dataset.countupPrefix ?? '';
        const format = new Intl.NumberFormat('tr-TR');

        inView(
            el,
            () => {
                animate(0, target, {
                    duration: 1.6,
                    easing: 'ease-out',
                    onUpdate: (value) => {
                        el.textContent = prefix + format.format(Math.round(value)) + suffix;
                    },
                });
            },
            { amount: 0.6 },
        );
    });

    document.querySelectorAll('[data-step-card]').forEach((card) => {
        inView(
            card,
            () => {
                card.classList.add('is-active');

                return () => card.classList.remove('is-active');
            },
            { amount: 0.6, margin: '-20% 0px -20% 0px' },
        );
    });

    // Sipariş yığını: 5 kart, cubic-bezier — üst üste yarım görünür
    document.querySelectorAll('[data-order-stack]').forEach((stack) => {
        const cards = [...stack.querySelectorAll('[data-order-card]')];
        if (cards.length < 2) return;

        const ease = [0.25, 0.1, 0.25, 1];
        const duration = 0.7;
        const n = cards.length;

        // Her kartın yarısı görünsün
        const cardH = Math.max(cards[0].getBoundingClientRect().height, 56);
        const gap = Math.round(cardH * 0.5);
        stack.style.height = `${Math.ceil(cardH + gap * (n - 1) + 8)}px`;

        // 1..5 choreography (senin 3'lü sıranın 5'li uzantısı)
        const steps = [
            [0, 1, 2, 3, 4], // 1 2 3 4 5
            [1, 0, 2, 3, 4], // 2 → 1 üstüne
            [0, 2, 3, 4, 1], // 2 → en alta
            [2, 0, 3, 4, 1], // 3 → en üste
            [1, 0, 3, 4, 2], // 3 ↔ 2
            [0, 3, 4, 2, 1], // (üst) → en alta
            [3, 0, 4, 2, 1], // 4 → en üste
            [1, 0, 4, 2, 3], // 4 ↔ 2
            [0, 4, 2, 3, 1], // (üst) → en alta
            [4, 0, 2, 3, 1], // 5 → en üste
            [1, 0, 2, 3, 4], // 5 ↔ 2
        ];

        const slotAt = (depth) => ({
            y: depth * gap,
            scale: 1 - depth * 0.015,
            opacity: Math.max(0.88, 1 - depth * 0.04),
            z: n - depth,
        });

        cards.forEach((card) => {
            card.style.transformOrigin = '50% 0%';
            card.style.backfaceVisibility = 'hidden';
        });

        let running = null;

        const applyOrder = (order, animated) => {
            if (running) {
                running.stop?.();
                running = null;
            }

            // Hedef z-index'i animasyondan önce sabitle (ortada z değişimi kasmasın)
            order.forEach((cardIndex, depth) => {
                cards[cardIndex].style.zIndex = String(slotAt(depth).z);
            });

            const animations = order.map((cardIndex, depth) => {
                const slot = slotAt(depth);

                return animate(
                    cards[cardIndex],
                    {
                        y: slot.y,
                        scale: slot.scale,
                        opacity: slot.opacity,
                    },
                    {
                        duration: animated ? duration : 0,
                        easing: ease,
                    },
                );
            });

            running = {
                stop() {
                    animations.forEach((a) => a?.stop?.());
                },
            };

            return Promise.all(animations);
        };

        const wait = (ms) => new Promise((r) => setTimeout(r, ms));

        applyOrder(steps[0], false);

        const run = async (alive) => {
            let step = 0;
            while (alive()) {
                await wait(step === 0 ? 1000 : 1400);
                if (!alive()) return;
                step += 1;
                // Son adım (5↔2 → 2/1/3/4/5) sonrası "2 en alta" ile devam
                if (step >= steps.length) {
                    step = 2;
                }
                await applyOrder(steps[step], true);
            }
        };

        inView(
            stack,
            () => {
                let active = true;
                run(() => active);

                return () => {
                    active = false;
                    running?.stop?.();
                };
            },
            { amount: 0.35 },
        );
    });

    // Odometre: rakamlar 0-9 şeridi üzerinde yuvarlanarak hedefe oturur
    document.querySelectorAll('[data-odometer]').forEach((el) => {
        const target = Number(el.dataset.odometer || '0');
        const suffix = el.dataset.odometerSuffix ?? '';
        const formatted = target.toLocaleString('tr-TR');

        inView(
            el,
            () => {
                el.textContent = '';
                [...formatted].forEach((ch) => {
                    if (!/\d/.test(ch)) {
                        el.appendChild(Object.assign(document.createElement('span'), { textContent: ch }));

                        return;
                    }

                    const col = document.createElement('span');
                    col.className = 'odo-col';
                    const strip = document.createElement('span');
                    strip.className = 'odo-strip';
                    for (let i = 0; i <= 9; i++) {
                        strip.appendChild(Object.assign(document.createElement('span'), { textContent: String(i) }));
                    }
                    col.appendChild(strip);
                    el.appendChild(col);
                    animate(
                        strip,
                        { transform: `translateY(-${Number(ch) * 10}%)` },
                        { duration: 1.5, easing: springEase },
                    );
                });
                if (suffix) {
                    el.appendChild(Object.assign(document.createElement('span'), { textContent: suffix }));
                }
            },
            { amount: 0.6 },
        );
    });

    // Hero'daki dönen kelime: sıradaki kelime aşağıdan kayarak girer
    document.querySelectorAll('[data-word-rotor]').forEach((rotor) => {
        const words = [...rotor.children];

        if (words.length < 2) return;

        const wrapper = rotor.closest('.rotor-wrapper');
        const measure = wrapper.querySelector('.rotor-measure');

        let index = 0;

        const setWidth = (px, animateWidth = false) => {
            if (!animateWidth) {
                wrapper.style.transition = 'none';
            } else {
                wrapper.style.transition = '';
            }
            wrapper.style.width = `${Math.round(px)}px`;
            if (!animateWidth) {
                // reflow sonra transition'ı geri aç
                void wrapper.offsetWidth;
                wrapper.style.transition = '';
            }
        };

        measure.textContent = words[0].textContent;
        setWidth(measure.offsetWidth, false);

        words.forEach((word, i) => {
            word.style.opacity = i === 0 ? 1 : 0;
            word.style.transform = i === 0 ? 'translate(-50%, 0%)' : 'translate(-50%, 100%)';
        });

        setInterval(() => {
            const current = words[index];
            index = (index + 1) % words.length;
            const next = words[index];

            measure.textContent = next.textContent;
            setWidth(measure.offsetWidth, true);

            animate(
                current,
                { opacity: 0, transform: 'translate(-50%, -100%)' },
                { duration: 0.4, easing: [0.33, 1, 0.68, 1] },
            );

            next.style.transform = 'translate(-50%, 100%)';

            animate(
                next,
                { opacity: 1, transform: 'translate(-50%, 0%)' },
                { duration: 0.4, easing: [0.33, 1, 0.68, 1] },
            );
        }, 2400);
    });
} else {
    document.querySelectorAll('[data-countup]').forEach((el) => {
        const format = new Intl.NumberFormat('tr-TR');
        el.textContent = (el.dataset.countupPrefix ?? '')
            + format.format(Number(el.dataset.countup || '0'))
            + (el.dataset.countupSuffix ?? '');
    });
}
