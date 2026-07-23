<?php

namespace App\Models;

use App\Enums\ChatbotMessageRole;
use Database\Factories\ChatbotMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'chatbot_conversation_id',
    'session_token',
    'role',
    'content',
])]
class ChatbotMessage extends Model
{
    /** @use HasFactory<ChatbotMessageFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => ChatbotMessageRole::class,
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

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatbotConversation::class, 'chatbot_conversation_id');
    }
}
