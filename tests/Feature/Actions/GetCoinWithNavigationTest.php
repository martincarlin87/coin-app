<?php

declare(strict_types=1);

use App\Actions\GetCoinWithNavigation;
use App\Models\Coin;

beforeEach(function () {
    $this->action = new GetCoinWithNavigation;
});

it('adds next coin id when there is a higher ranked coin', function () {
    $coin1 = Coin::factory()->create(['market_cap_rank' => 1]);
    $coin2 = Coin::factory()->create(['market_cap_rank' => 2]);

    $result = $this->action->execute($coin1, null, 10);

    expect($result->next_coin_id)->toBe($coin2->id)
        ->and(isset($result->previous_coin_id))->toBeFalse();
});

it('adds previous coin id when there is a lower ranked coin', function () {
    $coin1 = Coin::factory()->create(['market_cap_rank' => 1]);
    $coin2 = Coin::factory()->create(['market_cap_rank' => 2]);

    $result = $this->action->execute($coin2, null, 10);

    expect($result->previous_coin_id)->toBe($coin1->id)
        ->and($result->next_coin_id)->toBeNull();
});

it('adds both next and previous coin ids when coin is in the middle', function () {
    $coin1 = Coin::factory()->create(['market_cap_rank' => 1]);
    $coin2 = Coin::factory()->create(['market_cap_rank' => 2]);
    $coin3 = Coin::factory()->create(['market_cap_rank' => 3]);

    $result = $this->action->execute($coin2, null, 10);

    expect($result->previous_coin_id)->toBe($coin1->id)
        ->and($result->next_coin_id)->toBe($coin3->id);
});

it('does not add next coin id when at the end of length-limited results', function () {
    // Create 10 coins with ranks 1-10 to fill the length=10 results
    for ($i = 1; $i <= 9; $i++) {
        Coin::factory()->create(['market_cap_rank' => $i]);
    }
    $lastCoin = Coin::factory()->create(['market_cap_rank' => 10]);
    Coin::factory()->create(['market_cap_rank' => 11]); // Beyond length limit

    $result = $this->action->execute($lastCoin, null, 10);

    // Coin at rank 10 is at index 9 (last in the length=10 results)
    expect(isset($result->next_coin_id))->toBeFalse();
});

it('respects search filter when determining navigation', function () {
    $bitcoin = Coin::factory()->create(['name' => 'Bitcoin', 'market_cap_rank' => 1]);
    $bitcoinCash = Coin::factory()->create(['name' => 'Bitcoin Cash', 'market_cap_rank' => 2]);
    Coin::factory()->create(['name' => 'Ethereum', 'market_cap_rank' => 3]);

    $result = $this->action->execute($bitcoin, 'Bitcoin', 10);

    expect($result->next_coin_id)->toBe($bitcoinCash->id);
});

it('respects search filter by symbol', function () {
    $coin1 = Coin::factory()->create(['symbol' => 'BTC', 'market_cap_rank' => 1]);
    $coin2 = Coin::factory()->create(['symbol' => 'BCH', 'market_cap_rank' => 2]);
    Coin::factory()->create(['symbol' => 'ETH', 'market_cap_rank' => 3]);

    $result = $this->action->execute($coin1, 'BTC', 10);

    // Should not find next coin since only BTC matches the search
    expect(isset($result->next_coin_id))->toBeFalse();
});

it('handles empty search string as null', function () {
    $coin1 = Coin::factory()->create(['market_cap_rank' => 1]);
    $coin2 = Coin::factory()->create(['market_cap_rank' => 2]);

    $result = $this->action->execute($coin1, '', 10);

    // Empty string should behave like no filter
    expect($result->next_coin_id)->toBe($coin2->id);
});

it('respects custom length parameter', function () {
    $coin1 = Coin::factory()->create(['market_cap_rank' => 1]);
    $coin2 = Coin::factory()->create(['market_cap_rank' => 2]);
    $coin3 = Coin::factory()->create(['market_cap_rank' => 3]);
    Coin::factory()->create(['market_cap_rank' => 4]);

    // With length of 3, coin3 should not have a next_coin_id
    $result = $this->action->execute($coin3, null, 3);

    expect(isset($result->next_coin_id))->toBeFalse()
        ->and($result->previous_coin_id)->toBe($coin2->id);
});
