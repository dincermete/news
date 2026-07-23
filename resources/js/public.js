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
    
        // İlk kelime
        measure.textContent = words[0].textContent;
    
        words.forEach((word, i) => {
            word.style.opacity = i === 0 ? 1 : 0;
            word.style.transform = i === 0
                ? 'translateY(0%)'
                : 'translateY(100%)';
        });
    
        setInterval(() => {
    
            const current = words[index];
    
            index = (index + 1) % words.length;
    
            const next = words[index];
    
            // Kutu genişliğini yeni kelimeye göre değiştir
            measure.textContent = next.textContent;
    
            animate(
                current,
                {
                    opacity: 0,
                    transform: 'translateY(-100%)'
                },
                {
                    duration: 0.45,
                    easing: springEase
                }
            );
    
            next.style.transform = 'translateY(100%)';
    
            animate(
                next,
                {
                    opacity: 1,
                    transform: 'translateY(0%)'
                },
                {
                    duration: 0.45,
                    easing: springEase
                }
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
