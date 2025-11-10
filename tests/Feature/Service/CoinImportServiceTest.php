<?php

use App\Jobs\ImportCoinMetadataJob;
use App\Models\Coin;
use App\Models\CoinMetadata;
use App\Service\CoinImportService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

describe('getCoinMarketData', function () {
    it('imports coin market data from API', function () {
        Http::fake(function ($request) {
            return Http::response([
                [
                    'id' => 'bitcoin',
                    'symbol' => 'btc',
                    'name' => 'Bitcoin',
                    'image' => 'https://example.com/bitcoin.png',
                    'current_price' => 50000.12345678,
                    'market_cap' => 1000000000000,
                    'market_cap_rank' => 1,
                    'fully_diluted_valuation' => 1100000000000,
                    'total_volume' => 50000000000,
                    'high_24h' => 51000,
                    'low_24h' => 49000,
                    'price_change_24h' => 1000,
                    'price_change_percentage_24h' => 2.04,
                    'market_cap_change_24h' => 20000000000,
                    'market_cap_change_percentage_24h' => 2.0,
                    'circulating_supply' => 19000000,
                    'total_supply' => 21000000,
                    'max_supply' => 21000000,
                    'ath' => 69000,
                    'ath_change_percentage' => -27.54,
                    'ath_date' => '2021-11-10T14:24:11.849Z',
                    'atl' => 67.81,
                    'atl_change_percentage' => 73645.23,
                    'atl_date' => '2013-07-06T00:00:00.000Z',
                    'roi' => null,
                    'last_updated' => '2025-11-10T20:00:00.000Z',
                ],
            ], 200);
        });

        $service = new CoinImportService;
        $service->getCoinMarketData();

        expect(Coin::where('slug', 'bitcoin')->exists())->toBeTrue();

        $coin = Coin::where('slug', 'bitcoin')->first();
        expect($coin->name)->toBe('Bitcoin')
            ->and($coin->symbol)->toBe('btc')
            ->and($coin->market_cap_rank)->toBe(1)
            ->and($coin->current_price)->toBe('50000.12345678');
    });

    it('dispatches jobs to import coin metadata', function () {
        Queue::fake();

        Http::fake(function ($request) {
            return Http::response([
                [
                    'id' => 'bitcoin',
                    'symbol' => 'btc',
                    'name' => 'Bitcoin',
                    'image' => 'https://example.com/bitcoin.png',
                    'current_price' => 50000,
                    'market_cap' => 1000000000000,
                    'market_cap_rank' => 1,
                    'fully_diluted_valuation' => 1100000000000,
                    'total_volume' => 50000000000,
                    'high_24h' => 51000,
                    'low_24h' => 49000,
                    'price_change_24h' => 1000,
                    'price_change_percentage_24h' => 2.04,
                    'market_cap_change_24h' => 20000000000,
                    'market_cap_change_percentage_24h' => 2.0,
                    'circulating_supply' => 19000000,
                    'total_supply' => 21000000,
                    'max_supply' => 21000000,
                    'ath' => 69000,
                    'ath_change_percentage' => -27.54,
                    'ath_date' => '2021-11-10T14:24:11.849Z',
                    'atl' => 67.81,
                    'atl_change_percentage' => 73645.23,
                    'atl_date' => '2013-07-06T00:00:00.000Z',
                    'roi' => null,
                    'last_updated' => '2025-11-10T20:00:00.000Z',
                ],
                [
                    'id' => 'ethereum',
                    'symbol' => 'eth',
                    'name' => 'Ethereum',
                    'image' => 'https://example.com/ethereum.png',
                    'current_price' => 3000,
                    'market_cap' => 400000000000,
                    'market_cap_rank' => 2,
                    'fully_diluted_valuation' => 400000000000,
                    'total_volume' => 20000000000,
                    'high_24h' => 3100,
                    'low_24h' => 2900,
                    'price_change_24h' => 100,
                    'price_change_percentage_24h' => 3.45,
                    'market_cap_change_24h' => 13000000000,
                    'market_cap_change_percentage_24h' => 3.36,
                    'circulating_supply' => 120000000,
                    'total_supply' => 120000000,
                    'max_supply' => null,
                    'ath' => 4878.26,
                    'ath_change_percentage' => -38.5,
                    'ath_date' => '2021-11-10T14:24:19.604Z',
                    'atl' => 0.432979,
                    'atl_change_percentage' => 692500,
                    'atl_date' => '2015-10-20T00:00:00.000Z',
                    'roi' => null,
                    'last_updated' => '2025-11-10T20:00:00.000Z',
                ],
            ], 200);
        });

        $service = new CoinImportService;
        $service->getCoinMarketData();

        Queue::assertPushed(ImportCoinMetadataJob::class, 2);
        Queue::assertPushed(ImportCoinMetadataJob::class, fn ($job) => $job->coinSlug === 'bitcoin');
        Queue::assertPushed(ImportCoinMetadataJob::class, fn ($job) => $job->coinSlug === 'ethereum');
    });

    it('respects custom options parameter', function () {
        Http::fake(function ($request) {
            return Http::response([
                [
                    'id' => 'bitcoin',
                    'symbol' => 'btc',
                    'name' => 'Bitcoin',
                    'image' => 'https://example.com/bitcoin.png',
                    'current_price' => 50000,
                    'market_cap' => 1000000000000,
                    'market_cap_rank' => 1,
                    'fully_diluted_valuation' => 1100000000000,
                    'total_volume' => 50000000000,
                    'high_24h' => 51000,
                    'low_24h' => 49000,
                    'price_change_24h' => 1000,
                    'price_change_percentage_24h' => 2.04,
                    'market_cap_change_24h' => 20000000000,
                    'market_cap_change_percentage_24h' => 2.0,
                    'circulating_supply' => 19000000,
                    'total_supply' => 21000000,
                    'max_supply' => 21000000,
                    'ath' => 69000,
                    'ath_change_percentage' => -27.54,
                    'ath_date' => '2021-11-10T14:24:11.849Z',
                    'atl' => 67.81,
                    'atl_change_percentage' => 73645.23,
                    'atl_date' => '2013-07-06T00:00:00.000Z',
                    'roi' => null,
                    'last_updated' => '2025-11-10T20:00:00.000Z',
                ],
            ], 200);
        });

        $service = new CoinImportService;
        $service->getCoinMarketData(['per_page' => 1, 'page' => 2]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'coins/markets')
                && $request['per_page'] === 1
                && $request['page'] === 2;
        });
    });
});

describe('getCoinMetadata', function () {
    it('imports coin metadata from API', function () {
        $coin = Coin::factory()->create(['slug' => 'bitcoin']);

        Http::fake(function ($request) {
            return Http::response([
                'id' => 'bitcoin',
                'symbol' => 'btc',
                'name' => 'Bitcoin',
                'web_slug' => 'bitcoin',
                'asset_platform_id' => null,
                'block_time_in_minutes' => 10,
                'hashing_algorithm' => 'SHA-256',
                'categories' => ['Cryptocurrency', 'Store of Value'],
                'preview_listing' => false,
                'public_notice' => null,
                'additional_notices' => [],
                'genesis_date' => '2009-01-03',
                'sentiment_votes_up_percentage' => 75.5,
                'sentiment_votes_down_percentage' => 24.5,
                'watchlist_portfolio_users' => 1500000,
                'platforms' => [],
                'detail_platforms' => [],
                'localization' => ['en' => 'Bitcoin'],
                'description' => [
                    'en' => 'Bitcoin is the first successful internet money based on peer-to-peer technology.',
                ],
                'links' => [
                    'homepage' => ['https://bitcoin.org'],
                    'blockchain_site' => ['https://blockchain.info'],
                ],
                'community_data' => [
                    'twitter_followers' => 5000000,
                ],
                'developer_data' => [
                    'forks' => 35000,
                    'stars' => 70000,
                ],
                'status_updates' => [],
            ], 200);
        });

        $service = new CoinImportService;
        $service->getCoinMetadata('bitcoin');

        expect(CoinMetadata::where('coin_id', $coin->id)->exists())->toBeTrue();

        $metadata = CoinMetadata::where('coin_id', $coin->id)->first();
        expect($metadata->hashing_algorithm)->toBe('SHA-256')
            ->and($metadata->block_time_in_minutes)->toBe(10)
            ->and($metadata->categories)->toBe(['Cryptocurrency', 'Store of Value'])
            ->and($metadata->description)->toHaveKey('en');
    });

    it('throws exception when coin not found in database', function () {
        Http::fake(function ($request) {
            return Http::response([
                'id' => 'nonexistent-coin',
                'symbol' => 'nec',
                'name' => 'Nonexistent Coin',
                'web_slug' => 'nonexistent-coin',
                'asset_platform_id' => null,
                'block_time_in_minutes' => null,
                'hashing_algorithm' => null,
                'categories' => [],
            ], 200);
        });

        $service = new CoinImportService;
        $service->getCoinMetadata('nonexistent-coin');
    })->throws(Exception::class, 'Coin with slug nonexistent-coin not found in database');
});

describe('rate limiting', function () {
    it('retries on 429 response with exponential backoff', function () {
        $callCount = 0;

        Http::fake(function ($request) use (&$callCount) {
            $callCount++;

            if ($callCount === 1) {
                return Http::response([], 429, ['Retry-After' => '0']);
            }

            return Http::response([
                [
                    'id' => 'bitcoin',
                    'symbol' => 'btc',
                    'name' => 'Bitcoin',
                    'image' => 'https://example.com/bitcoin.png',
                    'current_price' => 50000,
                    'market_cap' => 1000000000000,
                    'market_cap_rank' => 1,
                    'fully_diluted_valuation' => 1100000000000,
                    'total_volume' => 50000000000,
                    'high_24h' => 51000,
                    'low_24h' => 49000,
                    'price_change_24h' => 1000,
                    'price_change_percentage_24h' => 2.04,
                    'market_cap_change_24h' => 20000000000,
                    'market_cap_change_percentage_24h' => 2.0,
                    'circulating_supply' => 19000000,
                    'total_supply' => 21000000,
                    'max_supply' => 21000000,
                    'ath' => 69000,
                    'ath_change_percentage' => -27.54,
                    'ath_date' => '2021-11-10T14:24:11.849Z',
                    'atl' => 67.81,
                    'atl_change_percentage' => 73645.23,
                    'atl_date' => '2013-07-06T00:00:00.000Z',
                    'roi' => null,
                    'last_updated' => '2025-11-10T20:00:00.000Z',
                ],
            ], 200);
        });

        $service = new CoinImportService;
        $service->getCoinMarketData();

        expect(Coin::where('slug', 'bitcoin')->exists())->toBeTrue();
        expect($callCount)->toBeGreaterThan(1); // Verify retry happened after 429
    });

    it('throws exception after max retry attempts', function () {
        Http::fake(function ($request) {
            return Http::response([], 429);
        });

        $service = new CoinImportService;
        $service->getCoinMarketData();
    })->throws(Exception::class, 'Rate limit exceeded');
});
