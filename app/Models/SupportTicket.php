<?php

namespace App\Models;

use App\Enums\SupportTicketPriority;
use App\Enums\SupportTicketSource;
use App\Enums\SupportTicketStatus;
use Database\Factories\SupportTicketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'subject',
    'body',
    'status',
    'priority',
    'assigned_to',
    'source',
    'chatbot_conversation_id',
    'last_replied_at',
    'closed_at',
])]
class SupportTicket extends Model
{
    /** @use HasFactory<SupportTicketFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'open',
        'priority' => 'normal',
        'source' => 'manual',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SupportTicketStatus::class,
            'priority' => SupportTicketPriority::class,
            'source' => SupportTicketSource::class,
            'last_replied_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function chatbotConversation(): BelongsTo
    {
        return $this->belongsTo(ChatbotConversation::class);
    }

    public function chatbotMessages(): HasMany
    {
        return $this->hasMany(ChatbotMessage::class, 'chatbot_conversation_id', 'chatbot_conversation_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(SupportTicketNote::class);
    }

    public function addMessage(?User $author, string $body, bool $isStaff = false): SupportTicketMessage
    {
        /** @var SupportTicketMessage $message */
        $message = $this->messages()->create([
            'user_id' => $author?->id,
            'body' => $body,
            'is_staff' => $isStaff,
        ]);

        $this->forceFill([
            'last_replied_at' => now(),
            'body' => $body,
        ])->save();

        return $message;
    }

    public function markInProgress(): void
    {
        $this->forceFill([
            'status' => SupportTicketStatus::InProgress,
            'closed_at' => null,
        ])->save();
    }

    public function markClosed(): void
    {
        $this->forceFill([
            'status' => SupportTicketStatus::Closed,
            'closed_at' => now(),
        ])->save();
    }

    public function reopen(): void
    {
        $this->forceFill([
            'status' => SupportTicketStatus::Open,
            'closed_at' => null,
        ])->save();
    }
}
