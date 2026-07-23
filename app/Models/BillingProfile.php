<?php

namespace App\Models;

use App\Enums\BillingProfileType;
use Database\Factories\BillingProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'type',
    'tax_id',
    'company_name',
    'address',
    'city',
    'district',
    'tax_office',
])]
class BillingProfile extends Model
{
    /** @use HasFactory<BillingProfileFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => BillingProfileType::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function displayName(): string
    {
        if ($this->type === BillingProfileType::Corporate && filled($this->company_name)) {
            return $this->company_name;
        }

        return $this->user?->name ?? 'Fatura profili #'.$this->id;
    }
}
