<?php

namespace App\Services;

use App\Enums\SiteStatus;
use App\Models\Province;
use App\Models\Site;
use App\Support\DomainProvinceMatcher;
use Illuminate\Support\Facades\DB;

class AssignProvincesToSites
{
    public function __construct(
        protected ProvinceStatsService $provinceStats,
        protected DomainProvinceMatcher $matcher,
    ) {}

    /**
     * Infer provinces from each site domain and rewrite pivot rows.
     *
     * @return array{sites: int, matched_sites: int, links: int, unmatched: int}
     */
    public function fromDomains(bool $activeOnly = true, bool $replace = true, bool $dryRun = false): array
    {
        $provinces = Province::query()->get(['id', 'slug', 'name']);

        $query = Site::query()->orderBy('id');
        if ($activeOnly) {
            $query->where('status', SiteStatus::Active);
        }

        $sites = $query->get(['id', 'domain']);
        $rows = [];
        $matchedSites = 0;

        foreach ($sites as $site) {
            $provinceIds = $this->matcher->matchIds((string) $site->domain, $provinces);

            if ($provinceIds === []) {
                continue;
            }

            $matchedSites++;

            foreach ($provinceIds as $provinceId) {
                $rows[] = [
                    'province_id' => $provinceId,
                    'site_id' => (int) $site->id,
                ];
            }
        }

        if ($dryRun) {
            return [
                'sites' => $sites->count(),
                'matched_sites' => $matchedSites,
                'links' => count($rows),
                'unmatched' => $sites->count() - $matchedSites,
            ];
        }

        if ($replace) {
            DB::table('province_site')->delete();
        }

        foreach (array_chunk($rows, 1000) as $batch) {
            DB::table('province_site')->insertOrIgnore($batch);
        }

        $this->provinceStats->forgetHub();
        $provinces->each(fn (Province $province) => $this->provinceStats->forget($province));

        return [
            'sites' => $sites->count(),
            'matched_sites' => $matchedSites,
            'links' => count($rows),
            'unmatched' => $sites->count() - $matchedSites,
        ];
    }
}
