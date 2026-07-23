<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletBalanceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array{currency: string, total: float, buckets: array<string, float>} $payload */
        $payload = $this->resource;

        return [
            'currency' => $payload['currency'],
            'total_available' => $payload['total'],
            'buckets' => $payload['buckets'],
        ];
    }
}
