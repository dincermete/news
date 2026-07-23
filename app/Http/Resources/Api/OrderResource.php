<?php

namespace App\Http\Resources\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->value,
            'product_type' => $this->product_type?->value,
            'price' => (float) $this->price,
            'currency' => $this->currency?->value,
            'site' => $this->whenLoaded('site', fn (): ?array => $this->site === null ? null : [
                'id' => $this->site->id,
                'domain' => $this->site->domain,
            ]),
            'published_link' => $this->whenLoaded('publishedLink', fn (): ?array => $this->publishedLink === null ? null : [
                'url' => $this->publishedLink->published_url,
                'is_live' => (bool) $this->publishedLink->is_live,
                'published_at' => $this->publishedLink->published_at?->toIso8601String(),
                'guarantee_until' => $this->publishedLink->guarantee_until?->toIso8601String(),
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
