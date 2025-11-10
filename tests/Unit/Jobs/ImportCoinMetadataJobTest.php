<?php

use App\Jobs\ImportCoinMetadataJob;

it('has correct retry configuration', function () {
    $job = new ImportCoinMetadataJob('bitcoin');

    expect($job->tries)->toBe(3)
        ->and($job->backoff)->toBe(60);
});

it('stores the coin slug correctly', function () {
    $job = new ImportCoinMetadataJob('ethereum');

    expect($job->coinSlug)->toBe('ethereum');
});
