<?php

namespace App\Models;

use App\Enums\SeoAnalysisServiceType;
use App\Enums\SeoAnalysisStatus;
use Database\Factories\SeoAnalysisRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'site_url',
    'service_type',
    'brief',
    'status',
    'result',
    'completed_at',
])]
class SeoAnalysisRequest extends Model
{
    /** @use HasFactory<SeoAnalysisRequestFactory> */
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
            'service_type' => SeoAnalysisServiceType::class,
            'status' => SeoAnalysisStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
