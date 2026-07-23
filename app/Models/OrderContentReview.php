<?php

namespace App\Models;

use App\Enums\ContentReviewStatus;
use Database\Factories\OrderContentReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'editor_id',
    'content_body',
    'version',
    'status',
])]
class OrderContentReview extends Model
{
    /** @use HasFactory<OrderContentReviewFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'draft',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'status' => ContentReviewStatus::class,
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (OrderContentReview $review): void {
            if (filled($review->version)) {
                return;
            }

            $maxVersion = static::query()
                ->where('order_id', $review->order_id)
                ->max('version');

            $review->version = ((int) $maxVersion) + 1;
        });

        static::updating(function (): bool {
            return false;
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }
}
