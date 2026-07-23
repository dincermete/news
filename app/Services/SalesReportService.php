<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Site;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    public function revenueBetween(Carbon $from, Carbon $to): float
    {
        return round((float) Payment::query()
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('paid_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->sum('amount'), 2);
    }

    /**
     * @return list<array{site_id: int, domain: string, orders_count: int, revenue: float}>
     */
    public function topSellingSites(int $limit = 10, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $rows = Order::query()
            ->select([
                'site_id',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('COALESCE(SUM(price), 0) as revenue'),
            ])
            ->whereNotNull('site_id')
            ->when(
                $from !== null && $to !== null,
                fn ($query) => $query->whereBetween('created_at', [
                    $from->copy()->startOfDay(),
                    $to->copy()->endOfDay(),
                ]),
            )
            ->groupBy('site_id')
            ->orderByDesc('orders_count')
            ->limit($limit)
            ->get();

        $domains = Site::query()
            ->whereIn('id', $rows->pluck('site_id'))
            ->pluck('domain', 'id');

        return $rows->map(fn ($row): array => [
            'site_id' => (int) $row->site_id,
            'domain' => (string) ($domains[$row->site_id] ?? '—'),
            'orders_count' => (int) $row->orders_count,
            'revenue' => round((float) $row->revenue, 2),
        ])->values()->all();
    }

    /**
     * @return list<array{category_id: int|null, category: string, orders_count: int, revenue: float}>
     */
    public function categoryPerformance(): array
    {
        $rows = Order::query()
            ->join('sites', 'orders.site_id', '=', 'sites.id')
            ->leftJoin('site_categories', 'sites.site_category_id', '=', 'site_categories.id')
            ->select([
                'sites.site_category_id as category_id',
                DB::raw("COALESCE(site_categories.name, 'Kategorisiz') as category"),
                DB::raw('COUNT(orders.id) as orders_count'),
                DB::raw('COALESCE(SUM(orders.price), 0) as revenue'),
            ])
            ->whereNotNull('orders.site_id')
            ->groupBy('sites.site_category_id', 'site_categories.name')
            ->orderByDesc('orders_count')
            ->get();

        return $rows->map(fn ($row): array => [
            'category_id' => $row->category_id !== null ? (int) $row->category_id : null,
            'category' => (string) $row->category,
            'orders_count' => (int) $row->orders_count,
            'revenue' => round((float) $row->revenue, 2),
        ])->values()->all();
    }

    /**
     * @return array{labels: list<string>, data: list<float>}
     */
    public function dailyRevenueSeries(Carbon $from, Carbon $to): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();

        $driver = DB::connection()->getDriverName();

        $dayExpression = match ($driver) {
            'sqlite' => "strftime('%Y-%m-%d', paid_at)",
            default => 'DATE(paid_at)',
        };

        $rows = Payment::query()
            ->select([
                DB::raw("{$dayExpression} as day"),
                DB::raw('COALESCE(SUM(amount), 0) as total'),
            ])
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('paid_at', [$from, $to])
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $data = [];

        foreach (CarbonPeriod::create($from->toDateString(), $to->copy()->startOfDay()->toDateString()) as $date) {
            $key = $date->toDateString();
            $labels[] = $date->format('d.m');
            $data[] = round((float) ($rows[$key] ?? 0), 2);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
