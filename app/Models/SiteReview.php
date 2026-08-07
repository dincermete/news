<?php

namespace App\Models;

use Database\Factories\SiteReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'site_id',
    'site_bundle_id',
    'seo_package_id',
    'backlink_package_id',
    'user_id',
    'name',
    'email',
    'phone',
    'message',
    'is_approved',
    'approved_by',
    'approved_at',
])]
class SiteReview extends Model
{
    /** @use HasFactory<SiteReviewFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_approved' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(SiteBundle::class, 'site_bundle_id');
    }

    public function seoPackage(): BelongsTo
    {
        return $this->belongsTo(SeoPackage::class);
    }

    public function backlinkPackage(): BelongsTo
    {
        return $this->belongsTo(BacklinkPackage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query
            ->where('is_approved', true)
            ->whereNotNull('approved_at');
    }
}
