<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CoinImportService;
use Illuminate\Console\Command;
use JetBrains\PhpStorm\NoReturn;

/**
 * Console command to import coin data.
 *
 * @see CoinImportService::class
 */
final class ImportCoinData extends Command
{
    protected $signature = 'app:import-coin-data';

    protected $description = 'Command description';

    public function __construct(
        private readonly CoinImportService $coinImportService
    ) {
        parent::__construct();
    }

    /**
     * Call the getCoinMarketData method on the injected coinImportService.
     *
     * @throws \Exception
     */
    #[NoReturn]
    public function handle(): void
    {
        $this->coinImportService->getCoinMarketData();
    }
}
