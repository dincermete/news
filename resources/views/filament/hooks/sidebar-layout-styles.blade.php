<style data-filament-admin-layout>
    /* ── Tokens ─────────────────────────────────────────────── */
    html.fi {
        --fs-topbar-height: 56px;
        --fs-sidebar-header-height: 56px;
        --fs-sidebar-desktop-position: sticky;
        --fs-sidebar-desktop-top: var(--fs-topbar-height);
        --fs-sidebar-desktop-height: calc(100svh - var(--fs-topbar-height));
        --fs-sidebar-desktop-min-height: calc(100svh - var(--fs-topbar-height));
        --fs-sidebar-desktop-align-self: flex-start;
        --fs-collapsed-sidebar-top: var(--fs-topbar-height);
        --fs-collapsed-sidebar-height: calc(100svh - var(--fs-topbar-height));

        /* Single corner radius (shadcn-style) */
        --radius: 0.5rem;
        --radius-sm: var(--radius);
        --radius-md: var(--radius);
        --radius-lg: var(--radius);
        --radius-xl: var(--radius);
        --radius-2xl: var(--radius);
        --radius-3xl: var(--radius);
        --radius-4xl: var(--radius);

        --fs-card-radius: var(--radius);
        --fs-control-radius: var(--radius);
        --fs-button-radius: var(--radius);
        --fs-button-group-radius: var(--radius);
        --fs-dropdown-radius: var(--radius);
        --fs-sidebar-item-radius: var(--radius);
        --fs-badge-radius: var(--radius);
        --fs-surface-shadow: none;
        --fs-input-shadow: none;
        --fs-body-font-size: 0.875rem;
    }

    /* ── Typography (~14px body) ─────────────────────────────── */
    .fi-body,
    .fi-simple-layout,
    .fi-main,
    .fi-main-ctn {
        font-size: var(--fs-body-font-size, 0.875rem) !important;
        line-height: 1.5;
    }

    .fi-topbar,
    .fi-topbar-ctn {
        height: 56px !important;
        min-height: 56px !important;
        max-height: 56px !important;
    }

    /* ── Sticky sidebar shell ───────────────────────────────── */
    @media (min-width: 1024px) {
        .fi-body-has-topbar .fi-main-sidebar.fi-sidebar {
            display: flex !important;
            flex-direction: column !important;
            align-self: flex-start !important;
            position: sticky !important;
            top: var(--fs-topbar-height) !important;
            bottom: auto !important;
            height: calc(100svh - var(--fs-topbar-height)) !important;
            max-height: calc(100svh - var(--fs-topbar-height)) !important;
            min-height: calc(100svh - var(--fs-topbar-height)) !important;
            overflow: hidden !important;
        }
    }

    .fi-sidebar-nav {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
        overscroll-behavior: contain;
        scrollbar-width: thin;
        scrollbar-color: color-mix(in oklch, var(--sidebar-foreground) 28%, transparent) transparent;
    }

    .fi-sidebar-nav::-webkit-scrollbar {
        width: 6px;
    }

    .fi-sidebar-nav::-webkit-scrollbar-track {
        background: transparent;
        margin-block: 0.25rem;
    }

    .fi-sidebar-nav::-webkit-scrollbar-thumb {
        background: color-mix(in oklch, var(--sidebar-foreground) 28%, transparent);
        border-radius: 999px;
        border: 1px solid transparent;
        background-clip: content-box;
    }

    .fi-sidebar-nav::-webkit-scrollbar-thumb:hover {
        background: color-mix(in oklch, var(--sidebar-foreground) 45%, transparent);
        background-clip: content-box;
    }

    .fi-sidebar-footer {
        flex: 0 0 auto !important;
        position: sticky !important;
        bottom: 0 !important;
        margin-top: auto !important;
        z-index: 20;
        background: var(--sidebar) !important;
        border-top: 1px solid var(--sidebar-border) !important;
        padding: 0.5rem 0.75rem !important;
    }

    .fi-sidebar-footer .fi-user-menu {
        width: 100%;
    }

    .fi-sidebar-footer .fi-user-menu-trigger {
        width: 100% !important;
        justify-content: flex-start !important;
    }

    /* ── Sidebar active: muted pill, no primary color ───────── */
    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn,
    .fi-topbar-item.fi-active > .fi-topbar-item-btn {
        background: var(--sidebar-accent) !important;
        color: var(--sidebar-accent-foreground) !important;
        box-shadow: none !important;
    }

    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn .fi-sidebar-item-icon,
    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn .fi-sidebar-item-label,
    .fi-topbar-item.fi-active > .fi-topbar-item-btn .fi-sidebar-item-icon,
    .fi-topbar-item.fi-active > .fi-topbar-item-btn .fi-sidebar-item-label {
        color: var(--sidebar-accent-foreground) !important;
        opacity: 1 !important;
    }

    .fi-sidebar-item:not(.fi-active) > .fi-sidebar-item-btn .fi-sidebar-item-icon {
        color: color-mix(in oklch, var(--sidebar-foreground) 55%, transparent) !important;
    }

    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn .fi-badge {
        background: color-mix(in oklch, var(--sidebar-foreground) 10%, transparent) !important;
        color: var(--sidebar-accent-foreground) !important;
        border-color: transparent !important;
    }

    /* ── Cards / sections: border only, no shadow ───────────── */
    .fi-section:not(.fi-section-not-contained),
    .fi-ta-ctn,
    .fi-wi-widget:not(.fi-wi-stats-overview),
    .fi-wi-chart,
    .fi-wi-table,
    .fi-wi-stats-overview-stat,
    .fi-fo-repeater-item,
    .fi-fieldset,
    .fi-modal-window,
    .fi-dropdown-panel,
    .fi-fo-wizard-header-step-icon-ctn {
        box-shadow: none !important;
    }

    .fi-section:not(.fi-section-not-contained),
    .fi-ta-ctn,
    .fi-wi-widget:not(.fi-wi-stats-overview),
    .fi-wi-chart,
    .fi-wi-table,
    .fi-wi-stats-overview-stat,
    .fi-fo-repeater-item,
    .fi-fieldset {
        border: 1px solid var(--border, #e4e4e7) !important;
        border-radius: var(--radius) !important;
        background: var(--card, #fff) !important;
    }

    /* ── Unified radius on controls ─────────────────────────── */
    .fi-btn,
    .fi-icon-btn,
    .fi-input-wrp,
    .fi-select-input,
    .fi-textarea,
    .fi-badge,
    .fi-tabs,
    .fi-tabs-item,
    .fi-pagination-item,
    .fi-dropdown-panel,
    .fi-modal-window {
        border-radius: var(--radius) !important;
    }

    /* ── Buttons: flat primary, outlined secondary ──────────── */
    .fi-btn,
    .fi-icon-btn {
        box-shadow: none !important;
        background-image: none !important;
    }

    .fi-btn.fi-color-primary:not(.fi-outlined):not(.fi-btn-outlined),
    .fi-ac-btn-action.fi-color-primary:not(.fi-outlined) {
        background: var(--primary) !important;
        background-image: none !important;
        border-color: var(--primary) !important;
        color: var(--primary-foreground) !important;
        box-shadow: none !important;
    }

    .fi-btn.fi-color-primary:not(.fi-outlined):hover,
    .fi-ac-btn-action.fi-color-primary:not(.fi-outlined):hover {
        background: color-mix(in oklch, var(--primary) 90%, var(--foreground)) !important;
        background-image: none !important;
        border-color: color-mix(in oklch, var(--primary) 90%, var(--foreground)) !important;
        box-shadow: none !important;
    }

    .fi-btn.fi-color-gray,
    .fi-btn.fi-outlined,
    .fi-btn[class*='fi-outlined'] {
        background: var(--background, #fff) !important;
        border: 1px solid var(--border, #e4e4e7) !important;
        color: var(--foreground) !important;
        box-shadow: none !important;
    }

    .fi-btn.fi-color-gray:hover,
    .fi-btn.fi-outlined:hover {
        background: var(--accent, #f4f4f5) !important;
        box-shadow: none !important;
    }

    /* ── Form density ───────────────────────────────────────── */
    .fi-sc-has-gap {
        gap: 1rem !important; /* gap-4 */
    }

    .fi-sc-has-gap.fi-sc-dense {
        gap: 0.75rem !important;
    }

    .fi-section-content,
    .fi-fo-component-ctn,
    .fi-grid {
        row-gap: 1rem !important;
    }

    .fi-fo-field-wrp {
        gap: 0.375rem !important;
    }

    .fi-section-content-ctn > .fi-section-content {
        padding-block: 1rem !important;
    }

    /* ── Empty states: compact + muted ──────────────────────── */
    .fi-ta-empty-state {
        padding-block: 2rem !important;
        padding-inline: 1.5rem !important;
    }

    .fi-ta-empty-state-content {
        gap: 0.5rem !important;
    }

    .fi-ta-empty-state-icon-bg {
        margin-bottom: 0.5rem !important;
        padding: 0.5rem !important;
        background: transparent !important;
        border-radius: var(--radius) !important;
    }

    .fi-ta-empty-state-icon-bg .fi-icon,
    .fi-ta-empty-state-icon-bg svg {
        width: 1.75rem !important; /* 28px */
        height: 1.75rem !important;
        color: var(--muted-foreground) !important;
        opacity: 0.7;
    }

    .fi-ta-empty-state-heading {
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        color: var(--muted-foreground) !important;
    }

    .fi-ta-empty-state-description {
        margin-top: 0.125rem !important;
        font-size: 0.8125rem !important;
        color: color-mix(in oklch, var(--muted-foreground) 85%, transparent) !important;
    }

    .fi-ta-empty-state .fi-ta-actions {
        margin-top: 1rem !important;
    }

    /* Page-level empty / no-records style blocks */
    .fi-page-empty-state-icon,
    .fi-simple-page .fi-icon {
        width: 1.75rem !important;
        height: 1.75rem !important;
    }

    /* ── Icons: thinner stroke (Lucide-like; SVGs still Heroicons) */
    .fi-sidebar-item-icon svg,
    .fi-icon svg,
    .fi-btn svg,
    .fi-icon-btn svg,
    .fi-ta-empty-state-icon-bg svg {
        stroke-width: 1.5;
    }
</style>
