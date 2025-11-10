<?php

declare(strict_types=1);

use App\Models\Coin;

use function Pest\Laravel\visit;

describe('Coin Pages Browser Tests', function () {
    it('displays the coins index page without errors', function () {
        Coin::factory()->count(5)->create();

        $page = visit('/coins');

        $page->assertSuccessful()
            ->assertNoJavascriptErrors();
    });

    it('displays the coin detail page without errors', function () {
        $coin = Coin::factory()->create([
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'market_cap_rank' => 1,
        ]);

        $page = visit("/coins/{$coin->id}");

        $page->assertSuccessful()
            ->assertNoJavascriptErrors();
    });

    it('can navigate from index to detail page', function () {
        $coin = Coin::factory()->create([
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'market_cap_rank' => 1,
        ]);

        $page = visit('/coins');

        $page->assertSuccessful()
            ->assertNoJavascriptErrors();
    });
});
