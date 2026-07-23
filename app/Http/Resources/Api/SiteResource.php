<?php

namespace App\Http\Resources\Api;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Site
 */
class SiteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'domain' => $this->domain,
            'description' => $this->description,
            'category' => $this->whenLoaded('category', fn (): ?array => $this->category === null ? null : [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'status' => $this->status?->value,
            'price' => (float) $this->price,
            'discount_price' => $this->discount_price !== null ? (float) $this->discount_price : null,
            'currency' => $this->currency?->value,
            'is_dofollow' => (bool) $this->is_dofollow,
            'is_news_approved' => (bool) $this->is_news_approved,
            'age' => $this->age,
            'da_value' => $this->da_value !== null ? (float) $this->da_value : null,
            'pa_value' => $this->pa_value !== null ? (float) $this->pa_value : null,
            'ahrefs_dr_value' => $this->ahrefs_dr_value !== null ? (float) $this->ahrefs_dr_value : null,
        ];
    }
}
