<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use App\Enums\PaymentStatus;
use App\Enums\SpinCreditTransactionType;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name',
    'email',
    'phone',
    'password',
    'role',
    'status',
    'email_consent',
    'sms_consent',
    'affiliate_code',
    'affiliate_commission_rate',
    'referred_by_id',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => 'admin',
        'status' => 'active',
        'email_consent' => false,
        'sms_consent' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => CustomerStatus::class,
            'email_consent' => 'boolean',
            'sms_consent' => 'boolean',
            'affiliate_commission_rate' => 'decimal:2',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role->isStaff();
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isEditor(): bool
    {
        return $this->role === UserRole::Editor;
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::Customer;
    }

    public function isSuspended(): bool
    {
        return $this->status === CustomerStatus::Suspended;
    }

    public function totalSpent(): float
    {
        return round((float) Payment::query()
            ->where('status', PaymentStatus::Paid)
            ->where(function ($query): void {
                $query->whereHas('order', fn ($order) => $order->where('user_id', $this->id))
                    ->orWhereHas('orderGroup', fn ($group) => $group->where('user_id', $this->id));
            })
            ->sum('amount'), 2);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    public function billingProfiles(): HasMany
    {
        return $this->hasMany(BillingProfile::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orderGroups(): HasMany
    {
        return $this->hasMany(OrderGroup::class);
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(
            Payment::class,
            OrderGroup::class,
            'user_id',
            'order_group_id',
            'id',
            'id',
        );
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(
            Invoice::class,
            OrderGroup::class,
            'user_id',
            'order_group_id',
            'id',
            'id',
        );
    }

    public function spinCreditTransactions(): HasMany
    {
        return $this->hasMany(SpinCreditTransaction::class);
    }

    public function spinWheelSpins(): HasMany
    {
        return $this->hasMany(SpinWheelSpin::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function siteSubmissions(): HasMany
    {
        return $this->hasMany(SiteSubmission::class);
    }

    public function seoAnalysisRequests(): HasMany
    {
        return $this->hasMany(SeoAnalysisRequest::class);
    }

    public function customerNotes(): HasMany
    {
        return $this->hasMany(CustomerNote::class);
    }

    public function notificationsInbox(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by_id');
    }

    public function affiliateCommissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class, 'referrer_id');
    }

    public function ensureAffiliateCode(): string
    {
        if (filled($this->affiliate_code)) {
            return (string) $this->affiliate_code;
        }

        do {
            $code = Str::upper(Str::random(8));
        } while (static::query()->where('affiliate_code', $code)->exists());

        $this->forceFill(['affiliate_code' => $code])->save();

        return $code;
    }

    public function spinCreditBalance(): int
    {
        $credits = (int) $this->spinCreditTransactions()
            ->where('type', SpinCreditTransactionType::Credit)
            ->sum('amount');

        $debits = (int) $this->spinCreditTransactions()
            ->where('type', SpinCreditTransactionType::Debit)
            ->sum('amount');

        return $credits - $debits;
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];
        $letters = collect($parts)
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');

        return $letters !== '' ? $letters : 'NT';
    }
}
