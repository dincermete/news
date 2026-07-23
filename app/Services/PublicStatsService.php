<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\SiteStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PublicStatsService
{
    public const CACHE_TTL_SECONDS = 300;

    public const CACHE_KEY_ACTIVE_SITES = 'public_stats.active_sites';

    public const CACHE_KEY_PUBLISHED_ORDERS = 'public_stats.published_orders';

    public const CACHE_KEY_CUSTOMERS = 'public_stats.customers';

    public function activeSiteCount(): int
    {
        return (int) Cache::remember(
            self::CACHE_KEY_ACTIVE_SITES,
            self::CACHE_TTL_SECONDS,
            fn (): int => Site::query()->where('status', SiteStatus::Active)->count(),
        );
    }

    public function publishedOrderCount(): int
    {
        return (int) Cache::remember(
            self::CACHE_KEY_PUBLISHED_ORDERS,
            self::CACHE_TTL_SECONDS,
            fn (): int => Order::query()
                ->whereIn('status', [
                    OrderStatus::Published,
                    OrderStatus::ReportSent,
                ])
                ->count(),
        );
    }

    public function customerCount(): int
    {
        return (int) Cache::remember(
            self::CACHE_KEY_CUSTOMERS,
            self::CACHE_TTL_SECONDS,
            fn (): int => User::query()
                ->whereNotIn('role', [UserRole::Admin, UserRole::Editor])
                ->count(),
        );
    }

    /**
     * @return array{active_sites: int, published_orders: int, customers: int}
     */
    public function all(): array
    {
        return [
            'active_sites' => $this->activeSiteCount(),
            'published_orders' => $this->publishedOrderCount(),
            'customers' => $this->customerCount(),
        ];
    }

    public function forgetActiveSites(): void
    {
        Cache::forget(self::CACHE_KEY_ACTIVE_SITES);
    }

    public function forget(): void
    {
        $this->forgetActiveSites();
        Cache::forget(self::CACHE_KEY_PUBLISHED_ORDERS);
        Cache::forget(self::CACHE_KEY_CUSTOMERS);
    }

    /**
     * @return array{active_sites: int, published_orders: int, customers: int}
     */
    public function refresh(): array
    {
        $this->forget();

        return $this->all();
    }
}
