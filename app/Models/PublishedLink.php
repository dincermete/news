<?php

namespace App\Models;

use Database\Factories\PublishedLinkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable([
    'order_id',
    'published_url',
    'is_live',
    'is_dofollow_verified',
    'last_checked_at',
    'published_at',
    'guarantee_until',
])]
class PublishedLink extends Model
{
    /** @use HasFactory<PublishedLinkFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_live' => true,
        'is_dofollow_verified' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_live' => 'boolean',
            'is_dofollow_verified' => 'boolean',
            'last_checked_at' => 'datetime',
            'published_at' => 'datetime',
            'guarantee_until' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PublishedLink $link): void {
            $link->published_at ??= now();
            $link->guarantee_until ??= Carbon::parse($link->published_at)->addMonths(6);
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isWithinGuarantee(): bool
    {
        return $this->guarantee_until !== null
            && $this->guarantee_until->isFuture();
    }
}
