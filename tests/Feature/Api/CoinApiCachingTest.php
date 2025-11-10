<?php

declare(strict_types=1);

use App\Models\Coin;
use App\Models\CoinMetadata;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\getJson;

describe('Coin API Caching', function () {
    beforeEach(function () {
        Cache::flush();
    });

    describe('Index Endpoint', function () {
        it('caches results on first request', function () {
            Coin::factory()->count(5)->create();

            // First request should hit the database
            $response = getJson('/api/coins');
            $response->assertSuccessful();

            // Verify cache was created
            $cacheKey = 'coins:index:'.md5(json_encode([
                'sort' => 'asc',
                'start' => 0,
                'length' => 10,
                'search' => '',
            ]));

            expect(Cache::has($cacheKey))->toBeTrue();
        });

        it('serves subsequent requests from cache', function () {
            Coin::factory()->count(5)->create();

            // First request
            getJson('/api/coins')->assertSuccessful();

            // Count queries on second request
            DB::enableQueryLog();
            $response = getJson('/api/coins');
            $queryCount = count(DB::getQueryLog());
            DB::disableQueryLog();

            $response->assertSuccessful();
            // Should have 0 queries because data is served from cache
            expect($queryCount)->toBe(0);
        });

        it('creates different cache keys for different query parameters', function () {
            Coin::factory()->count(5)->create();

            // Request with default parameters
            getJson('/api/coins')->assertSuccessful();

            // Request with different sort
            getJson('/api/coins?sort=desc')->assertSuccessful();

            // Request with search
            getJson('/api/coins?search=Bitcoin')->assertSuccessful();

            // Verify multiple cache entries exist
            $defaultKey = 'coins:index:'.md5(json_encode([
                'sort' => 'asc',
                'start' => 0,
                'length' => 10,
                'search' => '',
            ]));

            $sortKey = 'coins:index:'.md5(json_encode([
                'sort' => 'desc',
                'start' => 0,
                'length' => 10,
                'search' => '',
            ]));

            $searchKey = 'coins:index:'.md5(json_encode([
                'sort' => 'asc',
                'start' => 0,
                'length' => 10,
                'search' => 'Bitcoin',
            ]));

            expect(Cache::has($defaultKey))->toBeTrue()
                ->and(Cache::has($sortKey))->toBeTrue()
                ->and(Cache::has($searchKey))->toBeTrue();
        });

        it('respects 5 minute cache TTL', function () {
            Coin::factory()->count(5)->create();

            getJson('/api/coins')->assertSuccessful();

            $cacheKey = 'coins:index:'.md5(json_encode([
                'sort' => 'asc',
                'start' => 0,
                'length' => 10,
                'search' => '',
            ]));

            // Cache should exist
            expect(Cache::has($cacheKey))->toBeTrue();

            // Travel forward 6 minutes
            $this->travel(6)->minutes();

            // Cache should have expired
            expect(Cache::has($cacheKey))->toBeFalse();
        });
    });

    describe('Show Endpoint', function () {
        it('caches results on first request', function () {
            $coin = Coin::factory()->create();

            getJson("/api/coins/{$coin->id}")->assertSuccessful();

            $cacheKey = 'coins:show:'.$coin->id.':'.md5(json_encode([
                'search' => '',
                'length' => 10,
            ]));

            expect(Cache::has($cacheKey))->toBeTrue();
        });

        it('serves subsequent requests from cache', function () {
            $coin = Coin::factory()->create();

            // First request
            getJson("/api/coins/{$coin->id}")->assertSuccessful();

            // Count queries on second request
            DB::enableQueryLog();
            $response = getJson("/api/coins/{$coin->id}");
            $queryCount = count(DB::getQueryLog());
            DB::disableQueryLog();

            $response->assertSuccessful();
            // Should have minimal queries because data is served from cache
            // Note: Route model binding still does a query, but the main data fetch is cached
            expect($queryCount)->toBeLessThan(3);
        });

        it('creates different cache keys for different coins', function () {
            $coin1 = Coin::factory()->create();
            $coin2 = Coin::factory()->create();

            getJson("/api/coins/{$coin1->id}")->assertSuccessful();
            getJson("/api/coins/{$coin2->id}")->assertSuccessful();

            $key1 = 'coins:show:'.$coin1->id.':'.md5(json_encode([
                'search' => '',
                'length' => 10,
            ]));

            $key2 = 'coins:show:'.$coin2->id.':'.md5(json_encode([
                'search' => '',
                'length' => 10,
            ]));

            expect(Cache::has($key1))->toBeTrue()
                ->and(Cache::has($key2))->toBeTrue();
        });

        it('creates different cache keys for different query parameters', function () {
            $coin = Coin::factory()->create();

            getJson("/api/coins/{$coin->id}")->assertSuccessful();
            getJson("/api/coins/{$coin->id}?search=Bitcoin")->assertSuccessful();

            $defaultKey = 'coins:show:'.$coin->id.':'.md5(json_encode([
                'search' => '',
                'length' => 10,
            ]));

            $searchKey = 'coins:show:'.$coin->id.':'.md5(json_encode([
                'search' => 'Bitcoin',
                'length' => 10,
            ]));

            expect(Cache::has($defaultKey))->toBeTrue()
                ->and(Cache::has($searchKey))->toBeTrue();
        });

        it('caches metadata when loaded', function () {
            $coin = Coin::factory()->create();
            CoinMetadata::factory()->create([
                'coin_id' => $coin->id,
                'hashing_algorithm' => 'SHA-256',
            ]);

            getJson("/api/coins/{$coin->id}")->assertSuccessful();

            // Second request should still return metadata from cache
            DB::enableQueryLog();
            $response = getJson("/api/coins/{$coin->id}");
            DB::disableQueryLog();

            $response->assertSuccessful()
                ->assertJsonPath('data.metadata.hashing_algorithm', 'SHA-256');
        });
    });
});
