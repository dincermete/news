<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Normalized GET filters for /siteler (SEO-friendly query string).
 *
 * @phpstan-type SortOption 'price_asc'|'price_desc'|'da_asc'|'da_desc'|'newest'
 */
final class SiteCatalogFilters
{
    public const PER_PAGE = 24;

    public const DEFAULT_SORT = 'price_asc';

    /**
     * @var list<string>
     */
    public const SORTS = [
        'price_asc',
        'price_desc',
        'da_asc',
        'da_desc',
        'newest',
    ];

    public function __construct(
        public readonly ?string $q,
        public readonly ?string $kategori,
        public readonly ?float $fiyatMin,
        public readonly ?float $fiyatMax,
        public readonly ?float $daMin,
        public readonly ?float $daMax,
        public readonly bool $dofollowOnly,
        public readonly bool $newsApprovedOnly,
        public readonly string $sort,
        public readonly int $page,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $sort = (string) $request->query('sort', self::DEFAULT_SORT);
        if (! in_array($sort, self::SORTS, true)) {
            $sort = self::DEFAULT_SORT;
        }

        $kategori = $request->query('kategori');
        $kategori = is_string($kategori) && $kategori !== '' ? $kategori : null;

        $q = $request->query('q');
        $q = is_string($q) ? mb_substr(trim($q), 0, 100) : null;
        $q = $q !== '' ? $q : null;

        return new self(
            q: $q,
            kategori: $kategori,
            fiyatMin: self::nullableFloat($request->query('fiyat_min')),
            fiyatMax: self::nullableFloat($request->query('fiyat_max')),
            daMin: self::nullableFloat($request->query('da_min')),
            daMax: self::nullableFloat($request->query('da_max')),
            dofollowOnly: $request->boolean('dofollow'),
            newsApprovedOnly: $request->boolean('news'),
            sort: $sort,
            page: max(1, (int) $request->query('page', 1)),
        );
    }

    /**
     * Stable cache fingerprint for filter + page combination.
     */
    public function fingerprint(): string
    {
        return hash('xxh128', json_encode([
            'q' => $this->q,
            'kategori' => $this->kategori,
            'fiyat_min' => $this->fiyatMin,
            'fiyat_max' => $this->fiyatMax,
            'da_min' => $this->daMin,
            'da_max' => $this->daMax,
            'dofollow' => $this->dofollowOnly,
            'news' => $this->newsApprovedOnly,
            'sort' => $this->sort,
            'page' => $this->page,
            'per_page' => self::PER_PAGE,
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * Query string values for forms / pagination (omit empty defaults).
     *
     * @return array<string, scalar>
     */
    public function toQueryParameters(): array
    {
        $params = [];

        if ($this->q !== null) {
            $params['q'] = $this->q;
        }
        if ($this->kategori !== null) {
            $params['kategori'] = $this->kategori;
        }
        if ($this->fiyatMin !== null) {
            $params['fiyat_min'] = $this->fiyatMin;
        }
        if ($this->fiyatMax !== null) {
            $params['fiyat_max'] = $this->fiyatMax;
        }
        if ($this->daMin !== null) {
            $params['da_min'] = $this->daMin;
        }
        if ($this->daMax !== null) {
            $params['da_max'] = $this->daMax;
        }
        if ($this->dofollowOnly) {
            $params['dofollow'] = 1;
        }
        if ($this->newsApprovedOnly) {
            $params['news'] = 1;
        }
        if ($this->sort !== self::DEFAULT_SORT) {
            $params['sort'] = $this->sort;
        }

        return $params;
    }

    /**
     * @param  Builder<\App\Models\Site>  $query
     * @return Builder<\App\Models\Site>
     */
    public function apply(Builder $query): Builder
    {
        if ($this->q !== null) {
            $escaped = addcslashes($this->q, '%_\\');
            $query->where('domain', 'like', "%{$escaped}%");
        }

        if ($this->kategori !== null) {
            $query->whereHas('category', fn (Builder $category) => $category->where('slug', $this->kategori));
        }

        if ($this->fiyatMin !== null) {
            $query->where('price', '>=', $this->fiyatMin);
        }

        if ($this->fiyatMax !== null) {
            $query->where('price', '<=', $this->fiyatMax);
        }

        if ($this->daMin !== null) {
            $query->where('da_value', '>=', $this->daMin);
        }

        if ($this->daMax !== null) {
            $query->where('da_value', '<=', $this->daMax);
        }

        if ($this->dofollowOnly) {
            $query->where('is_dofollow', true);
        }

        if ($this->newsApprovedOnly) {
            $query->where('is_news_approved', true);
        }

        return match ($this->sort) {
            'price_desc' => $query->orderByDesc('price')->orderBy('id'),
            'da_asc' => $query->orderBy('da_value')->orderBy('id'),
            'da_desc' => $query->orderByDesc('da_value')->orderBy('id'),
            'newest' => $query->orderByDesc('created_at')->orderByDesc('id'),
            default => $query->orderBy('price')->orderBy('id'),
        };
    }

    private static function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }
}
