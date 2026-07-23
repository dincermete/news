<?php

return [
    'style' => 'lyra',
    'base_color' => 'zinc',
    'theme_color' => 'amber',
    'chart_color' => 'amber',
    'font' => 'inter',
    'heading_font' => 'inherit',
    'icon_library' => 'lucide',
    'css_mode' => 'inline',
    /*
     * Single radius token (0.5rem). Lyra zeros corners unless radius is
     * configured; hasExplicitRadius then remaps component radii — we force
     * every surface to the same --radius value via style_overrides below.
     */
    'radius' => 'default',
    'menu_color' => 'default',
    'menu_accent' => 'subtle',
    'sidebar_variant' => 'sidebar',
    'surface_shadow' => 'none',
    'apply_panel_font' => true,
    'default_theme_mode' => 'light',
    'dark_mode' => true,
    'force_dark_mode' => false,
    'token_overrides' => [
        'light' => [
            // Active nav = zinc-100 pill, darker text (not primary amber)
            'sidebar-accent' => 'oklch(0.967 0.001 286.375)', // zinc-100
            'sidebar-accent-foreground' => 'oklch(0.21 0.006 285.885)', // zinc-900
        ],
        'dark' => [
            'sidebar-accent' => 'oklch(0.274 0.006 286.033)', // zinc-800
            'sidebar-accent-foreground' => 'oklch(0.985 0 0)',
        ],
    ],
    'style_overrides' => [
        // Typography / density
        'radius' => '0.5rem',
        'fs-body-font-size' => '0.875rem',
        'fs-font-size-sm' => '0.8125rem',
        'fs-page-gap' => '1rem',
        'fs-card-gap' => '1rem',
        'fs-main-padding-y' => '1rem',
        'fs-page-header-main-gap' => '1rem',
        'fs-section-padding' => '0.75rem',
        'fs-card-padding-x' => '1rem',
        'fs-card-padding-y' => '1rem',
        'fs-stat-gap' => '0.75rem',

        // One radius everywhere
        'fs-card-radius' => 'var(--radius)',
        'fs-control-radius' => 'var(--radius)',
        'fs-button-radius' => 'var(--radius)',
        'fs-button-group-radius' => 'var(--radius)',
        'fs-dropdown-radius' => 'var(--radius)',
        'fs-sidebar-item-radius' => 'var(--radius)',
        'fs-dropdown-item-radius' => 'var(--radius)',
        'fs-badge-radius' => 'var(--radius)',
        'fs-checkbox-radius' => 'calc(var(--radius) * 0.5)',
        'fs-table-header-button-radius' => 'var(--radius)',
        'fs-pagination-radius' => 'var(--radius)',
        'fs-tab-list-radius' => 'var(--radius)',
        'fs-tab-item-radius' => 'var(--radius)',
        'fs-rich-editor-tool-radius' => 'var(--radius)',
        'fs-skeleton-line-radius' => 'var(--radius)',

        // Flat surfaces
        'fs-surface-shadow' => 'none',
        'fs-input-shadow' => 'none',

        // Sticky sidebar (existing)
        'fs-topbar-height' => '56px',
        'fs-sidebar-header-height' => '56px',
        'fs-sidebar-desktop-position' => 'sticky',
        'fs-sidebar-desktop-top' => 'var(--fs-topbar-height)',
        'fs-sidebar-desktop-height' => 'calc(100svh - var(--fs-topbar-height))',
        'fs-sidebar-desktop-min-height' => 'calc(100svh - var(--fs-topbar-height))',
        'fs-sidebar-desktop-align-self' => 'flex-start',
        'fs-collapsed-sidebar-top' => 'var(--fs-topbar-height)',
        'fs-collapsed-sidebar-height' => 'calc(100svh - var(--fs-topbar-height))',
    ],
    'selector_map' => [],
];
