<?php

namespace App\Models\Concerns;

use App\Services\ProductPublicUrl;
use Illuminate\Support\Facades\Storage;

/**
 * @property string|null $public_path
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property string|null $og_image
 */
trait HasStorefrontSeo
{
    /**
     * @return list<string>
     */
    public static function storefrontSeoAttributeNames(): array
    {
        return [
            'public_path',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'og_image',
        ];
    }

    public function hasPublicPath(): bool
    {
        return filled($this->public_path);
    }

    public function canonicalUrl(): string
    {
        return app(ProductPublicUrl::class)->urlFor($this);
    }

    public function technicalUrl(): string
    {
        return app(ProductPublicUrl::class)->technicalUrlFor($this);
    }

    public function ogImageUrl(): ?string
    {
        if (! filled($this->og_image)) {
            return null;
        }

        return Storage::disk('public')->url($this->og_image);
    }
}
