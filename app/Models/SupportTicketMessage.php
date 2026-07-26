<?php

namespace App\Models;

use Database\Factories\SupportTicketMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'support_ticket_id',
    'user_id',
    'body',
    'is_staff',
])]
class SupportTicketMessage extends Model
{
    /** @use HasFactory<SupportTicketMessageFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_staff' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_staff' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (): bool {
            return false;
        });

        static::deleting(function (): bool {
            return false;
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
