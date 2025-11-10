<?php

use App\Models\Coin;
use App\Models\CoinMetadata;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
uses()->group('model');

it('has a metadata relationship', function () {
    $coin = Coin::factory()->create();
    $metadata = CoinMetadata::factory()->create(['coin_id' => $coin->id]);

    expect($coin->metadata)->toBeInstanceOf(CoinMetadata::class)
        ->and($coin->metadata->id)->toBe($metadata->id);
});

it('casts roi to array', function () {
    $coin = Coin::factory()->create([
        'roi' => ['times' => 2.5, 'currency' => 'usd', 'percentage' => 150],
    ]);

    expect($coin->roi)->toBeArray()
        ->and($coin->roi['times'])->toBe(2.5);
});

it('casts prices to decimal', function () {
    $coin = Coin::factory()->create([
        'current_price' => '12345.67890123',
    ]);

    expect($coin->current_price)->toBeString()
        ->and($coin->current_price)->toBe('12345.67890123');
});

it('casts dates properly', function () {
    $coin = Coin::factory()->create();

    expect($coin->ath_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($coin->atl_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($coin->last_updated)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('uses soft deletes', function () {
    $coin = Coin::factory()->create();
    $coinId = $coin->id;

    $coin->delete();

    expect(Coin::find($coinId))->toBeNull()
        ->and(Coin::withTrashed()->find($coinId))->not->toBeNull();
});
