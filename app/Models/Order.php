<?php

namespace App\Models;

use App\Enums\ContentMode;
use App\Enums\ContentSource;
use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Enums\UserRole;
use App\Observers\OrderObserver;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy([OrderObserver::class])]
#[Fillable([
    'user_id',
    'site_id',
    'site_package_id',
    'status',
    'content_source',
    'due_date',
    'price',
    'currency',
    'assigned_editor_id',
    'product_type',
    'site_bundle_id',
    'footer_link_duration_option_id',
    'article_word_package_id',
    'instagram_account_id',
    'instagram_story_price_id',
    'seo_package_id',
    'seo_package_duration_option_id',
    'backlink_package_id',
    'wallet_topup_package_id',
    'content_mode',
    'content_payload',
    'order_group_id',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'payment_pending',
        'currency' => 'USD',
        'product_type' => 'site_article',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'content_source' => ContentSource::class,
            'due_date' => 'date',
            'price' => 'decimal:2',
            'currency' => Currency::class,
            'site_package_id' => 'integer',
            'product_type' => ProductType::class,
            'content_mode' => ContentMode::class,
            'content_payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function assignedEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_editor_id');
    }

    public function siteBundle(): BelongsTo
    {
        return $this->belongsTo(SiteBundle::class);
    }

    public function footerLinkDurationOption(): BelongsTo
    {
        return $this->belongsTo(FooterLinkDurationOption::class);
    }

    public function articleWordPackage(): BelongsTo
    {
        return $this->belongsTo(ArticleWordPackage::class);
    }

    public function instagramAccount(): BelongsTo
    {
        return $this->belongsTo(InstagramAccount::class);
    }

    public function instagramStoryPrice(): BelongsTo
    {
        return $this->belongsTo(InstagramStoryPrice::class);
    }

    public function seoPackage(): BelongsTo
    {
        return $this->belongsTo(SeoPackage::class);
    }

    public function seoPackageDurationOption(): BelongsTo
    {
        return $this->belongsTo(SeoPackageDurationOption::class);
    }

    public function backlinkPackage(): BelongsTo
    {
        return $this->belongsTo(BacklinkPackage::class);
    }

    public function walletTopupPackage(): BelongsTo
    {
        return $this->belongsTo(WalletTopupPackage::class);
    }

    public function orderGroup(): BelongsTo
    {
        return $this->belongsTo(OrderGroup::class);
    }

    public function contentReviews(): HasMany
    {
        return $this->hasMany(OrderContentReview::class);
    }

    public function publishedLink(): HasOne
    {
        return $this->hasOne(PublishedLink::class);
    }

    public function publishedLinks(): HasMany
    {
        return $this->hasMany(PublishedLink::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function canTransitionTo(OrderStatus $status): bool
    {
        return $this->status->canTransitionTo($status);
    }

    public function transitionTo(OrderStatus $status): bool
    {
        if (! $this->canTransitionTo($status)) {
            return false;
        }

        return $this->update(['status' => $status]);
    }

    /**
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    public function scopeAssignedToEditor(Builder $query, User $editor): Builder
    {
        return $query->where('assigned_editor_id', $editor->id);
    }

    public static function editorsQuery(): Builder
    {
        return User::query()->where('role', UserRole::Editor);
    }
}
