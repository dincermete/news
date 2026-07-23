<?php

namespace Tests\Feature;

use App\Enums\ApiTokenAbility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_exceeding_token_rate_limit_returns_429(): void
    {
        config(['sanctum.api_rate_limit_per_minute' => 2]);

        $user = User::factory()->customer()->create();
        $token = $user->createToken('rate', [ApiTokenAbility::ReadWallet->value])->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/wallet/balance')->assertOk();
        $this->withToken($token)->getJson('/api/v1/wallet/balance')->assertOk();
        $this->withToken($token)->getJson('/api/v1/wallet/balance')->assertStatus(429);
    }
}
