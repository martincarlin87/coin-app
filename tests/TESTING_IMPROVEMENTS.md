# Testing Improvements

## Current State

The application now has comprehensive test coverage for:

### Feature Tests (`tests/Feature/Api/CoinApiTest.php`)
- ✅ Coin API Index endpoint
  - Pagination
  - Sorting (ascending/descending)
  - Search by name and symbol
  - Custom length parameter
  - Start parameter for pagination
- ✅ Coin API Show endpoint
  - Single coin retrieval
  - 404 handling
  - Metadata inclusion
  - Next/Previous coin navigation
  - Search filter respect in navigation

### Feature Tests (`tests/Feature/Models/CoinTest.php`)
- ✅ Coin Model
  - Metadata relationship
  - ROI array casting
  - Decimal casting for prices
  - Date casting
  - Soft deletes

### Feature Tests (`tests/Feature/Service/CoinImportServiceTest.php`)
- ✅ CoinImportService
  - Importing coin market data from API
  - Job dispatching for metadata import
  - Custom options parameter handling
  - Metadata import functionality
  - Exception handling for missing coins
  - Rate limiting with retry logic (429 responses)
  - Maximum retry attempts with exception

### Unit Tests (`tests/Unit/Jobs/ImportCoinMetadataJobTest.php`)
- ✅ ImportCoinMetadataJob
  - Retry configuration
  - Coin slug storage

## Previously Considered Components

### CoinImportService
**Previous Issue**: The `CoinImportService` class makes direct HTTP calls to the CoinGecko API using Laravel's Http facade.

**Solution Implemented**: ✅ Used Laravel's `Http::fake()` with callback functions to intercept all HTTP requests without modifying production code.

**Current Code Pattern**:
```php
protected function sendRequest(string $endpoint, array $params): Response
{
    return Http::withHeaders([...])
        ->get($endpoint, $params);
}
```

## Suggested Improvements

### 1. Make CoinImportService Testable

**Option A: Use Http::fake() in Tests** (Easiest)
No changes to production code needed. In tests, use Laravel's built-in HTTP faking:

```php
use Illuminate\Support\Facades\Http;

it('imports coin market data successfully', function () {
    Http::fake([
        'https://pro-api.coingecko.com/api/v3/coins/markets*' => Http::response([
            [
                'id' => 'bitcoin',
                'symbol' => 'btc',
                'name' => 'Bitcoin',
                // ... other fields
            ],
        ], 200),
    ]);

    $service = new CoinImportService();
    $service->getCoinMarketData();

    expect(Coin::where('slug', 'bitcoin')->exists())->toBeTrue();
});
```

**Option B: Create an HTTP Client Interface** (More Testable)
Create an interface for HTTP calls that can be mocked:

```php
// app/Contracts/HttpClientInterface.php
interface HttpClientInterface
{
    public function get(string $url, array $params = []): Response;
}

// app/Service/LaravelHttpClient.php
class LaravelHttpClient implements HttpClientInterface
{
    public function get(string $url, array $params = []): Response
    {
        return Http::withHeaders([...])
            ->get($url, $params);
    }
}

// Update CoinImportService constructor
public function __construct(
    private HttpClientInterface $httpClient
) {}
```

Then in tests, inject a mock:
```php
$mockClient = Mockery::mock(HttpClientInterface::class);
$mockClient->shouldReceive('get')->andReturn($mockResponse);

$service = new CoinImportService($mockClient);
```

**Option C: Extract to a Repository Pattern** (Best for Scaling)
Create a CoinGecko API repository:

```php
// app/Repositories/CoinGeckoRepository.php
class CoinGeckoRepository
{
    public function getMarketData(array $options): array { ... }
    public function getCoinById(string $id): array { ... }
}

// Interface for testing
interface CoinGeckoRepositoryInterface
{
    public function getMarketData(array $options): array;
    public function getCoinById(string $id): array;
}
```

### 2. Additional Test Coverage Recommendations

#### Web Controllers (`tests/Feature/CoinControllerTest.php`)
```php
it('renders coin index page', function () {
    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('Coins/Index'));
});

it('renders coin show page', function () {
    $coin = Coin::factory()->create();

    $response = $this->get("/coins/{$coin->id}");

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Coins/Show')
            ->has('coinId'));
});
```

#### Resource Transformation Tests (`tests/Unit/Resources/CoinResourceTest.php`)
```php
it('transforms coin correctly', function () {
    $coin = Coin::factory()->create(['name' => 'Bitcoin']);

    $resource = new CoinResource($coin);
    $array = $resource->toArray(request());

    expect($array)
        ->toHaveKey('id')
        ->toHaveKey('name')
        ->and($array['name'])->toBe('Bitcoin');
});

it('includes metadata when loaded', function () {
    $coin = Coin::factory()->create();
    $metadata = CoinMetadata::factory()->create(['coin_id' => $coin->id]);
    $coin->load('metadata');

    $resource = new CoinResource($coin);
    $array = $resource->toArray(request());

    expect($array['metadata'])->not->toBeNull();
});
```

#### Rate Limiting Tests
```php
it('handles 429 rate limits with retry', function () {
    Http::fake([
        '*' => Http::sequence()
            ->push([], 429, ['Retry-After' => '1'])
            ->push([/* valid data */], 200),
    ]);

    $service = new CoinImportService();
    $result = $service->getCoinMarketData();

    expect($result)->not->toBeNull();
    Http::assertSentCount(2); // First 429, then success
});
```

## Running Tests

```bash
# Run all tests
vendor/bin/sail artisan test

# Run specific test file
vendor/bin/sail artisan test tests/Feature/Api/CoinApiTest.php

# Run tests with coverage (requires Xdebug)
vendor/bin/sail artisan test --coverage

# Run tests in parallel
vendor/bin/sail artisan test --parallel
```

## Test Database

Tests use the `:memory:` SQLite database for speed. If you need to use MySQL for testing:

1. Update `phpunit.xml`:
```xml
<env name="DB_CONNECTION" value="mysql"/>
<env name="DB_DATABASE" value="coin_app_test"/>
```

2. Create test database:
```bash
vendor/bin/sail mysql -e "CREATE DATABASE coin_app_test"
```
