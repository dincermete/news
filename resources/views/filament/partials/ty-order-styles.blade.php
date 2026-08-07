<style>
    .ty-order {
        --ty-border: color-mix(in oklab, var(--gray-950, #111) 12%, transparent);
        --ty-muted: color-mix(in oklab, var(--gray-950, #111) 55%, transparent);
        --ty-soft: color-mix(in oklab, var(--gray-950, #111) 4%, transparent);
        --ty-card: var(--color-white, #fff);
        --ty-radius: 12px;
        --ty-gap: 16px;
        color: var(--gray-950, #111827);
    }

    .dark .ty-order,
    html.dark .ty-order {
        --ty-border: color-mix(in oklab, white 12%, transparent);
        --ty-muted: color-mix(in oklab, white 55%, transparent);
        --ty-soft: color-mix(in oklab, white 6%, transparent);
        --ty-card: color-mix(in oklab, white 4%, transparent);
        color: #f3f4f6;
    }

    .ty-order * { box-sizing: border-box; }

    .ty-order__layout {
        display: flex;
        flex-direction: column;
        gap: var(--ty-gap);
        align-items: stretch;
    }

    @media (min-width: 960px) {
        .ty-order__layout {
            flex-direction: row;
            align-items: flex-start;
        }

        .ty-order__main {
            flex: 1 1 0;
            min-width: 0;
        }

        .ty-order__side {
            flex: 0 0 320px;
            width: 320px;
            position: sticky;
            top: 1rem;
        }
    }

    .ty-order__main,
    .ty-order__side {
        display: flex;
        flex-direction: column;
        gap: var(--ty-gap);
    }

    .ty-card {
        background: var(--ty-card);
        border: 1px solid var(--ty-border);
        border-radius: var(--ty-radius);
        overflow: hidden;
    }

    .ty-card__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 16px;
        border-bottom: 1px solid var(--ty-border);
    }

    .ty-card__title {
        margin: 0;
        font-size: 13px;
        font-weight: 650;
        letter-spacing: -0.01em;
    }

    .ty-card__body { padding: 14px 16px; }

    .ty-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 24px;
        padding: 0 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .ty-pill--warning { background: #fef3c7; color: #92400e; }
    .ty-pill--success { background: #d1fae5; color: #065f46; }
    .ty-pill--danger { background: #fee2e2; color: #991b1b; }
    .ty-pill--info { background: #dbeafe; color: #1e40af; }
    .ty-pill--primary { background: #ede9fe; color: #5b21b6; }
    .ty-pill--gray { background: var(--ty-soft); color: var(--ty-muted); border-color: var(--ty-border); }

    .dark .ty-pill--warning { background: rgba(245, 158, 11, .18); color: #fcd34d; }
    .dark .ty-pill--success { background: rgba(16, 185, 129, .18); color: #6ee7b7; }
    .dark .ty-pill--danger { background: rgba(239, 68, 68, .18); color: #fca5a5; }
    .dark .ty-pill--info { background: rgba(59, 130, 246, .18); color: #93c5fd; }
    .dark .ty-pill--primary { background: rgba(139, 92, 246, .18); color: #c4b5fd; }

    .ty-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
        color: var(--ty-muted);
        font-size: 13px;
    }

    .ty-line {
        display: grid;
        grid-template-columns: 48px minmax(0, 1fr) auto;
        gap: 12px;
        align-items: start;
    }

    .ty-thumb {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        border: 1px solid var(--ty-border);
        background: var(--ty-soft);
        display: grid;
        place-items: center;
        overflow: hidden;
        flex-shrink: 0;
        font-size: 11px;
        font-weight: 700;
        color: var(--ty-muted);
    }

    .ty-thumb img { width: 100%; height: 100%; object-fit: cover; }

    .ty-line__title {
        margin: 0;
        font-size: 14px;
        font-weight: 650;
        line-height: 1.35;
        word-break: break-word;
    }

    .ty-line__sub {
        margin: 4px 0 0;
        font-size: 12px;
        color: var(--ty-muted);
        line-height: 1.4;
    }

    .ty-line__price {
        font-size: 14px;
        font-weight: 650;
        white-space: nowrap;
        text-align: right;
    }

    .ty-line__qty {
        margin-top: 2px;
        font-size: 12px;
        color: var(--ty-muted);
        text-align: right;
    }

    .ty-kv {
        display: grid;
        gap: 10px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid var(--ty-border);
    }

    .ty-kv__row {
        display: grid;
        grid-template-columns: 140px minmax(0, 1fr);
        gap: 12px;
        font-size: 13px;
    }

    @media (max-width: 640px) {
        .ty-kv__row { grid-template-columns: 1fr; gap: 2px; }
    }

    .ty-kv__label { color: var(--ty-muted); }
    .ty-kv__value { word-break: break-word; }

    .ty-totals {
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid var(--ty-border);
        display: grid;
        gap: 8px;
        max-width: 280px;
        margin-left: auto;
        width: 100%;
    }

    .ty-totals__row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        font-size: 13px;
    }

    .ty-totals__row--muted { color: var(--ty-muted); }
    .ty-totals__row--total {
        padding-top: 8px;
        border-top: 1px solid var(--ty-border);
        font-weight: 700;
        font-size: 14px;
    }

    .ty-totals__discount { color: #059669; }
    .dark .ty-totals__discount { color: #34d399; }

    .ty-empty {
        margin: 0;
        font-size: 13px;
        color: var(--ty-muted);
    }

    .ty-link {
        color: #2563eb;
        text-decoration: none;
    }

    .ty-link:hover { text-decoration: underline; }
    .dark .ty-link { color: #93c5fd; }

    .ty-customer {
        display: grid;
        grid-template-columns: 40px minmax(0, 1fr);
        gap: 12px;
        align-items: start;
    }

    .ty-avatar {
        width: 40px;
        height: 40px;
        border-radius: 999px;
        background: linear-gradient(135deg, #111827, #4b5563);
        color: #fff;
        display: grid;
        place-items: center;
        font-size: 12px;
        font-weight: 700;
    }

    .ty-stack { display: grid; gap: 6px; font-size: 13px; }
    .ty-stack a { color: inherit; text-decoration: none; }
    .ty-stack a:hover { text-decoration: underline; }
    .ty-stack__muted { color: var(--ty-muted); font-size: 12px; }

    .ty-divider {
        height: 1px;
        background: var(--ty-border);
        margin: 12px 0;
    }

    .ty-side-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: 13px;
    }

    .ty-side-row span:first-child { color: var(--ty-muted); }
    .ty-side-row span:last-child { text-align: right; word-break: break-word; }

    .ty-timeline { list-style: none; margin: 0; padding: 0; }
    .ty-timeline li {
        position: relative;
        padding: 0 0 14px 18px;
        border-left: 2px solid var(--ty-border);
    }
    .ty-timeline li:last-child { padding-bottom: 0; border-left-color: transparent; }
    .ty-timeline li::before {
        content: "";
        position: absolute;
        left: -5px;
        top: 4px;
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #9ca3af;
        box-shadow: 0 0 0 3px var(--ty-card);
    }
    .ty-timeline li[data-color="success"]::before { background: #10b981; }
    .ty-timeline li[data-color="warning"]::before { background: #f59e0b; }
    .ty-timeline li[data-color="danger"]::before { background: #ef4444; }
    .ty-timeline li[data-color="primary"]::before { background: #8b5cf6; }
    .ty-timeline li[data-color="info"]::before { background: #3b82f6; }

    .ty-timeline__label { font-size: 13px; font-weight: 600; margin: 0; }
    .ty-timeline__desc { margin: 2px 0 0; font-size: 12px; color: var(--ty-muted); word-break: break-word; }
    .ty-timeline__at { margin: 2px 0 0; font-size: 11px; color: var(--ty-muted); }

    .ty-sibling {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        padding: 8px 0;
        border-top: 1px solid var(--ty-border);
        font-size: 12px;
    }
    .ty-sibling:first-child { border-top: 0; padding-top: 0; }
</style>
