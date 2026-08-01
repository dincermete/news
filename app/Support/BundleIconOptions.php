<?php

namespace App\Support;

use Filament\Support\Colors\Color;

final class BundleIconOptions
{
    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'heroicon-o-cube' => 'Paket',
            'heroicon-o-gift' => 'Hediye',
            'heroicon-o-rocket-launch' => 'Roket',
            'heroicon-o-sparkles' => 'Parıltı',
            'heroicon-o-star' => 'Yıldız',
            'heroicon-o-fire' => 'Ateş',
            'heroicon-o-bolt' => 'Şimşek',
            'heroicon-o-megaphone' => 'Megafon',
            'heroicon-o-newspaper' => 'Gazete',
            'heroicon-o-globe-alt' => 'Küre',
            'heroicon-o-link' => 'Bağlantı',
            'heroicon-o-chart-bar' => 'Grafik',
            'heroicon-o-trophy' => 'Kupa',
            'heroicon-o-shopping-bag' => 'Alışveriş',
            'heroicon-o-building-office-2' => 'Ofis',
            'heroicon-o-cpu-chip' => 'Teknoloji',
            'heroicon-o-heart' => 'Kalp',
            'heroicon-o-academic-cap' => 'Eğitim',
            'heroicon-o-briefcase' => 'Çanta',
            'heroicon-o-tag' => 'Etiket',
            'heroicon-o-users' => 'Kullanıcılar',
            'heroicon-o-currency-dollar' => 'Para',
            'heroicon-o-presentation-chart-line' => 'Sunum',
            'heroicon-o-squares-2x2' => 'Kareler',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function icons(): array
    {
        $icons = [];

        foreach (array_keys(self::labels()) as $icon) {
            $icons[$icon] = $icon;
        }

        return $icons;
    }

    public static function default(): string
    {
        return 'heroicon-o-cube';
    }

    public static function defaultPaletteKey(): string
    {
        return 'coral';
    }

    /**
     * Ordered gradient palettes for cards and icon accent colors.
     *
     * @return array<string, array{from: string, to: string, label: string}>
     */
    public static function palette(): array
    {
        return [
            'coral' => ['from' => '#ef4444', 'to' => '#f97316', 'label' => 'Mercan'],
            'amber' => ['from' => '#f59e0b', 'to' => '#fbbf24', 'label' => 'Amber'],
            'lime' => ['from' => '#65a30d', 'to' => '#a3e635', 'label' => 'Lime'],
            'emerald' => ['from' => '#059669', 'to' => '#34d399', 'label' => 'Zümrüt'],
            'teal' => ['from' => '#0d9488', 'to' => '#5eead4', 'label' => 'Turkuaz'],
            'cyan' => ['from' => '#0891b2', 'to' => '#67e8f9', 'label' => 'Cyan'],
            'sky' => ['from' => '#0284c7', 'to' => '#7dd3fc', 'label' => 'Gökyüzü'],
            'blue' => ['from' => '#2563eb', 'to' => '#93c5fd', 'label' => 'Mavi'],
            'indigo' => ['from' => '#4f46e5', 'to' => '#a5b4fc', 'label' => 'Indigo'],
            'violet' => ['from' => '#7c3aed', 'to' => '#c4b5fd', 'label' => 'Mor'],
            'fuchsia' => ['from' => '#c026d3', 'to' => '#f0abfc', 'label' => 'Fuşya'],
            'rose' => ['from' => '#e11d48', 'to' => '#fb7185', 'label' => 'Gül'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function paletteLabels(): array
    {
        return collect(self::palette())
            ->mapWithKeys(fn (array $swatch, string $key): array => [$key => $swatch['label']])
            ->all();
    }

    /**
     * Filament button colors for the icon grid, cycled in palette order.
     *
     * @return array<string, array<int, string>>
     */
    public static function iconFilamentColors(): array
    {
        $palette = array_values(self::palette());
        $colors = [];

        foreach (array_keys(self::labels()) as $index => $icon) {
            $swatch = $palette[$index % count($palette)];
            $colors[$icon] = Color::hex($swatch['from']);
        }

        return $colors;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function paletteFilamentColors(): array
    {
        $colors = [];

        foreach (self::palette() as $key => $swatch) {
            $colors[$key] = Color::hex($swatch['from']);
        }

        return $colors;
    }

    /**
     * @return array{from: string, to: string, label: string}
     */
    public static function paletteForIcon(string $icon): array
    {
        $icons = array_keys(self::labels());
        $palette = array_values(self::palette());
        $index = array_search($icon, $icons, true);

        if ($index === false) {
            return self::palette()[self::defaultPaletteKey()];
        }

        return $palette[$index % count($palette)];
    }

    public static function paletteKeyForIcon(string $icon): string
    {
        $icons = array_keys(self::labels());
        $keys = array_keys(self::palette());
        $index = array_search($icon, $icons, true);

        if ($index === false) {
            return self::defaultPaletteKey();
        }

        return $keys[$index % count($keys)];
    }

    public static function matchPaletteKey(?string $from, ?string $to): ?string
    {
        foreach (self::palette() as $key => $swatch) {
            if (strcasecmp((string) $from, $swatch['from']) === 0
                && strcasecmp((string) $to, $swatch['to']) === 0) {
                return $key;
            }
        }

        return null;
    }

    /**
     * @return list<array{from: string, to: string}>
     */
    public static function palettePairs(): array
    {
        return array_values(array_map(
            fn (array $swatch): array => ['from' => $swatch['from'], 'to' => $swatch['to']],
            self::palette(),
        ));
    }
}
