<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\ImportCoinMetadataJob;
use App\Models\Coin;
use App\Models\CoinMetadata;
use Carbon\Carbon;
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
     * Default options used when fetching coin data
     *
     * @see CoinImportService::getCoinMarketData
     */
    private const array DEFAULT_OPTIONS = [
        'vs_currency' => 'usd',
        'order' => 'market_cap_desc',
        'per_page' => 100,
        'page' => 1,
        'sparkline' => false,
    ];

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
        // Allow overriding of default options
        $coinMarketData = $this->call('coins/markets', [
            ...self::DEFAULT_OPTIONS,
            ...$options,
        ]);

        // Map API data to database columns for bulk upsert.
        // We save the id from the response as the 'slug' attribute to avoid clashing with the integer primary key
        $coinsToUpsert = collect($coinMarketData)->map(function (array $coinData): array {
            $coinData['slug'] = $coinData['id'];
            $coinData['updated_at'] = now();

            // Manually encode array/JSON fields since upsert() doesn't apply model casts
            $coinData['roi'] = json_encode($coinData['roi']);

            // Convert ISO 8601 datetime strings to MySQL format since upsert() doesn't apply model casts
            foreach (['ath_date', 'atl_date', 'last_updated'] as $dateField) {
                if (!empty($coinData[$dateField])) {
                    $coinData[$dateField] = Carbon::parse($coinData[$dateField])->format('Y-m-d H:i:s');
                }
            }

            unset($coinData['id']);

            return $coinData;
        })->toArray();

        // Dynamically get columns to update (all except the unique key and auto-managed timestamps)
        $updateColumns = array_diff(
            array_keys($coinsToUpsert[0] ?? []),
            ['slug', 'created_at']
        );

        // Bulk upsert in a single query (insert or update based on slug)
        Coin::upsert(
            $coinsToUpsert,
            // Unique key to match on
            ['slug'],
            // Columns to update if record exists
            $updateColumns
        );

        // Queue background jobs to import coin metadata
        foreach ($coinMarketData as $coinData) {
            ImportCoinMetadataJob::dispatch($coinData['id']);
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
        ], $coinData);
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
            $waitSeconds = $retryAfter ? (int)$retryAfter : (2 ** $attempt);

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
