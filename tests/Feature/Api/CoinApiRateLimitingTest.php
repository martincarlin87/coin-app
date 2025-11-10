<?php

use App\Models\Coin;

use function Pest\Laravel\getJson;

describe('Coin API Rate Limiting', function () {
    it('allows up to 60 requests per minute', function () {
        Coin::factory()->create(['market_cap_rank' => 1]);

        // Make 60 requests - should all succeed
        for ($i = 0; $i < 60; $i++) {
            $response = getJson('/api/coins');
            $response->assertSuccessful();
        }
    });

    it('blocks requests after exceeding rate limit', function () {
        Coin::factory()->create(['market_cap_rank' => 1]);

        // Make 61 requests - the 61st should be rate limited
        for ($i = 0; $i < 60; $i++) {
            getJson('/api/coins');
        }

        $response = getJson('/api/coins');
        $response->assertStatus(429); // Too Many Requests
    });

    it('includes rate limit headers in response', function () {
        Coin::factory()->create(['market_cap_rank' => 1]);

        $response = getJson('/api/coins');

        $response->assertSuccessful()
            ->assertHeader('X-RateLimit-Limit', '60')
            ->assertHeader('X-RateLimit-Remaining');
    });
});
