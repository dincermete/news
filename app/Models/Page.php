<?php

namespace App\Models;

use App\Observers\PageObserver;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([PageObserver::class])]
#[Fillable([
    'slug',
    'route_key',
    'title',
    'meta_title',
    'meta_description',
    'meta_keywords',
    'og_image',
    'content',
    'is_active',
    'is_system',
    'is_legal',
])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
        'is_system' => false,
        'is_legal' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_system' => 'boolean',
            'is_legal' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeLegal(Builder $query): Builder
    {
        return $query->where('is_legal', true);
    }

    public static function findByRouteKey(string $routeKey): ?self
    {
        return static::query()
            ->where('route_key', $routeKey)
            ->where('is_active', true)
            ->first();
    }
}
