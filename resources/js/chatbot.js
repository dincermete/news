/**
 * Chatbot widget — deferred module, idle-mounted (not on critical path).
 * Intercom-style Messenger: Home / Messages / Help / News tabs, a separate
 * conversation screen, and Motion-powered open/close + typing indicator.
 */
import { animate } from 'motion';

const STORAGE_KEY = 'nt_chatbot_session';
const SESSION_OPEN_KEY = 'nt_chatbot_open';
const SESSION_SCREEN_KEY = 'nt_chatbot_screen';
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
            ? 'max-w-[85%] rounded-2xl rounded-br-sm bg-white px-3.5 py-2.5 text-[13px] leading-relaxed text-ink'
            : 'chatbot-markdown max-w-[85%] rounded-2xl rounded-bl-sm bg-white/10 px-3.5 py-2.5 text-[13px] leading-relaxed text-white';

    if (role === 'user') {
        bubble.textContent = text;
    } else {
        bubble.innerHTML = renderMarkdown(text);
    }

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
    bubble.className = 'flex items-center gap-1 rounded-2xl rounded-bl-sm bg-white/10 px-4 py-3';
    bubble.innerHTML = [0, 1, 2]
        .map(
            (i) =>
                `<span class="chatbot-dot inline-block size-1.5 rounded-full bg-white/50" style="animation-delay:${i * 0.15}s"></span>`,
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
    card.className = 'max-w-[90%] rounded-2xl rounded-bl-sm border border-brand-400/30 bg-brand-950/40 p-3.5 text-[13px] text-white';
    card.innerHTML = `
        <p class="font-semibold">Destek ekibine yönlendirildiniz</p>
        <p class="mt-1 text-white/70">Destek talebiniz oluşturuldu (#${escapeHtml(String(escalation.support_ticket_id))}).</p>
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

/**
 * Quick-reply chips shown under the latest bot message. Each button shows the short
 * `label`, but tapping it sends the full `text` as if the user had typed it themselves.
 */
function appendSuggestions(container, suggestions, onPick) {
    if (!Array.isArray(suggestions) || suggestions.length === 0) {
        return;
    }

    const row = document.createElement('div');
    row.className = 'flex flex-wrap gap-1.5 ps-8';
    row.dataset.chatbotSuggestions = '1';

    suggestions.slice(0, 3).forEach((suggestion) => {
        const label = String(suggestion?.label || '').trim();
        const text = String(suggestion?.text || '').trim();
        if (!label || !text) {
            return;
        }

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className =
            'max-w-[13rem] truncate rounded-full border border-white/15 bg-white/5 px-3 py-1.5 text-[12px] font-medium text-white/80 transition hover:border-white/30 hover:bg-white/10 hover:text-white';
        btn.textContent = label;
        btn.title = text;
        btn.addEventListener('click', () => {
            row.remove();
            onPick(text);
        });
        row.appendChild(btn);
    });

    if (!row.childElementCount) {
        return;
    }

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

/**
 * Minimal, XSS-safe Markdown renderer for bot replies: escapes HTML first, then
 * only ever introduces tags for a small known subset (bold/italic/code/links/lists),
 * so nothing from the model's raw text can inject arbitrary markup.
 */
function renderMarkdown(text) {
    const escaped = escapeHtml(text).replace(/\r\n/g, '\n');
    const blocks = escaped.split(/\n{2,}/);

    return blocks
        .map((block) => {
            const lines = block.split('\n').filter((line) => line.trim() !== '');
            if (lines.length === 0) {
                return '';
            }

            const isBulletList = lines.every((line) => /^\s*[-*]\s+/.test(line));
            const isNumberedList = lines.every((line) => /^\s*\d+[.)]\s+/.test(line));

            if (isBulletList) {
                const items = lines.map((line) => `<li>${inlineMarkdown(line.replace(/^\s*[-*]\s+/, ''))}</li>`).join('');

                return `<ul class="list-disc space-y-0.5 ps-4">${items}</ul>`;
            }

            if (isNumberedList) {
                const items = lines.map((line) => `<li>${inlineMarkdown(line.replace(/^\s*\d+[.)]\s+/, ''))}</li>`).join('');

                return `<ol class="list-decimal space-y-0.5 ps-4">${items}</ol>`;
            }

            return `<p>${lines.map(inlineMarkdown).join('<br>')}</p>`;
        })
        .filter(Boolean)
        .join('');
}

function inlineMarkdown(line) {
    return line
        .replace(/`([^`]+)`/g, '<code class="rounded bg-black/20 px-1 py-0.5 text-[12px]">$1</code>')
        .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
        .replace(/(?<!\*)\*([^*]+)\*(?!\*)/g, '<em>$1</em>')
        .replace(
            /(https?:\/\/[^\s<]+)/g,
            '<a href="$1" target="_blank" rel="noopener noreferrer" class="underline underline-offset-2">$1</a>',
        );
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
    const conversationEndpoint = root.dataset.chatbotConversationEndpoint;
    const csrf = root.dataset.chatbotCsrf;
    const panel = root.querySelector('[data-chatbot-panel]');
    const toggle = root.querySelector('[data-chatbot-toggle]');
    const closeBtns = root.querySelectorAll('[data-chatbot-close]');
    const form = root.querySelector('[data-chatbot-form]');
    const input = root.querySelector('[data-chatbot-input]');
    const sendBtn = root.querySelector('[data-chatbot-send]');
    const messages = root.querySelector('[data-chatbot-messages]');
    const welcomeRow = root.querySelector('[data-chatbot-welcome]');
    const chips = root.querySelectorAll('[data-chip]');
    const chipsRow = root.querySelector('[data-chatbot-chips]');
    const badge = root.querySelector('[data-chatbot-badge]');
    const iconChat = root.querySelector('[data-chatbot-icon-chat]');
    const iconClose = root.querySelector('[data-chatbot-icon-close]');

    const screens = root.querySelectorAll('[data-chatbot-screen]');
    const tabbar = root.querySelector('[data-chatbot-tabbar]');
    const tabButtons = root.querySelectorAll('[data-chatbot-tab]');
    const tabTargetButtons = root.querySelectorAll('[data-chatbot-tab-target]');
    const backBtn = root.querySelector('[data-chatbot-back]');
    const faqToggles = root.querySelectorAll('[data-chatbot-faq-toggle]');

    const recentCard = root.querySelector('[data-chatbot-recent-card]');
    const recentText = root.querySelector('[data-chatbot-recent-text]');
    const recentTime = root.querySelector('[data-chatbot-recent-time]');
    const messagesCard = root.querySelector('[data-chatbot-messages-card]');
    const messagesText = root.querySelector('[data-chatbot-messages-text]');
    const messagesTime = root.querySelector('[data-chatbot-messages-time]');
    const messagesEmpty = root.querySelector('[data-chatbot-messages-empty]');

    let open = false;
    let sending = false;
    let historyLoaded = false;
    let historyRendered = false;
    let token = sessionToken();

    // Kullanıcının dikkatini çekmek için bir kereye mahsus bildirim rozeti.
    window.setTimeout(() => {
        if (!open && badge) {
            badge.classList.remove('hidden');
            badge.classList.add('inline-flex');
        }
    }, 3000);

    let currentScreen = 'home';

    const showScreen = (name) => {
        if (name === currentScreen) {
            return;
        }

        let enteringEl = null;

        screens.forEach((screen) => {
            const isTarget = screen.dataset.chatbotScreen === name;
            screen.classList.toggle('hidden', !isTarget);
            screen.classList.toggle('flex', isTarget);
            if (isTarget) {
                enteringEl = screen;
            }
        });

        currentScreen = name;
        try {
            sessionStorage.setItem(SESSION_SCREEN_KEY, name);
        } catch {
            // Gizlilik modu / depolama kapalı — sekmeler arası hatırlama devre dışı kalır.
        }
        tabbar?.classList.toggle('hidden', name === 'conversation');

        tabButtons.forEach((btn) => {
            const active = btn.dataset.chatbotTab === name;
            btn.classList.toggle('text-white', active);
            btn.classList.toggle('text-white/45', !active);
        });

        if (enteringEl && !prefersReducedMotion) {
            animate(
                enteringEl,
                { opacity: [0, 1], transform: ['translateY(6px)', 'translateY(0)'] },
                { duration: 0.22, easing: SPRING },
            );
        }

        if (name === 'conversation') {
            window.setTimeout(() => input?.focus(), 200);
        }
    };

    const goToConversation = () => {
        showScreen('conversation');
        renderHistoryIntoConversation();
    };

    /**
     * "Yeni sohbet başlat": eskisinden ayrı, boş bir konuşma. Yeni bir session
     * token üretir (sunucu tarafında da gerçekten yeni bir ChatbotConversation
     * satırı açılır), önbellekteki eski geçmişi temizler ve konuşma ekranını
     * hoş geldin balonuyla sıfırdan gösterir.
     */
    const startNewConversation = () => {
        try {
            localStorage.removeItem(STORAGE_KEY);
        } catch {
            // Gizlilik modu / depolama kapalı — yine de yeni bir token üretilir.
        }
        token = sessionToken();
        cachedHistory = [];
        historyLoaded = true;
        historyRendered = true;

        if (messages) {
            messages.innerHTML = '';
            if (welcomeRow) {
                messages.appendChild(welcomeRow);
            }
        }
        chipsRow?.classList.remove('hidden');
        recentCard?.classList.add('hidden');
        recentCard?.classList.remove('flex');
        messagesCard?.classList.add('hidden');
        messagesCard?.classList.remove('flex');
        messagesEmpty?.classList.remove('hidden');
        messagesEmpty?.classList.add('flex');

        showScreen('conversation');
    };

    tabButtons.forEach((btn) => {
        btn.addEventListener('click', () => showScreen(btn.dataset.chatbotTab));
    });
    tabTargetButtons.forEach((btn) => {
        btn.addEventListener('click', () => showScreen(btn.dataset.chatbotTabTarget));
    });
    root.querySelectorAll('[data-chatbot-open-conversation]').forEach((el) => {
        el.addEventListener('click', goToConversation);
    });
    root.querySelectorAll('[data-chatbot-start-conversation]').forEach((el) => {
        el.addEventListener('click', startNewConversation);
    });
    backBtn?.addEventListener('click', () => showScreen('home'));

    faqToggles.forEach((btn) => {
        const panelEl = btn.nextElementSibling;
        const chevron = btn.querySelector('[data-chatbot-faq-chevron]');

        btn.addEventListener('click', () => {
            const isOpen = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            panelEl?.classList.toggle('hidden', isOpen);
            chevron?.classList.toggle('rotate-90', !isOpen);
        });
    });

    const morphIcon = (isOpen) => {
        if (!iconChat || !iconClose) {
            return;
        }

        // Tailwind's rotate-45/scale-0 utilities set the independent CSS `rotate`/`scale`
        // properties (not the legacy `transform` shorthand), so these must be set the same
        // way here — setting `transform` inline would only *compose* with, not override, them.
        iconChat.style.scale = isOpen ? '0' : '1';
        iconChat.style.rotate = isOpen ? '-45deg' : '0deg';
        iconChat.style.opacity = isOpen ? '0' : '1';
        iconClose.style.scale = isOpen ? '1' : '0';
        iconClose.style.rotate = isOpen ? '0deg' : '45deg';
        iconClose.style.opacity = isOpen ? '1' : '0';
    };

    const setOpen = (next) => {
        open = next;
        toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
        morphIcon(open);

        try {
            if (open) {
                sessionStorage.setItem(SESSION_OPEN_KEY, '1');
            } else {
                sessionStorage.removeItem(SESSION_OPEN_KEY);
            }
        } catch {
            // Gizlilik modu / depolama kapalı — sekmeler arası hatırlama devre dışı kalır.
        }

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

            loadHistory();
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
    closeBtns.forEach((btn) => btn.addEventListener('click', () => setOpen(false)));

    let cachedHistory = [];

    async function loadHistory() {
        if (historyLoaded || !conversationEndpoint) {
            return;
        }
        historyLoaded = true;

        try {
            const url = new URL(conversationEndpoint, window.location.origin);
            url.searchParams.set('session_token', token);
            const response = await fetch(url.toString(), {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            cachedHistory = Array.isArray(data.messages) ? data.messages : [];

            if (cachedHistory.length === 0) {
                messagesEmpty?.classList.remove('hidden');
                messagesEmpty?.classList.add('flex');
                return;
            }

            const last = cachedHistory[cachedHistory.length - 1];
            const preview = last.content.length > 60 ? `${last.content.slice(0, 60)}…` : last.content;

            if (recentCard) {
                recentCard.classList.remove('hidden');
                recentCard.classList.add('flex');
            }
            if (recentText) recentText.textContent = preview;
            if (recentTime) recentTime.textContent = last.created_at_display || '';

            if (messagesCard) {
                messagesCard.classList.remove('hidden');
                messagesCard.classList.add('flex');
            }
            if (messagesText) messagesText.textContent = preview;
            if (messagesTime) messagesTime.textContent = last.created_at_display || '';
            messagesEmpty?.classList.add('hidden');
        } catch {
            // Sessizce yok say — geçmiş olmadan da sohbet açılabilir.
        }
    }

    function renderHistoryIntoConversation() {
        if (historyRendered || cachedHistory.length === 0 || !messages) {
            return;
        }
        historyRendered = true;

        welcomeRow?.remove();
        chipsRow?.classList.add('hidden');

        cachedHistory.forEach((entry) => {
            appendBubble(messages, entry.content, entry.role === 'user' ? 'user' : 'bot');
        });
    }

    const send = async (text) => {
        const message = String(text || '').trim();
        if (!message || sending || !endpoint) {
            return;
        }

        sending = true;
        if (sendBtn) {
            sendBtn.disabled = true;
        }

        messages?.querySelectorAll('[data-chatbot-suggestions]').forEach((el) => el.remove());
        chipsRow?.classList.add('hidden');
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
                } else if (Array.isArray(data.suggestions) && data.suggestions.length > 0) {
                    appendSuggestions(messages, data.suggestions, send);
                }
            }

            historyRendered = true;
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
            goToConversation();
            send(chip.getAttribute('data-chip'));
        });
    });

    /**
     * Bu bir tam sayfa yenilemeli site (SPA değil): normal bir link tıklaması
     * widget'ı sıfırdan yeniden mount eder. Kullanıcı sohbeti açık bırakıp
     * başka bir sayfaya geçtiyse, burada aynı sekme oturumu boyunca
     * (sessionStorage) açık/konuşma durumunu geri yükleyip devam ettiriyoruz.
     */
    (async () => {
        let wasOpen = false;
        let savedScreen = 'home';
        try {
            wasOpen = sessionStorage.getItem(SESSION_OPEN_KEY) === '1';
            savedScreen = sessionStorage.getItem(SESSION_SCREEN_KEY) || 'home';
        } catch {
            return;
        }

        if (!wasOpen) {
            return;
        }

        await loadHistory();
        setOpen(true);

        if (savedScreen === 'conversation') {
            goToConversation();
        } else if (savedScreen !== 'home') {
            showScreen(savedScreen);
        }
    })();
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
