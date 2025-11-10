<?php

declare(strict_types=1);

use App\Console\Commands\ImportCoinData;
use App\Models\Coin;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

it('successfully imports coin data from the API', function () {
    Queue::fake();

    // Mock the HTTP calls that CoinImportService would make
    Http::fake([
        'api.coingecko.com/*' => Http::response([
            [
                'id' => 'bitcoin',
                'symbol' => 'btc',
                'name' => 'Bitcoin',
                'image' => 'https://example.com/bitcoin.png',
                'current_price' => 50000,
                'market_cap' => 1000000000000,
                'market_cap_rank' => 1,
                'fully_diluted_valuation' => null,
                'total_volume' => 50000000000,
                'high_24h' => 51000,
                'low_24h' => 49000,
                'price_change_24h' => 1000,
                'price_change_percentage_24h' => 2.0,
                'market_cap_change_24h' => 10000000000,
                'market_cap_change_percentage_24h' => 1.0,
                'circulating_supply' => 19000000,
                'total_supply' => 21000000,
                'max_supply' => 21000000,
                'ath' => 69000,
                'ath_change_percentage' => -27.54,
                'ath_date' => '2021-11-10T14:24:11.849Z',
                'atl' => 67.81,
                'atl_change_percentage' => 73643.48,
                'atl_date' => '2013-07-06T00:00:00.000Z',
                'roi' => null,
                'last_updated' => '2024-01-01T00:00:00.000Z',
            ],
        ], 200),
    ]);

    $this->artisan(ImportCoinData::class)
        ->assertSuccessful();

    // Verify that the coin was actually imported
    expect(Coin::where('slug', 'bitcoin')->exists())->toBeTrue();
});
