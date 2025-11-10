<?php

use App\Models\Coin;
use App\Models\CoinMetadata;

use function Pest\Laravel\getJson;

describe('Coin API Index', function () {
    it('returns paginated list of coins', function () {
        Coin::factory()->count(15)->sequence(
            fn ($sequence) => ['market_cap_rank' => $sequence->index + 1]
        )->create();

        $response = getJson('/api/coins');

        $response->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'slug',
                        'symbol',
                        'name',
                        'image',
                        'current_price',
                        'market_cap',
                        'market_cap_rank',
                    ],
                ],
            ])
            ->assertJsonCount(10, 'data'); // Default limit is 10
    });

    it('returns coins sorted by market cap rank ascending by default', function () {
        Coin::factory()->create(['market_cap_rank' => 3]);
        Coin::factory()->create(['market_cap_rank' => 1]);
        Coin::factory()->create(['market_cap_rank' => 2]);

        $response = getJson('/api/coins');

        $data = $response->json('data');
        expect($data[0]['market_cap_rank'])->toBe(1)
            ->and($data[1]['market_cap_rank'])->toBe(2)
            ->and($data[2]['market_cap_rank'])->toBe(3);
    });

    it('can sort coins in descending order', function () {
        Coin::factory()->create(['market_cap_rank' => 1]);
        Coin::factory()->create(['market_cap_rank' => 2]);
        Coin::factory()->create(['market_cap_rank' => 3]);

        $response = getJson('/api/coins?sort=desc');

        $data = $response->json('data');
        expect($data[0]['market_cap_rank'])->toBe(3)
            ->and($data[1]['market_cap_rank'])->toBe(2)
            ->and($data[2]['market_cap_rank'])->toBe(1);
    });

    it('can search coins by name', function () {
        Coin::factory()->create(['name' => 'Bitcoin', 'market_cap_rank' => 1]);
        Coin::factory()->create(['name' => 'Ethereum', 'market_cap_rank' => 2]);
        Coin::factory()->create(['name' => 'Dogecoin', 'market_cap_rank' => 3]);

        $response = getJson('/api/coins?search=Bitcoin');

        $response->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => 'Bitcoin']);
    });

    it('can search coins by symbol', function () {
        Coin::factory()->create(['symbol' => 'BTC', 'name' => 'Bitcoin', 'market_cap_rank' => 1]);
        Coin::factory()->create(['symbol' => 'ETH', 'name' => 'Ethereum', 'market_cap_rank' => 2]);

        $response = getJson('/api/coins?search=BTC');

        $response->assertJsonCount(1, 'data')
            ->assertJsonFragment(['symbol' => 'BTC']);
    });

    it('respects custom length parameter', function () {
        Coin::factory()->count(20)->sequence(
            fn ($sequence) => ['market_cap_rank' => $sequence->index + 1]
        )->create();

        $response = getJson('/api/coins?length=5');

        $response->assertJsonCount(5, 'data');
    });

    it('can paginate with start parameter', function () {
        Coin::factory()->create(['market_cap_rank' => 1, 'name' => 'First Coin']);
        Coin::factory()->create(['market_cap_rank' => 2, 'name' => 'Second Coin']);
        Coin::factory()->create(['market_cap_rank' => 3, 'name' => 'Third Coin']);

        $response = getJson('/api/coins?start=1&length=10');

        $response->assertSuccessful();
        // Should skip first and return second and third (within top 10)
        $response->assertJsonFragment(['name' => 'Second Coin'])
            ->assertJsonFragment(['name' => 'Third Coin'])
            ->assertJsonMissing(['name' => 'First Coin']);
    });

    it('only searches within top N coins by market cap rank', function () {
        // Create coins with various ranks, some within top 10, some outside
        Coin::factory()->create(['name' => 'Bitcoin', 'market_cap_rank' => 1]);
        Coin::factory()->create(['name' => 'Bitcoin Cash', 'market_cap_rank' => 14]);
        Coin::factory()->create(['name' => 'Bitcoin SV', 'market_cap_rank' => 19]);
        Coin::factory()->create(['name' => 'Ethereum', 'market_cap_rank' => 2]);

        $response = getJson('/api/coins?search=Bitcoin');

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data') // Only Bitcoin (rank 1) should be returned
            ->assertJsonFragment(['name' => 'Bitcoin', 'market_cap_rank' => 1])
            ->assertJsonMissing(['name' => 'Bitcoin Cash'])
            ->assertJsonMissing(['name' => 'Bitcoin SV']);
    });
});

describe('Coin API Show', function () {
    it('returns a single coin', function () {
        $coin = Coin::factory()->create();

        $response = getJson("/api/coins/{$coin->slug}");

        $response->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'slug',
                    'symbol',
                    'name',
                    'image',
                    'current_price',
                    'market_cap',
                    'market_cap_rank',
                    'fully_diluted_valuation',
                    'total_volume',
                    'high_24h',
                    'low_24h',
                    'price_change_24h',
                    'price_change_percentage_24h',
                    'ath',
                    'atl',
                    'last_updated',
                ],
            ])
            ->assertJsonFragment(['slug' => $coin->slug]);
    });

    it('returns 404 for non-existent coin', function () {
        $response = getJson('/api/coins/non-existent-coin');

        $response->assertNotFound();
    });

    it('includes metadata when available', function () {
        $coin = Coin::factory()->create();
        $metadata = CoinMetadata::factory()->create([
            'coin_id' => $coin->id,
            'hashing_algorithm' => 'SHA-256',
            'genesis_date' => '2009-01-03',
        ]);

        $response = getJson("/api/coins/{$coin->slug}");

        $response->assertSuccessful()
            ->assertJsonPath('data.metadata.hashing_algorithm', 'SHA-256');

        expect($response->json('data.metadata.genesis_date'))->toStartWith('2009-01-03');
    });

    it('includes next coin slug when there is a higher ranked coin', function () {
        $coin1 = Coin::factory()->create(['market_cap_rank' => 1]);
        $coin2 = Coin::factory()->create(['market_cap_rank' => 2]);

        $response = getJson("/api/coins/{$coin1->slug}?length=10");

        $response->assertSuccessful()
            ->assertJsonPath('data.next_coin_slug', $coin2->slug);
    });

    it('includes previous coin slug when there is a lower ranked coin', function () {
        $coin1 = Coin::factory()->create(['market_cap_rank' => 1]);
        $coin2 = Coin::factory()->create(['market_cap_rank' => 2]);

        $response = getJson("/api/coins/{$coin2->slug}?length=10");

        $response->assertSuccessful()
            ->assertJsonPath('data.previous_coin_slug', $coin1->slug);
    });

    it('does not include next coin slug when at the end of filtered results', function () {
        Coin::factory()->create(['market_cap_rank' => 1]);
        $lastCoin = Coin::factory()->create(['market_cap_rank' => 10]);

        $response = getJson("/api/coins/{$lastCoin->slug}?length=10");

        $response->assertSuccessful();
        expect($response->json('data.next_coin_slug'))->toBeNull();
    });

    it('respects search filter for next/previous navigation', function () {
        $bitcoin = Coin::factory()->create(['name' => 'Bitcoin', 'market_cap_rank' => 1]);
        $bitcoinCash = Coin::factory()->create(['name' => 'Bitcoin Cash', 'market_cap_rank' => 2]);
        Coin::factory()->create(['name' => 'Ethereum', 'market_cap_rank' => 3]);

        $response = getJson("/api/coins/{$bitcoin->slug}?search=Bitcoin&length=10");

        $response->assertSuccessful()
            ->assertJsonPath('data.next_coin_slug', $bitcoinCash->slug);
    });
});
