<?php

namespace App\Models;

use Database\Factories\LiveSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'session_token',
    'user_id',
    'current_url',
    'ip',
    'user_agent',
    'last_seen_at',
])]
class LiveSession extends Model
{
    /** @use HasFactory<LiveSessionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function siteViews(): HasMany
    {
        return $this->hasMany(SiteView::class);
    }

    /**
     * @param  array{current_url: string, user_id?: ?int, ip?: ?string, user_agent?: ?string}  $attributes
     */
    public static function upsertHeartbeat(string $sessionToken, array $attributes): self
    {
        $session = static::query()->firstOrNew(['session_token' => $sessionToken]);

        $session->fill([
            'current_url' => $attributes['current_url'],
            'user_id' => $attributes['user_id'] ?? $session->user_id,
            'ip' => $attributes['ip'] ?? $session->ip,
            'user_agent' => $attributes['user_agent'] ?? $session->user_agent,
            'last_seen_at' => now(),
        ]);

        $session->save();

        return $session->fresh() ?? $session;
    }
}
