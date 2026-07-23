<?php

namespace Database\Factories;

use App\Models\ApiRequestLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @extends Factory<ApiRequestLog>
 */
class ApiRequestLogFactory extends Factory
{
    protected $model = ApiRequestLog::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'personal_access_token_id' => null,
            'endpoint' => '/api/v1/'.fake()->randomElement(['sites', 'orders', 'wallet/balance']),
            'method' => fake()->randomElement(['GET', 'POST']),
            'status_code' => fake()->randomElement([200, 201, 403, 422, 429]),
            'ip' => fake()->ipv4(),
        ];
    }

    public function forToken(PersonalAccessToken $token): static
    {
        return $this->state(fn (): array => [
            'personal_access_token_id' => $token->id,
        ]);
    }
}
