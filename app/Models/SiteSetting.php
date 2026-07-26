<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    public const CACHE_KEY = 'site_settings.current';

    public const DEFAULT_SITE_NAME = 'Tanıtım Yazısı';

    public const DEFAULT_SITE_DOMAIN = 'tanitimyazisi.com.tr';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'site_name',
        'site_domain',
        'legal_name',
        'tagline',
        'meta_description',
        'logo_path',
        'logo_dark_path',
        'favicon_path',
        'og_image_path',
        'support_phone',
        'support_phone_display',
        'support_email',
        'whatsapp_number',
        'address',
        'social_instagram',
        'social_x',
        'social_youtube',
        'social_linkedin',
        'paytr_merchant_id',
        'paytr_merchant_key',
        'paytr_merchant_salt',
        'paytr_test_mode',
        'netgsm_username',
        'netgsm_password',
        'netgsm_header',
        'openai_api_key',
        'openai_model',
        'openai_chatbot_model',
        'openai_article_model',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'paytr_test_mode' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (): void {
            Cache::forget(self::CACHE_KEY);
        });

        static::deleted(function (): void {
            Cache::forget(self::CACHE_KEY);
        });
    }

    public static function current(): self
    {
        /** @var array<string, mixed>|null $cached */
        $cached = Cache::get(self::CACHE_KEY);

        if (is_array($cached) && isset($cached['id'])) {
            return (new static)->newFromBuilder($cached);
        }

        // Drop legacy serialized Eloquent payloads that can unserialize as Incomplete_Class.
        if ($cached !== null) {
            Cache::forget(self::CACHE_KEY);
        }

        $row = static::query()->first();

        if ($row === null) {
            $row = static::query()->create([
                'site_name' => self::DEFAULT_SITE_NAME,
                'site_domain' => self::DEFAULT_SITE_DOMAIN,
                'meta_description' => 'Kaliteli backlink, yazı ve medya paketleri — '.self::DEFAULT_SITE_NAME,
                'support_phone' => '08503052241',
                'support_phone_display' => '0850 305 22 41',
                'support_email' => 'info@tanitimyazisi.com.tr',
                'paytr_test_mode' => true,
            ]);
        }

        Cache::forever(self::CACHE_KEY, $row->getAttributes());

        return $row;
    }

    public function siteName(): string
    {
        return filled($this->site_name) ? (string) $this->site_name : self::DEFAULT_SITE_NAME;
    }

    public function siteDomain(): string
    {
        return filled($this->site_domain) ? (string) $this->site_domain : self::DEFAULT_SITE_DOMAIN;
    }

    public function publicUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public function logoUrl(bool $dark = false): ?string
    {
        $path = $dark
            ? ($this->logo_dark_path ?: $this->logo_path)
            : ($this->logo_path ?: $this->logo_dark_path);

        return $this->publicUrl($path);
    }

    public function faviconUrl(): ?string
    {
        return $this->publicUrl($this->favicon_path);
    }

    public function ogImageUrl(): ?string
    {
        return $this->publicUrl($this->og_image_path);
    }
}
