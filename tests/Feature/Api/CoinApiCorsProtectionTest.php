<?php

use App\Models\Coin;

use function Pest\Laravel\getJson;

describe('Coin API CORS Protection', function () {
    it('is publicly accessible without authentication', function () {
        Coin::factory()->create(['market_cap_rank' => 1]);

        $response = getJson('/api/coins');

        $response->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'slug',
                        'symbol',
                        'name',
                    ],
                ],
            ]);
    });

    it('includes CORS headers for allowed origin', function () {
        Coin::factory()->create(['market_cap_rank' => 1]);

        $response = $this->getJson('/api/coins', [
            'Origin' => config('app.url'),
        ]);

        $response->assertSuccessful()
            ->assertHeader('Access-Control-Allow-Origin', config('app.url'))
            ->assertHeader('Access-Control-Allow-Credentials', 'true');
    });

    it('allows individual coin access without authentication', function () {
        $coin = Coin::factory()->create(['market_cap_rank' => 1]);

        $response = getJson("/api/coins/{$coin->slug}");

        $response->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'slug',
                    'symbol',
                    'name',
                ],
            ]);
    });

    it('supports credentials for stateful requests', function () {
        Coin::factory()->create(['market_cap_rank' => 1]);

        $response = $this->getJson('/api/coins', [
            'Origin' => config('app.url'),
        ]);

        // Verify CORS credentials support is enabled
        $response->assertHeader('Access-Control-Allow-Credentials', 'true');
    });

    it('rejects requests from non-whitelisted domains', function () {
        Coin::factory()->create(['market_cap_rank' => 1]);

        $response = $this->getJson('/api/coins', [
            'Origin' => 'https://malicious-site.com',
        ]);

        // The request succeeds (API is public), but CORS headers should not
        // allow the malicious origin - browser will block the response
        $response->assertSuccessful();

        // The Access-Control-Allow-Origin header should either:
        // 1. Not be present at all, OR
        // 2. Be present but NOT match the malicious origin
        $allowOrigin = $response->headers->get('Access-Control-Allow-Origin');

        expect($allowOrigin)->not->toBe('https://malicious-site.com');
    });
});
