<?php

namespace App\Services;

use App\Enums\SiteStatus;
use App\Models\Site;
use Illuminate\Support\Collection;

class BudgetPackageSuggester
{
    /**
     * @param  array<string, float>|null  $categoryWeights  category name => weight (0-1)
     * @return array{sites: list<Site>, total: float}
     */
    public function suggest(
        float $budget,
        ?int $categoryId = null,
        ?array $categoryWeights = null,
        ?int $count = null,
    ): array {
        if ($budget <= 0) {
            return ['sites' => [], 'total' => 0.0];
        }

        /** @var Collection<int, Site> $candidates */
        $candidates = Site::query()
            ->with('category')
            ->where('status', SiteStatus::Active)
            ->when(
                $categoryId !== null,
                fn ($query) => $query->where('site_category_id', $categoryId),
            )
            ->where('price', '>', 0)
            ->where('price', '<=', $budget)
            ->orderBy('price')
            ->get();

        if ($candidates->isEmpty()) {
            return ['sites' => [], 'total' => 0.0];
        }

        $byCategory = $candidates->groupBy('site_category_id');
        $selected = collect();
        $remaining = $budget;

        $categoryOrder = $byCategory->keys()->all();

        if ($categoryWeights !== null && $categoryWeights !== []) {
            $categoryOrder = $byCategory->keys()
                ->sortByDesc(function ($categoryIdKey) use ($byCategory, $categoryWeights): float {
                    $name = strtolower((string) ($byCategory[$categoryIdKey]->first()?->category?->name ?? ''));

                    foreach ($categoryWeights as $weightName => $weight) {
                        if ($name !== '' && str_contains($name, strtolower((string) $weightName))) {
                            return (float) $weight;
                        }
                    }

                    return 0.0;
                })
                ->values()
                ->all();
        }

        while ($remaining > 0) {
            $pickedInRound = false;

            foreach ($categoryOrder as $categoryIdKey) {
                $sites = $byCategory[$categoryIdKey] ?? collect();

                $affordable = $sites
                    ->reject(fn (Site $site): bool => $selected->contains('id', $site->id))
                    ->filter(fn (Site $site): bool => (float) $site->price <= $remaining)
                    ->sortBy('price')
                    ->values();

                if ($affordable->isEmpty()) {
                    continue;
                }

                /** @var Site $pick */
                $pick = $affordable->first();
                $selected->push($pick);
                $remaining = round($remaining - (float) $pick->price, 2);
                $pickedInRound = true;

                if ($count !== null && $selected->count() >= $count) {
                    break 2;
                }

                if ($remaining <= 0) {
                    break 2;
                }
            }

            if (! $pickedInRound) {
                break;
            }
        }

        $leftovers = $candidates
            ->reject(fn (Site $site): bool => $selected->contains('id', $site->id))
            ->sortBy('price')
            ->values();

        foreach ($leftovers as $site) {
            if ($count !== null && $selected->count() >= $count) {
                break;
            }

            if ((float) $site->price > $remaining) {
                continue;
            }

            $selected->push($site);
            $remaining = round($remaining - (float) $site->price, 2);
        }

        $sites = $selected->unique('id')->values()->all();

        if ($count !== null) {
            $sites = array_slice($sites, 0, $count);
        }

        $total = round(collect($sites)->sum(fn (Site $site): float => (float) $site->price), 2);

        return [
            'sites' => $sites,
            'total' => $total,
        ];
    }
}
