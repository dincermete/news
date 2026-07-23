<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\SiteStatus;
use Database\Factories\SiteBundleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'description',
    'price',
    'currency',
    'status',
])]
class SiteBundle extends Model
{
    /** @use HasFactory<SiteBundleFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency' => 'TRY',
        'status' => 'draft',
    ];

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

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'site_bundle_site');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
