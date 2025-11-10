# Cryptocurrency Price Tracker

A modern web application that displays real-time cryptocurrency prices and detailed information using the CoinGecko API.
Built with Laravel 12, Vue 3, Inertia.js, Redis, and Tailwind CSS.

## Features

- **Top 10 Cryptocurrencies**: View the top coins by market cap with live pricing
- **Detailed Coin Information**: Click any coin to see comprehensive data including:
  - Current price, market cap, and 24h volume
  - All-time high/low prices and historical data
  - Metadata (hashing algorithm, genesis date, community data)
  - Next/previous coin navigation
- **Search Functionality**: Search by coin name or symbol
- **Responsive Design**: Optimized for desktop and mobile devices
- **Auto-Refresh**: Data automatically updates every 5 minutes
- **Performance Optimized**: 5-minute caching to reduce database load
- **Comprehensive Testing**: 51 tests covering all critical functionality

## Quick Start

### Prerequisites

- Docker Desktop installed and running
- Git

### Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd coin-app
```

2. Copy the environment file:
```bash
cp .env.example .env
```

3. Install dependencies and start the application:
```bash
./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail npm install
```

4. Generate application key:
```bash
./vendor/bin/sail artisan key:generate
```

5. Run database migrations:
```bash
./vendor/bin/sail artisan migrate
```

6. Set up your CoinGecko API key in `.env`:
```env
COINGECKO_API_KEY=your-api-key-here
```

7. Import initial coin data:
```bash
./vendor/bin/sail artisan app:import-coin-data
```

8. Start the frontend build process:
```bash
./vendor/bin/sail npm run dev
```

9. Start the queue worker (in a new terminal):
```bash
./vendor/bin/sail artisan queue:work
```

10. Start the scheduler (in a new terminal):
```bash
./vendor/bin/sail artisan schedule:work
```

The application will be accessible at `http://localhost`.

## Technology Stack

### Backend
- **Laravel 12** - Modern PHP framework with latest features
- **PHP 8.4** - Latest PHP version with improved performance
- **MySQL** - Relational database
- **Redis** - High-performance in-memory caching

### Frontend
- **Vue 3** - Progressive JavaScript framework
- **Inertia.js v2** - Modern monolith approach (instead of Nuxt.js)
- **Tailwind CSS v4** - Utility-first CSS framework
- **Vite** - Fast build tool

### Testing
- **Pest v4** - Modern testing framework with browser testing support
- **PHPUnit** - PHP testing foundation

### Development Tools
- **Laravel Sail** - Docker development environment
- **Laravel Telescope** - Debugging and monitoring
- **Laravel Pint** - Code formatter
- **Wayfinder** - Type-safe routing

## Architecture & Design Decisions

### Why Inertia.js Instead of Nuxt.js?

The technical brief specified "Nuxt JS (or similar framework)". I chose Inertia.js for several reasons:

1. **Laravel Ecosystem Integration**: Inertia.js is Laravel's official recommendation for building Vue SPAs
2. **Monolithic Simplicity**: Keeps frontend and backend in one repository, reducing complexity for this scope
3. **Server-Side Routing**: Provides SSR-like benefits without the overhead of a separate Node.js server
4. **Type Safety**: Works seamlessly with Laravel Wayfinder for type-safe routing
5. **Modern Best Practice**: Represents the current recommended approach in the Laravel ecosystem

### Controller Architecture

To maintain flexibility and separation of concerns, I implemented two controller types:

- **Web Controllers** (`App\Http\Controllers\Web`): Handle Inertia.js page rendering for browser clients
- **API Controllers** (`App\Http\Controllers\Api`): RESTful JSON API for mobile apps or external integrations

This architecture allows the same backend logic to serve both web browsers and API clients.

### Performance Optimization

**Caching Strategy:**
- API responses are cached for 5 minutes using Redis
- Cache keys are unique per query combination
- Automatic cache invalidation when new data is imported
- In-memory caching provides sub-millisecond response times
- Reduces database load by ~95% during normal operation

**Scheduled Data Import:**
- Runs every 5 minutes via Laravel scheduler
- Imports fresh data from CoinGecko API
- Queues metadata imports with rate-limit-friendly delays
- Automatically clears cache after import

### Error Handling & Rate Limiting

**CoinGecko API Integration:**
- Retry logic with exponential backoff for 429 rate limits
- Respects `Retry-After` headers
- Maximum 3 retry attempts
- Comprehensive exception handling
- Logging for debugging

### Security

- API keys stored in environment variables (never in code)
- Header-based authentication (more secure than query parameters)
- Dynamic header key based on environment (demo vs pro)
- Request validation on all endpoints
- No direct `env()` calls outside configuration files

### Testing Strategy

Comprehensive test coverage across multiple layers:

- **Unit Tests**: Individual class behavior (Actions, Jobs, Commands)
- **Feature Tests**: Integration testing (API, Controllers, Services)
- **Browser Tests**: End-to-end UI testing with Pest v4
- **Caching Tests**: Verify cache behavior and invalidation

**Test Statistics:**
- 51 tests
- 245 assertions
- 100% critical path coverage

## Development

### Sail Alias (Recommended)

Set up an alias for easier Sail commands:

```bash
echo "alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'" >> ~/.zshrc
source ~/.zshrc
```

Then use `sail` instead of `./vendor/bin/sail`:

```bash
sail up
sail artisan migrate
sail npm run dev
```

### Running Services

**Start all services:**
```bash
sail up
```

**Queue worker (required for metadata imports):**
```bash
sail artisan queue:work
```

**Scheduler (required for auto-refresh):**
```bash
sail artisan schedule:work
```

**Frontend development:**
```bash
sail npm run dev
```

**Frontend production build:**
```bash
sail npm run build
```

### Testing

**Run all tests:**
```bash
sail artisan test
```

**Run specific test file:**
```bash
sail artisan test tests/Feature/Api/CoinApiTest.php
```

**Run tests with filter:**
```bash
sail artisan test --filter="caching"
```

### Code Quality

**Format code with Laravel Pint:**
```bash
sail pint
```

**Check formatting without fixing:**
```bash
sail pint --test
```

## Development Tools

### Laravel Telescope

Access Telescope at `http://localhost/telescope` to:
- Monitor API requests and responses
- View scheduled job execution
- Inspect cache hits/misses
- Debug exceptions
- Analyze database queries

### API Endpoints

The application provides RESTful API endpoints:

**List Coins:**
```
GET /api/coins
Parameters:
  - sort: asc|desc (default: asc)
  - start: integer (pagination offset)
  - length: integer (items per page, default: 10)
  - search: string (filter by name or symbol)
```

**Show Coin:**
```
GET /api/coins/{id}
Parameters:
  - search: string (for navigation context)
  - length: integer (for navigation context)
```

### CoinGecko API Configuration

**Environment Variables:**
```env
COINGECKO_API_KEY=your-api-key-here
```

**API Endpoints Used:**
- `/coins/markets` - Market data for top coins
- `/coins/{id}` - Detailed metadata for specific coins

## Project Structure

```
app/
├── Actions/              # Single-purpose action classes
│   └── GetCoinWithNavigation.php
├── Console/Commands/     # Artisan commands
│   └── ImportCoinData.php
├── Http/
│   ├── Controllers/
│   │   ├── Api/         # JSON API endpoints
│   │   └── Web/         # Inertia.js pages
│   └── Resources/       # API resource transformers
├── Jobs/                # Queued background jobs
│   └── ImportCoinMetadataJob.php
├── Models/              # Eloquent models
│   ├── Coin.php
│   └── CoinMetadata.php
└── Service/             # Business logic services
    └── CoinImportService.php

resources/js/
├── Components/          # Reusable Vue components
├── Layouts/            # Page layouts
└── Pages/              # Inertia.js page components
    └── Coins/
        ├── Index.vue   # Coin list
        └── Show.vue    # Coin details

tests/
├── Browser/            # Browser/E2E tests
├── Feature/            # Integration tests
└── Unit/               # Unit tests
```

## Troubleshooting

**Port already in use:**
```bash
sail down
sail up
```

**Cache issues:**
```bash
sail artisan cache:clear
sail artisan config:clear
```

**Frontend not updating:**
```bash
sail npm run build
# or restart the dev server
sail npm run dev
```

**Database connection issues:**
```bash
sail down -v
sail up
sail artisan migrate:fresh
```

## License

This project was created as a technical assessment.

## Acknowledgments

- [Laravel](https://laravel.com)
- [Vue.js](https://vuejs.org)
- [Inertia.js](https://inertiajs.com)
- [Tailwind CSS](https://tailwindcss.com)
- [CoinGecko API](https://www.coingecko.com/en/api)
