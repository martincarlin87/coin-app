<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Service\CoinImportService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use JetBrains\PhpStorm\NoReturn;

final class ImportCoinMetadataJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    public function __construct(
        public string $coinSlug
    ) {}

    /**
     * Execute the job.
     *
     * @throws Exception
     */
    #[NoReturn]
    public function handle(CoinImportService $coinImportService): void
    {
        $coinImportService->getCoinMetadata($this->coinSlug);
    }
}
