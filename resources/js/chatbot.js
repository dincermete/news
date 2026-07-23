/**
 * Chatbot widget — deferred module, idle-mounted (not on critical path).
 * Intercom-style launcher/panel with Motion-powered open/close and a typing indicator.
 */
import { animate } from 'motion';

const STORAGE_KEY = 'nt_chatbot_session';
const SPRING = [0.22, 0.61, 0.36, 1];
const MIN_TYPING_MS = 550;

function uuid() {
    if (window.crypto?.randomUUID) {
        return window.crypto.randomUUID();
    }

    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;
        const v = c === 'x' ? r : (r & 0x3) | 0x8;

        return v.toString(16);
    });
}

function sessionToken() {
    try {
        let token = localStorage.getItem(STORAGE_KEY);
        if (!token) {
            token = uuid();
            localStorage.setItem(STORAGE_KEY, token);
        }

        return token;
    } catch {
        return uuid();
    }
}

function botAvatar() {
    const span = document.createElement('span');
    span.className =
        'inline-flex size-6 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-accent-500 to-brand-500 text-white';
    span.innerHTML =
        '<svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>';

    return span;
}

function animateIn(el) {
    animate(el, { opacity: [0, 1], transform: ['translateY(8px)', 'translateY(0)'] }, { duration: 0.32, easing: SPRING });
}

function appendBubble(container, text, role) {
    const row = document.createElement('div');
    row.className = role === 'user' ? 'flex justify-end' : 'flex items-end gap-2';

    if (role !== 'user') {
        row.appendChild(botAvatar());
    }

    const bubble = document.createElement('div');
    bubble.className =
        role === 'user'
            ? 'max-w-[85%] rounded-2xl rounded-br-sm bg-gradient-to-br from-accent-600 to-brand-600 px-3.5 py-2.5 text-[13px] leading-relaxed text-white'
            : 'max-w-[85%] rounded-2xl rounded-bl-sm bg-white px-3.5 py-2.5 text-[13px] leading-relaxed text-ink shadow-soft';
    bubble.textContent = text;
    row.appendChild(bubble);

    container.appendChild(row);
    container.scrollTop = container.scrollHeight;
    animateIn(row);

    return row;
}

function appendTyping(container) {
    const row = document.createElement('div');
    row.className = 'flex items-end gap-2';
    row.dataset.chatbotTyping = '1';
    row.appendChild(botAvatar());

    const bubble = document.createElement('div');
    bubble.className = 'flex items-center gap-1 rounded-2xl rounded-bl-sm bg-white px-4 py-3 shadow-soft';
    bubble.innerHTML = [0, 1, 2]
        .map(
            (i) =>
                `<span class="chatbot-dot inline-block size-1.5 rounded-full bg-ink-3" style="animation-delay:${i * 0.15}s"></span>`,
        )
        .join('');
    row.appendChild(bubble);

    container.appendChild(row);
    container.scrollTop = container.scrollHeight;
    animateIn(row);

    return row;
}

function appendEscalation(container, escalation) {
    const row = document.createElement('div');
    row.className = 'flex items-end gap-2';
    row.appendChild(botAvatar());

    const card = document.createElement('div');
    card.className = 'max-w-[90%] rounded-2xl rounded-bl-sm border border-brand-200 bg-brand-50 p-3.5 text-[13px] text-brand-950';
    card.innerHTML = `
        <p class="font-semibold">Destek ekibine yönlendirildiniz</p>
        <p class="mt-1 text-brand-900">Destek talebiniz oluşturuldu (#${escapeHtml(String(escalation.support_ticket_id))}).</p>
        ${
            escalation.whatsapp_link
                ? `<a href="${escapeHtml(escalation.whatsapp_link)}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex items-center gap-x-1.5 rounded-full bg-gradient-to-br from-accent-600 to-brand-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:scale-105">WhatsApp ile devam et</a>`
                : ''
        }
    `;
    row.appendChild(card);

    container.appendChild(row);
    container.scrollTop = container.scrollHeight;
    animateIn(row);
}

function escapeHtml(text) {
    return String(text)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
}

function mountChatbot() {
    const root = document.getElementById('chatbot-widget');
    if (!root || root.dataset.mounted === '1') {
        return;
    }

    root.dataset.mounted = '1';
    root.removeAttribute('x-ignore');

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const endpoint = root.dataset.chatbotEndpoint;
    const csrf = root.dataset.chatbotCsrf;
    const panel = root.querySelector('[data-chatbot-panel]');
    const toggle = root.querySelector('[data-chatbot-toggle]');
    const closeBtn = root.querySelector('[data-chatbot-close]');
    const form = root.querySelector('[data-chatbot-form]');
    const input = root.querySelector('[data-chatbot-input]');
    const sendBtn = root.querySelector('[data-chatbot-send]');
    const messages = root.querySelector('[data-chatbot-messages]');
    const chips = root.querySelectorAll('[data-chip]');
    const badge = root.querySelector('[data-chatbot-badge]');
    const iconChat = root.querySelector('[data-chatbot-icon-chat]');
    const iconClose = root.querySelector('[data-chatbot-icon-close]');

    let open = false;
    let sending = false;
    const token = sessionToken();

    // Kullanıcının dikkatini çekmek için bir kereye mahsus bildirim rozeti.
    window.setTimeout(() => {
        if (!open && badge) {
            badge.classList.remove('hidden');
            badge.classList.add('inline-flex');
        }
    }, 3000);

    const morphIcon = (isOpen) => {
        if (!iconChat || !iconClose) {
            return;
        }

        iconChat.style.transform = isOpen ? 'scale(0) rotate(-45deg)' : 'scale(1) rotate(0deg)';
        iconChat.style.opacity = isOpen ? '0' : '1';
        iconClose.style.transform = isOpen ? 'scale(1) rotate(0deg)' : 'scale(0) rotate(45deg)';
        iconClose.style.opacity = isOpen ? '1' : '0';
    };

    const setOpen = (next) => {
        open = next;
        toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
        morphIcon(open);

        if (open) {
            badge?.classList.add('hidden');
            badge?.classList.remove('inline-flex');
            panel?.classList.remove('hidden');
            panel?.classList.add('flex');

            if (prefersReducedMotion) {
                panel.style.opacity = '1';
            } else {
                animate(
                    panel,
                    { opacity: [0, 1], transform: ['scale(0.92) translateY(16px)', 'scale(1) translateY(0)'] },
                    { duration: 0.32, easing: SPRING },
                );
            }

            window.setTimeout(() => input?.focus(), 320);
        } else if (panel) {
            const finish = () => {
                panel.classList.add('hidden');
                panel.classList.remove('flex');
            };

            if (prefersReducedMotion) {
                panel.style.opacity = '0';
                finish();
            } else {
                animate(
                    panel,
                    { opacity: [1, 0], transform: ['scale(1) translateY(0)', 'scale(0.95) translateY(10px)'] },
                    { duration: 0.2, easing: SPRING },
                ).finished.then(finish);
            }
        }
    };

    toggle?.addEventListener('click', () => setOpen(!open));
    closeBtn?.addEventListener('click', () => setOpen(false));

    const send = async (text) => {
        const message = String(text || '').trim();
        if (!message || sending || !endpoint) {
            return;
        }

        sending = true;
        if (sendBtn) {
            sendBtn.disabled = true;
        }

        appendBubble(messages, message, 'user');
        if (input) {
            input.value = '';
        }

        const typingRow = appendTyping(messages);
        const startedAt = Date.now();

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    session_token: token,
                    message,
                }),
            });

            const data = await response.json();

            const elapsed = Date.now() - startedAt;
            if (elapsed < MIN_TYPING_MS) {
                await new Promise((resolve) => window.setTimeout(resolve, MIN_TYPING_MS - elapsed));
            }

            typingRow.remove();

            if (!response.ok) {
                appendBubble(messages, data.message || 'Bir hata oluştu. Lütfen tekrar deneyin.', 'bot');
            } else {
                if (data.reply) {
                    appendBubble(messages, data.reply, 'bot');
                }
                if (data.escalation) {
                    appendEscalation(messages, data.escalation);
                }
            }
        } catch {
            typingRow.remove();
            appendBubble(messages, 'Bağlantı hatası. Lütfen tekrar deneyin.', 'bot');
        } finally {
            sending = false;
            if (sendBtn) {
                sendBtn.disabled = false;
            }
        }
    };

    form?.addEventListener('submit', (event) => {
        event.preventDefault();
        send(input?.value);
    });

    chips.forEach((chip) => {
        chip.addEventListener('click', () => {
            setOpen(true);
            send(chip.getAttribute('data-chip'));
        });
    });
}

function scheduleMount() {
    const run = () => mountChatbot();

    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(run, { timeout: 2000 });
    } else {
        window.setTimeout(run, 1);
    }
}

if (document.readyState === 'complete') {
    scheduleMount();
} else {
    window.addEventListener('load', scheduleMount, { once: true });
}
