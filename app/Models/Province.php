<?php

namespace App\Models;

use Database\Factories\ProvinceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Province extends Model
{
    /** @use HasFactory<ProvinceFactory> */
    use HasFactory;

    public const INDEX_MIN_SITES = 3;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'plate_code',
        'name_locative',
    ];

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function url(): string
    {
        return route('provinces.sites', $this->slug);
    }

    public function sitesCount(): int
    {
        if (array_key_exists('active_sites_count', $this->attributes)) {
            return (int) $this->attributes['active_sites_count'];
        }

        if (array_key_exists('sites_count', $this->attributes)) {
            return (int) $this->attributes['sites_count'];
        }

        return (int) ($this->active_sites_count ?? $this->sites_count ?? 0);
    }

    public function isIndexable(?int $sitesCount = null): bool
    {
        return ($sitesCount ?? $this->sitesCount()) >= self::INDEX_MIN_SITES;
    }

    /**
     * Choropleth bucket: 0 | 1-2 | 3-9 | 10-24 | 25+.
     */
    public function bucket(?int $sitesCount = null): int
    {
        $count = $sitesCount ?? $this->sitesCount();

        return match (true) {
            $count <= 0 => 0,
            $count <= 2 => 1,
            $count <= 9 => 2,
            $count <= 24 => 3,
            default => 4,
        };
    }

    public function robotsDirective(?int $sitesCount = null): string
    {
        return $this->isIndexable($sitesCount) ? 'index, follow' : 'noindex, follow';
    }
}
