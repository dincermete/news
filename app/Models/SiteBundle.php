<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\SiteStatus;
use App\Models\Concerns\HasStorefrontSeo;
use App\Support\BundleIconOptions;
use Database\Factories\SiteBundleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'description',
    'content',
    'icon',
    'bg_color_from',
    'bg_color_to',
    'price',
    'currency',
    'status',
    'public_path',
    'meta_title',
    'meta_description',
    'meta_keywords',
    'og_image',
])]
class SiteBundle extends Model
{
    /** @use HasFactory<SiteBundleFactory> */
    use HasFactory;

    use HasStorefrontSeo;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency' => 'TRY',
        'status' => 'draft',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'currency' => Currency::class,
            'status' => SiteStatus::class,
        ];
    }

    public function resolvedIcon(): string
    {
        $icon = $this->icon;

        if (filled($icon) && array_key_exists($icon, BundleIconOptions::labels())) {
            return $icon;
        }

        return BundleIconOptions::default();
    }

    public function resolvedBgColorFrom(): string
    {
        return filled($this->bg_color_from) ? $this->bg_color_from : '#ef4444';
    }

    public function resolvedBgColorTo(): string
    {
        return filled($this->bg_color_to) ? $this->bg_color_to : '#f97316';
    }

    public function iconBadgeStyle(): string
    {
        return sprintf(
            'background-image: linear-gradient(to bottom right, %s, %s);',
            $this->resolvedBgColorFrom(),
            $this->resolvedBgColorTo(),
        );
    }

    public function cardBackgroundStyle(): string
    {
        return sprintf(
            'background: linear-gradient(165deg, %s22 0%%, %s14 42%%, #f7f8f9 100%%);',
            $this->resolvedBgColorFrom(),
            $this->resolvedBgColorTo(),
        );
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'site_bundle_site');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
