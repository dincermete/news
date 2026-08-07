<?php

namespace App\Services;

use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Models\Province;
use App\Models\Site;
use App\Support\CatalogQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProvinceStatsService
{
    public const CACHE_TTL_SECONDS = 3600;

    public const HUB_CACHE_KEY = 'provinces.hub.counts.v1';

    /**
     * @return array{
     *     sites_count: int,
     *     top_categories: list<array{name: string, slug: string, count: int}>,
     *     da_min: float|null,
     *     da_max: float|null,
     *     traffic_min: float|null,
     *     traffic_max: float|null,
     *     price_min: float|null,
     *     price_max: float|null,
     *     summary: string,
     *     faqs: list<array{question: string, answer: string}>,
     *     similar_provinces: list<array{id: int, name: string, slug: string, plate_code: string, sites_count: int}>,
     *     related_sites: list<Site>
     * }
     */
    public function forProvince(Province $province): array
    {
        return Cache::remember(
            $this->cacheKey($province),
            self::CACHE_TTL_SECONDS,
            fn (): array => $this->build($province),
        );
    }

    /**
     * @return Collection<int, Province>
     */
    public function provincesWithCounts(): Collection
    {
        /** @var array<int, int> $counts */
        $counts = Cache::remember(
            self::HUB_CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            fn (): array => $this->activeSiteCountsByProvince(),
        );

        return Province::query()
            ->orderBy('name')
            ->get()
            ->each(function (Province $province) use ($counts): void {
                $province->setAttribute('active_sites_count', $counts[$province->id] ?? 0);
            });
    }

    public function forget(Province $province): void
    {
        Cache::forget($this->cacheKey($province));
        Cache::forget(self::HUB_CACHE_KEY);
    }

    public function forgetHub(): void
    {
        Cache::forget(self::HUB_CACHE_KEY);
    }

    /**
     * @return array<string, mixed>
     */
    protected function build(Province $province): array
    {
        $siteIds = $province->sites()
            ->where('sites.status', SiteStatus::Active)
            ->pluck('sites.id');

        $sitesCount = $siteIds->count();

        $topCategories = [];
        $daMin = $daMax = $trafficMin = $trafficMax = null;
        $priceMin = $priceMax = null;

        if ($sitesCount > 0) {
            $topCategories = DB::table('sites')
                ->join('site_categories', 'site_categories.id', '=', 'sites.site_category_id')
                ->whereIn('sites.id', $siteIds)
                ->selectRaw('site_categories.name, site_categories.slug, COUNT(*) as aggregate_count')
                ->groupBy('site_categories.id', 'site_categories.name', 'site_categories.slug')
                ->orderByDesc('aggregate_count')
                ->limit(3)
                ->get()
                ->map(fn ($row): array => [
                    'name' => (string) $row->name,
                    'slug' => (string) $row->slug,
                    'count' => (int) $row->aggregate_count,
                ])
                ->all();

            $metrics = Site::query()
                ->whereIn('id', $siteIds)
                ->selectRaw('MIN(da_value) as da_min, MAX(da_value) as da_max, MIN(monthly_traffic_value) as traffic_min, MAX(monthly_traffic_value) as traffic_max')
                ->first();

            $daMin = $metrics?->da_min !== null ? (float) $metrics->da_min : null;
            $daMax = $metrics?->da_max !== null ? (float) $metrics->da_max : null;
            $trafficMin = $metrics?->traffic_min !== null ? (float) $metrics->traffic_min : null;
            $trafficMax = $metrics?->traffic_max !== null ? (float) $metrics->traffic_max : null;

            $prices = DB::table('promotional_listings')
                ->whereIn('site_id', $siteIds)
                ->where('type', PromotionalListingType::SiteArticle->value)
                ->where('status', SiteStatus::Active->value)
                ->selectRaw('MIN(COALESCE(discount_price, price)) as price_min, MAX(price) as price_max')
                ->first();

            $priceMin = $prices?->price_min !== null ? (float) $prices->price_min : null;
            $priceMax = $prices?->price_max !== null ? (float) $prices->price_max : null;
        }

        $similar = $this->similarProvinces($province, $topCategories);
        $relatedSites = $this->relatedSites($province, $similar, $sitesCount);

        return [
            'sites_count' => $sitesCount,
            'top_categories' => $topCategories,
            'da_min' => $daMin,
            'da_max' => $daMax,
            'traffic_min' => $trafficMin,
            'traffic_max' => $trafficMax,
            'price_min' => $priceMin,
            'price_max' => $priceMax,
            'summary' => $this->summary($province, $sitesCount, $topCategories, $daMin, $daMax),
            'faqs' => $this->faqs($province, $sitesCount, $topCategories, $priceMin, $priceMax, $daMin, $daMax),
            'similar_provinces' => $similar,
            'related_sites' => $relatedSites,
        ];
    }

    /**
     * @param  list<array{name: string, slug: string, count: int}>  $topCategories
     */
    protected function summary(
        Province $province,
        int $sitesCount,
        array $topCategories,
        ?float $daMin,
        ?float $daMax,
    ): string {
        if ($sitesCount === 0) {
            return "{$province->name} için yayın siteleri çok yakında listelenecek; benzer illerdeki siteleri şimdiden inceleyebilirsiniz.";
        }

        $parts = ["{$province->name} için {$sitesCount} yayın sitesi listeleniyor"];

        if ($topCategories !== []) {
            $names = collect($topCategories)->pluck('name')->implode(', ');
            $parts[] = "en yoğun kategoriler: {$names}";
        }

        if ($daMin !== null && $daMax !== null) {
            $parts[] = 'DA aralığı '.number_format($daMin, 0, ',', '.').'–'.number_format($daMax, 0, ',', '.');
        }

        return implode('; ', $parts).'.';
    }

    /**
     * @param  list<array{name: string, slug: string, count: int}>  $topCategories
     * @return list<array{question: string, answer: string}>
     */
    protected function faqs(
        Province $province,
        int $sitesCount,
        array $topCategories,
        ?float $priceMin,
        ?float $priceMax,
        ?float $daMin,
        ?float $daMax,
    ): array {
        $locative = $province->name_locative;
        $categoryText = $topCategories !== []
            ? collect($topCategories)->pluck('name')->implode(', ')
            : 'haber, e-ticaret, sağlık ve yerel yayın';

        $priceAnswer = ($priceMin !== null && $priceMax !== null)
            ? "{$province->name} listesindeki tanıtım yazısı fiyatları yaklaşık ".number_format($priceMin, 0, ',', '.').'₺ ile '.number_format($priceMax, 0, ',', '.').'₺ arasında değişir; güncel fiyatları site kartlarından karşılaştırabilirsiniz.'
            : "{$province->name} için tanıtım yazısı fiyatları, yayın sitesinin DA/trafik metriklerine ve yayın türüne göre değişir. Tüm siteler kataloğundan güncel fiyatları inceleyebilirsiniz.";

        $metricAnswer = ($daMin !== null && $daMax !== null)
            ? 'Listelenen sitelerin Domain Authority değerleri '.number_format($daMin, 0, ',', '.').'–'.number_format($daMax, 0, ',', '.').' aralığındadır; her kartta 14 SEO metriğini şeffaf biçimde görebilirsiniz.'
            : "Her yayın sitesinde DA, PA, trafik ve backlink metriklerini yan yana karşılaştırarak {$locative} bütçenize uygun seçeneği belirleyebilirsiniz.";

        return [
            [
                'question' => "{$province->name}'de tanıtım yazısı fiyatları ne kadar?",
                'answer' => $priceAnswer,
            ],
            [
                'question' => "{$locative} hangi sektörlere uygun siteler var?",
                'answer' => $sitesCount > 0
                    ? "Bu ilde öne çıkan kategoriler: {$categoryText}. İhtiyacınıza göre kategori filtreleriyle katalogda daraltabilirsiniz."
                    : "{$province->name} sayfası henüz dolduruluyor; benzer illerde {$categoryText} odaklı yayın sitelerini şimdiden inceleyebilirsiniz.",
            ],
            [
                'question' => "{$province->name} için site seçerken nelere bakmalıyım?",
                'answer' => $metricAnswer.' Dofollow, News onayı ve tahmini teslimat süresi de kararınızı kolaylaştırır.',
            ],
            [
                'question' => "{$locative} tanıtım yazısı ne kadar sürede yayınlanır?",
                'answer' => 'Teslimat süresi yayın sitesine göre değişir; her ürün kartında tahmini teslimat bilgisi yer alır. Sipariş sonrası içerik onay sürecini hesabınızdan takip edebilirsiniz.',
            ],
        ];
    }

    /**
     * @param  list<array{name: string, slug: string, count: int}>  $topCategories
     * @return list<array{id: int, name: string, slug: string, plate_code: string, sites_count: int}>
     */
    protected function similarProvinces(Province $province, array $topCategories): array
    {
        $categorySlugs = collect($topCategories)->pluck('slug')->all();
        $counts = $this->activeSiteCountsByProvince();

        if ($categorySlugs === []) {
            return Province::query()
                ->where('id', '!=', $province->id)
                ->orderBy('name')
                ->get()
                ->sortByDesc(fn (Province $p): int => $counts[$p->id] ?? 0)
                ->take(12)
                ->map(fn (Province $p): array => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'plate_code' => $p->plate_code,
                    'sites_count' => $counts[$p->id] ?? 0,
                ])
                ->values()
                ->all();
        }

        $scored = DB::table('province_site')
            ->join('sites', 'sites.id', '=', 'province_site.site_id')
            ->join('site_categories', 'site_categories.id', '=', 'sites.site_category_id')
            ->join('provinces', 'provinces.id', '=', 'province_site.province_id')
            ->where('sites.status', SiteStatus::Active->value)
            ->where('provinces.id', '!=', $province->id)
            ->whereIn('site_categories.slug', $categorySlugs)
            ->selectRaw('provinces.id, provinces.name, provinces.slug, provinces.plate_code, COUNT(*) as overlap_count')
            ->groupBy('provinces.id', 'provinces.name', 'provinces.slug', 'provinces.plate_code')
            ->orderByDesc('overlap_count')
            ->limit(12)
            ->get();

        if ($scored->isEmpty()) {
            return $this->similarProvinces($province, []);
        }

        return $scored->map(fn ($row): array => [
            'id' => (int) $row->id,
            'name' => (string) $row->name,
            'slug' => (string) $row->slug,
            'plate_code' => (string) $row->plate_code,
            'sites_count' => $counts[(int) $row->id] ?? 0,
        ])->all();
    }

    /**
     * @param  list<array{id: int, name: string, slug: string, plate_code: string, sites_count: int}>  $similar
     * @return list<Site>
     */
    protected function relatedSites(Province $province, array $similar, int $sitesCount): array
    {
        if ($sitesCount >= 3 || $similar === []) {
            return [];
        }

        $provinceIds = collect($similar)->pluck('id')->take(6)->all();

        return CatalogQuery::activeSitesWithListing(PromotionalListingType::SiteArticle)
            ->whereHas('provinces', fn ($q) => $q->whereIn('provinces.id', $provinceIds))
            ->whereDoesntHave('provinces', fn ($q) => $q->where('provinces.id', $province->id))
            ->orderByDesc('sites.da_value')
            ->orderBy('sites.id')
            ->limit(8)
            ->get()
            ->each(fn (Site $site) => $site->normalizeJoinedPricingAttributes())
            ->all();
    }

    /**
     * @return array<int, int>
     */
    protected function activeSiteCountsByProvince(): array
    {
        return DB::table('province_site')
            ->join('sites', 'sites.id', '=', 'province_site.site_id')
            ->where('sites.status', SiteStatus::Active->value)
            ->groupBy('province_site.province_id')
            ->pluck(DB::raw('COUNT(*)'), 'province_site.province_id')
            ->map(fn ($count): int => (int) $count)
            ->all();
    }

    protected function cacheKey(Province $province): string
    {
        return 'provinces.stats.'.$province->id.'.v1';
    }
}
