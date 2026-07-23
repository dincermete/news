import Alpine from 'alpinejs';
import { HSStaticMethods } from 'preline/non-auto';
import { animate, inView, stagger } from 'motion';

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
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
        async markRead(item) {
            if (item.read_at || !this.markUrlTemplate) {
                return;
            }

            const url = this.markUrlTemplate.replace('__ID__', String(item.id));

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

                const data = await response.json();
                item.read_at = new Date().toISOString();
                this.unread = Number(data.unread_count ?? Math.max(0, this.unread - 1));
            } catch {
                // ignore network errors in UI
            }
        },
    }));

    Alpine.data('fakeOrderToast', (endpoint) => ({
        endpoint,
        visible: false,
        message: '',
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
 | [data-step-card]     : süreç kartı görünüme girince "aktif" (koyu) stile döner
 | [data-order-stack]   : sipariş kartlarını slot'lar arasında layout-style döndürür
 */
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

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
            word.style.transform = i === 0 ? 'translateY(0%)' : 'translateY(100%)';
        });

        setInterval(() => {
            const current = words[index];
            index = (index + 1) % words.length;
            const next = words[index];

            measure.textContent = next.textContent;
            setWidth(measure.offsetWidth, true);

            animate(
                current,
                { opacity: 0, transform: 'translateY(-100%)' },
                { duration: 0.4, easing: [0.33, 1, 0.68, 1] },
            );

            next.style.transform = 'translateY(100%)';

            animate(
                next,
                { opacity: 1, transform: 'translateY(0%)' },
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
