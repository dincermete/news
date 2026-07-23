<?php

namespace App\Models;

use App\Enums\SupportTicketSource;
use App\Enums\SupportTicketStatus;
use Database\Factories\SupportTicketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'subject',
    'body',
    'status',
    'source',
    'chatbot_conversation_id',
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
        'source' => 'manual',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SupportTicketStatus::class,
            'source' => SupportTicketSource::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chatbotConversation(): BelongsTo
    {
        return $this->belongsTo(ChatbotConversation::class);
    }

    public function chatbotMessages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ChatbotMessage::class, 'chatbot_conversation_id', 'chatbot_conversation_id');
    }
}
