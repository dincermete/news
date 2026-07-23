<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\MetricSource;
use App\Enums\SiteStatus;
use App\Observers\SiteObserver;
use App\Support\SiteSeoMetrics;
use Database\Factories\SiteFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([SiteObserver::class])]
class Site extends Model
{
    /** @use HasFactory<SiteFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'domain',
        'site_category_id',
        'description',
        'age',
        'is_dofollow',
        'is_news_approved',
        'status',
        'price',
        'discount_price',
        'currency',
        'daily_capacity',
        'weekly_capacity',
        'internal_notes',
        'site_owner_name',
        'site_owner_contact',
        'site_owner_payment_info',
        'da_value',
        'da_source',
        'da_updated_at',
        'pa_value',
        'pa_source',
        'pa_updated_at',
        'spam_score_value',
        'spam_score_source',
        'spam_score_updated_at',
        'moz_rank_value',
        'moz_rank_source',
        'moz_rank_updated_at',
        'moz_trust_value',
        'moz_trust_source',
        'moz_trust_updated_at',
        'majestic_cf_value',
        'majestic_cf_source',
        'majestic_cf_updated_at',
        'majestic_tf_value',
        'majestic_tf_source',
        'majestic_tf_updated_at',
        'ahrefs_dr_value',
        'ahrefs_dr_source',
        'ahrefs_dr_updated_at',
        'ahrefs_traffic_value',
        'ahrefs_traffic_source',
        'ahrefs_traffic_updated_at',
        'semrush_authority_score_value',
        'semrush_authority_score_source',
        'semrush_authority_score_updated_at',
        'organic_traffic_value',
        'organic_traffic_source',
        'organic_traffic_updated_at',
        'monthly_traffic_value',
        'monthly_traffic_source',
        'monthly_traffic_updated_at',
        'backlinks_value',
        'backlinks_source',
        'backlinks_updated_at',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_dofollow' => true,
        'is_news_approved' => false,
        'status' => 'draft',
        'currency' => 'USD',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        $casts = [
            'age' => 'integer',
            'is_dofollow' => 'boolean',
            'is_news_approved' => 'boolean',
            'status' => SiteStatus::class,
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'currency' => Currency::class,
            'daily_capacity' => 'integer',
            'weekly_capacity' => 'integer',
        ];

        foreach (SiteSeoMetrics::keys() as $metric) {
            $casts["{$metric}_value"] = 'decimal:2';
            $casts["{$metric}_source"] = MetricSource::class;
            $casts["{$metric}_updated_at"] = 'datetime';
        }

        return $casts;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SiteCategory::class, 'site_category_id');
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'site_label');
    }

    public function bundles(): BelongsToMany
    {
        return $this->belongsToMany(SiteBundle::class, 'site_bundle_site');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SiteQuestion::class);
    }
}
