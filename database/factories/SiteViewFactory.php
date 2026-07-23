<?php

namespace Database\Factories;

use App\Models\LiveSession;
use App\Models\Site;
use App\Models\SiteView;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteView>
 */
class SiteViewFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'live_session_id' => null,
            'viewed_at' => now(),
        ];
    }

    public function withSession(?LiveSession $session = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'live_session_id' => $session?->id ?? LiveSession::factory(),
        ]);
    }
}
