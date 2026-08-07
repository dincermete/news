<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\CategoryPerformanceWidget;
use App\Filament\Widgets\LiveVisitorsWidget;
use App\Filament\Widgets\PublicStatsOverview;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\TopSellingSitesWidget;
use Filament\Enums\UserMenuPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Irajul\FilamentShadcnTheme\FilamentShadcnThemePlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName(function (): string {
                try {
                    return site_setting('site_name');
                } catch (\Throwable) {
                    return 'Tanıtım Yazısı';
                }
            })
            ->brandLogo(function (): ?string {
                try {
                    return site_setting()->logoUrl();
                } catch (\Throwable) {
                    return null;
                }
            })
            ->darkModeBrandLogo(function (): ?string {
                try {
                    return site_setting()->logoUrl(dark: true) ?: site_setting()->logoUrl();
                } catch (\Throwable) {
                    return null;
                }
            })
            ->brandLogoHeight('2rem')
            ->databaseNotifications()
            ->userMenu(position: UserMenuPosition::Sidebar)
            ->plugin(FilamentShadcnThemePlugin::make())
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->collapsibleNavigationGroups()
            ->navigationGroups([
                NavigationGroup::make('Siparişler')->collapsible(),
                NavigationGroup::make('Ödemeler')->collapsible(),
                NavigationGroup::make('Müşteriler')->collapsible(),
                NavigationGroup::make('Siteler')->collapsible(),
                NavigationGroup::make('Ürünler')->collapsible(),
                NavigationGroup::make('Kampanyalar')->collapsible(),
                NavigationGroup::make('Destek')->collapsible(),
                NavigationGroup::make('İçerik')->collapsible(),
                NavigationGroup::make('Bildirimler')->collapsible(),
                NavigationGroup::make('Sistem')->collapsible(),
            ])
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                PublicStatsOverview::class,
                LiveVisitorsWidget::class,
                RevenueChartWidget::class,
                TopSellingSitesWidget::class,
                CategoryPerformanceWidget::class,
                AccountWidget::class,
            ])
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn (): string => view('filament.hooks.sidebar-layout-styles')->render(),
            )
            ->renderHook(
                PanelsRenderHook::SCRIPTS_AFTER,
                fn (): string => Blade::render("@vite('resources/js/realtime.js')"),
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
