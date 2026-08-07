<?php

namespace App\Services;

use App\Enums\Currency;
use App\Enums\MetricSource;
use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Models\Label;
use App\Models\PromotionalListing;
use App\Models\Site;
use App\Models\SiteCategory;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WooCommerceSiteImporter
{
    /**
     * @var array<string, int>
     */
    protected array $stats = [
        'rows' => 0,
        'sites_upserted' => 0,
        'listings_upserted' => 0,
        'skipped_package' => 0,
        'skipped_variation' => 0,
        'skipped_no_domain' => 0,
        'skipped_other' => 0,
        'seo_description' => 0,
        'seo_keywords' => 0,
        'owners' => 0,
        'categories' => 0,
        'logos' => 0,
        'analytics_unresolved' => 0,
        'related_links' => 0,
        'recommended_links' => 0,
        'conflicts' => 0,
    ];

    /**
     * @var array<int, int> WC product id => promotional_listing id
     */
    protected array $wcToListingId = [];

    /**
     * @var list<array{wc_id: int, related: list<int>, recommended: list<int>}>
     */
    protected array $crossSellQueue = [];

    /**
     * @var array<string, array{da?: float|null, pa?: float|null}>
     */
    protected array $domainMetricSeen = [];

    protected ImportedContentCleaner $cleaner;

    public function __construct(
        protected ProductPublicUrl $publicUrls,
        protected bool $dryRun = false,
        protected bool $downloadMedia = true,
        ?ImportedContentCleaner $cleaner = null,
    ) {
        $this->cleaner = $cleaner ?? new ImportedContentCleaner;
    }

    /**
     * @return array<string, int|list<string>>
     */
    public function import(string $csvPath): array
    {
        if (! is_readable($csvPath)) {
            throw new \InvalidArgumentException("CSV okunamadı: {$csvPath}");
        }

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            throw new \InvalidArgumentException("CSV açılamadı: {$csvPath}");
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            throw new \InvalidArgumentException('CSV başlık satırı yok.');
        }

        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]) ?? $headers[0];
        $index = array_flip($headers);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) {
                continue;
            }

            $this->stats['rows']++;
            $this->processRow($this->assoc($headers, $row), $index);
        }

        fclose($handle);

        if (! $this->dryRun) {
            $this->applyCrossSell();
        }

        return $this->stats;
    }

    /**
     * @param  array<string, string|null>  $row
     * @param  array<string, int>  $index
     */
    protected function processRow(array $row, array $index): void
    {
        $type = trim((string) ($row['Tür'] ?? ''));
        $categories = (string) ($row['Kategoriler'] ?? '');
        $name = trim((string) ($row['İsim'] ?? ''));
        $wcId = (int) ($row['Kimlik'] ?? 0);

        if (str_contains($categories, 'Paketler') || filled(trim((string) ($row['Meta: paket_urunleri'] ?? '')))) {
            $this->stats['skipped_package']++;

            return;
        }

        if ($type === 'variation') {
            $this->stats['skipped_variation']++;

            return;
        }

        $listingType = $this->resolveListingType($categories, $name, $type, (string) ($row['Nitelik 1 değer(ler)i'] ?? ''));

        if ($listingType === null) {
            $this->stats['skipped_other']++;

            return;
        }

        $domain = $this->normalizeDomain(
            (string) ($row['Meta: site_url'] ?? ''),
            $name,
        );

        if ($domain === null) {
            $this->stats['skipped_no_domain']++;

            return;
        }

        if ($this->dryRun) {
            $this->tallyDryRun($row, $listingType, $domain);

            return;
        }

        $category = $this->resolveCategory($categories);
        $site = $this->upsertSite($row, $domain, $category);
        $this->stats['sites_upserted']++;

        if ($this->hasOwner($row)) {
            $this->stats['owners']++;
        }

        if ($category !== null) {
            $this->stats['categories']++;
        }

        $this->syncLabels($site, $row, $categories);

        $listing = $this->upsertListing($site, $listingType, $row, $name);
        $this->stats['listings_upserted']++;

        if ($wcId > 0) {
            $this->wcToListingId[$wcId] = $listing->id;
            $this->crossSellQueue[] = [
                'wc_id' => $wcId,
                'related' => $this->parseIdList((string) ($row['Tavsiye Ettiklerimiz'] ?? '')),
                'recommended' => $this->parseIdList((string) ($row['Bu ürüne ek olarak'] ?? '')),
            ];
        }

        $attr = (string) ($row['Nitelik 1 değer(ler)i'] ?? '');
        if (
            $listingType === PromotionalListingType::SiteArticle
            && str_contains($attr, 'Footer Link')
        ) {
            $footer = $this->upsertListing($site, PromotionalListingType::FooterLink, $row, $name.' (Footer)');
            $this->stats['listings_upserted']++;
            if ($wcId > 0) {
                // Keep article as primary map for cross-sell; footer is secondary.
                unset($footer);
            }
        }
    }

    /**
     * @param  array<string, string|null>  $row
     */
    protected function tallyDryRun(array $row, PromotionalListingType $type, string $domain): void
    {
        $this->stats['sites_upserted']++;
        $this->stats['listings_upserted']++;

        if ($this->hasOwner($row)) {
            $this->stats['owners']++;
        }

        if ($this->resolveCategoryName((string) ($row['Kategoriler'] ?? '')) !== null) {
            $this->stats['categories']++;
        }

        if (filled(trim((string) ($row['Meta: rank_math_description'] ?? '')))) {
            $this->stats['seo_description']++;
        }

        if (filled(trim((string) ($row['Meta: rank_math_focus_keyword'] ?? '')))) {
            $this->stats['seo_keywords']++;
        }

        if (filled(trim((string) ($row['Meta: site_analytics'] ?? '')))) {
            $this->stats['analytics_unresolved']++;
        }

        $da = $this->parseNumber($row['Meta: moz_da'] ?? null);
        $pa = $this->parseNumber($row['Meta: moz_pa'] ?? null);
        if (isset($this->domainMetricSeen[$domain])) {
            $prev = $this->domainMetricSeen[$domain];
            if (($prev['da'] ?? null) !== $da || ($prev['pa'] ?? null) !== $pa) {
                $this->stats['conflicts']++;
            }
        }
        $this->domainMetricSeen[$domain] = ['da' => $da, 'pa' => $pa];

        unset($type);
    }

    protected function resolveListingType(string $categories, string $name, string $wcType, string $attr): ?PromotionalListingType
    {
        if (
            str_contains($categories, 'Bülten Liste')
            || str_contains($name, 'Bülten')
        ) {
            return PromotionalListingType::PressRelease;
        }

        if (
            (str_contains($categories, 'Footer Link') && ! str_contains($categories, 'Tanıtım Yazısı'))
            || ($wcType === 'variable' && str_contains($attr, 'Aylık'))
        ) {
            return PromotionalListingType::FooterLink;
        }

        if (
            str_contains($categories, 'Tanıtım Yazısı')
            || str_contains($categories, 'Yabancı')
            || str_contains($categories, 'Yabanci')
            || str_contains($name, 'Tanıtım Yazısı')
        ) {
            return PromotionalListingType::SiteArticle;
        }

        if (filled(trim((string) ($categories))) && filled($name)) {
            return PromotionalListingType::SiteArticle;
        }

        return null;
    }

    protected function normalizeDomain(string $siteUrl, string $name): ?string
    {
        $candidate = trim($siteUrl);

        if ($candidate === '') {
            if (preg_match('/([a-z0-9][a-z0-9.-]+\.[a-z]{2,})/iu', $name, $matches) === 1) {
                $candidate = $matches[1];
            } else {
                return null;
            }
        }

        if (! str_contains($candidate, '://')) {
            $candidate = 'https://'.$candidate;
        }

        $host = parse_url($candidate, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return null;
        }

        $host = Str::lower($host);
        $host = Str::startsWith($host, 'www.') ? substr($host, 4) : $host;

        return $host !== '' ? $host : null;
    }

    /**
     * @param  array<string, string|null>  $row
     */
    protected function upsertSite(array $row, string $domain, ?SiteCategory $category): Site
    {
        $published = trim((string) ($row['Yayımlanmış'] ?? '0'));
        $status = $published === '1' ? SiteStatus::Active : SiteStatus::Draft;

        $linkType = Str::lower(trim((string) ($row['Meta: link_turu'] ?? '')));
        $isDofollow = $linkType === '' || $linkType === 'dofollow';

        $news = trim((string) ($row['Meta: google_news'] ?? ''));
        $indexRaw = trim((string) ($row['Meta: google_index'] ?? ''));

        $openedAt = $this->openedAtFromUnix($row['Meta: site_acilis'] ?? null);
        $age = $openedAt !== null ? (int) $openedAt->diffInYears(now()) : null;
        $ownerContact = $this->ownerContact($row);

        /** @var Site $site */
        $site = Site::query()->firstOrNew(['domain' => $domain]);

        $site->fill([
            'site_category_id' => $category?->id ?? $site->site_category_id ?? SiteCategory::query()->firstOrCreate(
                ['slug' => 'genel'],
                ['name' => 'Genel'],
            )->id,
            'short_description' => $this->cleaner->plainText($row['Kısa açıklama'] ?? null),
            'description' => $this->cleaner->richText($row['Açıklama'] ?? null) ?: $site->description,
            'status' => $status,
            'opened_at' => $openedAt ?? $site->opened_at,
            'is_dofollow' => $isDofollow,
            'is_news_approved' => $news === 'Kayıtlı',
            'is_google_indexed' => $indexRaw !== '' && $indexRaw !== '0',
            'age' => $age ?? $site->age,
            'da_value' => $this->parseNumber($row['Meta: moz_da'] ?? null) ?? $site->da_value,
            'da_source' => MetricSource::Manual,
            'pa_value' => $this->parseNumber($row['Meta: moz_pa'] ?? null) ?? $site->pa_value,
            'pa_source' => MetricSource::Manual,
            'monthly_traffic_value' => $this->parseNumber($row['Meta: gunluk_hit'] ?? null) ?? $site->monthly_traffic_value,
            'monthly_traffic_source' => MetricSource::Manual,
            'max_link_count' => $this->parseInt($row['Meta: link_cikisi'] ?? null) ?? $site->max_link_count,
            'site_owner_name' => trim((string) ($row['Meta: site_adsoyad'] ?? '')) ?: $site->site_owner_name,
            'site_owner_contact' => $ownerContact ?: $site->site_owner_contact,
        ]);

        $analytics = trim((string) ($row['Meta: site_analytics'] ?? ''));
        if ($analytics !== '') {
            $this->stats['analytics_unresolved']++;
            $note = trim((string) $site->internal_notes);
            $tag = 'WC analytics attachment: '.$analytics;
            if (! str_contains($note, $tag)) {
                $site->internal_notes = trim($note."\n".$tag);
            }
        }

        $site->save();

        if ($this->downloadMedia) {
            $logoUrl = $this->firstImageUrl((string) ($row['Görseller'] ?? ''));
            if ($logoUrl !== null && ! filled($site->logo_path)) {
                $path = $this->downloadImage($logoUrl, 'site-logos');
                if ($path !== null) {
                    $site->forceFill(['logo_path' => $path])->saveQuietly();
                    $this->stats['logos']++;
                }
            }
        }

        return $site->fresh() ?? $site;
    }

    /**
     * @param  array<string, string|null>  $row
     */
    protected function upsertListing(
        Site $site,
        PromotionalListingType $type,
        array $row,
        string $name,
    ): PromotionalListing {
        $published = trim((string) ($row['Yayımlanmış'] ?? '0'));
        $status = $published === '1' ? SiteStatus::Active : SiteStatus::Draft;
        $currency = Currency::tryFrom(trim((string) ($row['Meta: _alg_wc_cpp_currency'] ?? ''))) ?? Currency::Try;
        $price = $this->parseNumber($row['Normal fiyat'] ?? null) ?? 0.0;
        $discount = $this->parseNumber($row['İndirimli satış fiyatı'] ?? null);
        $estimated = trim((string) ($row['Meta: teslimat_zamani'] ?? ''));
        $short = $this->cleaner->plainText($row['Kısa açıklama'] ?? null);
        $description = $this->cleaner->richText($row['Açıklama'] ?? null);
        $metaDescription = $this->cleaner->plainText($row['Meta: rank_math_description'] ?? null, 0);
        $metaKeywords = trim((string) ($row['Meta: rank_math_focus_keyword'] ?? ''));
        $desiredSlug = trim((string) ($row['Meta: _wp_desired_post_slug'] ?? ''));

        if ($metaDescription !== '') {
            $this->stats['seo_description']++;
        }
        if ($metaKeywords !== '') {
            $this->stats['seo_keywords']++;
        }

        $productName = $name !== '' ? $name : ($site->domain.' — '.$type->getLabel());

        /** @var PromotionalListing $listing */
        $listing = PromotionalListing::query()->firstOrNew([
            'site_id' => $site->id,
            'type' => $type,
        ]);

        $publicPath = $listing->public_path;
        if (! filled($publicPath)) {
            $publicPath = $this->uniquePublicPath(
                $desiredSlug !== '' ? $desiredSlug : Str::slug($productName),
                $site->domain,
                $type,
                $listing->id,
            );
        }

        $listing->fill([
            'name' => $productName,
            'price' => $price,
            'discount_price' => $discount,
            'currency' => $currency,
            'status' => $status,
            'short_description' => $short !== '' ? $short : $listing->short_description,
            'description' => $description !== '' ? $description : $listing->description,
            'estimated_delivery' => $estimated !== '' ? Str::limit($estimated, 100, '') : $listing->estimated_delivery,
            'delivery_details' => $estimated !== ''
                ? '<p>'.e($estimated).'</p>'
                : $listing->delivery_details,
            'meta_title' => $productName,
            'meta_description' => $metaDescription !== ''
                ? Str::limit($metaDescription, 512, '')
                : ($short !== '' ? $short : $listing->meta_description),
            'meta_keywords' => $metaKeywords !== '' ? $metaKeywords : $listing->meta_keywords,
            'public_path' => $publicPath,
        ]);

        $listing->save();

        return $listing;
    }

    protected function uniquePublicPath(string $base, string $domain, PromotionalListingType $type, ?int $ignoreId): string
    {
        $slug = $this->publicUrls->normalize($base) ?? Str::slug($domain.'-'.$type->value);
        $candidate = $slug;
        $i = 2;

        while (
            PromotionalListing::query()
                ->where('public_path', $candidate)
                ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
            || in_array($candidate, $this->publicUrls->reservedPaths(), true)
        ) {
            $candidate = $slug.'-'.$i;
            $i++;
        }

        return $candidate;
    }

    protected function resolveCategory(string $categories): ?SiteCategory
    {
        $name = $this->resolveCategoryName($categories);
        if ($name === null) {
            return null;
        }

        return SiteCategory::query()->firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name],
        );
    }

    protected function resolveCategoryName(string $categories): ?string
    {
        foreach (preg_split('/\s*,\s*/', $categories) ?: [] as $part) {
            if (str_starts_with($part, 'Kategoriler > ')) {
                $name = trim(substr($part, strlen('Kategoriler > ')));

                return $name !== '' ? $name : null;
            }
        }

        return null;
    }

    /**
     * @param  array<string, string|null>  $row
     */
    protected function syncLabels(Site $site, array $row, string $categories): void
    {
        $names = [];

        if (trim((string) ($row['Öne çıkan?'] ?? '')) === '1') {
            $names[] = 'Öne Çıkan';
        }

        foreach (preg_split('/\s*,\s*/', $categories) ?: [] as $part) {
            if (in_array($part, ['USA', 'UK', 'Öne Çıkanlar'], true)) {
                $names[] = $part === 'Öne Çıkanlar' ? 'Öne Çıkan' : $part;
            }
        }

        $ids = [];
        foreach (array_unique($names) as $name) {
            $ids[] = Label::query()->firstOrCreate(
                ['name' => $name],
                ['color' => '#64748b'],
            )->id;
        }

        if ($ids !== []) {
            $site->labels()->syncWithoutDetaching($ids);
        }
    }

    protected function applyCrossSell(): void
    {
        foreach ($this->crossSellQueue as $item) {
            $listingId = $this->wcToListingId[$item['wc_id']] ?? null;
            if ($listingId === null) {
                continue;
            }

            /** @var PromotionalListing|null $listing */
            $listing = PromotionalListing::query()->find($listingId);
            if ($listing === null) {
                continue;
            }

            $relatedIds = [];
            foreach ($item['related'] as $wcRelated) {
                if (isset($this->wcToListingId[$wcRelated])) {
                    $relatedIds[] = $this->wcToListingId[$wcRelated];
                }
            }

            $recommendedIds = [];
            foreach ($item['recommended'] as $wcRec) {
                if (isset($this->wcToListingId[$wcRec])) {
                    $recommendedIds[] = $this->wcToListingId[$wcRec];
                }
            }

            if ($relatedIds !== []) {
                $listing->relatedListings()->sync(
                    collect($relatedIds)->unique()->values()->mapWithKeys(
                        fn (int $id, int $order): array => [$id => ['sort_order' => $order]],
                    )->all(),
                );
                $this->stats['related_links'] += count($relatedIds);
            }

            if ($recommendedIds !== []) {
                $listing->recommendedListings()->sync(
                    collect($recommendedIds)->unique()->values()->mapWithKeys(
                        fn (int $id, int $order): array => [$id => ['sort_order' => $order]],
                    )->all(),
                );
                $this->stats['recommended_links'] += count($recommendedIds);
            }
        }
    }

    /**
     * @return list<int>
     */
    protected function parseIdList(string $raw): array
    {
        if ($raw === '') {
            return [];
        }

        preg_match_all('/id:(\d+)/', $raw, $matches);

        return array_map('intval', $matches[1] ?? []);
    }

    /**
     * @param  list<string|null>  $headers
     * @param  list<string|null>  $row
     * @return array<string, string|null>
     */
    protected function assoc(array $headers, array $row): array
    {
        $out = [];
        foreach ($headers as $i => $header) {
            $out[(string) $header] = $row[$i] ?? null;
        }

        return $out;
    }

    /**
     * @param  array<string, string|null>  $row
     */
    protected function hasOwner(array $row): bool
    {
        return filled(trim((string) ($row['Meta: site_adsoyad'] ?? '')))
            || filled(trim((string) ($row['Meta: site_telefon'] ?? '')))
            || filled(trim((string) ($row['Meta: site_email'] ?? '')));
    }

    /**
     * @param  array<string, string|null>  $row
     */
    protected function ownerContact(array $row): ?string
    {
        $parts = array_filter([
            trim((string) ($row['Meta: site_telefon'] ?? '')),
            trim((string) ($row['Meta: site_email'] ?? '')),
        ]);

        return $parts === [] ? null : implode(' | ', $parts);
    }

    protected function openedAtFromUnix(mixed $value): ?CarbonImmutable
    {
        if (! is_numeric($value)) {
            return null;
        }

        $ts = (int) $value;
        if ($ts <= 0) {
            return null;
        }

        return CarbonImmutable::createFromTimestampUTC($ts)->startOfDay();
    }

    protected function parseNumber(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $raw = str_replace(['.', ' '], '', $raw);
        $raw = str_replace(',', '.', $raw);

        return is_numeric($raw) ? (float) $raw : null;
    }

    protected function parseInt(mixed $value): ?int
    {
        $number = $this->parseNumber($value);

        return $number === null ? null : (int) round($number);
    }

    protected function firstImageUrl(string $images): ?string
    {
        foreach (preg_split('/\s*,\s*/', $images) ?: [] as $url) {
            $url = trim($url);
            if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
                return $url;
            }
        }

        return null;
    }

    protected function downloadImage(string $url, string $directory): ?string
    {
        try {
            $response = Http::timeout(20)->get($url);
            if (! $response->successful()) {
                return null;
            }

            $extension = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg';
            $extension = Str::lower(Str::limit(preg_replace('/[^a-z0-9]/i', '', $extension) ?? 'jpg', 5, ''));
            if ($extension === '') {
                $extension = 'jpg';
            }

            $path = $directory.'/'.Str::uuid().'.'.$extension;
            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Throwable) {
            return null;
        }
    }
}
