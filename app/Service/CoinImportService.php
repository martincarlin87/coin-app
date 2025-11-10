<?php

declare(strict_types=1);

namespace App\Service;

use App\Jobs\ImportCoinMetadataJob;
use App\Models\Coin;
use App\Models\CoinMetadata;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\NoReturn;

final readonly class CoinImportService
{
    /**
     * Import coin market data from CoinGecko.
     * The default hardcoded options can be overridden by passing an array of options.
     *
     * @see https://docs.coingecko.com/reference/coins-markets
     *
     * @throws Exception
     */
    #[NoReturn]
    public function getCoinMarketData(array $options = []): void
    {
        $coinMarketData = $this->call('coins/markets', [
            'vs_currency' => 'usd',
            'order' => 'market_cap_desc',
            'per_page' => 100,
            'page' => 1,
            'sparkline' => false,
            ...$options,
        ]);

        foreach ($coinMarketData as $index => $coinData) {
            // Save each coin result in the database using updateOrCreate to preserve integer primary keys
            Coin::updateOrCreate([
                'slug' => $coinData['id'],
            ], [
                'symbol' => $coinData['symbol'],
                'name' => $coinData['name'],
                'image' => $coinData['image'],
                'current_price' => $coinData['current_price'],
                'market_cap' => $coinData['market_cap'],
                'market_cap_rank' => $coinData['market_cap_rank'],
                'fully_diluted_valuation' => $coinData['fully_diluted_valuation'],
                'total_volume' => $coinData['total_volume'],
                'high_24h' => $coinData['high_24h'],
                'low_24h' => $coinData['low_24h'],
                'price_change_24h' => $coinData['price_change_24h'],
                'price_change_percentage_24h' => $coinData['price_change_percentage_24h'],
                'market_cap_change_24h' => $coinData['market_cap_change_24h'],
                'market_cap_change_percentage_24h' => $coinData['market_cap_change_percentage_24h'],
                'circulating_supply' => $coinData['circulating_supply'],
                'total_supply' => $coinData['total_supply'],
                'max_supply' => $coinData['max_supply'],
                'ath' => $coinData['ath'],
                'ath_change_percentage' => $coinData['ath_change_percentage'],
                'ath_date' => $coinData['ath_date'],
                'atl' => $coinData['atl'],
                'atl_change_percentage' => $coinData['atl_change_percentage'],
                'atl_date' => $coinData['atl_date'],
                'roi' => $coinData['roi'],
                'last_updated' => $coinData['last_updated'],
            ]);

            // Queue a background job to import the coin metadata with a delay to avoid rate limits
            // Each job is delayed by (index * 2) seconds to spread out API requests
            ImportCoinMetadataJob::dispatch($coinData['id'])->delay(now()->addSeconds($index * 2));
        }

        // Clear all coin-related caches to ensure fresh data is served
        Cache::flush();
    }

    /**
     * Import coin specific metadata from CoinGecko.
     *
     * @see https://docs.coingecko.com/reference/coins-id
     *
     * @throws Exception
     */
    #[NoReturn]
    public function getCoinMetadata(string $slug, array $options = []): void
    {
        $coinData = $this->call("coins/{$slug}", $options);

        // Find the coin by slug to get the database ID
        $coinModel = Coin::where('slug', $slug)->first();

        if ($coinModel === null) {
            throw new Exception("Coin with slug {$slug} not found in database");
        }

        // Import metadata using updateOrCreate
        CoinMetadata::updateOrCreate([
            'coin_id' => $coinModel->id,
        ], [
            'web_slug' => $coinData['web_slug'] ?? null,
            'asset_platform_id' => $coinData['asset_platform_id'] ?? null,
            'block_time_in_minutes' => $coinData['block_time_in_minutes'] ?? null,
            'hashing_algorithm' => $coinData['hashing_algorithm'] ?? null,
            'categories' => $coinData['categories'] ?? null,
            'preview_listing' => $coinData['preview_listing'] ?? false,
            'public_notice' => $coinData['public_notice'] ?? null,
            'additional_notices' => $coinData['additional_notices'] ?? null,
            'genesis_date' => $coinData['genesis_date'] ?? null,
            'sentiment_votes_up_percentage' => $coinData['sentiment_votes_up_percentage'] ?? null,
            'sentiment_votes_down_percentage' => $coinData['sentiment_votes_down_percentage'] ?? null,
            'watchlist_portfolio_users' => $coinData['watchlist_portfolio_users'] ?? null,
            'platforms' => $coinData['platforms'] ?? null,
            'detail_platforms' => $coinData['detail_platforms'] ?? null,
            'localization' => $coinData['localization'] ?? null,
            'description' => $coinData['description'] ?? null,
            'links' => $coinData['links'] ?? null,
            'community_data' => $coinData['community_data'] ?? null,
            'developer_data' => $coinData['developer_data'] ?? null,
            'status_updates' => $coinData['status_updates'] ?? null,
        ]);
    }

    /**
     * Wrapper method to send a request to the CoinGecko API and return the contents as json.
     * Handles rate limiting with automatic retries.
     *
     * @throws ConnectionException
     * @throws Exception
     */
    protected function call(string $endpoint, array $params, int $attempt = 1): array
    {
        $response = $this->sendRequest($endpoint, $params);

        /**
         * Handle 429 rate limit responses
         *
         * @see https://docs.coingecko.com/docs/common-errors-rate-limit
         */
        if ($response->status() === 429) {
            $maxAttempts = 3;

            if ($attempt >= $maxAttempts) {
                throw new Exception("Rate limit exceeded for endpoint: {$endpoint} after {$maxAttempts} attempts");
            }

            // Get retry-after header (in seconds) or default to exponential backoff
            $retryAfter = $response->header('Retry-After');
            $waitSeconds = $retryAfter ? (int) $retryAfter : (2 ** $attempt);

            Log::warning("CoinGecko API rate limit hit for {$endpoint}. Retrying after {$waitSeconds} seconds. Attempt {$attempt}/{$maxAttempts}");

            sleep($waitSeconds);

            return $this->call($endpoint, $params, $attempt + 1);
        }

        $decodedResponse = $response->json();

        if ($decodedResponse === null) {
            throw new Exception("Invalid response from CoinGecko API for endpoint: {$endpoint}");
        }

        if (isset($decodedResponse['error'])) {
            throw new Exception("Invalid response from CoinGecko API for endpoint: {$endpoint}: {$decodedResponse['error']}");
        }

        return $decodedResponse;
    }

    /**
     * Send a request to the CoinGecko API.
     *
     * @throws ConnectionException
     */
    protected function sendRequest(string $endpoint, array $params): Response
    {
        return Http::withHeaders([
            // The header key is dynamic based on the current app environment.
            config('coingecko.header') => config('coingecko.key'),
        ])
            ->asJson()
            ->acceptJson()
            ->baseUrl(config('coingecko.base_url'))
            ->timeout(5)
            ->connectTimeout(5)
            ->get($endpoint, $params);
    }
}
