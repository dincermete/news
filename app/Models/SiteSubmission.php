<?php

namespace App\Models;

use App\Enums\SiteSubmissionStatus;
use App\Observers\SiteSubmissionObserver;
use Database\Factories\SiteSubmissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([SiteSubmissionObserver::class])]
#[Fillable([
    'user_id',
    'url',
    'price',
    'site_category_id',
    'age',
    'status',
    'admin_note',
    'reviewed_by',
    'reviewed_at',
])]
class SiteSubmission extends Model
{
    /** @use HasFactory<SiteSubmissionFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'age' => 'integer',
            'status' => SiteSubmissionStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SiteCategory::class, 'site_category_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function domainFromUrl(): string
    {
        $host = parse_url($this->url, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            $host = preg_replace('#^https?://#i', '', $this->url) ?? $this->url;
            $host = explode('/', (string) $host)[0] ?? $this->url;
        }

        return (string) preg_replace('/^www\./i', '', $host);
    }

    /**
     * @return array{domain: string, price: string, site_category_id?: int, age?: int}
     */
    public function siteCreateQuery(): array
    {
        $query = [
            'domain' => $this->domainFromUrl(),
            'price' => (string) $this->price,
        ];

        if ($this->site_category_id !== null) {
            $query['site_category_id'] = (int) $this->site_category_id;
        }

        if ($this->age !== null) {
            $query['age'] = (int) $this->age;
        }

        return $query;
    }
}
